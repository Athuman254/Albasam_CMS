<?php
session_start();
require_once 'db.php';

// // Enhanced authentication check
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'AcademicTeacher') {
//     header("Location: login.php");
//     exit();
// }

// Initialize variables with default values
$total_students = 0;
$total_teachers = 0;
$pending_approvals = 0;
$performance_rate = 0;
$pending_results = 0;
$timetable_status = 'Not Generated';
$academic_teacher_name = $_SESSION['full_name'] ?? 'Academic Teacher';

try {
    // Total active students
    $query = "SELECT COUNT(*) as total FROM students WHERE current_status = 'Active'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_students = $row['total'] ?? 0;

    // Total active teachers
    $query = "SELECT COUNT(*) as total FROM staff WHERE position = 'Teacher' AND status = 'Active'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_teachers = $row['total'] ?? 0;

    // Pending mark approvals
    $query = "SELECT COUNT(DISTINCT class_id) as total FROM marks 
              WHERE approval_status = 'Submitted' 
              AND exam_term_id IN (SELECT id FROM exam_terms WHERE is_current = 1)";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $pending_results = $row['total'] ?? 0;

    // Timetable status
    $query = "SELECT status FROM timetable_versions WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $timetable_status = $row ? ucfirst($row['status']) : 'Not Generated';

    // Performance rate (based on completed assessments)
    $query = "SELECT 
                ROUND(
                    (COUNT(CASE WHEN approval_status = 'Approved' THEN 1 END) * 100.0 / 
                    NULLIF(COUNT(*), 0)
                ), 1
              ) as rate 
              FROM marks 
              WHERE exam_term_id IN (SELECT id FROM exam_terms WHERE is_current = 1)";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $performance_rate = $row['rate'] ?? 0;

} catch(Exception $exception) {
    error_log("Database error in dashboard metrics: " . $exception->getMessage());
}

$recent_activities = [];
$pending_tasks = [];
$upcoming_events = [];

try {
    // Recent activities
    $query = "SELECT activity_type, description, created_at FROM system_activities 
              WHERE user_role = 'AcademicTeacher' 
              ORDER BY created_at DESC LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recent_activities[] = $row;
    }

    // Pending tasks for academic teacher
    $query = "SELECT 'results_approval' as task_type, COUNT(*) as count 
              FROM marks WHERE approval_status = 'Submitted'
              UNION ALL
              SELECT 'timetable_review' as task_type, COUNT(*) as count 
              FROM timetable_versions WHERE status = 'Draft'
              UNION ALL
              SELECT 'grading_config' as task_type, COUNT(*) as count 
              FROM grading_systems WHERE needs_review = 1 AND is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row['count'] > 0) {
            $pending_tasks[] = $row;
        }
    }

    // Upcoming academic events
    $query = "SELECT event_name, event_date, description, event_type 
              FROM academic_events 
              WHERE event_date >= CURDATE() 
              AND status = 'Scheduled'
              ORDER BY event_date ASC LIMIT 4";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $upcoming_events[] = $row;
    }

} catch(Exception $exception) {
    error_log("Database error in dashboard activities: " . $exception->getMessage());
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Teacher Dashboard - School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --info-color: #2980b9;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --academic-color: #8e44ad;
            --timetable-color: #16a085;
            --grading-color: #d35400;
            --purple: #8e44ad;
            --teal: #16a085;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            padding-top: 70px;
        }

        /* Enhanced Navigation */
        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            padding: 0.5rem 1rem;
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.2rem;
            color: white !important;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
            border-radius: 4px;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.15);
        }

        /* Enhanced Dropdown Styling */
        .dropdown:hover .dropdown-menu {
            display: block;
            margin-top: 0;
        }

        .dropdown-menu {
            background-color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 6px;
            min-width: 220px;
        }

        .dropdown-item {
            padding: 0.6rem 1.2rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        /* Distinct background colors for dropdown items */
        .dropdown-timetable { border-left-color: var(--timetable-color); }
        .dropdown-timetable:hover { background-color: rgba(22, 160, 133, 0.1); }

        .dropdown-grading { border-left-color: var(--grading-color); }
        .dropdown-grading:hover { background-color: rgba(211, 84, 0, 0.1); }

        .dropdown-results { border-left-color: var(--academic-color); }
        .dropdown-results:hover { background-color: rgba(142, 68, 173, 0.1); }

        .dropdown-analytics { border-left-color: var(--info-color); }
        .dropdown-analytics:hover { background-color: rgba(41, 128, 185, 0.1); }

        .dropdown-administrative { border-left-color: var(--success-color); }
        .dropdown-administrative:hover { background-color: rgba(39, 174, 96, 0.1); }

        .dropdown-item:hover {
            color: var(--primary-color);
            transform: translateX(5px);
        }

        /* Main Content */
        .main-content {
            padding: 2rem 1rem;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            transition: all 0.3s;
        }

        .card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #eaeaea;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        /* Stats Cards */
        .stat-card {
            border-left: 4px solid;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card-primary { border-left-color: var(--secondary-color); }
        .stat-card-success { border-left-color: var(--success-color); }
        .stat-card-info { border-left-color: var(--info-color); }
        .stat-card-warning { border-left-color: var(--warning-color); }
        .stat-card-academic { border-left-color: var(--academic-color); }
        .stat-card-timetable { border-left-color: var(--timetable-color); }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
        }

        /* Quick Actions */
        .action-btn {
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        /* Activity List */
        .activity-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .activity-item {
            padding: 0.75rem 1rem;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }

        .activity-item:hover {
            background-color: #f8f9fa;
            border-left-color: var(--secondary-color);
        }

        .activity-time {
            font-size: 0.75rem;
            color: #6c757d;
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }

        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color), var(--academic-color));
            color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Task Indicators */
        .task-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .task-high { background-color: var(--danger-color); }
        .task-medium { background-color: var(--warning-color); }
        .task-low { background-color: var(--info-color); }

        /* Progress Bars */
        .progress {
            height: 6px;
            margin-top: 5px;
        }

        /* Color utilities */
        .text-purple { color: var(--purple); }
        .text-teal { color: var(--teal); }
        .text-academic { color: var(--academic-color); }
        .text-timetable { color: var(--timetable-color); }

        .btn-academic { background-color: var(--academic-color); color: white; }
        .btn-timetable { background-color: var(--timetable-color); color: white; }
        .btn-grading { background-color: var(--grading-color); color: white; }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding-top: 60px;
                font-size: 0.9rem;
            }
            
            .main-content {
                padding: 1rem 0.5rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .action-btn {
                padding: 0.6rem 0.8rem;
                font-size: 0.85rem;
            }

            .dropdown:hover .dropdown-menu {
                display: none;
            }
        }

        @media (max-width: 576px) {
            body {
                font-size: 0.85rem;
            }
            
            .stat-number {
                font-size: 1.3rem;
            }
            
            .action-btn {
                font-size: 0.8rem;
                padding: 0.5rem 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Enhanced Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="academic_dashboard.php">
                <i class="fas fa-user-graduate me-2"></i>
                Academic Teacher Dashboard
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAcademic">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarAcademic">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="academic_dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    
                    <!-- Timetable Management -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="timetableDropdown">
                            <i class="fas fa-calendar-alt me-1"></i>Timetable Management
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item dropdown-timetable" href="timetable_setup.php"><i class="fas fa-cog me-2"></i>Foundation Setup</a></li>
                            <li><a class="dropdown-item dropdown-timetable" href="generate_timetable.php"><i class="fas fa-magic me-2"></i>Auto Generation</a></li>
                            <li><a class="dropdown-item dropdown-timetable" href="adjust_timetable.php"><i class="fas fa-edit me-2"></i>Manual Adjustment</a></li>
                            <li><a class="dropdown-item dropdown-timetable" href="publish_timetable.php"><i class="fas fa-bullhorn me-2"></i>Publish Timetable</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item dropdown-timetable" href="timetable_reports.php"><i class="fas fa-chart-bar me-2"></i>Timetable Analytics</a></li>
                        </ul>
                    </li>
                    
                    <!-- Academic Integrity & Results -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="resultsDropdown">
                            <i class="fas fa-chart-line me-1"></i>Academic Integrity
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item dropdown-grading" href="grading_config.php"><i class="fas fa-sliders-h me-2"></i>Grading System Setup</a></li>
                            <li><a class="dropdown-item dropdown-results" href="results_oversight.php"><i class="fas fa-eye me-2"></i>Results Oversight</a></li>
                            <li><a class="dropdown-item dropdown-results" href="mark_approval.php"><i class="fas fa-clipboard-check me-2"></i>Mark Approval</a></li>
                            <li><a class="dropdown-item dropdown-results" href="mark_adjustment.php"><i class="fas fa-edit me-2"></i>Mark Adjustment</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item dropdown-results" href="final_processing.php"><i class="fas fa-lock me-2"></i>Final Result Processing</a></li>
                        </ul>
                    </li>
                    
                    <!-- Analytics & Reporting -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="analyticsDropdown">
                            <i class="fas fa-chart-pie me-1"></i>Analytics & Reports
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item dropdown-analytics" href="performance_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Performance Dashboard</a></li>
                            <li><a class="dropdown-item dropdown-analytics" href="student_reports.php"><i class="fas fa-user-graduate me-2"></i>Student Reports</a></li>
                            <li><a class="dropdown-item dropdown-analytics" href="class_analytics.php"><i class="fas fa-chalkboard me-2"></i>Class Analytics</a></li>
                            <li><a class="dropdown-item dropdown-analytics" href="institutional_reports.php"><i class="fas fa-university me-2"></i>Institutional Reports</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item dropdown-analytics" href="audit_trail.php"><i class="fas fa-history me-2"></i>Audit Trail</a></li>
                        </ul>
                    </li>
                    
                    <!-- Administrative -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown">
                            <i class="fas fa-cogs me-1"></i>Administrative
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item dropdown-administrative" href="subject_management.php"><i class="fas fa-book me-2"></i>Subject Management</a></li>
                            <li><a class="dropdown-item dropdown-administrative" href="teacher_management.php"><i class="fas fa-chalkboard-teacher me-2"></i>Teacher Allocation</a></li>
                            <li><a class="dropdown-item dropdown-administrative" href="class_management.php"><i class="fas fa-door-open me-2"></i>Class Management</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item dropdown-administrative" href="system_config.php"><i class="fas fa-sliders-h me-2"></i>System Configuration</a></li>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo htmlspecialchars($academic_teacher_name); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="academic_profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="academic_settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><a class="dropdown-item" href="notifications.php"><i class="fas fa-bell me-2"></i>Notifications</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container-fluid main-content">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h3 mb-2">Welcome, <?php echo htmlspecialchars($academic_teacher_name); ?></h1>
                    <p class="mb-0">Academic oversight dashboard - Monitor results, manage timetables, and generate institutional reports.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-light me-2" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                    <a href="export_academic_report.php" class="btn btn-outline-light">
                        <i class="fas fa-download me-1"></i> Export Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Key Academic Metrics -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                <div class="card stat-card stat-card-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-number"><?php echo $total_students; ?></div>
                                <div class="stat-label">Total Students</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x text-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                <div class="card stat-card stat-card-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-number"><?php echo $total_teachers; ?></div>
                                <div class="stat-label">Teaching Staff</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chalkboard-teacher fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                <div class="card stat-card stat-card-academic">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-number"><?php echo $pending_results; ?></div>
                                <div class="stat-label">Pending Results</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clipboard-list fa-2x text-purple"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                <div class="card stat-card stat-card-timetable">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-number"><?php echo $timetable_status; ?></div>
                                <div class="stat-label">Timetable Status</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-alt fa-2x text-teal"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                <div class="card stat-card stat-card-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-number"><?php echo $performance_rate; ?>%</div>
                                <div class="stat-label">Completion Rate</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                <div class="card stat-card stat-card-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-number"><?php echo count($pending_tasks); ?></div>
                                <div class="stat-label">Pending Tasks</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-tasks fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Overview -->
        <div class="row">
            <!-- Quick Actions -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Academic Quick Actions</h5>
                        <span class="badge bg-primary">Priority Tasks</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4 col-sm-6">
                                <a href="mark_approval.php" class="btn btn-academic w-100 action-btn">
                                    <i class="fas fa-clipboard-check me-2"></i>Approve Marks
                                    <?php if($pending_results > 0): ?>
                                        <span class="badge bg-danger ms-2"><?php echo $pending_results; ?></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <a href="generate_timetable.php" class="btn btn-timetable w-100 action-btn">
                                    <i class="fas fa-magic me-2"></i>Generate Timetable
                                </a>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <a href="grading_config.php" class="btn btn-grading w-100 action-btn">
                                    <i class="fas fa-sliders-h me-2"></i>Grading Setup
                                </a>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <a href="final_processing.php" class="btn btn-success w-100 action-btn">
                                    <i class="fas fa-lock me-2"></i>Lock Results
                                </a>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <a href="performance_dashboard.php" class="btn btn-info w-100 action-btn">
                                    <i class="fas fa-chart-bar me-2"></i>View Analytics
                                </a>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <a href="student_reports.php" class="btn btn-warning w-100 action-btn">
                                    <i class="fas fa-file-pdf me-2"></i>Generate Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Tasks -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Pending Academic Tasks</h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-list">
                            <?php if (!empty($pending_tasks)): ?>
                                <?php foreach($pending_tasks as $task): ?>
                                    <div class="activity-item">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <div>
                                                <span class="task-indicator task-high"></span>
                                                <h6 class="mb-1 d-inline"><?php echo ucwords(str_replace('_', ' ', $task['task_type'])); ?></h6>
                                            </div>
                                            <div>
                                                <span class="badge bg-primary"><?php echo $task['count']; ?> pending</span>
                                                <a href="<?php echo getTaskLink($task['task_type']); ?>" class="btn btn-sm btn-outline-primary ms-2">Action</a>
                                            </div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: <?php echo min($task['count'] * 10, 100); ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="activity-item">
                                    <p class="mb-1 text-muted">No pending tasks - all caught up!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Academic Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-list">
                            <?php if (!empty($recent_activities)): ?>
                                <?php foreach($recent_activities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo ucfirst(str_replace('_', ' ', $activity['activity_type'])); ?></h6>
                                            <small class="activity-time"><?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($activity['description']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="activity-item">
                                    <p class="mb-1 text-muted">No recent activities</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Overview & Upcoming -->
            <div class="col-lg-4 mb-4">
                <!-- Academic Progress -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Academic Progress</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="academicProgressChart"></canvas>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Results Approval</span>
                                <span><?php echo $performance_rate; ?>%</span>
                            </div>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-success" style="width: <?php echo $performance_rate; ?>%"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Timetable Completion</span>
                                <span><?php echo $timetable_status === 'Published' ? '100%' : '65%'; ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: <?php echo $timetable_status === 'Published' ? '100' : '65'; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Academic Events -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Upcoming Academic Events</h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-list">
                            <?php if (!empty($upcoming_events)): ?>
                                <?php foreach($upcoming_events as $event): ?>
                                    <div class="activity-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($event['event_name']); ?></h6>
                                            <small class="activity-time"><?php echo date('M j', strtotime($event['event_date'])); ?></small>
                                        </div>
                                        <p class="mb-1 small"><?php echo htmlspecialchars($event['description']); ?></p>
                                        <span class="badge bg-secondary"><?php echo ucfirst($event['event_type']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="activity-item">
                                    <p class="mb-1 text-muted">No upcoming academic events</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="text-academic">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <div class="h5 mb-1"><?php echo $performance_rate; ?>%</div>
                                    <small>Approval Rate</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-timetable">
                                    <i class="fas fa-clock fa-2x mb-2"></i>
                                    <div class="h5 mb-1"><?php echo $pending_results; ?></div>
                                    <small>Awaiting Review</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Academic Progress Chart
            const ctx = document.getElementById('academicProgressChart').getContext('2d');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Approved', 'Pending Review', 'Not Submitted'],
                        datasets: [{
                            data: [<?php echo $performance_rate; ?>, <?php echo $pending_results * 5; ?>, <?php echo max(0, 100 - $performance_rate - $pending_results * 5); ?>],
                            backgroundColor: ['#27ae60', '#f39c12', '#e74c3c'],
                            hoverBackgroundColor: ['#219653', '#e67e22', '#d63031'],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                }
                            }
                        },
                        cutout: '60%',
                    },
                });
            }
        });

        function refreshDashboard() {
            // Show loading state
            const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
            const originalText = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...';
            refreshBtn.disabled = true;
            
            // Reload the page to get fresh data
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }

        // Enhanced dropdown functionality for mobile
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(dropdown => {
                dropdown.addEventListener('mouseenter', function() {
                    if (window.innerWidth > 768) {
                        const menu = this.querySelector('.dropdown-menu');
                        if (menu) {
                            menu.style.display = 'block';
                        }
                    }
                });
                
                dropdown.addEventListener('mouseleave', function() {
                    if (window.innerWidth > 768) {
                        const menu = this.querySelector('.dropdown-menu');
                        if (menu) {
                            menu.style.display = 'none';
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
// Helper function to get task links
function getTaskLink($taskType) {
    $links = [
        'results_approval' => 'mark_approval.php',
        'timetable_review' => 'adjust_timetable.php',
        'grading_config' => 'grading_config.php'
    ];
    return $links[$taskType] ?? '#';
}
?>