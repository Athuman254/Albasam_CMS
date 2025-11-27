<?php
session_start();
require_once 'db.php';

// Authentication (optional)
$academic_teacher_name = $_SESSION['full_name'] ?? 'Academic Teacher';

// === Academic Year ===
$current_academic_year = $_POST['academic_year'] ?? date('Y') . '-' . (date('Y') + 1);

// === Configurations ===
$lesson_minutes = 45;
$short_break_minutes = 15;
$lunch_minutes = 90;

// === Initialize ===
$error = '';
$success = '';
$periods = [];
$constraints = [];

try {
    // === ADD PERIOD ===
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_period'])) {
        $period_name = trim($_POST['period_name']);
        $start_time = trim($_POST['start_time']);
        $days_of_week = $_POST['days_of_week'] ?? [];
        $is_break = isset($_POST['is_break']) ? 1 : 0;
        $break_type = $_POST['break_type'] ?? null;
        $academic_year = $_POST['academic_year'] ?? $current_academic_year;

        if (empty($period_name)) throw new Exception("Period name is required.");
        if (empty($start_time)) throw new Exception("Start time is required.");
        if (empty($days_of_week)) throw new Exception("Select at least one day.");

        // Duration
        if ($is_break) {
            if ($break_type === 'Short Break') $duration_minutes = $short_break_minutes;
            elseif ($break_type === 'Lunch') $duration_minutes = $lunch_minutes;
            else $duration_minutes = $lesson_minutes;
        } else {
            $duration_minutes = $lesson_minutes;
        }

        // --- FIXED TIME HANDLING ---
        $start = DateTime::createFromFormat('H:i', $start_time);
        if (!$start) throw new Exception("Invalid start time format.");

        $end = clone $start;
        $end->modify("+{$duration_minutes} minutes");

        $start_time_24h = $start->format('H:i:s');
        $end_time_24h = $end->format('H:i:s');

        // Override if custom end time provided
        if (!empty($_POST['end_time'])) {
            $custom_end = DateTime::createFromFormat('H:i', $_POST['end_time']);
            if ($custom_end) $end_time_24h = $custom_end->format('H:i:s');
        }

        foreach ($days_of_week as $day) {
            $stmt = $conn->prepare("
                INSERT INTO timetable_periods 
                (period_name, start_time, end_time, day_of_week, is_break, break_type, academic_year)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssiss", $period_name, $start_time_24h, $end_time_24h, $day, $is_break, $break_type, $academic_year);
            $stmt->execute();
        }

        $success = "✅ Period added successfully.";
    }

    // === DELETE PERIOD ===
    elseif (isset($_POST['delete_period'])) {
        $id = intval($_POST['period_id']);
        $stmt = $conn->prepare("DELETE FROM timetable_periods WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $success = "🗑️ Period deleted successfully.";
    }

  // === ADD CONSTRAINT ===
elseif (isset($_POST['add_constraint'])) {
    $constraint_type = $_POST['constraint_type'] ?? '';
    $teacher_id = !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;
    $class_id   = !empty($_POST['class_id'])   ? intval($_POST['class_id'])   : null;
    $subject_id = !empty($_POST['subject_id']) ? intval($_POST['subject_id']) : null;
    $days = $_POST['constraint_days'] ?? [];
    $period_number = intval($_POST['period_number'] ?? 0);
    $constraint_value = trim($_POST['constraint_value'] ?? '');
    $notes = trim($_POST['constraint_notes'] ?? '');
    $academic_year = $_POST['academic_year'] ?? $current_academic_year;

    if (empty($constraint_type)) {
        throw new Exception("Constraint type is required.");
    }

    // Allow empty day (for general constraint)
    if (empty($days)) $days = [null];

    foreach ($days as $day) {
        $stmt = $conn->prepare("
            INSERT INTO timetable_constraints
            (constraint_type, teacher_id, class_id, subject_id, day_of_week, period_number, constraint_value, academic_year, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // Use NULL-safe binding
        $stmt->bind_param(
            "siiisisss",
            $constraint_type,
            $teacher_id,
            $class_id,
            $subject_id,
            $day,
            $period_number,
            $constraint_value,
            $academic_year,
            $notes
        );

        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }
    }

    $success = "✅ Constraint added successfully.";
}
    // === DELETE CONSTRAINT ===
    elseif (isset($_POST['delete_constraint'])) {
        $id = intval($_POST['constraint_id']);
        $stmt = $conn->prepare("DELETE FROM timetable_constraints WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $success = "🗑️ Constraint deleted successfully.";
    }

    // === FETCH DATA ===
    $stmt = $conn->prepare("SELECT * FROM timetable_periods WHERE academic_year = ? ORDER BY day_of_week, start_time");
    $stmt->bind_param("s", $current_academic_year);
    $stmt->execute();
    $periods = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $conn->prepare("
        SELECT tc.*, s.full_name AS teacher_name, c.class_name, sub.subject_name 
        FROM timetable_constraints tc
        LEFT JOIN staff s ON tc.teacher_id = s.id
        LEFT JOIN classes c ON tc.class_id = c.id
        LEFT JOIN subjects sub ON tc.subject_id = sub.id
        WHERE tc.academic_year = ?
        ORDER BY tc.constraint_type, tc.day_of_week
    ");
    $stmt->bind_param("s", $current_academic_year);
    $stmt->execute();
    $constraints = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $teachers = $conn->query("SELECT id, full_name FROM staff WHERE position='Teacher' AND status='Active' ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);
    $classes = $conn->query("SELECT id, class_name FROM classes WHERE status='Active' ORDER BY level, stream, section")->fetch_all(MYSQLI_ASSOC);
    $subjects = $conn->query("SELECT id, subject_name FROM subjects WHERE status='Active' ORDER BY subject_name")->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable Setup - School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --academic-color: #8e44ad;
            --timetable-color: #16a085;
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
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #eaeaea;
            font-weight: 600;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--primary-color);
        }

        .btn-timetable {
            background-color: var(--timetable-color);
            color: white;
        }

        .btn-timetable:hover {
            background-color: #138a72;
            color: white;
        }

        .period-break {
            background-color: #fff3cd !important;
        }

        .constraint-unavailable {
            background-color: #f8d7da !important;
        }

        .constraint-preferred {
            background-color: #d1ecf1 !important;
        }

        .day-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        
        .modal-backdrop {
            z-index: 1040;
        }
        .modal {
            z-index: 1050;
        }
        
        .alert-auto-close {
            animation: fadeOut 5s forwards;
        }
        
        @keyframes fadeOut {
            0% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }
        
        .time-input-group {
            position: relative;
        }
        .duration-badge {
            position: absolute;
            top: -8px;
            right: 10px;
            background: var(--timetable-color);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            z-index: 10;
        }

        /* Toggle Table Styles */
        .toggle-btn {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .toggle-btn:hover {
            background-color: #f8f9fa;
        }
        .collapsible-table {
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .table-collapsed {
            max-height: 0;
            opacity: 0;
            display: none;
        }
        .table-expanded {
            max-height: 1000px;
            opacity: 1;
            display: block;
        }
        .toggle-icon {
            transition: transform 0.3s ease;
        }
        .toggle-rotated {
            transform: rotate(180deg);
        }
        .section-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-left: 4px solid var(--timetable-color);
        }
        
        /* Multi-select checkboxes */
        .days-checkbox-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        .day-checkbox-item {
            display: flex;
            align-items: center;
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .day-checkbox-item:hover {
            background-color: #f8f9fa;
        }
        .day-checkbox-item.selected {
            background-color: #e7f3ff;
            border-color: #0d6efd;
        }
        .day-checkbox-item input {
            margin-right: 8px;
        }
    </style>
</head>
<body>
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
                <a class="btn btn-outline-light btn-sm" href="academic_teacher_dashboard.php">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
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
                        <h1 class="h3 mb-1">Timetable Foundation Setup</h1>
                        <p class="text-muted mb-0">Configure periods, constraints, and basic timetable structure for <?php echo $current_academic_year; ?></p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary fs-6">Academic Year: <?php echo $current_academic_year; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show alert-auto-close" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Periods Configuration -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Periods Configuration</h5>
                        <div>
                            <button class="btn btn-timetable btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addPeriodModal">
                                <i class="fas fa-plus me-1"></i> Add Period
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="toggleAllSections('periods')">
                                <i class="fas fa-expand me-1"></i> Toggle All
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($periods)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No periods configured yet. Add your first period to get started.</p>
                            </div>
                        <?php else: ?>
                            <!-- Group periods by day -->
                            <?php
                            $periods_by_day = [];
                            foreach ($periods as $period) {
                                $day = $period['day_of_week'];
                                if (!isset($periods_by_day[$day])) {
                                    $periods_by_day[$day] = [];
                                }
                                $periods_by_day[$day][] = $period;
                            }
                            ?>
                            
                            <?php foreach ($periods_by_day as $day => $day_periods): ?>
                                <div class="section-header toggle-btn" onclick="toggleSection('periods-<?php echo preg_replace('/[^a-zA-Z0-9]/', '-', $day); ?>')">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-calendar-day me-2 text-primary"></i>
                                            <strong><?php echo $day; ?></strong>
                                            <span class="badge bg-secondary ms-2"><?php echo count($day_periods); ?> periods</span>
                                        </div>
                                        <div>
                                            <i class="fas fa-chevron-down toggle-icon" id="periods-icon-<?php echo preg_replace('/[^a-zA-Z0-9]/', '-', $day); ?>"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="collapsible-table table-expanded" id="periods-<?php echo preg_replace('/[^a-zA-Z0-9]/', '-', $day); ?>">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm mb-4">
                                            <thead>
                                                <tr>
                                                    <th>Period</th>
                                                    <th>Time</th>
                                                    <th>Type</th>
                                                    <th>Duration</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($day_periods as $period): 
                                                    $start = strtotime($period['start_time']);
                                                    $end = strtotime($period['end_time']);
                                                    $duration_minutes = round(($end - $start) / 60);
                                                ?>
                                                    <tr class="<?php echo $period['is_break'] ? 'period-break' : ''; ?>">
                                                        <td><?php echo htmlspecialchars($period['period_name']); ?></td>
                                                        <td>
                                                            <?php echo date('g:i A', $start); ?> - 
                                                            <?php echo date('g:i A', $end); ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($period['is_break']): ?>
                                                                <span class="badge bg-warning"><?php echo $period['break_type'] ?: 'Break'; ?></span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success">Teaching</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary"><?php echo $duration_minutes; ?> min</span>
                                                        </td>
                                                        <td>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="period_id" value="<?php echo $period['id']; ?>">
                                                                <button type="submit" name="delete_period" class="btn btn-sm btn-outline-danger" 
                                                                        onclick="return confirm('Are you sure you want to delete this period?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Constraints Management -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-ban me-2"></i>Constraints Management</h5>
                        <div>
                            <button class="btn btn-timetable btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addConstraintModal">
                                <i class="fas fa-plus me-1"></i> Add Constraint
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="toggleAllSections('constraints')">
                                <i class="fas fa-expand me-1"></i> Toggle All
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($constraints)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-ban fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No constraints configured yet. Add constraints to optimize timetable generation.</p>
                            </div>
                        <?php else: ?>
                            <!-- Group constraints by type -->
                            <?php
                            $constraints_by_type = [];
                            foreach ($constraints as $constraint) {
                                $type = $constraint['constraint_type'];
                                if (!isset($constraints_by_type[$type])) {
                                    $constraints_by_type[$type] = [];
                                }
                                $constraints_by_type[$type][] = $constraint;
                            }
                            ?>
                            
                            <?php foreach ($constraints_by_type as $type => $type_constraints): ?>
                                <div class="section-header toggle-btn" onclick="toggleSection('constraints-<?php echo preg_replace('/[^a-zA-Z0-9]/', '-', $type); ?>')">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-sliders-h me-2 text-info"></i>
                                            <strong><?php echo $type; ?></strong>
                                            <span class="badge bg-secondary ms-2"><?php echo count($type_constraints); ?> constraints</span>
                                        </div>
                                        <div>
                                            <i class="fas fa-chevron-down toggle-icon" id="constraints-icon-<?php echo preg_replace('/[^a-zA-Z0-9]/', '-', $type); ?>"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="collapsible-table table-expanded" id="constraints-<?php echo preg_replace('/[^a-zA-Z0-9]/', '-', $type); ?>">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm mb-4">
                                            <thead>
                                                <tr>
                                                    <th>Target</th>
                                                    <th>Day/Period</th>
                                                    <th>Value</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($type_constraints as $constraint): ?>
                                                    <tr class="<?php echo $constraint['constraint_value'] === 'Unavailable' ? 'constraint-unavailable' : 'constraint-preferred'; ?>">
                                                        <td>
                                                            <?php
                                                            if ($constraint['teacher_name']) {
                                                                echo '<i class="fas fa-user me-1 text-primary"></i>' . htmlspecialchars($constraint['teacher_name']);
                                                            } elseif ($constraint['class_name']) {
                                                                echo '<i class="fas fa-door-open me-1 text-success"></i>' . htmlspecialchars($constraint['class_name']);
                                                            } elseif ($constraint['subject_name']) {
                                                                echo '<i class="fas fa-book me-1 text-warning"></i>' . htmlspecialchars($constraint['subject_name']);
                                                            } else {
                                                                echo '<i class="fas fa-globe me-1 text-secondary"></i>General';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            if ($constraint['day_of_week'] && $constraint['period_number']) {
                                                                echo '<span class="badge bg-light text-dark">' . $constraint['day_of_week'] . '</span> ';
                                                                echo '<span class="badge bg-dark">Period ' . $constraint['period_number'] . '</span>';
                                                            } elseif ($constraint['day_of_week']) {
                                                                echo '<span class="badge bg-light text-dark">' . $constraint['day_of_week'] . '</span>';
                                                            } else {
                                                                echo '<span class="text-muted">Any</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php echo $constraint['constraint_value'] === 'Unavailable' ? 'bg-danger' : 'bg-info'; ?>">
                                                                <?php echo $constraint['constraint_value']; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="constraint_id" value="<?php echo $constraint['id']; ?>">
                                                                <button type="submit" name="delete_constraint" class="btn btn-sm btn-outline-danger" 
                                                                        onclick="return confirm('Are you sure you want to delete this constraint?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3><?php echo count($periods); ?></h3>
                        <p class="mb-0">Total Periods</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3><?php echo count($constraints); ?></h3>
                        <p class="mb-0">Constraints</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3><?php echo count($teachers); ?></h3>
                        <p class="mb-0">Active Teachers</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3><?php echo count($classes); ?></h3>
                        <p class="mb-0">Active Classes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Period Modal -->
    <div class="modal fade" id="addPeriodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="periodForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Period</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Academic Year -->
                        <div class="mb-3">
                            <label class="form-label">Academic Year *</label>
                            <input type="text" class="form-control" name="academic_year" required 
                                   value="<?php echo htmlspecialchars($current_academic_year); ?>" 
                                   placeholder="e.g., 2025-2026">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Period Name *</label>
                            <input type="text" class="form-control" name="period_name" required placeholder="e.g., Period 1, Morning Assembly">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="time-input-group">
                                    <span class="duration-badge" id="startDurationBadge">Start</span>
                                    <label class="form-label">Start Time *</label>
                                    <input type="time" class="form-control" name="start_time" id="startTime" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="time-input-group">
                                    <span class="duration-badge" id="endDurationBadge">Auto</span>
                                    <label class="form-label">End Time *</label>
                                    <input type="time" class="form-control" name="end_time" id="endTime" required readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <div id="timeCalculationInfo" class="alert alert-info py-2">
                                <i class="fas fa-calculator me-2"></i>
                                <span id="calculationText">End time will be calculated automatically</span>
                            </div>
                        </div>
                        
                        <!-- FIXED: Multi-day selection -->
                        <div class="mb-3">
                            <label class="form-label">Days of Week *</label>
                            <div class="days-checkbox-group" id="daysCheckboxGroup">
                                <label class="day-checkbox-item">
                                    <input type="checkbox" name="days_of_week[]" value="Monday"> Monday
                                </label>
                                <label class="day-checkbox-item">
                                    <input type="checkbox" name="days_of_week[]" value="Tuesday"> Tuesday
                                </label>
                                <label class="day-checkbox-item">
                                    <input type="checkbox" name="days_of_week[]" value="Wednesday"> Wednesday
                                </label>
                                <label class="day-checkbox-item">
                                    <input type="checkbox" name="days_of_week[]" value="Thursday"> Thursday
                                </label>
                                <label class="day-checkbox-item">
                                    <input type="checkbox" name="days_of_week[]" value="Friday"> Friday
                                </label>
                                <label class="day-checkbox-item">
                                    <input type="checkbox" name="days_of_week[]" value="Saturday"> Saturday
                                </label>
                            </div>
                            <small class="text-muted">Select one or more days for this period</small>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_break" id="isBreak">
                                <label class="form-check-label" for="isBreak">This is a break period</label>
                            </div>
                        </div>
                        <div class="mb-3" id="breakTypeContainer" style="display: none;">
                            <label class="form-label">Break Type</label>
                            <select class="form-select" name="break_type" id="breakType">
                                <option value="">Select Break Type</option>
                                <option value="Short Break">Short Break (15 min)</option>
                                <option value="Lunch">Lunch (90 min)</option>
                                <option value="Assembly">Assembly (45 min)</option>
                                <option value="Games">Games (45 min)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_period" class="btn btn-timetable">Add Period</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Constraint Modal -->
    <div class="modal fade" id="addConstraintModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="constraintForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Constraint</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Academic Year -->
                        <div class="mb-3">
                            <label class="form-label">Academic Year *</label>
                            <input type="text" class="form-control" name="academic_year" required 
                                   value="<?php echo htmlspecialchars($current_academic_year); ?>" 
                                   placeholder="e.g., 2025-2026">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Constraint Type *</label>
                                <select class="form-select" name="constraint_type" id="constraintType" required>
                                    <option value="">Select Type</option>
                                    <option value="Teacher Availability">Teacher Availability</option>
                                    <option value="Room Availability">Room Availability</option>
                                    <option value="Subject Preference">Subject Preference</option>
                                    <option value="Class Capacity">Class Capacity</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Constraint Value *</label>
                                <select class="form-select" name="constraint_value" id="constraintValue" required>
                                    <option value="">Select Value</option>
                                    <option value="Available">Available</option>
                                    <option value="Unavailable">Unavailable</option>
                                    <option value="Preferred">Preferred</option>
                                    <option value="Not Preferred">Not Preferred</option>
                                </select>
                            </div>
                        </div>

                        <!-- Teacher/Class/Subject fields -->
                        <div id="teacherFields" class="constraint-fields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Teacher *</label>
                                <select class="form-select" name="teacher_id" id="teacherSelect">
                                    <option value="">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['full_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div id="classFields" class="constraint-fields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Class *</label>
                                <select class="form-select" name="class_id" id="classSelect">
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div id="subjectFields" class="constraint-fields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Subject *</label>
                                <select class="form-select" name="subject_id" id="subjectSelect">
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['subject_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- FIXED: Multi-day selection for constraints -->
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Days of Week</label>
                                <div class="days-checkbox-group">
                                    <label class="day-checkbox-item">
                                        <input type="checkbox" name="constraint_days[]" value="Monday"> Mon
                                    </label>
                                    <label class="day-checkbox-item">
                                        <input type="checkbox" name="constraint_days[]" value="Tuesday"> Tue
                                    </label>
                                    <label class="day-checkbox-item">
                                        <input type="checkbox" name="constraint_days[]" value="Wednesday"> Wed
                                    </label>
                                    <label class="day-checkbox-item">
                                        <input type="checkbox" name="constraint_days[]" value="Thursday"> Thu
                                    </label>
                                    <label class="day-checkbox-item">
                                        <input type="checkbox" name="constraint_days[]" value="Friday"> Fri
                                    </label>
                                    <label class="day-checkbox-item">
                                        <input type="checkbox" name="constraint_days[]" value="Saturday"> Sat
                                    </label>
                                </div>
                                <small class="text-muted">Leave empty for all days</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Period Number</label>
                                <input type="number" class="form-control" name="period_number" min="1" max="12" placeholder="e.g., 1, 2, 3...">
                                <small class="text-muted">Leave empty for all periods</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="constraint_notes" rows="2" placeholder="Optional notes about this constraint..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_constraint" class="btn btn-timetable">Add Constraint</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
// Enhanced time calculation with proper AM/PM handling
document.addEventListener('DOMContentLoaded', function() {
    // Time calculation elements
    const startTimeInput = document.getElementById('startTime');
    const endTimeInput = document.getElementById('endTime');
    const isBreakCheckbox = document.getElementById('isBreak');
    const breakTypeSelect = document.getElementById('breakType');
    const calculationInfo = document.getElementById('timeCalculationInfo');
    const calculationText = document.getElementById('calculationText');
    const endDurationBadge = document.getElementById('endDurationBadge');

    // Duration constants (in minutes)
    const LESSON_DURATION = 45;
    const SHORT_BREAK_DURATION = 15;
    const LUNCH_DURATION = 90;

    // Function to convert 24h time to 12h with AM/PM
    function formatTime24To12(time24) {
        if (!time24) return '';
        const [hours, minutes] = time24.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minutes} ${ampm}`;
    }

    // Function to convert 12h time to 24h
    function formatTime12To24(time12) {
        if (!time12) return '';
        const [time, period] = time12.split(' ');
        let [hours, minutes] = time.split(':');
        
        hours = parseInt(hours);
        if (period === 'PM' && hours < 12) {
            hours += 12;
        } else if (period === 'AM' && hours === 12) {
            hours = 0;
        }
        
        return `${hours.toString().padStart(2, '0')}:${minutes}`;
    }

    // Function to calculate end time with proper AM/PM handling
    function calculateEndTime() {
        const startTime24 = startTimeInput.value;
        if (!startTime24) return;

        let durationMinutes = LESSON_DURATION; // Default teaching period
        
        // Adjust duration based on break type
        if (isBreakCheckbox.checked) {
            const breakType = breakTypeSelect.value;
            switch (breakType) {
                case 'Short Break':
                    durationMinutes = SHORT_BREAK_DURATION;
                    break;
                case 'Lunch':
                    durationMinutes = LUNCH_DURATION;
                    break;
                default:
                    durationMinutes = LESSON_DURATION; // Assembly, Games, etc.
            }
        }

        // FIXED: Proper time calculation using 24-hour format
        const [startHours, startMinutes] = startTime24.split(':');
        let startTotalMinutes = parseInt(startHours) * 60 + parseInt(startMinutes);
        let endTotalMinutes = startTotalMinutes + durationMinutes;
        
        // Handle day rollover (if end time goes past midnight)
        let endHours = Math.floor(endTotalMinutes / 60) % 24;
        let endMinutes = endTotalMinutes % 60;
        
        // Format end time in 24-hour format for the input
        const endTime24 = `${endHours.toString().padStart(2, '0')}:${endMinutes.toString().padStart(2, '0')}`;
        
        // Update end time input
        endTimeInput.value = endTime24;
        endDurationBadge.textContent = `${durationMinutes}m`;
        
        // Display in 12-hour format for user clarity
        const startDisplay = formatTime24To12(startTime24);
        const endDisplay = formatTime24To12(endTime24);
        
        calculationInfo.innerHTML = `
            <i class="fas fa-clock me-2"></i>
            <strong>${startDisplay} → ${endDisplay}</strong> (${durationMinutes} minutes)
            ${isBreakCheckbox.checked ? '<span class="badge bg-warning ms-2">Break</span>' : '<span class="badge bg-success ms-2">Teaching</span>'}
        `;
    }

    // Set default start time to 1:30 PM (13:30 in 24-hour format)
    function setDefaultTime() {
        startTimeInput.value = '13:30'; // 1:30 PM
        calculateEndTime();
    }

    // Event listeners for time calculation
    startTimeInput.addEventListener('change', calculateEndTime);
    startTimeInput.addEventListener('input', calculateEndTime);
    
    isBreakCheckbox.addEventListener('change', function() {
        document.getElementById('breakTypeContainer').style.display = this.checked ? 'block' : 'none';
        calculateEndTime();
    });
    
    breakTypeSelect.addEventListener('change', calculateEndTime);

    // Set default time when modal opens
    const periodModal = document.getElementById('addPeriodModal');
    periodModal.addEventListener('show.bs.modal', setDefaultTime);

    // Enhanced day selection with visual feedback
    const dayCheckboxes = document.querySelectorAll('input[name="days_of_week[]"], input[name="constraint_days[]"]');
    dayCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const parent = this.closest('.day-checkbox-item');
            if (this.checked) {
                parent.classList.add('selected');
            } else {
                parent.classList.remove('selected');
            }
        });
    });

    // Enhanced constraint type selection
    document.getElementById('constraintType').addEventListener('change', function() {
        const type = this.value;
        
        // Hide all fields first
        document.querySelectorAll('.constraint-fields').forEach(field => {
            field.style.display = 'none';
            field.querySelector('select')?.removeAttribute('required');
        });
        
        if (type === 'Teacher Availability') {
            document.getElementById('teacherFields').style.display = 'block';
            document.getElementById('teacherSelect').setAttribute('required', 'true');
        } else if (type === 'Room Availability' || type === 'Class Capacity') {
            document.getElementById('classFields').style.display = 'block';
            document.getElementById('classSelect').setAttribute('required', 'true');
        } else if (type === 'Subject Preference') {
            document.getElementById('subjectFields').style.display = 'block';
            document.getElementById('subjectSelect').setAttribute('required', 'true');
        }
    });

    // Reset modals when closed
    periodModal.addEventListener('hidden.bs.modal', function() {
        document.getElementById('periodForm').reset();
        document.getElementById('breakTypeContainer').style.display = 'none';
        endDurationBadge.textContent = 'Auto';
        calculationText.textContent = 'End time will be calculated automatically';
        calculationInfo.innerHTML = '<i class="fas fa-calculator me-2"></i><span id="calculationText">End time will be calculated automatically</span>';
        
        // Reset day selection styles
        document.querySelectorAll('.day-checkbox-item').forEach(item => {
            item.classList.remove('selected');
        });
    });

    const constraintModal = document.getElementById('addConstraintModal');
    constraintModal.addEventListener('hidden.bs.modal', function() {
        document.getElementById('constraintForm').reset();
        document.querySelectorAll('.constraint-fields').forEach(field => {
            field.style.display = 'none';
        });
        
        // Reset day selection styles
        document.querySelectorAll('.day-checkbox-item').forEach(item => {
            item.classList.remove('selected');
        });
    });

    // Form validation for days selection
    document.getElementById('periodForm').addEventListener('submit', function(e) {
        const dayCheckboxes = this.querySelectorAll('input[name="days_of_week[]"]');
        const checkedDays = Array.from(dayCheckboxes).filter(cb => cb.checked);
        
        if (checkedDays.length === 0) {
            e.preventDefault();
            alert('Please select at least one day of the week.');
            return false;
        }
    });

    // Auto-hide success alerts
    const successAlerts = document.querySelectorAll('.alert-auto-close');
    successAlerts.forEach(alert => {
        setTimeout(() => {
            if (alert && alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });

    // Real-time validation
    document.querySelectorAll('[required]').forEach(field => {
        field.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    });
});

// Toggle Table Functions
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    const icon = document.getElementById(sectionId.replace('periods-', 'periods-icon-').replace('constraints-', 'constraints-icon-'));
    
    if (section.classList.contains('table-expanded')) {
        section.classList.remove('table-expanded');
        section.classList.add('table-collapsed');
        icon.classList.add('toggle-rotated');
    } else {
        section.classList.remove('table-collapsed');
        section.classList.add('table-expanded');
        icon.classList.remove('toggle-rotated');
    }
}

function toggleAllSections(type) {
    const sections = document.querySelectorAll(`.card-body .collapsible-table`);
    const allExpanded = Array.from(sections).every(section => section.classList.contains('table-expanded'));
    
    sections.forEach(section => {
        if (allExpanded) {
            section.classList.remove('table-expanded');
            section.classList.add('table-collapsed');
        } else {
            section.classList.remove('table-collapsed');
            section.classList.add('table-expanded');
        }
    });
    
    // Update all icons
    const icons = document.querySelectorAll('.toggle-icon');
    icons.forEach(icon => {
        if (allExpanded) {
            icon.classList.add('toggle-rotated');
        } else {
            icon.classList.remove('toggle-rotated');
        }
    });
}

// Clear URL parameters to prevent message showing on reload
if (window.history.replaceState && window.location.search) {
    const cleanURL = window.location.protocol + "//" + window.location.host + window.location.pathname;
    window.history.replaceState({}, document.title, cleanURL);
}
</script>
</body>
</html>