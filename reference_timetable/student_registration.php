<?php
require_once 'db.php';
require_once 'promotion.php';
require_once 'kenyan_locations.php'; 

// Initialize database connection from db.php
global $conn;

// Initialize models and controllers
$studentController = new StudentController($conn);
$promotionController = new PromotionController($conn);
$classModel = new ClassModel($conn);
$studentModel = new StudentModel($conn);

// Handle form submissions
$message = '';
$message_type = '';
$needs_confirmation = false;
$promotion_preview = [];

if ($_POST) {
    // Sanitize all POST data
    foreach ($_POST as $key => $value) {
        if (is_array($value)) {
            $_POST[$key] = array_map(function($val) {
                return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
            }, $value);
        } else {
            $_POST[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
    }
    
    if (isset($_POST['register_student'])) {
        $result = $studentController->registerStudent($_POST);
        if ($result['success']) {
            $message = "Student registered successfully! Admission Number: " . $result['admission_number'] . 
                      ". Temporary password: " . $result['temp_password'] . " (Please change on first login)";
            $message_type = 'success';
        } else {
            $message = "Error: " . $result['message'];
            $message_type = 'danger';
        }
    } elseif (isset($_POST['process_promotion'])) {
        $confirmation = isset($_POST['confirm_promotion']);
        $result = $promotionController->processAnnualPromotion($confirmation);
        
        if (isset($result['needs_confirmation']) && $result['needs_confirmation']) {
            $needs_confirmation = true;
            $promotion_preview = $result['preview'];
            $message = "Please review the promotion preview below and confirm to proceed";
            $message_type = 'warning';
        } else {
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'danger';
        }
    } elseif (isset($_POST['mark_repetition'])) {
        $student_id = $_POST['student_id'] ?? null;
        if ($student_id && $studentController->markStudentForRepetition($student_id)) {
            $message = "Student marked for repetition";
            $message_type = 'success';
        } else {
            $message = "Failed to mark student for repetition";
            $message_type = 'danger';
        }
    }
}

// Get current page for student listing
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Get data for display
$students_count = $studentModel->countActiveStudents();
$classes_count = $classModel->countActiveClasses();
$classes = $classModel->getAllClasses();
$students_result = $studentController->getAllStudents($current_page);
$students = $students_result['students'];
$total_pages = $students_result['total_pages'];

// Kenyan locations data is now loaded from kenyan_locations.php
// $kenyan_locations variable is available from the required file
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-card {
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .form-section h4 {
            color: #007bff;
            margin-bottom: 15px;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
        }
        .pagination {
            justify-content: center;
        }
        .preview-section {
            max-height: 400px;
            overflow-y: auto;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        
        /* Enhanced Navigation Styles */
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-nav .nav-link {
            color: #fff !important;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            transform: translateY(-1px);
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 0.5rem;
        }
        
        .dropdown-item {
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            border-radius: 0.25rem;
            margin: 0.1rem 0.5rem;
            width: auto;
        }
        
        .dropdown-item:hover {
            background-color: #007bff;
            color: white;
            transform: translateX(5px);
        }
        
        .dropdown-toggle::after {
            transition: transform 0.3s ease;
        }
        
        .dropdown:hover .dropdown-toggle::after {
            transform: rotate(180deg);
        }
        
        /* Location Dropdown Styles */
        .location-dropdowns .dropdown-menu {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .dropdown-menu .dropdown-item {
            position: relative;
        }
        
        .dropdown-menu .dropdown-item::after {
            content: "→";
            position: absolute;
            right: 15px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .dropdown-menu .dropdown-item:hover::after {
            opacity: 1;
        }
        
        /* Tab Styles */
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link:hover {
            color: #007bff;
            background-color: rgba(0,123,255,0.1);
        }
        
        .nav-tabs .nav-link.active {
            color: #007bff;
            border-bottom: 3px solid #007bff;
            background-color: transparent;
        }
        
        /* Button Styles */
        .btn {
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        /* Table Styles */
        .table-hover tbody tr:hover {
            background-color: rgba(0,123,255,0.05);
        }
        
        /* Modal Styles */
        .modal-content {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        /* Alert Styles */
        .alert {
            border: none;
            border-radius: 0.75rem;
        }
        
        /* Location Dropdown Active State */
        .btn-success {
            background-color: #198754 !important;
            border-color: #198754 !important;
        }
        
        /* Simplified Location Styles */
        .location-selects select:disabled {
            background-color: #e9ecef;
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <!-- Enhanced Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-graduation-cap me-2"></i>
                School Management System
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="studentsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-users me-1"></i>
                            Students
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#register" onclick="switchToRegistrationTab()">
                                <i class="fas fa-user-plus me-2"></i>Register Student
                            </a></li>
                            <li><a class="dropdown-item" href="#students" onclick="switchToStudentsTab()">
                                <i class="fas fa-list me-2"></i>View Students
                            </a></li>
                            <li><a class="dropdown-item" href="#promotion" onclick="switchToPromotionTab()">
                                <i class="fas fa-chart-line me-2"></i>Student Promotion
                            </a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-bar me-1"></i>
                            Reports
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-file-alt me-2"></i>Student Reports
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-chart-pie me-2"></i>Analytics
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-print me-2"></i>Print Reports
                            </a></li>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-user me-2"></i>Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Display Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?> me-2"></i>
                    <div><?php echo $message; ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Dashboard -->
        <div class="row mb-4" id="dashboard">
            <div class="col-md-4">
                <div class="card dashboard-card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Students</h5>
                                <h2 class="card-text"><?php echo $students_count; ?></h2>
                                <p class="card-text">Active Students</p>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Classes</h5>
                                <h2 class="card-text"><?php echo $classes_count; ?></h2>
                                <p class="card-text">Active Classes</p>
                            </div>
                            <i class="fas fa-chalkboard-teacher fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Current Month</h5>
                                <h2 class="card-text"><?php echo date('F'); ?></h2>
                                <p class="card-text"><?php echo date('Y'); ?></p>
                            </div>
                            <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">
                    <i class="fas fa-user-plus me-2"></i>Student Registration
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button" role="tab">
                    <i class="fas fa-list me-2"></i>View Students
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="promotion-tab" data-bs-toggle="tab" data-bs-target="#promotion" type="button" role="tab">
                    <i class="fas fa-chart-line me-2"></i>Student Promotion
                </button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <!-- Student Registration Tab -->
            <div class="tab-pane fade show active" id="register" role="tabpanel">
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="fas fa-user-plus me-2"></i>Student Registration</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="register_student" value="1">
                            
                            <!-- Personal Information -->
                            <div class="form-section">
                                <h4><i class="fas fa-user me-2"></i>Personal Information</h4>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-field">Full Name</label>
                                        <input type="text" class="form-control" name="full_name" required 
                                               value="<?php echo $_POST['full_name'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-field">Gender</label>
                                        <select class="form-control" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male" <?php echo (($_POST['gender'] ?? '') == 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo (($_POST['gender'] ?? '') == 'Female') ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-field">Date of Birth</label>
                                        <input type="date" class="form-control" name="date_of_birth" required
                                               value="<?php echo $_POST['date_of_birth'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Birth Certificate Number</label>
                                        <input type="text" class="form-control" name="birth_certificate_number"
                                               value="<?php echo $_POST['birth_certificate_number'] ?? ''; ?>">
                                    </div>
                                </div>
                                
                                <!-- SIMPLIFIED Location Dropdowns - Using Regular Select Elements -->
                                <div class="row location-selects">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">County</label>
                                        <select class="form-control" name="county" id="countySelect" onchange="onCountyChange(this.value)">
                                            <option value="">Select County</option>
                                            <?php foreach ($kenyan_locations as $county => $subcounties): ?>
                                                <option value="<?php echo htmlspecialchars($county); ?>" 
                                                    <?php echo (($_POST['county'] ?? '') == $county) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($county); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Sub-County</label>
                                        <select class="form-control" name="sub_county" id="subCountySelect" onchange="onSubCountyChange(this.value)" disabled>
                                            <option value="">Select Sub-County</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Ward</label>
                                        <select class="form-control" name="ward" id="wardSelect" disabled>
                                            <option value="">Select Ward</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control" name="address" rows="2"><?php echo $_POST['address'] ?? ''; ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Information -->
                            <div class="form-section">
                                <h4><i class="fas fa-graduation-cap me-2"></i>Academic Information</h4>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-field">Class</label>
                                        <select class="form-control" name="class_id" required>
                                            <option value="">Select Class</option>
                                            <?php 
                                            $classes->data_seek(0); // Reset pointer
                                            while ($class = $classes->fetch_assoc()): 
                                                $selected = (($_POST['class_id'] ?? '') == $class['id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?php echo $class['id']; ?>" <?php echo $selected; ?>>
                                                    <?php echo $class['class_name'] . ' - ' . $class['level'] . ' ' . $class['stream']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Stream</label>
                                        <select class="form-control" name="stream">
                                            <option value="">Select Stream</option>
                                            <option value="Science" <?php echo (($_POST['stream'] ?? '') == 'Science') ? 'selected' : ''; ?>>Science</option>
                                            <option value="Arts" <?php echo (($_POST['stream'] ?? '') == 'Arts') ? 'selected' : ''; ?>>Arts</option>
                                            <option value="Business" <?php echo (($_POST['stream'] ?? '') == 'Business') ? 'selected' : ''; ?>>Business</option>
                                            <option value="Technical" <?php echo (($_POST['stream'] ?? '') == 'Technical') ? 'selected' : ''; ?>>Technical</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Previous School</label>
                                        <input type="text" class="form-control" name="previous_school_name"
                                               value="<?php echo $_POST['previous_school_name'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">KCPE Marks</label>
                                        <input type="number" class="form-control" name="kcpe_marks" min="0" max="500"
                                               value="<?php echo $_POST['kcpe_marks'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">KCPE Index Number</label>
                                        <input type="text" class="form-control" name="kcpe_index_number"
                                               value="<?php echo $_POST['kcpe_index_number'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Year of Completion</label>
                                        <input type="number" class="form-control" name="year_of_completion" min="2000" max="<?php echo date('Y'); ?>"
                                               value="<?php echo $_POST['year_of_completion'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Parent/Guardian Information -->
                            <div class="form-section">
                                <h4><i class="fas fa-users me-2"></i>Parent/Guardian Information</h4>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-field">Parent Name</label>
                                        <input type="text" class="form-control" name="parent_name" required
                                               value="<?php echo $_POST['parent_name'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Relationship to Student</label>
                                        <input type="text" class="form-control" name="relationship_to_student"
                                               value="<?php echo $_POST['relationship_to_student'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-field">Parent Phone</label>
                                        <input type="tel" class="form-control" name="parent_phone" required
                                               value="<?php echo $_POST['parent_phone'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Parent Email</label>
                                        <input type="email" class="form-control" name="parent_email"
                                               value="<?php echo $_POST['parent_email'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>Register Student
                                    </button>
                                    <button type="reset" class="btn btn-secondary btn-lg" onclick="resetLocationDropdowns()">
                                        <i class="fas fa-redo me-2"></i>Reset Form
                                    </button>
                                    <small class="form-text text-muted d-block mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Fields marked with <span class="text-danger">*</span> are required.
                                        Login credentials: Admission Number as username, Birth Certificate as password.
                                    </small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- View Students Tab -->
            <div class="tab-pane fade" id="students" role="tabpanel">
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0"><i class="fas fa-list me-2"></i>Active Students List</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($students->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Admission No.</th>
                                        <th>Full Name</th>
                                        <th>Gender</th>
                                        <th>Class</th>
                                        <th>Stream</th>
                                        <th>Parent Phone</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($student = $students->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($student['admission_number']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($student['class_name'] . ' ' . $student['level']); ?></td>
                                        <td><?php echo htmlspecialchars($student['stream']); ?></td>
                                        <td><?php echo htmlspecialchars($student['parent_phone']); ?></td>
                                        <td>
                                            <span class="badge bg-success"><?php echo htmlspecialchars($student['current_status']); ?></span>
                                            <?php if ($student['promotion_pending'] === 'Repeat'): ?>
                                                <span class="badge bg-warning">Repeat</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                                <button type="submit" name="mark_repetition" class="btn btn-warning btn-sm" 
                                                        onclick="return confirm('Are you sure you want to mark <?php echo htmlspecialchars($student['full_name']); ?> for repetition?')">
                                                    <i class="fas fa-redo me-1"></i>Mark Repeat
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-info btn-sm" 
                                                    onclick="viewStudentDetails(<?php echo $student['student_id']; ?>)">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Student pagination">
                            <ul class="pagination">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>#students">
                                            <i class="fas fa-chevron-left me-1"></i>Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>#students"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>#students">
                                            Next<i class="fas fa-chevron-right ms-1"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>

                        <?php else: ?>
                        <div class="alert alert-info text-center">
                            <h5><i class="fas fa-info-circle me-2"></i>No Students Found</h5>
                            <p>There are no active students in the system yet.</p>
                            <a href="#register" class="btn btn-primary" onclick="switchToRegistrationTab()">
                                <i class="fas fa-user-plus me-2"></i>Register First Student
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Student Promotion Tab -->
            <div class="tab-pane fade" id="promotion" role="tabpanel">
                <div class="card mt-4">
                    <div class="card-header bg-warning">
                        <h3 class="mb-0"><i class="fas fa-chart-line me-2"></i>Annual Student Promotion</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle me-2"></i>Promotion Information</h5>
                            <ul>
                                <li>Promotion automatically runs in December each year for the Kenyan curriculum</li>
                                <li>Only active students are processed</li>
                                <li>Students in final classes (Form 4) will be graduated</li>
                                <li>Students marked for repetition will repeat their current class</li>
                                <li>Current month: <strong><?php echo date('F'); ?></strong></li>
                                <li>Total active students: <strong><?php echo $students_count; ?></strong></li>
                            </ul>
                        </div>

                        <?php if ($needs_confirmation && !empty($promotion_preview)): ?>
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-eye me-2"></i>Promotion Preview - Please Review Before Proceeding</h5>
                            
                            <div class="preview-section">
                                <?php if (!empty($promotion_preview['to_graduate'])): ?>
                                <div class="mb-3">
                                    <h6>Students to Graduate (<?php echo count($promotion_preview['to_graduate']); ?>)</h6>
                                    <ul class="list-group">
                                        <?php foreach ($promotion_preview['to_graduate'] as $student): ?>
                                        <li class="list-group-item">
                                            <strong><?php echo htmlspecialchars($student['name']); ?></strong> - 
                                            <?php echo htmlspecialchars($student['current_class']); ?> 
                                            <span class="badge bg-success float-end">Graduation</span>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($promotion_preview['to_promote'])): ?>
                                <div class="mb-3">
                                    <h6>Students to Promote (<?php echo count($promotion_preview['to_promote']); ?>)</h6>
                                    <ul class="list-group">
                                        <?php foreach ($promotion_preview['to_promote'] as $student): ?>
                                        <li class="list-group-item">
                                            <strong><?php echo htmlspecialchars($student['name']); ?></strong> - 
                                            <?php echo htmlspecialchars($student['current_class']); ?> → 
                                            <?php echo htmlspecialchars($student['next_class']); ?>
                                            <span class="badge bg-primary float-end">Promotion</span>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($promotion_preview['to_repeat'])): ?>
                                <div class="mb-3">
                                    <h6>Students to Repeat (<?php echo count($promotion_preview['to_repeat']); ?>)</h6>
                                    <ul class="list-group">
                                        <?php foreach ($promotion_preview['to_repeat'] as $student): ?>
                                        <li class="list-group-item">
                                            <strong><?php echo htmlspecialchars($student['name']); ?></strong> - 
                                            <?php echo htmlspecialchars($student['current_class']); ?>
                                            <span class="badge bg-warning float-end">Repetition</span>
                                            <small class="d-block text-muted">Reason: <?php echo htmlspecialchars($student['reason']); ?></small>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <form method="POST" class="mt-3">
                                <input type="hidden" name="process_promotion" value="1">
                                <input type="hidden" name="confirm_promotion" value="1">
                                <div class="alert alert-danger">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Warning: This action cannot be undone!</h6>
                                    <p class="mb-0">Please ensure you have reviewed all students before proceeding with the promotion.</p>
                                </div>
                                <button type="submit" class="btn btn-warning btn-lg" 
                                        onclick="return confirm('ARE YOU ABSOLUTELY SURE? This will promote/graduate/repeat all students according to the preview above. This action cannot be undone!')">
                                    <i class="fas fa-check-circle me-2"></i>Confirm and Process Promotion
                                </button>
                                <a href="?" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </form>
                        </div>
                        <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="process_promotion" value="1">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Current Academic Year</label>
                                        <input type="text" class="form-control" value="<?php echo date('Y'); ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Current Month</label>
                                        <input type="text" class="form-control" value="<?php echo date('F'); ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-circle me-2"></i>Important Notice</h6>
                                <p class="mb-0">Clicking the button below will generate a preview of all promotion actions. You will have a chance to review before confirming the actual promotion.</p>
                            </div>
                            
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-chart-bar me-2"></i>Generate Promotion Preview
                            </button>
                            <small class="form-text text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Note: This will show a preview of all promotion actions before processing.
                                Students marked for repetition will repeat their current class.
                            </small>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>
                        School Management System &copy; <?php echo date('Y'); ?>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Student Details Modal -->
    <div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="studentDetailsModalLabel">
                        <i class="fas fa-user me-2"></i>Student Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="studentDetailsContent">
                    <!-- Student details will be loaded here via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Kenyan locations data from PHP
        const kenyanLocations = <?php echo json_encode($kenyan_locations); ?>;

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
            
            // Preserve tab on page reload
            var activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                var tabTrigger = new bootstrap.Tab(document.querySelector(activeTab));
                tabTrigger.show();
            }
            
            var tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
            tabEls.forEach(function(tabEl) {
                tabEl.addEventListener('shown.bs.tab', function (event) {
                    localStorage.setItem('activeTab', event.target.getAttribute('data-bs-target'));
                });
            });

            // Initialize location dropdowns with existing values
            const county = document.getElementById('countySelect').value;
            if (county) {
                onCountyChange(county);
                const subCounty = document.getElementById('subCountySelect').value;
                if (subCounty) {
                    onSubCountyChange(subCounty);
                }
            }
        });

        // Switch to registration tab
        function switchToRegistrationTab() {
            var tab = new bootstrap.Tab(document.querySelector('#register-tab'));
            tab.show();
        }

        function switchToStudentsTab() {
            var tab = new bootstrap.Tab(document.querySelector('#students-tab'));
            tab.show();
        }

        function switchToPromotionTab() {
            var tab = new bootstrap.Tab(document.querySelector('#promotion-tab'));
            tab.show();
        }

        // View student details
        function viewStudentDetails(studentId) {
            fetch('get_student_details.php?id=' + studentId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('studentDetailsContent').innerHTML = data;
                    var modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
                    modal.show();
                })
                .catch(error => {
                    document.getElementById('studentDetailsContent').innerHTML = '<div class="alert alert-danger">Error loading student details.</div>';
                    var modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
                    modal.show();
                });
        }

        // SIMPLIFIED Location Dropdown Functions
        function onCountyChange(county) {
            const subCountySelect = document.getElementById('subCountySelect');
            const wardSelect = document.getElementById('wardSelect');
            
            // Reset dependent dropdowns
            subCountySelect.innerHTML = '<option value="">Select Sub-County</option>';
            wardSelect.innerHTML = '<option value="">Select Ward</option>';
            wardSelect.disabled = true;
            
            if (county && kenyanLocations[county]) {
                subCountySelect.disabled = false;
                Object.keys(kenyanLocations[county]).forEach(subCounty => {
                    const option = document.createElement('option');
                    option.value = subCounty;
                    option.textContent = subCounty;
                    // Check if this was previously selected
                    if (subCounty === '<?php echo $_POST['sub_county'] ?? ''; ?>') {
                        option.selected = true;
                    }
                    subCountySelect.appendChild(option);
                });
                
                // If there's a previously selected sub-county, trigger its change
                const previousSubCounty = '<?php echo $_POST['sub_county'] ?? ''; ?>';
                if (previousSubCounty) {
                    onSubCountyChange(previousSubCounty);
                }
            } else {
                subCountySelect.disabled = true;
            }
        }

        function onSubCountyChange(subCounty) {
            const county = document.getElementById('countySelect').value;
            const wardSelect = document.getElementById('wardSelect');
            
            wardSelect.innerHTML = '<option value="">Select Ward</option>';
            
            if (county && subCounty && kenyanLocations[county] && kenyanLocations[county][subCounty]) {
                wardSelect.disabled = false;
                kenyanLocations[county][subCounty].forEach(ward => {
                    const option = document.createElement('option');
                    option.value = ward;
                    option.textContent = ward;
                    // Check if this was previously selected
                    if (ward === '<?php echo $_POST['ward'] ?? ''; ?>') {
                        option.selected = true;
                    }
                    wardSelect.appendChild(option);
                });
            } else {
                wardSelect.disabled = true;
            }
        }

        // Reset location dropdowns when form is reset
        function resetLocationDropdowns() {
            const countySelect = document.getElementById('countySelect');
            const subCountySelect = document.getElementById('subCountySelect');
            const wardSelect = document.getElementById('wardSelect');
            
            // Reset to default values
            countySelect.selectedIndex = 0;
            subCountySelect.innerHTML = '<option value="">Select Sub-County</option>';
            wardSelect.innerHTML = '<option value="">Select Ward</option>';
            
            // Disable dependent dropdowns
            subCountySelect.disabled = true;
            wardSelect.disabled = true;
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let valid = true;
                    
                    requiredFields.forEach(function(field) {
                        if (!field.value.trim()) {
                            valid = false;
                            field.classList.add('is-invalid');
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });
                    
                    if (!valid) {
                        event.preventDefault();
                        alert('Please fill in all required fields.');
                    }
                });
            });
        });
    </script>
</body>
</html>