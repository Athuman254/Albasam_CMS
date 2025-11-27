<?php
session_start();
require_once 'db.php';

// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'principal') {
//     header("Location: login.php");
//     exit();
// }

$total_students = 0;
$total_teachers = 0;
$pending_approvals = 0;
$performance_rate = 0;

try {
    // Total active students
    $query = "SELECT COUNT(*) as total FROM students WHERE status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_students = $row['total'];

    // Total active teachers
    $query = "SELECT COUNT(*) as total FROM staff WHERE status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_teachers = $row['total'];

    // Pending approvals (transfer + leave requests)
    $query = "SELECT COUNT(*) as total FROM transfer_requests WHERE status = 'pending' 
              UNION ALL 
              SELECT COUNT(*) as total FROM leave_requests WHERE status = 'pending'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $pending_approvals = 0;
    while ($row = $result->fetch_assoc()) {
        $pending_approvals += $row['total'];
    }

    // Performance rate (attendance percentage)
    $query = "SELECT ROUND(AVG(attendance_percentage), 1) as rate FROM class_attendance WHERE month = MONTH(CURRENT_DATE())";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $performance_rate = $row['rate'] ?: 84.5;

} catch(Exception $exception) {
    error_log("Database error: " . $exception->getMessage());
}

$recent_activities = [];
try {
    $query = "SELECT activity_type, description, created_at FROM system_activities 
              ORDER BY created_at DESC LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $recent_activities = [];
    while ($row = $result->fetch_assoc()) {
        $recent_activities[] = $row;
    }
} catch(Exception $exception) {
    error_log("Database error: " . $exception->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal Dashboard - School Management System</title>
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
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            padding-top: 70px;
        }

        /* Fixed Navigation */
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
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .dropdown-menu {
            background-color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 6px;
        }

        .dropdown-item {
            padding: 0.6rem 1.2rem;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--primary-color);
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
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

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
    <!-- Fixed Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-school me-2"></i>
                School Management System
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPrincipal">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarPrincipal">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="principal_dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-users-cog me-1"></i>Staff Management
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="add_staff.php"><i class="fas fa-user-plus me-2"></i>Add staff</a></li>
                            <li><a class="dropdown-item" href="deactivate_staff.php"><i class="fas fa-user-plus me-2"></i>Deactivate staff</a></li>
                            <li><a class="dropdown-item" href="subject_allocation.php"><i class="fas fa-book me-2"></i>Subject Allocation</a></li>
                            <li><a class="dropdown-item" href="class_teacher_allocation.php"><i class="fas fa-chalkboard-teacher me-2"></i>Class Teacher Allocation</a></li>
                            <li><a class="dropdown-item" href="create_classes.php"><i class="fas fa-door-open me-2"></i>Create Classes</a></li>
                            <li><a class="dropdown-item" href="add_subject.php"><i class="fas fa-door-open me-2"></i>subject</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="studentDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-graduate me-1"></i>Student Administration
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="student_registration.php"><i class="fas fa-user-plus me-2"></i>Student Registration</a></li>
                            <li><a class="dropdown-item" href="student_transfer.php"><i class="fas fa-exchange-alt me-2"></i>Student Transfer</a></li>
                            <li><a class="dropdown-item" href="student_records.php"><i class="fas fa-file-alt me-2"></i>Student Records</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="oversightDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-line me-1"></i>Oversight & Analytics
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="reports.php"><i class="fas fa-chart-pie me-2"></i>Dashboard Overview</a></li>
                            <li><a class="dropdown-item" href="report_review.php"><i class="fas fa-file-alt me-2"></i>Report Review</a></li>
                            <li><a class="dropdown-item" href="approvals.php"><i class="fas fa-clipboard-check me-2"></i>Approval Workflows</a></li>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
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
                    <h1 class="h3 mb-2">Welcome back, <?php echo $_SESSION['Full_name']; ?></h1>
                    <p class="mb-0">Here's what's happening at your school today.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-light me-2" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                    <a href="export_report.php" class="btn btn-outline-light">
                        <i class="fas fa-download me-1"></i> Export Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
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

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card stat-card-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-number"><?php echo $total_teachers; ?></div>
                                <div class="stat-label">Total Teachers</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chalkboard-teacher fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card stat-card-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-number"><?php echo $pending_approvals; ?></div>
                                <div class="stat-label">Pending Approvals</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clipboard-list fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card stat-card-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stat-number"><?php echo $performance_rate; ?>%</div>
                                <div class="stat-label">Performance Rate</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Recent Activity -->
        <div class="row">
            <!-- Quick Actions -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3 col-sm-6">
                                <a href="add_staff.php" class="btn btn-primary w-100 action-btn">
                                    <i class="fas fa-user-plus me-2"></i>Add staff
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="student_registration.php" class="btn btn-success w-100 action-btn">
                                    <i class="fas fa-user-graduate me-2"></i>Register Student
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="approvals.php" class="btn btn-warning w-100 action-btn">
                                    <i class="fas fa-clipboard-check me-2"></i>Pending Approvals
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="reports.php" class="btn btn-info w-100 action-btn">
                                    <i class="fas fa-chart-bar me-2"></i>View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Activity</h5>
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
                                        <p class="mb-1"><?php echo $activity['description']; ?></p>
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

            <!-- System Overview -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">System Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="systemOverviewChart"></canvas>
                        </div>
                        <div class="mt-4 text-center">
                            <span class="me-3">
                                <i class="fas fa-circle text-primary me-1"></i> Active
                            </span>
                            <span class="me-3">
                                <i class="fas fa-circle text-success me-1"></i> Completed
                            </span>
                            <span>
                                <i class="fas fa-circle text-info me-1"></i> Pending
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Upcoming Events</h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-list">
                            <?php
try {
    $query = "SELECT event_name, event_date, description FROM events 
             WHERE event_date >= CURDATE() 
             ORDER BY event_date ASC LIMIT 3";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $upcoming_events = [];
    while ($row = $result->fetch_assoc()) {
        $upcoming_events[] = $row;
    }
    
    if (!empty($upcoming_events)) {
        foreach($upcoming_events as $event) {
            echo '<div class="activity-item">';
            echo '<div class="d-flex w-100 justify-content-between">';
            echo '<h6 class="mb-1">' . htmlspecialchars($event['event_name']) . '</h6>';
            echo '<small class="activity-time">' . date('M j, Y', strtotime($event['event_date'])) . '</small>';
            echo '</div>';
            echo '<p class="mb-1">' . htmlspecialchars($event['description']) . '</p>';
            echo '</div>';
        }
    } else {
        echo '<div class="activity-item">';
        echo '<p class="mb-1 text-muted">No upcoming events</p>';
        echo '</div>';
    }
} catch(Exception $e) {
    echo '<div class="activity-item">';
    echo '<p class="mb-1 text-muted">No upcoming events</p>';
    echo '</div>';
}
?>
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
            // System Overview Chart
            const ctx = document.getElementById('systemOverviewChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Active Tasks', 'Completed', 'Pending'],
                    datasets: [{
                        data: [55, 30, 15],
                        backgroundColor: ['#3498db', '#27ae60', '#2980b9'],
                        hoverBackgroundColor: ['#2980b9', '#219653', '#1c6ea4'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    cutout: '70%',
                },
            });
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
    </script>
</body>
</html>