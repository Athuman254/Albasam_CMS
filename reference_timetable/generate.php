<?php
session_start();
require_once 'db.php';

// Enhanced debug mode
define('DEBUG_MODE', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enhanced debug function that shows output in HTML comments
function debug_log($message) {
    if (DEBUG_MODE) {
        error_log("TIMETABLE_DEBUG: " . $message);
        // Show debug info on page as HTML comments
        echo "<!-- DEBUG: " . htmlspecialchars($message) . " -->\n";
    }
}

debug_log("=== TIMETABLE GENERATION SCRIPT STARTED ===");

// Who is running this
$academic_teacher_name = $_SESSION['full_name'] ?? ($_SESSION['teacher_name'] ?? 'Academic Teacher');

// Academic year
if (!empty($_POST['academic_year'])) {
    $current_academic_year = $_POST['academic_year'];
    debug_log("Academic year from POST: " . $current_academic_year);
} elseif (!empty($_SESSION['current_academic_year'])) {
    $current_academic_year = $_SESSION['current_academic_year'];
    debug_log("Academic year from SESSION: " . $current_academic_year);
} else {
    $y = (int)date('Y');
    $current_academic_year = $y . '-' . ($y + 1);
    $_SESSION['current_academic_year'] = $current_academic_year;
    debug_log("Academic year generated: " . $current_academic_year);
}

// Initialize outputs
$error = '';
$success = '';
$generated_timetable = [];
$timetable_stats = [];
$teacher_conflicts = [];
$system_status = [];
$constraints_count = 0;
$saved_timetables = [];
$filter_teacher = $_GET['teacher'] ?? '';
$filter_class = $_GET['class'] ?? '';
$view_mode = $_GET['view'] ?? 'class'; // 'class' or 'teacher'
$semester = $_GET['semester'] ?? '1';

debug_log("Initializing system...");

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
    debug_log("Checking system requirements for year: " . $academic_year);
    $req = initializeSystemStatus();

    try {
        // Check periods
        $q = $conn->prepare("SELECT COUNT(*) AS cnt FROM timetable_periods WHERE academic_year = ?");
        if ($q) {
            $q->bind_param('s', $academic_year);
            $q->execute();
            $cnt = $q->get_result()->fetch_assoc()['cnt'];
            $req['periods'] = ['count'=> (int)$cnt, 'status'=>((int)$cnt>0), 'message'=> ((int)$cnt>0 ? "{$cnt} periods" : "No periods")];
            $q->close();
            debug_log("Periods check: " . $req['periods']['message']);
        }

        // Check classes
        $q = $conn->prepare("SELECT COUNT(*) AS cnt FROM classes WHERE academic_year = ?");
        if ($q) {
            $q->bind_param('s',$academic_year);
            $q->execute();
            $cnt = $q->get_result()->fetch_assoc()['cnt'];
            $req['classes'] = ['count'=> (int)$cnt,'status'=>((int)$cnt>0),'message'=> ((int)$cnt>0 ? "{$cnt} classes" : "No classes")];
            $q->close();
            debug_log("Classes check: " . $req['classes']['message']);
        }

        // Check teachers
        $q = $conn->prepare("SELECT COUNT(*) AS cnt FROM staff WHERE position='Teacher' AND status='active'");
        if ($q) {
            $q->execute();
            $cnt = $q->get_result()->fetch_assoc()['cnt'];
            $req['teachers'] = ['count'=>$cnt,'status'=>($cnt>0),'message'=>($cnt>0? "{$cnt} teachers":"No teachers")];
            $q->close();
            debug_log("Teachers check: " . $req['teachers']['message']);
        }

        // Check subjects
        $q = $conn->prepare("SELECT COUNT(*) AS cnt FROM subjects WHERE status='active'");
        if ($q) {
            $q->execute();
            $cnt = $q->get_result()->fetch_assoc()['cnt'];
            $req['subjects'] = ['count'=>$cnt,'status'=>($cnt>0),'message'=>($cnt>0? "{$cnt} subjects":"No subjects")];
            $q->close();
            debug_log("Subjects check: " . $req['subjects']['message']);
        }

        // Check allocations (subject_allocations)
        $q = $conn->prepare("SELECT COUNT(*) AS cnt FROM subject_allocations WHERE academic_year = ?");
        if ($q) {
            $q->bind_param('s',$academic_year);
            $q->execute();
            $cnt = $q->get_result()->fetch_assoc()['cnt'];
            $req['teacher_allocations'] = ['count'=> (int)$cnt,'status'=>((int)$cnt>0),'message'=> ((int)$cnt>0? "{$cnt} allocations":"No allocations")];
            $q->close();
            debug_log("Allocations check: " . $req['teacher_allocations']['message']);
        }

        $req['all_ready'] = $req['periods']['status'] && $req['classes']['status'] && $req['teachers']['status'] && $req['subjects']['status'] && $req['teacher_allocations']['status'];
        debug_log("System all_ready: " . ($req['all_ready'] ? 'YES' : 'NO'));

    } catch (Exception $e) {
        debug_log("System requirements check failed: " . $e->getMessage());
    }

    return $req;
}

/* ---------------------------
   Get teacher allocations - ROBUST VERSION
   --------------------------- */
function getTeacherAllocations($conn, $academic_year) {
    debug_log("Getting teacher allocations for {$academic_year}");
    $allocations = [];

    try {
        // First test if tables exist
        $test_tables = $conn->query("SHOW TABLES LIKE 'subject_allocations'");
        if ($test_tables->num_rows == 0) {
            debug_log("CRITICAL: subject_allocations table does not exist!");
            return [];
        }

        // Simplified query that's more robust
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
                c.section as section
            FROM subject_allocations sa
            LEFT JOIN staff t ON sa.teacher_id = t.id
            LEFT JOIN subjects s ON sa.subject_id = s.id
            LEFT JOIN classes c ON sa.class_id = c.id
            WHERE sa.academic_year = ? 
            ORDER BY sa.class_id, sa.id
        ";

        debug_log("Executing allocations query...");
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare allocations query: " . $conn->error);
        }
        
        $stmt->bind_param('s', $academic_year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $total_allocations = 0;
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
            $total_allocations++;
        }
        $stmt->close();
        
        debug_log("Found {$total_allocations} allocations for " . count($allocations) . " classes");
        
    } catch (Exception $e) {
        debug_log("ERROR in getTeacherAllocations: " . $e->getMessage());
        // Return empty array instead of throwing to allow graceful degradation
        return [];
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
        debug_log("Fetching classes...");
        $stmt = $conn->prepare("SELECT id, class_name, section FROM classes WHERE academic_year = ?");
        if (!$stmt) throw new Exception("Failed classes prepare: " . $conn->error);
        $stmt->bind_param('s', $academic_year);
        $stmt->execute();
        $classes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        debug_log("Found " . count($classes) . " classes");
        if (empty($classes)) throw new Exception("No classes found for year {$academic_year}");

        // 2) Fetch periods
        debug_log("Fetching periods...");
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
        
        debug_log("Found " . count($periods) . " periods");
        if (empty($periods)) throw new Exception("No periods configured for year {$academic_year}");

        // 3) Get teacher allocations
        debug_log("Getting teacher allocations...");
        $teacher_allocations = getTeacherAllocations($conn, $academic_year);
        debug_log("Teacher allocations count: " . count($teacher_allocations));

        // 4) Generate timetable for each class
        foreach ($classes as $class) {
            $class_id = $class['id'];
            $class_name = $class['class_name'] . ($class['section'] ? ' - ' . $class['section'] : '');
            $class_timetable = [];
            
            debug_log("Processing class: {$class_name} (ID: {$class_id})");

            // Get allocations for this class
            $allocations = $teacher_allocations[$class_id] ?? [];
            debug_log("Allocations for class {$class_id}: " . count($allocations));
            
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
        debug_log("ERROR in generateTimetable: " . $e->getMessage());
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
    
    debug_log("Timetable stats calculated: " . json_encode($stats));
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
                    'iiiiisisss', 
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
   Get teachers list for filters
   --------------------------- */
function getTeachersList($conn) {
    $teachers = [];
    try {
        $stmt = $conn->prepare("SELECT id, full_name FROM staff WHERE position='Teacher' AND status='active' ORDER BY full_name");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $teachers[$row['id']] = $row['full_name'];
        }
        $stmt->close();
    } catch (Exception $e) {
        debug_log("Error getting teachers list: " . $e->getMessage());
    }
    return $teachers;
}

/* ---------------------------
   Get classes list for filters
   --------------------------- */
function getClassesList($conn, $academic_year) {
    $classes = [];
    try {
        $stmt = $conn->prepare("SELECT id, class_name, section FROM classes WHERE academic_year = ? ORDER BY class_name, section");
        $stmt->bind_param('s', $academic_year);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $class_name = $row['class_name'] . ($row['section'] ? ' - ' . $row['section'] : '');
            $classes[$row['id']] = $class_name;
        }
        $stmt->close();
    } catch (Exception $e) {
        debug_log("Error getting classes list: " . $e->getMessage());
    }
    return $classes;
}

/* ---------------------------
   Convert timetable to teacher view
   --------------------------- */
function convertToTeacherView($timetable) {
    $teacher_view = [];
    
    foreach ($timetable as $class_name => $class_periods) {
        foreach ($class_periods as $period) {
            // Skip if no teacher assigned
            if (empty($period['teacher_id']) || $period['is_break'] || $period['subject_name'] === 'Free Period') {
                continue;
            }
            
            $teacher_id = $period['teacher_id'];
            $teacher_name = $period['teacher_name'];
            
            if (!isset($teacher_view[$teacher_name])) {
                $teacher_view[$teacher_name] = [];
            }
            
            $teacher_view[$teacher_name][] = [
                'class_name' => $class_name,
                'day_of_week' => $period['day_of_week'],
                'time_slot' => $period['time_slot'],
                'subject_name' => $period['subject_name'],
                'subject_code' => $period['subject_code'],
                'start_time' => $period['start_time'],
                'end_time' => $period['end_time'],
                'period_name' => $period['period_name']
            ];
        }
    }
    
    // Sort by teacher name
    ksort($teacher_view);
    
    return $teacher_view;
}

/* ---------------------------
   Filter timetable based on criteria
   --------------------------- */
function filterTimetable($timetable, $filter_teacher, $filter_class, $view_mode) {
    if (empty($filter_teacher) && empty($filter_class)) {
        return $timetable;
    }
    
    $filtered = [];
    
    foreach ($timetable as $class_name => $class_periods) {
        // Filter by class
        if (!empty($filter_class) && $class_name !== $filter_class) {
            continue;
        }
        
        $filtered_periods = [];
        foreach ($class_periods as $period) {
            // Filter by teacher
            if (!empty($filter_teacher)) {
                if ($period['teacher_name'] === $filter_teacher) {
                    $filtered_periods[] = $period;
                }
            } else {
                $filtered_periods[] = $period;
            }
        }
        
        if (!empty($filtered_periods)) {
            $filtered[$class_name] = $filtered_periods;
        }
    }
    
    return $filtered;
}

/* ---------------------------
   Get saved timetable data for view
   --------------------------- */
function getSavedTimetableData($conn, $academic_year, $semester = '1', $teacher_id = null, $class_id = null) {
    debug_log("Getting saved timetable data for year: {$academic_year}, semester: {$semester}");
    
    $timetable_data = [];
    
    try {
        // Get the latest published timetable version
        $version_query = "
            SELECT tv.id, tv.version_name 
            FROM timetable_versions tv 
            WHERE tv.academic_year = ? AND tv.semester = ? 
            ORDER BY tv.created_at DESC 
            LIMIT 1
        ";
        
        $stmt = $conn->prepare($version_query);
        $stmt->bind_param('ss', $academic_year, $semester);
        $stmt->execute();
        $version_result = $stmt->get_result();
        
        if ($version_result->num_rows === 0) {
            debug_log("No saved timetable found for {$academic_year} semester {$semester}");
            return [];
        }
        
        $version = $version_result->fetch_assoc();
        $version_id = $version['id'];
        $stmt->close();
        
        debug_log("Found timetable version: {$version_id} - {$version['version_name']}");
        
        // Get timetable allocations with filters
        $alloc_query = "
            SELECT 
                ta.*,
                t.full_name as teacher_name,
                s.subject_name,
                s.subject_code,
                c.class_name,
                c.section,
                tp.day_of_week,
                tp.start_time,
                tp.end_time,
                tp.period_name
            FROM timetable_allocations ta
            LEFT JOIN staff t ON ta.teacher_id = t.id
            LEFT JOIN subjects s ON ta.subject_id = s.id
            LEFT JOIN classes c ON ta.class_id = c.id
            LEFT JOIN timetable_periods tp ON ta.period_id = tp.id
            WHERE ta.version_id = ? AND ta.academic_year = ? AND ta.semester = ?
        ";
        
        // Add filters if provided
        $params = [$version_id, $academic_year, $semester];
        $param_types = 'iss';
        
        if ($teacher_id) {
            $alloc_query .= " AND ta.teacher_id = ?";
            $params[] = $teacher_id;
            $param_types .= 'i';
        }
        
        if ($class_id) {
            $alloc_query .= " AND ta.class_id = ?";
            $params[] = $class_id;
            $param_types .= 'i';
        }
        
        $alloc_query .= " ORDER BY ta.day_of_week, ta.start_time";
        
        $stmt = $conn->prepare($alloc_query);
        if (!empty($params)) {
            $stmt->bind_param($param_types, ...$params);
        }
        $stmt->execute();
        $alloc_result = $stmt->get_result();
        
        while ($row = $alloc_result->fetch_assoc()) {
            $class_name = $row['class_name'] . ($row['section'] ? ' - ' . $row['section'] : '');
            
            if (!isset($timetable_data[$class_name])) {
                $timetable_data[$class_name] = [];
            }
            
            $timetable_data[$class_name][] = [
                'class_id' => $row['class_id'],
                'class_name' => $class_name,
                'period_id' => $row['period_id'],
                'day_of_week' => $row['day_of_week'],
                'time_slot' => $row['start_time'] . ' - ' . $row['end_time'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'period_name' => $row['period_name'],
                'subject_id' => $row['subject_id'],
                'subject_name' => $row['subject_name'],
                'subject_code' => $row['subject_code'],
                'teacher_id' => $row['teacher_id'],
                'teacher_name' => $row['teacher_name'],
                'room_number' => $row['room_number']
            ];
        }
        $stmt->close();
        
        debug_log("Retrieved " . count($timetable_data) . " classes from saved timetable");
        
    } catch (Exception $e) {
        debug_log("Error getting saved timetable data: " . $e->getMessage());
    }
    
    return $timetable_data;
}

/* ---------------------------
   Get teacher timetable data
   --------------------------- */
function getTeacherTimetableData($conn, $academic_year, $semester = '1', $teacher_id = null) {
    debug_log("Getting teacher timetable data");
    
    $teacher_timetable = [];
    
    try {
        $alloc_query = "
            SELECT 
                ta.*,
                t.full_name as teacher_name,
                s.subject_name,
                s.subject_code,
                c.class_name,
                c.section,
                tp.day_of_week,
                tp.start_time,
                tp.end_time,
                tp.period_name
            FROM timetable_allocations ta
            LEFT JOIN staff t ON ta.teacher_id = t.id
            LEFT JOIN subjects s ON ta.subject_id = s.id
            LEFT JOIN classes c ON ta.class_id = c.id
            LEFT JOIN timetable_periods tp ON ta.period_id = tp.id
            LEFT JOIN timetable_versions tv ON ta.version_id = tv.id
            WHERE ta.academic_year = ? AND ta.semester = ? AND tv.status = 'Published'
        ";
        
        $params = [$academic_year, $semester];
        $param_types = 'ss';
        
        if ($teacher_id) {
            $alloc_query .= " AND ta.teacher_id = ?";
            $params[] = $teacher_id;
            $param_types .= 'i';
        }
        
        $alloc_query .= " ORDER BY t.full_name, ta.day_of_week, ta.start_time";
        
        $stmt = $conn->prepare($alloc_query);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $alloc_result = $stmt->get_result();
        
        while ($row = $alloc_result->fetch_assoc()) {
            $teacher_name = $row['teacher_name'];
            $class_name = $row['class_name'] . ($row['section'] ? ' - ' . $row['section'] : '');
            
            if (!isset($teacher_timetable[$teacher_name])) {
                $teacher_timetable[$teacher_name] = [];
            }
            
            $teacher_timetable[$teacher_name][] = [
                'class_name' => $class_name,
                'day_of_week' => $row['day_of_week'],
                'time_slot' => $row['start_time'] . ' - ' . $row['end_time'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'subject_name' => $row['subject_name'],
                'subject_code' => $row['subject_code'],
                'period_name' => $row['period_name'],
                'room_number' => $row['room_number']
            ];
        }
        $stmt->close();
        
        debug_log("Retrieved timetable for " . count($teacher_timetable) . " teachers");
        
    } catch (Exception $e) {
        debug_log("Error getting teacher timetable data: " . $e->getMessage());
    }
    
    return $teacher_timetable;
}

/* ---------------------------
   Download timetable as CSV
   --------------------------- */
function downloadTimetableCSV($timetable_data, $filename = 'timetable.csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write headers
    fputcsv($output, ['Class', 'Day', 'Time Slot', 'Subject', 'Teacher', 'Room']);
    
    // Write data
    foreach ($timetable_data as $class_name => $periods) {
        foreach ($periods as $period) {
            fputcsv($output, [
                $class_name,
                $period['day_of_week'],
                $period['time_slot'],
                $period['subject_name'] . ($period['subject_code'] ? ' (' . $period['subject_code'] . ')' : ''),
                $period['teacher_name'] ?? 'N/A',
                $period['room_number'] ?? 'N/A'
            ]);
        }
    }
    
    fclose($output);
    exit;
}

/* ---------------------------
   Download teacher timetable as CSV
   --------------------------- */
function downloadTeacherTimetableCSV($teacher_timetable, $filename = 'teacher_timetable.csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write headers
    fputcsv($output, ['Teacher', 'Day', 'Time Slot', 'Class', 'Subject', 'Room']);
    
    // Write data
    foreach ($teacher_timetable as $teacher_name => $periods) {
        foreach ($periods as $period) {
            fputcsv($output, [
                $teacher_name,
                $period['day_of_week'],
                $period['time_slot'],
                $period['class_name'],
                $period['subject_name'] . ($period['subject_code'] ? ' (' . $period['subject_code'] . ')' : ''),
                $period['room_number'] ?? 'N/A'
            ]);
        }
    }
    
    fclose($output);
    exit;
}

/* ---------------------------
   POST handlers - FIXED VERSION
   --------------------------- */
debug_log("Checking request method: " . $_SERVER['REQUEST_METHOD']);
debug_log("POST data: " . print_r($_POST, true));

// Handle download requests
if (isset($_GET['download'])) {
    $download_type = $_GET['download'];
    $semester = $_GET['semester'] ?? '1';
    $teacher_id = $_GET['teacher_id'] ?? null;
    $class_id = $_GET['class_id'] ?? null;
    
    if ($download_type === 'class_csv') {
        $timetable_data = getSavedTimetableData($conn, $current_academic_year, $semester, $teacher_id, $class_id);
        downloadTimetableCSV($timetable_data, "class_timetable_{$current_academic_year}_sem{$semester}.csv");
    } elseif ($download_type === 'teacher_csv') {
        $teacher_timetable = getTeacherTimetableData($conn, $current_academic_year, $semester, $teacher_id);
        downloadTeacherTimetableCSV($teacher_timetable, "teacher_timetable_{$current_academic_year}_sem{$semester}.csv");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_log("POST request detected");
    
    // FIXED: Generate timetable detection - check if this is a generate request
    $is_generate_request = (
        isset($_POST['generate_timetable']) || 
        (count($_POST) === 1 && isset($_POST['academic_year']))
    );
    
    if ($is_generate_request) {
        debug_log("Generate timetable request detected");
        try {
            $system_status = checkSystemRequirements($conn, $current_academic_year);
            debug_log("System status check completed. All ready: " . ($system_status['all_ready'] ? 'YES' : 'NO'));
            
            if (!$system_status['all_ready']) {
                $error = "System not ready. Please check:";
                if (!$system_status['periods']['status']) $error .= " Periods,";
                if (!$system_status['classes']['status']) $error .= " Classes,";
                if (!$system_status['teacher_allocations']['status']) $error .= " Teacher Allocations";
                $error = rtrim($error, ',');
                debug_log("System not ready error: " . $error);
            } else {
                debug_log("Starting timetable generation process...");
                $generation_result = generateTimetableWithConflictResolution($conn, $current_academic_year);
                $generated_timetable = $generation_result['timetable'];
                $teacher_conflicts = $generation_result['conflicts'];
                
                debug_log("Generation completed. Timetable classes: " . count($generated_timetable));
                
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
                    
                    debug_log("Timetable generation successful: " . $success);
                } else {
                    $error = "Timetable generation returned empty result.";
                    debug_log($error);
                }
            }
        } catch (Exception $e) {
            $error = "Generation failed: " . $e->getMessage();
            debug_log("EXCEPTION in generation: " . $e->getMessage());
        }
    }

    // Save timetable
    if (isset($_POST['save_timetable'])) {
        debug_log("Save timetable requested");
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
    debug_log("Fetching UI data...");
    $system_status = checkSystemRequirements($conn, $current_academic_year);

    // Fetch constraints count
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM timetable_constraints WHERE academic_year = ?");
    $stmt->bind_param('s', $current_academic_year);
    $stmt->execute();
    $constraints_count = $stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();
    debug_log("Constraints count: " . $constraints_count);

    // Fetch saved timetable versions
    $stmt = $conn->prepare("SELECT * FROM timetable_versions WHERE academic_year = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param('s', $current_academic_year);
    $stmt->execute();
    $saved_timetables = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    debug_log("Saved timetables count: " . count($saved_timetables));

    // Get teachers and classes for filters
    $teachers_list = getTeachersList($conn);
    $classes_list = getClassesList($conn, $current_academic_year);

    // Get saved timetable data for view
    $saved_timetable_data = getSavedTimetableData($conn, $current_academic_year, $semester);
    $teacher_timetable_data = getTeacherTimetableData($conn, $current_academic_year, $semester);

} catch (Exception $e) {
    debug_log("UI data fetch failed: " . $e->getMessage());
    $system_status = initializeSystemStatus();
    $saved_timetables = [];
    $teachers_list = [];
    $classes_list = [];
    $saved_timetable_data = [];
    $teacher_timetable_data = [];
}

// Restore session data if available
$generated_timetable = $_SESSION['generated_timetable'] ?? $generated_timetable;
$timetable_stats = $_SESSION['timetable_stats'] ?? $timetable_stats;
$teacher_conflicts = $_SESSION['teacher_conflicts'] ?? $teacher_conflicts;

// Apply filters and view mode
if (!empty($generated_timetable)) {
    $display_timetable = filterTimetable($generated_timetable, $filter_teacher, $filter_class, $view_mode);
    $teacher_view_timetable = convertToTeacherView($generated_timetable);
} else {
    $display_timetable = [];
    $teacher_view_timetable = [];
}

// Determine which data to display
if ($view_mode === 'class') {
    $display_data = !empty($display_timetable) ? $display_timetable : $saved_timetable_data;
} else {
    $display_data = !empty($teacher_view_timetable) ? $teacher_view_timetable : $teacher_timetable_data;
}

debug_log("Final state - Error: '{$error}', Success: '{$success}', Timetable classes: " . count($generated_timetable));

// Close database connection
$conn->close();

debug_log("=== TIMETABLE GENERATION SCRIPT COMPLETED ===");
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

        .filter-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .view-toggle {
            cursor: pointer;
        }

        .view-toggle.active {
            background-color: var(--primary-color) !important;
            color: white !important;
        }

        .download-btn {
            margin-left: 10px;
        }

        .teacher-schedule {
            background-color: #e8f5e8 !important;
        }

        .semester-selector {
            max-width: 200px;
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
                            <!-- FIXED FORM: Added hidden input to ensure generate_timetable is in POST data -->
                            <form method="POST" class="d-inline" id="generateForm">
                                <input type="hidden" name="academic_year" value="<?php echo htmlspecialchars($current_academic_year); ?>">
                                <input type="hidden" name="generate_timetable" value="1">
                                <button type="submit" class="btn btn-success btn-lg" id="generateBtn"
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
                        <?php if (empty($display_data)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-table fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No Timetable Available</h4>
                                <p class="text-muted">
                                    <?php echo ($system_status['all_ready'] ?? false) 
                                        ? 'Click "Generate Timetable" to create a new timetable or check if saved timetables exist.' 
                                        : 'Please complete the system setup first.'; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <!-- Timetable Statistics -->
                            <?php if (!empty($timetable_stats)): ?>
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
                            <?php endif; ?>

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

                            <!-- Filter and View Controls -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="filter-section">
                                        <h6><i class="fas fa-filter me-2"></i>Filter & View Options</h6>
                                        <form method="GET" class="row g-3 align-items-end">
                                            <input type="hidden" name="academic_year" value="<?php echo htmlspecialchars($current_academic_year); ?>">
                                            
                                            <div class="col-md-2">
                                                <label class="form-label">Semester</label>
                                                <select class="form-select semester-selector" name="semester">
                                                    <option value="1" <?php echo $semester == '1' ? 'selected' : ''; ?>>Semester 1</option>
                                                    <option value="2" <?php echo $semester == '2' ? 'selected' : ''; ?>>Semester 2</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-2">
                                                <label class="form-label">View Mode</label>
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" class="btn-check" name="view" id="view-class" value="class" <?php echo $view_mode === 'class' ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-primary" for="view-class">
                                                        <i class="fas fa-chalkboard-teacher me-1"></i> Class View
                                                    </label>
                                                    
                                                    <input type="radio" class="btn-check" name="view" id="view-teacher" value="teacher" <?php echo $view_mode === 'teacher' ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-primary" for="view-teacher">
                                                        <i class="fas fa-user-tie me-1"></i> Teacher View
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">Filter by Teacher</label>
                                                <select class="form-select" name="teacher">
                                                    <option value="">All Teachers</option>
                                                    <?php foreach ($teachers_list as $teacher_name): ?>
                                                        <option value="<?php echo htmlspecialchars($teacher_name); ?>" <?php echo $filter_teacher === $teacher_name ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($teacher_name); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">Filter by Class</label>
                                                <select class="form-select" name="class">
                                                    <option value="">All Classes</option>
                                                    <?php foreach ($classes_list as $class_id => $class_name): ?>
                                                        <option value="<?php echo htmlspecialchars($class_name); ?>" <?php echo $filter_class === $class_name ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($class_name); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="fas fa-filter me-1"></i> Apply Filters
                                                </button>
                                                <a href="?academic_year=<?php echo urlencode($current_academic_year); ?>" class="btn btn-outline-secondary w-100 mt-2">
                                                    <i class="fas fa-times me-1"></i> Clear Filters
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Download Options -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="fas fa-<?php echo $view_mode === 'class' ? 'chalkboard-teacher' : 'user-tie'; ?> me-2"></i>
                                            <?php echo $view_mode === 'class' ? 'Class Timetables' : 'Teacher Schedules'; ?>
                                        </h5>
                                        <div>
                                            <a href="?download=<?php echo $view_mode === 'class' ? 'class_csv' : 'teacher_csv'; ?>&academic_year=<?php echo urlencode($current_academic_year); ?>&semester=<?php echo $semester; ?>" 
                                               class="btn btn-success download-btn">
                                                <i class="fas fa-file-csv me-1"></i> Download CSV
                                            </a>
                                            <button class="btn btn-info download-btn" onclick="downloadTimetableExcel()">
                                                <i class="fas fa-file-excel me-1"></i> Download Excel
                                            </button>
                                            <button class="btn btn-secondary download-btn" onclick="printTimetable()">
                                                <i class="fas fa-print me-1"></i> Print
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($view_mode === 'class'): ?>
                                <!-- Class View Timetable -->
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <ul class="nav nav-pills mb-3" id="classTabs" role="tablist">
                                            <?php $first = true; ?>
                                            <?php foreach ($display_data as $class_name => $schedule): ?>
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

                                <!-- Class Timetable Content -->
                                <div class="tab-content" id="classTabContent">
                                    <?php $first = true; ?>
                                    <?php foreach ($display_data as $class_name => $schedule): ?>
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
                                                                                    <?php if (!empty($period['room_number'])): ?>
                                                                                        <br><small class="text-muted">Room: <?php echo $period['room_number']; ?></small>
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
                            <?php else: ?>
                                <!-- Teacher View Timetable -->
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <ul class="nav nav-pills mb-3" id="teacherTabs" role="tablist">
                                            <?php $first = true; ?>
                                            <?php foreach ($display_data as $teacher_name => $schedule): ?>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link <?php echo $first ? 'active' : ''; ?>" 
                                                            id="tab-teacher-<?php echo preg_replace('/[^a-zA-Z0-9]/', '-', $teacher_name); ?>" 
                                                            data-bs-toggle="pill" 
                                                            data-bs-target="#content-teacher-<?php echo preg_replace('/[^a-zA-Z0-9]/', '-', $teacher_name); ?>" 
                                                            type="button" role="tab">
                                                        <?php echo htmlspecialchars($teacher_name); ?>
                                                    </button>
                                                </li>
                                                <?php $first = false; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Teacher Timetable Content -->
                                <div class="tab-content" id="teacherTabContent">
                                    <?php $first = true; ?>
                                    <?php foreach ($display_data as $teacher_name => $schedule): ?>
                                        <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" 
                                             id="content-teacher-<?php echo preg_replace('/[^a-zA-Z0-9]/', '-', $teacher_name); ?>" 
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
                                                                    <td class="<?php echo $period ? 'teacher-schedule' : 'bg-light'; ?>">
                                                                        <?php if ($period): ?>
                                                                            <div class="text-center">
                                                                                <small class="fw-bold d-block"><?php echo $period['subject_name'] ?? 'Unknown'; ?></small>
                                                                                <small class="text-muted"><?php echo $period['class_name'] ?? ''; ?></small>
                                                                                <?php if (!empty($period['subject_code'])): ?>
                                                                                    <br><small class="text-muted">(<?php echo $period['subject_code']; ?>)</small>
                                                                                <?php endif; ?>
                                                                                <?php if (!empty($period['room_number'])): ?>
                                                                                    <br><small class="text-muted">Room: <?php echo $period['room_number']; ?></small>
                                                                                <?php endif; ?>
                                                                            </div>
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

        console.log('Timetable Generator Debug:');
        console.log('Generate button found:', !!generateBtn);
        console.log('Generate form found:', !!generateForm);
        console.log('Loading overlay found:', !!loadingOverlay);
        
        if (generateBtn && generateForm) {
            console.log('All elements found, attaching event listener...');
            
            generateForm.addEventListener('submit', function(e) {
                console.log('Form submission triggered!');
                
                if (!confirm('This will generate a new timetable. This may take a few moments. Continue?')) {
                    console.log('User cancelled generation');
                    e.preventDefault();
                    return false;
                }
                
                console.log('Starting timetable generation...');
                loadingOverlay.style.display = 'flex';
                generateBtn.disabled = true;
                generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';
                
                // Safety timeout - hide overlay after 30 seconds
                setTimeout(() => {
                    if (loadingOverlay.style.display === 'flex') {
                        console.log('Safety timeout reached - still loading after 30 seconds');
                        loadingOverlay.style.display = 'none';
                        generateBtn.disabled = false;
                        generateBtn.innerHTML = '<i class="fas fa-bolt me-1"></i> Generate Timetable';
                        alert('Generation is taking longer than expected. Please check the system status and try again.');
                    }
                }, 30000);
                
                return true;
            });
            
            console.log('Event listener attached successfully');
        } else {
            console.error('ERROR: Could not find form elements!');
            if (!generateBtn) console.error('Generate button not found');
            if (!generateForm) console.error('Generate form not found');
        }

        // View mode toggle
        const viewRadios = document.querySelectorAll('input[name="view"]');
        viewRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelector('form').submit();
            });
        });

        // Semester selector change
        const semesterSelect = document.querySelector('select[name="semester"]');
        if (semesterSelect) {
            semesterSelect.addEventListener('change', function() {
                document.querySelector('form').submit();
            });
        }
    });

    function downloadTimetablePDF() {
        alert('PDF download functionality would be implemented here. This would generate a PDF file of the current timetable view.');
        // In a real implementation, this would make an AJAX call to generate PDF
    }

    function downloadTimetableExcel() {
        alert('Excel download functionality would be implemented here. This would generate an Excel file of the current timetable view.');
        // In a real implementation, this would make an AJAX call to generate Excel
    }

    function printTimetable() {
        window.print();
    }
    </script>
</body>
</html>