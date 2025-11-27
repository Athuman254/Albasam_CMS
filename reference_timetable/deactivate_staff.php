<?php
session_start();
require_once "db.php";

// ======================== SECURITY & ACCESS CONTROL ========================
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'principal') {
//     header("Location: login.php");
//     exit();
// }

// ======================== HANDLE STAFF SEARCH ========================
$search_results = [];
$search_performed = false;
$search_staff_no = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_staff'])) {
    $search_staff_no = trim($_POST['staff_no']);
    $search_performed = true;
    
    if (!empty($search_staff_no)) {
        $search_stmt = $conn->prepare("SELECT * FROM staff WHERE staff_no = ?");
        $search_stmt->bind_param("s", $search_staff_no);
        $search_stmt->execute();
        $search_result = $search_stmt->get_result();
        
        if ($search_result->num_rows > 0) {
            $search_results = $search_result->fetch_all(MYSQLI_ASSOC);
        }
        $search_stmt->close();
    }
}

// ======================== HANDLE STAFF DEACTIVATION ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate_staff'])) {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $staff_id = intval($_POST['staff_id']);
        $deactivation_reason = trim($_POST['deactivation_reason']);
        $effective_date = $_POST['effective_date'] ?: date('Y-m-d');

        // Validate required fields
        if (empty($staff_id) || empty($deactivation_reason)) {
            throw new Exception("Please provide a deactivation reason");
        }

        // Check if staff exists and is active
        $check_stmt = $conn->prepare("SELECT id, staff_no, full_name, status FROM staff WHERE id = ?");
        $check_stmt->bind_param("i", $staff_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Staff member not found");
        }
        
        $staff = $result->fetch_assoc();
        
        if ($staff['status'] === 'Inactive') {
            throw new Exception("Staff member is already inactive");
        }

        // Update staff status to inactive
        $update_stmt = $conn->prepare("UPDATE staff SET status = 'Inactive' WHERE id = ?");
        $update_stmt->bind_param("i", $staff_id);
        
        if ($update_stmt->execute()) {
            // Log the deactivation
            $log_stmt = $conn->prepare("INSERT INTO staff_deactivation_log (staff_id, deactivation_reason, effective_date, deactivated_by) VALUES (?, ?, ?, ?)");
            $deactivated_by = $_SESSION['user_id'] ?? 1; // Default to admin if not set
            $log_stmt->bind_param("issi", $staff_id, $deactivation_reason, $effective_date, $deactivated_by);
            $log_stmt->execute();
            $log_stmt->close();

            $response['success'] = true;
            $response['message'] = "Staff member " . $staff['full_name'] . " (ID: " . $staff['staff_no'] . ") has been deactivated successfully";
        } else {
            throw new Exception("Failed to deactivate staff member");
        }
        
        $update_stmt->close();
        $check_stmt->close();
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        // For non-AJAX requests, store message in session and redirect
        if ($response['success']) {
            $_SESSION['success_message'] = $response['message'];
        } else {
            $_SESSION['error_message'] = $response['message'];
        }
        header("Location: deactivate_staff.php");
        exit;
    }
}

// ======================== HANDLE STAFF REACTIVATION ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reactivate_staff'])) {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $staff_id = intval($_POST['staff_id']);
        $reactivation_reason = trim($_POST['reactivation_reason']);

        // Check if staff exists and is inactive
        $check_stmt = $conn->prepare("SELECT id, staff_no, full_name, status FROM staff WHERE id = ?");
        $check_stmt->bind_param("i", $staff_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Staff member not found");
        }
        
        $staff = $result->fetch_assoc();
        
        if ($staff['status'] === 'Active') {
            throw new Exception("Staff member is already active");
        }

        // Update staff status to active
        $update_stmt = $conn->prepare("UPDATE staff SET status = 'Active' WHERE id = ?");
        $update_stmt->bind_param("i", $staff_id);
        
        if ($update_stmt->execute()) {
            // Log the reactivation
            $log_stmt = $conn->prepare("INSERT INTO staff_reactivation_log (staff_id, reactivation_reason, reactivated_by) VALUES (?, ?, ?)");
            $reactivated_by = $_SESSION['user_id'] ?? 1; // Default to admin if not set
            $log_stmt->bind_param("isi", $staff_id, $reactivation_reason, $reactivated_by);
            $log_stmt->execute();
            $log_stmt->close();

            $response['success'] = true;
            $response['message'] = "Staff member " . $staff['full_name'] . " (ID: " . $staff['staff_no'] . ") has been reactivated successfully";
        } else {
            throw new Exception("Failed to reactivate staff member");
        }
        
        $update_stmt->close();
        $check_stmt->close();
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        // For non-AJAX requests
        if ($response['success']) {
            $_SESSION['success_message'] = $response['message'];
        } else {
            $_SESSION['error_message'] = $response['message'];
        }
        header("Location: deactivate_staff.php");
        exit;
    }
}

// ======================== STAFF FETCH (for listing) ========================
$active_staff_result = $conn->query("SELECT * FROM staff WHERE status = 'Active' ORDER BY full_name ASC");
$inactive_staff_result = $conn->query("SELECT * FROM staff WHERE status = 'Inactive' ORDER BY full_name ASC");

// ======================== AJAX GET STAFF DETAILS ========================
if (isset($_GET['get_staff_details'])) {
    $id = intval($_GET['get_staff_details']);
    $stmt = $conn->prepare("SELECT * FROM staff WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $staff = $stmt->get_result()->fetch_assoc();
    
    if ($staff) {
        echo json_encode([
            'success' => true,
            'staff' => $staff
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Staff member not found'
        ]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - School System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #ecf0f1;
            --dark: #34495e;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 70px;
        }
        
        /* Fixed Navigation */
        .navbar {
            background-color: var(--primary);
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
            color: var(--primary);
        }
        
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 30px auto;
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            position: relative;
        }
        
        .header-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="rgba(255,255,255,0.1)"><circle cx="50" cy="50" r="2"/></svg>');
        }
        
        .content-section {
            padding: 2rem;
        }
        
        .section-title {
            color: var(--primary);
            border-bottom: 3px solid var(--secondary);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #c0392b);
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #219653);
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #e67e22);
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            color: white;
        }
        
        .required-field::after {
            content: " *";
            color: var(--danger);
        }
        
        .staff-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .staff-card:hover {
            transform: translateY(-5px);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .badge {
            font-size: 0.75em;
            padding: 0.5em 0.75em;
        }
        
        .action-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            border-left: 4px solid var(--secondary);
            margin-bottom: 2rem;
        }
        
        .staff-info-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--info);
        }
        
        .search-section {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #90caf9;
        }
        
        .search-result-card {
            border-left: 4px solid var(--warning);
            background: #fff8e1;
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 60px;
            }
            
            .main-container {
                margin: 15px;
                border-radius: 15px;
            }
            
            .header-section {
                padding: 1.5rem;
            }
            
            .content-section {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Fixed Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="principal_dashboard.php">
                <i class="fas fa-school me-2"></i>
                School Management System
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPrincipal" aria-controls="navbarPrincipal" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarPrincipal">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="principal_dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-users-cog me-1"></i>Staff Management
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="staffDropdown">
                            <li><a class="dropdown-item" href="add_staff.php"><i class="fas fa-user-plus me-2"></i>Add Staff</a></li>
                            <li><a class="dropdown-item active" href="deactivate_staff.php"><i class="fas fa-user-minus me-2"></i>Deactivate Staff</a></li>
                            <li><a class="dropdown-item" href="subject_allocation.php"><i class="fas fa-book me-2"></i>Subject Allocation</a></li>
                            <li><a class="dropdown-item" href="class_teacher_allocation.php"><i class="fas fa-chalkboard-teacher me-2"></i>Class Teacher Allocation</a></li>
                            <li><a class="dropdown-item" href="create_classes.php"><i class="fas fa-door-open me-2"></i>Create Classes</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="studentDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-graduate me-1"></i>Student Administration
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="studentDropdown">
                            <li><a class="dropdown-item" href="student_registration.php"><i class="fas fa-user-plus me-2"></i>Student Registration</a></li>
                            <li><a class="dropdown-item" href="student_transfer.php"><i class="fas fa-exchange-alt me-2"></i>Student Transfer</a></li>
                            <li><a class="dropdown-item" href="student_records.php"><i class="fas fa-file-alt me-2"></i>Student Records</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="oversightDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-chart-line me-1"></i>Oversight & Analytics
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="oversightDropdown">
                            <li><a class="dropdown-item" href="reports.php"><i class="fas fa-chart-pie me-2"></i>Dashboard Overview</a></li>
                            <li><a class="dropdown-item" href="report_review.php"><i class="fas fa-file-alt me-2"></i>Report Review</a></li>
                            <li><a class="dropdown-item" href="approvals.php"><i class="fas fa-clipboard-check me-2"></i>Approval Workflows</a></li>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Principal'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
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
    
    <div class="container">
        <div class="main-container">
            <!-- Header Section -->
            <div class="header-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold mb-2"><i class="fas fa-user-cog me-3"></i>Staff Status Management</h1>
                        <p class="lead mb-0">Activate or deactivate staff members in the school system</p>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="content-section">
                <!-- Success/Error Messages -->
                <div id="messageContainer">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= $_SESSION['success_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= $_SESSION['error_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                </div>

                <!-- Staff Search Section -->
                <div class="search-section">
                    <h4 class="section-title"><i class="fas fa-search me-2"></i>Search Staff by Staff Number</h4>
                    <form method="POST" class="row g-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fas fa-id-card"></i>
                                </span>
                                <input type="text" class="form-control form-control-lg" name="staff_no" 
                                       placeholder="Enter Staff Number (e.g., STF001)" value="<?= htmlspecialchars($search_staff_no) ?>" 
                                       required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="search_staff" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-search me-2"></i>Search Staff
                            </button>
                        </div>
                    </form>
                    
                    <?php if ($search_performed): ?>
                        <div class="mt-4">
                            <?php if (!empty($search_results)): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Found <?= count($search_results) ?> staff member(s) with staff number: <strong><?= htmlspecialchars($search_staff_no) ?></strong>
                                </div>
                                
                                <?php foreach ($search_results as $staff): ?>
                                <div class="card search-result-card mb-3">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="card-title">
                                                    <?= htmlspecialchars($staff['full_name']) ?>
                                                    <span class="badge <?= $staff['status'] === 'Active' ? 'bg-success' : 'bg-danger' ?> ms-2">
                                                        <?= $staff['status'] ?>
                                                    </span>
                                                </h5>
                                                <p class="card-text mb-1">
                                                    <strong>Staff No:</strong> <?= $staff['staff_no'] ?> | 
                                                    <strong>Position:</strong> <?= $staff['position'] ?> | 
                                                    <strong>Department:</strong> <?= $staff['department'] ?: 'N/A' ?>
                                                </p>
                                                <p class="card-text mb-0">
                                                    <strong>Email:</strong> <?= $staff['email'] ?> | 
                                                    <strong>Phone:</strong> <?= $staff['phone'] ?: 'N/A' ?>
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <?php if ($staff['status'] === 'Active'): ?>
                                                    <button class="btn btn-danger deactivate-btn" 
                                                            data-staff-id="<?= $staff['id'] ?>" 
                                                            data-staff-name="<?= htmlspecialchars($staff['full_name']) ?>">
                                                        <i class="fas fa-user-slash me-1"></i>Deactivate
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-success reactivate-btn" 
                                                            data-staff-id="<?= $staff['id'] ?>" 
                                                            data-staff-name="<?= htmlspecialchars($staff['full_name']) ?>">
                                                        <i class="fas fa-user-check me-1"></i>Reactivate
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    No staff member found with staff number: <strong><?= htmlspecialchars($search_staff_no) ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Staff Action Section -->
                <div class="action-section">
                    <h4 class="section-title"><i class="fas fa-cogs me-2"></i>Staff Actions</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="staff-info-card">
                                <h6><i class="fas fa-users me-2"></i>Active Staff</h6>
                                <h3 class="text-success"><?= $active_staff_result->num_rows ?></h3>
                                <p class="text-muted mb-0">Currently active staff members</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="staff-info-card">
                                <h6><i class="fas fa-user-slash me-2"></i>Inactive Staff</h6>
                                <h3 class="text-danger"><?= $inactive_staff_result->num_rows ?></h3>
                                <p class="text-muted mb-0">Currently inactive staff members</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Staff List -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-user-check me-2 text-success"></i>Active Staff Members</h4>
                        <div>
                            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#activeStaffCollapse">
                                <i class="fas fa-chevron-down me-2"></i>Toggle View
                            </button>
                        </div>
                    </div>
                    <div class="collapse show" id="activeStaffCollapse">
                        <div class="card-body">
                            <?php if ($active_staff_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="activeStaffTable">
                                        <thead class="table-success">
                                            <tr>
                                                <th>Staff No</th>
                                                <th>Name</th>
                                                <th>Position</th>
                                                <th>Department</th>
                                                <th>Email</th>
                                                <th>Hire Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $active_staff_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?= $row['staff_no'] ?></strong></td>
                                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?= $row['position'] ?></span>
                                                </td>
                                                <td><?= $row['department'] ?: 'N/A' ?></td>
                                                <td><?= $row['email'] ?></td>
                                                <td><?= date('M j, Y', strtotime($row['hire_date'])) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger deactivate-btn" 
                                                            data-staff-id="<?= $row['id'] ?>" 
                                                            data-staff-name="<?= htmlspecialchars($row['full_name']) ?>">
                                                        <i class="fas fa-user-slash me-1"></i>Deactivate
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <h5>No Active Staff Members</h5>
                                    <p>All staff members are currently inactive.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Inactive Staff List -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-user-slash me-2 text-danger"></i>Inactive Staff Members</h4>
                        <div>
                            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#inactiveStaffCollapse">
                                <i class="fas fa-chevron-down me-2"></i>Toggle View
                            </button>
                        </div>
                    </div>
                    <div class="collapse show" id="inactiveStaffCollapse">
                        <div class="card-body">
                            <?php if ($inactive_staff_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="inactiveStaffTable">
                                        <thead class="table-danger">
                                            <tr>
                                                <th>Staff No</th>
                                                <th>Name</th>
                                                <th>Position</th>
                                                <th>Department</th>
                                                <th>Email</th>
                                                <th>Hire Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $inactive_staff_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?= $row['staff_no'] ?></strong></td>
                                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?= $row['position'] ?></span>
                                                </td>
                                                <td><?= $row['department'] ?: 'N/A' ?></td>
                                                <td><?= $row['email'] ?></td>
                                                <td><?= date('M j, Y', strtotime($row['hire_date'])) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-success reactivate-btn" 
                                                            data-staff-id="<?= $row['id'] ?>" 
                                                            data-staff-name="<?= htmlspecialchars($row['full_name']) ?>">
                                                        <i class="fas fa-user-check me-1"></i>Reactivate
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-user-slash fa-3x mb-3"></i>
                                    <h5>No Inactive Staff Members</h5>
                                    <p>All staff members are currently active.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deactivate Staff Modal -->
    <div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deactivateModalLabel"><i class="fas fa-user-slash me-2"></i>Deactivate Staff Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="deactivateForm">
                    <div class="modal-body">
                        <div id="deactivateStaffInfo" class="mb-3 p-3 bg-light rounded">
                            <!-- Staff info will be loaded here -->
                        </div>
                        <input type="hidden" name="staff_id" id="deactivateStaffId">
                        <div class="mb-3">
                            <label for="deactivation_reason" class="form-label required-field">Deactivation Reason</label>
                            <textarea class="form-control" id="deactivation_reason" name="deactivation_reason" rows="3" placeholder="Please provide the reason for deactivation..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="effective_date" class="form-label">Effective Date</label>
                            <input type="date" class="form-control" id="effective_date" name="effective_date" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="deactivate_staff" class="btn btn-danger">
                            <i class="fas fa-user-slash me-2"></i>Deactivate Staff
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reactivate Staff Modal -->
    <div class="modal fade" id="reactivateModal" tabindex="-1" aria-labelledby="reactivateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reactivateModalLabel"><i class="fas fa-user-check me-2"></i>Reactivate Staff Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="reactivateForm">
                    <div class="modal-body">
                        <div id="reactivateStaffInfo" class="mb-3 p-3 bg-light rounded">
                            <!-- Staff info will be loaded here -->
                        </div>
                        <input type="hidden" name="staff_id" id="reactivateStaffId">
                        <div class="mb-3">
                            <label for="reactivation_reason" class="form-label required-field">Reactivation Reason</label>
                            <textarea class="form-control" id="reactivation_reason" name="reactivation_reason" rows="3" placeholder="Please provide the reason for reactivation..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="reactivate_staff" class="btn btn-success">
                            <i class="fas fa-user-check me-2"></i>Reactivate Staff
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Set today's date as default for effective date
            document.getElementById('effective_date').value = new Date().toISOString().split('T')[0];
            
            // Initialize search functionality for tables
            initializeTableSearch();
        });

        // Deactivate staff button handler
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('deactivate-btn') || e.target.closest('.deactivate-btn')) {
                const btn = e.target.classList.contains('deactivate-btn') ? e.target : e.target.closest('.deactivate-btn');
                const staffId = btn.getAttribute('data-staff-id');
                const staffName = btn.getAttribute('data-staff-name');
                
                // Set staff info in modal
                document.getElementById('deactivateStaffInfo').innerHTML = `
                    <h6>Staff Information</h6>
                    <p class="mb-1"><strong>Name:</strong> ${staffName}</p>
                    <p class="mb-0"><strong>Staff ID:</strong> ${staffId}</p>
                `;
                document.getElementById('deactivateStaffId').value = staffId;
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('deactivateModal'));
                modal.show();
            }
        });

        // Reactivate staff button handler
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('reactivate-btn') || e.target.closest('.reactivate-btn')) {
                const btn = e.target.classList.contains('reactivate-btn') ? e.target : e.target.closest('.reactivate-btn');
                const staffId = btn.getAttribute('data-staff-id');
                const staffName = btn.getAttribute('data-staff-name');
                
                // Set staff info in modal
                document.getElementById('reactivateStaffInfo').innerHTML = `
                    <h6>Staff Information</h6>
                    <p class="mb-1"><strong>Name:</strong> ${staffName}</p>
                    <p class="mb-0"><strong>Staff ID:</strong> ${staffId}</p>
                `;
                document.getElementById('reactivateStaffId').value = staffId;
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('reactivateModal'));
                modal.show();
            }
        });

        // Deactivate form submission
        document.getElementById('deactivateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            submitBtn.disabled = true;
            
            const formData = new FormData(this);
            formData.append('ajax', 'true');
            
            fetch('deactivate_staff.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageContainer = document.getElementById('messageContainer');
                if (data.success) {
                    messageContainer.innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    // Close modal and reload page after 2 seconds
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deactivateModal'));
                    modal.hide();
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    messageContainer.innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const messageContainer = document.getElementById('messageContainer');
                messageContainer.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        An error occurred. Please try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Reactivate form submission
        document.getElementById('reactivateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            submitBtn.disabled = true;
            
            const formData = new FormData(this);
            formData.append('ajax', 'true');
            
            fetch('deactivate_staff.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageContainer = document.getElementById('messageContainer');
                if (data.success) {
                    messageContainer.innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    // Close modal and reload page after 2 seconds
                    const modal = bootstrap.Modal.getInstance(document.getElementById('reactivateModal'));
                    modal.hide();
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    messageContainer.innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const messageContainer = document.getElementById('messageContainer');
                messageContainer.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        An error occurred. Please try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Initialize table search functionality
        function initializeTableSearch() {
            // Active staff search
            const activeSearch = document.createElement('input');
            activeSearch.type = 'text';
            activeSearch.className = 'form-control mb-3';
            activeSearch.placeholder = 'Search active staff by name, staff no, or department...';
            
            const activeTable = document.getElementById('activeStaffTable');
            if (activeTable) {
                activeTable.parentNode.insertBefore(activeSearch, activeTable);
                
                activeSearch.addEventListener('input', function() {
                    const term = this.value.toLowerCase();
                    const rows = activeTable.querySelectorAll('tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(term) ? '' : 'none';
                    });
                });
            }
            
            // Inactive staff search
            const inactiveSearch = document.createElement('input');
            inactiveSearch.type = 'text';
            inactiveSearch.className = 'form-control mb-3';
            inactiveSearch.placeholder = 'Search inactive staff by name, staff no, or department...';
            
            const inactiveTable = document.getElementById('inactiveStaffTable');
            if (inactiveTable) {
                inactiveTable.parentNode.insertBefore(inactiveSearch, inactiveTable);
                
                inactiveSearch.addEventListener('input', function() {
                    const term = this.value.toLowerCase();
                    const rows = inactiveTable.querySelectorAll('tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(term) ? '' : 'none';
                    });
                });
            }
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>