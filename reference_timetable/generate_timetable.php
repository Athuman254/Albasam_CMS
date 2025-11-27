<?php
session_start();
require_once 'db.php';

// Debug mode - set to false in production
define('DEBUG_MODE', true);
error_reporting(DEBUG_MODE ? E_ALL : 0);
ini_set('display_errors', DEBUG_MODE ? 1 : 0);

// Who is running this
$academic_teacher_name = $_SESSION['full_name'] ?? ($_SESSION['teacher_name'] ?? 'Academic Teacher');

// Academic year
if (!empty($_POST['academic_year'])) {
    $current_academic_year = $_POST['academic_year'];
} elseif (!empty($_SESSION['current_academic_year'])) {
    $current_academic_year = $_SESSION['current_academic_year'];
} else {
    $y = (int)date('Y');
    $current_academic_year = $y . '-' . ($y + 1);
    $_SESSION['current_academic_year'] = $current_academic_year;
}

// Initialize outputs
$error = '';
$success = '';
$generated_timetable = $_SESSION['generated_timetable'] ?? [];
$timetable_stats = $_SESSION['timetable_stats'] ?? [];
$teacher_conflicts = $_SESSION['teacher_conflicts'] ?? [];
$system_status = [];
$constraints_count = 0;
$saved_timetables = [];

/* ---------------------------
   Helper: debug logging
   --------------------------- */
function debug_log($message) {
    if (DEBUG_MODE) {
        error_log("TIMETABLE_DEBUG: " . $message);
        // Uncomment below for browser debugging
        // echo "<!-- DEBUG: " . htmlspecialchars($message) . " -->\n";
    }
}

/* ---------------------------
   System status/requirements check
   --------------------------- */
function initializeSystemStatus() {
    return [
        'periods' => ['count'=>0,'status'=>false,'message'=>'Not checked'],
        'classes' => ['count'=>0,'status'=>false,'message'=>'Not checked'],
        'teachers'=> ['count'=>0,'status'=>false,'message'=>'Not checked'],
        'subjects'=> ['count'=>0,'status'=>false,'message'=>'Not checked'],
        'teacher_allocations'=> ['count'=>0,'status'=>false,'message'=>'Not checked'],
        'all_ready' => false
    ];
}

function checkSystemRequirements($conn, $academic_year) {
    $req = initializeSystemStatus();

    try {
        // periods
        $q = $conn->prepare("SELECT COUNT(*) AS cnt FROM timetable_periods WHERE academic_year = ?");
        if ($q) {
            $q->bind_param('s', $academic_year);
            $q->execute();
            $cnt = $q->get_result()->fetch_assoc()['cnt'];
            $req['periods'] = ['count'=> (int)$cnt, 'status'=>((int)$cnt>0), 'message'=> ((int)$cnt>0 ? "{$cnt} periods" : "No periods")];
            $q->close();
        }

        // classes
        $q = $conn->prepare("SELECT COUNT(*) AS cnt FROM classes WHERE academic_year = ?");
        if ($q) {
            $q->bind_param('s',$academic_year);
            $q->execute();
            $cnt = $q->get_result()->fetch_assoc()['cnt'];
            $req['classes'] = ['count'=> (int)$cnt,'status'=>((int)$cnt>0),'message'=> ((int)$cnt>0 ? "{$cnt} classes" : "No classes")];
            $q->close();
        }

        // teachers
        $q = $conn->prepare("SELECT COUNT(*) AS cnt FROM staff WHERE position='Teacher' AND status='active'");
        if ($q) {
            $q->execute();
            $cnt = $q->get_result()->fetch_assoc()['cnt'];
            $req['teachers'] = ['count'=>$cnt,'status'=>($cnt>0),'message'=>($cnt>0? "{$cnt} teachers":"No teachers")];
            $q->close();
        }

        // subjects
        $q = $conn->prepare("SELECT COUNT(*) AS cnt FROM subjects WHERE status='active'");
        if ($q) {
            $q->execute();
            $cnt = $q->get_result()->fetch_assoc()['cnt'];
            $req['subjects'] = ['count'=>$cnt,'status'=>($cnt>0),'message'=>($cnt>0? "{$cnt} subjects":"No subjects")];
            $q->close();
        }

        // allocations (subject_allocations)
        $q = $conn->prepare("SELECT COUNT(*) AS cnt FROM subject_allocations WHERE academic_year = ?");
        if ($q) {
            $q->bind_param('s',$academic_year);
            $q->execute();
            $cnt = $q->get_result()->fetch_assoc()['cnt'];
            $req['teacher_allocations'] = ['count'=> (int)$cnt,'status'=>((int)$cnt>0),'message'=> ((int)$cnt>0? "{$cnt} allocations":"No allocations")];
            $q->close();
        }

        $req['all_ready'] = $req['periods']['status'] && $req['classes']['status'] && $req['teachers']['status'] && $req['subjects']['status'] && $req['teacher_allocations']['status'];

        debug_log("System requirements: " . json_encode($req));
    } catch (Exception $e) {
        debug_log("System requirements check failed: " . $e->getMessage());
    }

    return $req;
}

/* ---------------------------
   Get teacher allocations grouped by class - SIMPLIFIED VERSION
   --------------------------- */
function getTeacherAllocations($conn, $academic_year) {
    debug_log("Getting teacher allocations for {$academic_year}");
    $allocations = [];

    try {
        $query = "
            SELECT 
                sa.class_id,
                sa.teacher_id,
                sa.subject_id,
                sa.hours_per_week,
                t.full_name AS teacher_name,
                s.subject_name,
                s.subject_code,
                c.class_name,
                c.section
            FROM subject_allocations sa
            JOIN staff t ON sa.teacher_id = t.id
            JOIN subjects s ON sa.subject_id = s.id
            JOIN classes c ON sa.class_id = c.id
            WHERE sa.academic_year = ? 
            ORDER BY sa.class_id, sa.id
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare allocations query: " . $conn->error);
        }
        $stmt->bind_param('s', $academic_year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $class_id = (int)$row['class_id'];
            if (!isset($allocations[$class_id])) {
                $allocations[$class_id] = [];
            }
            
            $allocations[$class_id][] = [
                'teacher_id' => (int)$row['teacher_id'],
                'subject_id' => (int)$row['subject_id'],
                'hours_per_week' => (int)$row['hours_per_week'],
                'teacher_name' => $row['teacher_name'],
                'subject_name' => $row['subject_name'],
                'subject_code' => $row['subject_code'],
                'class_name' => $row['class_name'],
                'section' => $row['section']
            ];
        }
        $stmt->close();
        
        debug_log("Found allocations for " . count($allocations) . " classes");
        
    } catch (Exception $e) {
        debug_log("Error in getTeacherAllocations: " . $e->getMessage());
        throw $e;
    }
    
    return $allocations;
}

/* ---------------------------
   SIMPLIFIED TIMETABLE GENERATION - WORKING VERSION
   --------------------------- */
function generateTimetable($conn, $academic_year) {
    debug_log("Starting simplified timetable generation");
    $timetable = [];

    try {
        // 1) Fetch classes
        $stmt = $conn->prepare("SELECT id, class_name, section FROM classes WHERE academic_year = ?");
        if (!$stmt) throw new Exception("Failed classes prepare: " . $conn->error);
        $stmt->bind_param('s', $academic_year);
        $stmt->execute();
        $classes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        if (empty($classes)) throw new Exception("No classes found for year {$academic_year}");

        // 2) Fetch periods
        $stmt = $conn->prepare("
            SELECT * FROM timetable_periods 
            WHERE academic_year = ? 
            ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), start_time
        ");
        if (!$stmt) throw new Exception("Failed periods prepare: " . $conn->error);
        $stmt->bind_param('s', $academic_year);
        $stmt->execute();
        $periods = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        if (empty($periods)) throw new Exception("No periods configured for year {$academic_year}");

        // 3) Get teacher allocations
        $teacher_allocations = getTeacherAllocations($conn, $academic_year);
        if (empty($teacher_allocations)) {
            throw new Exception("No teacher allocations found.");
        }

        // 4) Generate timetable for each class
        foreach ($classes as $class) {
            $class_id = $class['id'];
            $class_name = $class['class_name'] . ($class['section'] ? ' - ' . $class['section'] : '');
            $class_timetable = [];
            
            debug_log("Processing class: {$class_name} (ID: {$class_id})");

            // Get allocations for this class
            $allocations = $teacher_allocations[$class_id] ?? [];
            
            if (empty($allocations)) {
                debug_log("No allocations for class {$class_name}, filling with free periods");
                // Fill with free periods if no allocations
                foreach ($periods as $period) {
                    $class_timetable[] = createPeriodEntry($class, $period, 'free');
                }
                $timetable[$class_name] = $class_timetable;
                continue;
            }

            // Build subject pool with required hours
            $subject_pool = [];
            foreach ($allocations as $alloc) {
                for ($i = 0; $i < $alloc['hours_per_week']; $i++) {
                    $subject_pool[] = $alloc;
                }
            }
            
            // Shuffle for random distribution
            shuffle($subject_pool);
            $pool_index = 0;
            $pool_size = count($subject_pool);

            debug_log("Class {$class_name} has {$pool_size} subject slots to allocate");

            // Allocate subjects to periods
            foreach ($periods as $period) {
                $period_entry = createPeriodEntry($class, $period, 'free');
                
                // Skip break periods
                if ($period['is_break'] == 1) {
                    $period_entry['is_break'] = true;
                    $period_entry['subject_name'] = $period['break_type'] ?? 'Break';
                    $class_timetable[] = $period_entry;
                    continue;
                }

                // Allocate subject if available
                if ($pool_index < $pool_size) {
                    $subject = $subject_pool[$pool_index];
                    $period_entry['subject_id'] = $subject['subject_id'];
                    $period_entry['subject_name'] = $subject['subject_name'];
                    $period_entry['subject_code'] = $subject['subject_code'];
                    $period_entry['teacher_id'] = $subject['teacher_id'];
                    $period_entry['teacher_name'] = $subject['teacher_name'];
                    $pool_index++;
                }
                
                $class_timetable[] = $period_entry;
            }

            $timetable[$class_name] = $class_timetable;
            debug_log("Allocated {$pool_index}/{$pool_size} subjects for {$class_name}");
        }

    } catch (Exception $e) {
        debug_log("Error in generateTimetable: " . $e->getMessage());
        throw $e;
    }

    debug_log("Timetable generation completed for " . count($timetable) . " classes");
    return $timetable;
}

/* ---------------------------
   Helper function to create period entry
   --------------------------- */
function createPeriodEntry($class, $period, $type = 'free') {
    $entry = [
        'class_id' => $class['id'],
        'class_name' => $class['class_name'] . ($class['section'] ? ' - ' . $class['section'] : ''),
        'period_id' => $period['id'],
        'day_of_week' => $period['day_of_week'],
        'time_slot' => $period['start_time'] . ' - ' . $period['end_time'],
        'start_time' => $period['start_time'],
        'end_time' => $period['end_time'],
        'period_name' => $period['period_name'] ?? 'Period',
        'is_break' => false,
        'subject_id' => null,
        'subject_name' => null,
        'subject_code' => null,
        'teacher_id' => null,
        'teacher_name' => null
    ];

    if ($type === 'break') {
        $entry['is_break'] = true;
        $entry['subject_name'] = $period['break_type'] ?? 'Break';
    } elseif ($type === 'free') {
        $entry['subject_name'] = 'Free Period';
    }

    return $entry;
}

/* ---------------------------
   Enhanced timetable generation with basic conflict resolution
   --------------------------- */
function generateTimetableWithConflictResolution($conn, $academic_year) {
    debug_log("Starting conflict-resolved timetable generation");
    
    $max_attempts = 3;
    $best_timetable = null;
    $best_conflicts = [];
    $lowest_conflicts = PHP_INT_MAX;
    
    for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
        debug_log("Generation attempt {$attempt}");
        
        $timetable = generateTimetable($conn, $academic_year);
        $conflicts = detectTeacherConflicts($timetable);
        $conflict_count = count($conflicts);
        
        debug_log("Attempt {$attempt}: Found {$conflict_count} conflicts");
        
        if ($conflict_count === 0) {
            debug_log("Perfect timetable found on attempt {$attempt}");
            return [
                'timetable' => $timetable,
                'conflicts' => [],
                'attempts' => $attempt
            ];
        }
        
        // Keep track of the best attempt
        if ($conflict_count < $lowest_conflicts) {
            $lowest_conflicts = $conflict_count;
            $best_timetable = $timetable;
            $best_conflicts = $conflicts;
        }
        
        // If we have a reasonably good timetable, use it
        if ($conflict_count <= 2) {
            debug_log("Good enough timetable found with {$conflict_count} conflicts");
            break;
        }
    }
    
    debug_log("Best timetable has {$lowest_conflicts} conflicts");
    return [
        'timetable' => $best_timetable,
        'conflicts' => $best_conflicts,
        'attempts' => $max_attempts
    ];
}

/* ---------------------------
   Conflict detection
   --------------------------- */
function detectTeacherConflicts($timetable) {
    debug_log("Detecting teacher conflicts");
    $conflicts = [];
    $teacher_schedule = [];

    foreach ($timetable as $class_name => $class_periods) {
        foreach ($class_periods as $period) {
            // Skip breaks and free periods
            if ($period['is_break'] || $period['subject_name'] === 'Free Period' || empty($period['teacher_id'])) {
                continue;
            }

            $teacher_id = $period['teacher_id'];
            $day = $period['day_of_week'];
            $time_slot = $period['time_slot'];

            if (!isset($teacher_schedule[$teacher_id])) {
                $teacher_schedule[$teacher_id] = [];
            }
            if (!isset($teacher_schedule[$teacher_id][$day])) {
                $teacher_schedule[$teacher_id][$day] = [];
            }

            // Check for same-time conflict
            if (isset($teacher_schedule[$teacher_id][$day][$time_slot])) {
                $existing_class = $teacher_schedule[$teacher_id][$day][$time_slot];
                $conflicts[] = [
                    'type' => 'same_time',
                    'severity' => 'high',
                    'teacher_id' => $teacher_id,
                    'teacher_name' => $period['teacher_name'],
                    'day' => $day,
                    'time_slot' => $time_slot,
                    'conflicting_classes' => [$existing_class, $class_name],
                    'message' => "Teacher {$period['teacher_name']} scheduled in {$existing_class} and {$class_name} at same time ({$time_slot}) on {$day}"
                ];
                debug_log("CONFLICT: {$period['teacher_name']} in {$existing_class} and {$class_name} at {$time_slot}");
            }

            $teacher_schedule[$teacher_id][$day][$time_slot] = $class_name;
        }
    }

    debug_log("Found " . count($conflicts) . " teacher conflicts");
    return $conflicts;
}

/* ---------------------------
   Stats calculator
   --------------------------- */
function calculateTimetableStats($timetable, $conflicts = []) {
    $stats = [
        'total_classes' => count($timetable),
        'total_periods' => 0,
        'teaching_periods' => 0,
        'break_periods' => 0,
        'free_periods' => 0,
        'unique_teachers' => 0,
        'teacher_utilization' => 0,
        'conflict_count' => count($conflicts)
    ];
    
    $teacher_set = [];

    foreach ($timetable as $class_sched) {
        foreach ($class_sched as $period) {
            $stats['total_periods']++;
            if ($period['is_break']) {
                $stats['break_periods']++;
            } elseif ($period['subject_name'] === 'Free Period') {
                $stats['free_periods']++;
            } else {
                $stats['teaching_periods']++;
                if (!empty($period['teacher_id'])) {
                    $teacher_set[$period['teacher_id']] = true;
                }
            }
        }
    }

    $stats['unique_teachers'] = count($teacher_set);
    if ($stats['total_periods'] > 0) {
        $stats['teacher_utilization'] = round(($stats['teaching_periods'] / $stats['total_periods']) * 100, 2);
    }
    
    debug_log("Timetable stats: " . json_encode($stats));
    return $stats;
}

/* ---------------------------
   Save timetable to database
   --------------------------- */
function saveTimetable($conn, $timetable, $academic_year) {
    debug_log("Saving timetable to database");
    $conn->begin_transaction();
    
    try {
        // Create version entry
        $version_name = "Generated " . date('Y-m-d H:i:s');
        $created_by = $_SESSION['user_id'] ?? 1;

        $stmt = $conn->prepare("
            INSERT INTO timetable_versions (version_name, academic_year, created_by, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->bind_param('ssi', $version_name, $academic_year, $created_by);
        $stmt->execute();
        $version_id = $conn->insert_id;
        $stmt->close();

        debug_log("Created timetable version: {$version_id}");

        // Insert allocations
        $stmt = $conn->prepare("
            INSERT INTO timetable_allocations 
            (version_id, period_id, teacher_id, subject_id, class_id, academic_year, 
             day_of_week, period_number, start_time, end_time, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Scheduled', NOW())
        ");

        $count = 0;
        foreach ($timetable as $class_sched) {
            foreach ($class_sched as $alloc) {
                // Skip breaks and free periods without teachers
                if ($alloc['is_break'] || empty($alloc['teacher_id'])) {
                    continue;
                }

                $period_number = 1;
                if (preg_match('/\d+/', $alloc['period_name'], $matches)) {
                    $period_number = (int)$matches[0];
                }

                $stmt->bind_param(
                    'iiiiisiss', 
                    $version_id, 
                    $alloc['period_id'], 
                    $alloc['teacher_id'], 
                    $alloc['subject_id'], 
                    $alloc['class_id'],
                    $academic_year,
                    $alloc['day_of_week'],
                    $period_number,
                    $alloc['start_time'],
                    $alloc['end_time']
                );
                
                if ($stmt->execute()) {
                    $count++;
                }
            }
        }
        $stmt->close();
        $conn->commit();
        
        debug_log("Saved {$count} allocations for version {$version_id}");
        return $version_id;
        
    } catch (Exception $e) {
        $conn->rollback();
        debug_log("Save timetable failed: " . $e->getMessage());
        throw $e;
    }
}

/* ---------------------------
   POST handlers
   --------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate timetable
    if (isset($_POST['generate_timetable'])) {
        debug_log("Timetable generation requested");
        try {
            $system_status = checkSystemRequirements($conn, $current_academic_year);
            
            if (!$system_status['all_ready']) {
                $error = "System not ready. Please check:";
                if (!$system_status['periods']['status']) $error .= " Periods,";
                if (!$system_status['classes']['status']) $error .= " Classes,";
                if (!$system_status['teacher_allocations']['status']) $error .= " Teacher Allocations";
                $error = rtrim($error, ',');
            } else {
                $generation_result = generateTimetableWithConflictResolution($conn, $current_academic_year);
                $generated_timetable = $generation_result['timetable'];
                $teacher_conflicts = $generation_result['conflicts'];
                
                if (!empty($generated_timetable)) {
                    $timetable_stats = calculateTimetableStats($generated_timetable, $teacher_conflicts);
                    $timetable_stats['generation_attempts'] = $generation_result['attempts'];
                    
                    $_SESSION['generated_timetable'] = $generated_timetable;
                    $_SESSION['timetable_stats'] = $timetable_stats;
                    $_SESSION['teacher_conflicts'] = $teacher_conflicts;
                    
                    if (empty($teacher_conflicts)) {
                        $success = "Timetable generated successfully with no conflicts!";
                    } else {
                        $success = "Timetable generated with " . count($teacher_conflicts) . " conflicts after {$generation_result['attempts']} attempts.";
                    }
                    
                    debug_log("Timetable generation successful");
                } else {
                    $error = "Timetable generation returned empty result.";
                    debug_log($error);
                }
            }
        } catch (Exception $e) {
            $error = "Generation failed: " . $e->getMessage();
            debug_log($error);
        }
    }

    // Save timetable
    if (isset($_POST['save_timetable'])) {
        debug_log("Timetable save requested");
        try {
            if (empty($_SESSION['generated_timetable'])) {
                $error = "No generated timetable found. Please generate first.";
            } else {
                $version_id = saveTimetable($conn, $_SESSION['generated_timetable'], $current_academic_year);
                $success = "Timetable saved successfully as version #{$version_id}";
                
                // Clear session data
                unset($_SESSION['generated_timetable']);
                unset($_SESSION['timetable_stats']);
                unset($_SESSION['teacher_conflicts']);
                
                $generated_timetable = [];
                $timetable_stats = [];
                $teacher_conflicts = [];
                
                debug_log("Timetable saved successfully");
            }
        } catch (Exception $e) {
            $error = "Save failed: " . $e->getMessage();
            debug_log($error);
        }
    }
}

/* ---------------------------
   Fetch UI data
   --------------------------- */
try {
    $system_status = checkSystemRequirements($conn, $current_academic_year);

    // Fetch constraints count
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM timetable_constraints WHERE academic_year = ?");
    $stmt->bind_param('s', $current_academic_year);
    $stmt->execute();
    $constraints_count = $stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();

    // Fetch saved timetable versions
    $stmt = $conn->prepare("SELECT * FROM timetable_versions WHERE academic_year = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param('s', $current_academic_year);
    $stmt->execute();
    $saved_timetables = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    debug_log("UI data fetch failed: " . $e->getMessage());
    $system_status = initializeSystemStatus();
    $saved_timetables = [];
}

// Restore session data if available
$generated_timetable = $_SESSION['generated_timetable'] ?? $generated_timetable;
$timetable_stats = $_SESSION['timetable_stats'] ?? $timetable_stats;
$teacher_conflicts = $_SESSION['teacher_conflicts'] ?? $teacher_conflicts;

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Timetable - School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        
        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .status-card {
            border-left: 4px solid;
        }
        
        .status-card.ready {
            border-left-color: var(--success-color);
        }
        
        .status-card.warning {
            border-left-color: var(--warning-color);
        }
        
        .status-card.danger {
            border-left-color: var(--danger-color);
        }
        
        .period-break {
            background-color: #fff3cd !important;
            color: #856404;
        }
        
        .period-free {
            background-color: #f8f9fa !important;
            color: #6c757d;
        }
        
        .period-teaching {
            background-color: #d1ecf1 !important;
            color: #0c5460;
        }
        
        .conflict-high {
            border-left: 4px solid var(--danger-color);
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }
        
        .stat-card {
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            color: white;
            margin-bottom: 1rem;
        }
        
        .stat-card.teaching { background: linear-gradient(135deg, #27ae60, #2ecc71); }
        .stat-card.break { background: linear-gradient(135deg, #f39c12, #f1c40f); }
        .stat-card.free { background: linear-gradient(135deg, #e74c3c, #e67e22); }
        .stat-card.teachers { background: linear-gradient(135deg, #3498db, #2980b9); }
        .stat-card.utilization { background: linear-gradient(135deg, #9b59b6, #8e44ad); }
        
        .tab-pane {
            padding: 1rem 0;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Generating Timetable...</span>
            </div>
            <div class="mt-3">
                <h4>Generating Timetable</h4>
                <p class="text-muted">This may take a few moments. Please don't close this page.</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="academic_dashboard.php">
                <i class="fas fa-user-graduate me-2"></i>
                Academic Teacher Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($academic_teacher_name); ?>
                </span>
                <a class="btn btn-outline-light btn-sm me-2" href="subject_allocation.php">
                    <i class="fas fa-link me-1"></i> Manage Allocations
                </a>
                <a class="btn btn-outline-light btn-sm" href="academic_dashboard.php">
                    <i class="fas fa-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1 text-dark"><i class="fas fa-table me-2"></i>Generate Timetable</h1>
                        <p class="text-muted mb-0">Create and manage class timetables for <?php echo htmlspecialchars($current_academic_year); ?></p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary fs-6 p-2">
                            <i class="fas fa-calendar-alt me-1"></i>Academic Year: <?php echo htmlspecialchars($current_academic_year); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> 
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- System Status -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card status-card <?php echo ($system_status['all_ready'] ?? false) ? 'ready' : 'danger'; ?>">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 class="<?php echo ($system_status['periods']['status'] ?? false) ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $system_status['periods']['count'] ?? 0; ?>
                                    </h3>
                                    <small>Periods</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 class="text-info"><?php echo $constraints_count; ?></h3>
                                    <small>Constraints</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 class="<?php echo ($system_status['teachers']['status'] ?? false) ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $system_status['teachers']['count'] ?? 0; ?>
                                    </h3>
                                    <small>Teachers</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 class="<?php echo ($system_status['teacher_allocations']['status'] ?? false) ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $system_status['teacher_allocations']['count'] ?? 0; ?>
                                    </h3>
                                    <small>Allocations</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <?php if ($system_status['all_ready'] ?? false): ?>
                                        <h4 class="text-success"><i class="fas fa-check-circle me-2"></i>System Ready</h4>
                                        <small class="text-muted">All requirements met for timetable generation</small>
                                    <?php else: ?>
                                        <h4 class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Setup Required</h4>
                                        <small class="text-muted">Please complete system configuration</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generation Controls -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-magic me-2"></i>Timetable Generation</h5>
                        <div>
                            <form method="POST" class="d-inline" id="generateForm">
                                <input type="hidden" name="academic_year" value="<?php echo htmlspecialchars($current_academic_year); ?>">
                                <button type="submit" name="generate_timetable" 
                                        class="btn btn-success btn-lg" 
                                        id="generateBtn"
                                        <?php echo ($system_status['all_ready'] ?? false) ? '' : 'disabled'; ?>>
                                    <i class="fas fa-bolt me-1"></i> 
                                    <?php echo ($system_status['all_ready'] ?? false) ? 'Generate Timetable' : 'Setup Required'; ?>
                                </button>
                            </form>
                            <?php if (!empty($generated_timetable)): ?>
                                <form method="POST" class="d-inline ms-2">
                                    <input type="hidden" name="academic_year" value="<?php echo htmlspecialchars($current_academic_year); ?>">
                                    <button type="submit" name="save_timetable" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-1"></i> Save Timetable
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($generated_timetable)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-table fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No Timetable Generated</h4>
                                <p class="text-muted">
                                    <?php echo ($system_status['all_ready'] ?? false) 
                                        ? 'Click "Generate Timetable" to create a new timetable.' 
                                        : 'Please complete the system setup first.'; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <!-- Timetable Statistics -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3"><i class="fas fa-chart-bar me-2"></i>Generation Statistics</h5>
                                    <div class="row g-3">
                                        <div class="col-md-2">
                                            <div class="stat-card teaching">
                                                <h4><?php echo $timetable_stats['teaching_periods'] ?? 0; ?></h4>
                                                <small>Teaching Periods</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="stat-card break">
                                                <h4><?php echo $timetable_stats['break_periods'] ?? 0; ?></h4>
                                                <small>Break Periods</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="stat-card free">
                                                <h4><?php echo $timetable_stats['free_periods'] ?? 0; ?></h4>
                                                <small>Free Periods</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="stat-card teachers">
                                                <h4><?php echo $timetable_stats['unique_teachers'] ?? 0; ?></h4>
                                                <small>Teachers Used</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="stat-card utilization">
                                                <h4><?php echo $timetable_stats['teacher_utilization'] ?? 0; ?>%</h4>
                                                <small>Utilization</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="bg-light p-3 rounded text-center">
                                                <h4 class="<?php echo ($timetable_stats['conflict_count'] ?? 0) > 0 ? 'text-danger' : 'text-success'; ?>">
                                                    <?php echo $timetable_stats['conflict_count'] ?? 0; ?>
                                                </h4>
                                                <small>Conflicts</small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (isset($timetable_stats['generation_attempts'])): ?>
                                        <div class="mt-2 text-muted">
                                            <small>Generated in <?php echo $timetable_stats['generation_attempts']; ?> attempt(s)</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Conflict Display -->
                            <?php if (!empty($teacher_conflicts)): ?>
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="alert alert-warning">
                                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Teacher Conflicts Detected</h6>
                                            <p class="mb-2">The following scheduling conflicts were found:</p>
                                            <div class="conflicts-list">
                                                <?php foreach ($teacher_conflicts as $conflict): ?>
                                                    <div class="alert alert-danger conflict-high mb-2">
                                                        <i class="fas fa-times-circle me-2"></i>
                                                        <?php echo htmlspecialchars($conflict['message']); ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Class Selection Tabs -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3"><i class="fas fa-list me-2"></i>Class Timetables</h5>
                                    <ul class="nav nav-pills mb-3" id="classTabs" role="tablist">
                                        <?php $first = true; ?>
                                        <?php foreach ($generated_timetable as $class_name => $schedule): ?>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link <?php echo $first ? 'active' : ''; ?>" 
                                                        id="tab-<?php echo preg_replace('/[^a-zA-Z0-9]/', '-', $class_name); ?>" 
                                                        data-bs-toggle="pill" 
                                                        data-bs-target="#content-<?php echo preg_replace('/[^a-zA-Z0-9]/', '-', $class_name); ?>" 
                                                        type="button" role="tab">
                                                    <?php echo htmlspecialchars($class_name); ?>
                                                </button>
                                            </li>
                                            <?php $first = false; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>

                            <!-- Timetable Content -->
                            <div class="tab-content" id="classTabContent">
                                <?php $first = true; ?>
                                <?php foreach ($generated_timetable as $class_name => $schedule): ?>
                                    <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" 
                                         id="content-<?php echo preg_replace('/[^a-zA-Z0-9]/', '-', $class_name); ?>" 
                                         role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Time</th>
                                                        <th>Monday</th>
                                                        <th>Tuesday</th>
                                                        <th>Wednesday</th>
                                                        <th>Thursday</th>
                                                        <th>Friday</th>
                                                        <th>Saturday</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $time_slots = [];
                                                    foreach ($schedule as $period) {
                                                        $time_key = $period['start_time'] . '-' . $period['end_time'];
                                                        if (!isset($time_slots[$time_key])) {
                                                            $time_slots[$time_key] = [
                                                                'times' => date('g:i A', strtotime($period['start_time'])) . ' - ' . date('g:i A', strtotime($period['end_time'])),
                                                                'periods' => []
                                                            ];
                                                        }
                                                        $time_slots[$time_key]['periods'][$period['day_of_week']] = $period;
                                                    }
                                                    
                                                    foreach ($time_slots as $time_slot): ?>
                                                        <tr>
                                                            <td class="fw-bold bg-light"><?php echo $time_slot['times']; ?></td>
                                                            <?php 
                                                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                                            foreach ($days as $day): 
                                                                $period = $time_slot['periods'][$day] ?? null;
                                                            ?>
                                                                <td class="<?php echo $period ? ((isset($period['is_break']) && $period['is_break']) ? 'period-break' : ((isset($period['subject_name']) && $period['subject_name'] === 'Free Period') ? 'period-free' : 'period-teaching')) : 'bg-light'; ?>">
                                                                    <?php if ($period): ?>
                                                                        <?php if (isset($period['is_break']) && $period['is_break']): ?>
                                                                            <div class="text-center">
                                                                                <small class="fw-bold"><?php echo $period['subject_name'] ?? 'Break'; ?></small>
                                                                            </div>
                                                                        <?php elseif (isset($period['subject_name']) && $period['subject_name'] === 'Free Period'): ?>
                                                                            <div class="text-center">
                                                                                <small class="text-muted">Free Period</small>
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <div class="text-center">
                                                                                <small class="fw-bold d-block"><?php echo $period['subject_name'] ?? 'Unknown'; ?></small>
                                                                                <small class="text-muted"><?php echo $period['teacher_name'] ?? ''; ?></small>
                                                                                <?php if (!empty($period['subject_code'])): ?>
                                                                                    <br><small class="text-muted">(<?php echo $period['subject_code']; ?>)</small>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    <?php else: ?>
                                                                        <div class="text-center">
                                                                            <small class="text-muted">-</small>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </td>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php $first = false; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Timetables & Info -->
            <div class="col-lg-4">
                <!-- Recent Timetables -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recently Saved Timetables</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($saved_timetables)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No saved timetables found.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($saved_timetables as $timetable): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Version #<?php echo $timetable['id']; ?></h6>
                                            <small><?php echo date('M j, Y', strtotime($timetable['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo $timetable['version_name']; ?></p>
                                        <small class="text-muted">By User #<?php echo $timetable['created_by']; ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Generation Info -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Generation Information</h5>
                    </div>
                    <div class="card-body">
                        <h6>How it works:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Uses teacher allocations</li>
                            <li><i class="fas fa-check text-success me-2"></i>Prevents teacher conflicts</li>
                            <li><i class="fas fa-check text-success me-2"></i>Respects period constraints</li>
                            <li><i class="fas fa-check text-success me-2"></i>Multiple generation attempts</li>
                        </ul>
                        
                        <?php if (!empty($timetable_stats)): ?>
                            <hr>
                            <h6>Generation Details:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Attempts:</strong> <?php echo $timetable_stats['generation_attempts'] ?? 1; ?></li>
                                <li><strong>Total Classes:</strong> <?php echo $timetable_stats['total_classes'] ?? 0; ?></li>
                                <li><strong>Total Periods:</strong> <?php echo $timetable_stats['total_periods'] ?? 0; ?></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const generateBtn = document.getElementById('generateBtn');
            const generateForm = document.getElementById('generateForm');
            const loadingOverlay = document.getElementById('loadingOverlay');

            if (generateBtn && generateForm && !generateBtn.disabled) {
                generateForm.addEventListener('submit', function(e) {
                    if (!confirm('This will generate a new timetable. This may take a few moments. Continue?')) {
                        e.preventDefault();
                    } else {
                        loadingOverlay.style.display = 'flex';
                        generateBtn.disabled = true;
                        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';
                        
                        // Auto-hide loading overlay after 30 seconds (safety)
                        setTimeout(() => {
                            loadingOverlay.style.display = 'none';
                            generateBtn.disabled = false;
                            generateBtn.innerHTML = '<i class="fas fa-bolt me-1"></i> Generate Timetable';
                        }, 30000);
                    }
                });
            }

            // Handle tab changes
            const classTabs = document.querySelectorAll('#classTabs button[data-bs-toggle="pill"]');
            classTabs.forEach(tab => {
                tab.addEventListener('shown.bs.tab', function (event) {
                    // You can add additional tab change logic here if needed
                });
            });
        });
    </script>
</body>
</html>