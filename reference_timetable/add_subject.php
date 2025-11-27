<?php
// subjects_add.php
session_start();
require_once "db.php"; // your mysqli connection in $conn

// Initialize variables
$errors = [];
$success = "";

// Determine current year and default academic year using Jan->Dec logic
$current_year = (int)date('Y');
// Academic year runs Jan - Dec: current academic year label is "<year>-<year+1>"
$default_academic_year = $current_year . '-' . ($current_year + 1);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
    $subject_code = strtoupper(trim($_POST['subject_code'] ?? ''));
    $subject_name = trim($_POST['subject_name'] ?? '');
    $subject_description = trim($_POST['subject_description'] ?? '');
    // department_id may be provided in future; currently we accept department_name and keep department_id null
    $department_id_raw = trim($_POST['department_id'] ?? '');
    $department_id = ($department_id_raw === '' ? null : (is_numeric($department_id_raw) ? (int)$department_id_raw : null));
    $department_name = trim($_POST['department_name'] ?? '');
    $credit_hours = $_POST['credit_hours'] !== '' ? floatval($_POST['credit_hours']) : 0.0;
    $is_elective = isset($_POST['is_elective']) ? 1 : 0;
    $semester = isset($_POST['semester']) && $_POST['semester'] !== '' ? intval($_POST['semester']) : null;
    $academic_year = trim($_POST['academic_year'] ?? '');
    $max_students = isset($_POST['max_students']) && $_POST['max_students'] !== '' ? intval($_POST['max_students']) : 0;
    $teacher_id_raw = trim($_POST['teacher_id'] ?? '');
    $teacher_id = ($teacher_id_raw === '' ? null : (is_numeric($teacher_id_raw) ? (int)$teacher_id_raw : null));
    $status = trim($_POST['status'] ?? 'Active');

    // Basic validation
    if ($subject_code === '') $errors[] = "Subject code is required.";
    if ($subject_name === '') $errors[] = "Subject name is required.";

    // Validate subject_code pattern server-side (2-20 alphanumeric)
    if ($subject_code !== '' && !preg_match('/^[A-Z0-9]{2,20}$/', $subject_code)) {
        $errors[] = "Subject code must be 2-20 alphanumeric characters (letters & numbers only).";
    }

    // Validate academic_year format: YYYY-YYYY and logical (next year = year+1)
    if ($academic_year !== '') {
        if (!preg_match('/^\d{4}-\d{4}$/', $academic_year)) {
            $errors[] = "Academic year must be in format YYYY-YYYY (e.g., 2025-2026).";
        } else {
            list($y1, $y2) = explode('-', $academic_year);
            if (((int)$y2) !== ((int)$y1 + 1)) {
                $errors[] = "Academic year must represent consecutive years (e.g., 2025-2026).";
            }
        }
    } else {
        $errors[] = "Academic year is required.";
    }

    // Validate credit_hours range
    if ($credit_hours < 0 || $credit_hours > 10) {
        $errors[] = "Credit hours must be between 0.0 and 10.0.";
    }

    // Validate semester if provided
    if ($semester !== null && ($semester < 1 || $semester > 8)) {
        $errors[] = "Semester must be between 1 and 8.";
    }

    // Validate status enum
    if (!in_array($status, ['Active', 'Inactive'])) {
        $status = 'Active';
    }

    // Prevent duplicate subject_code for same academic year (optional, but recommended)
    if (empty($errors)) {
        $dupQuery = $conn->prepare("SELECT id FROM subjects WHERE subject_code = ? AND academic_year = ? LIMIT 1");
        if ($dupQuery) {
            $dupQuery->bind_param('ss', $subject_code, $academic_year);
            $dupQuery->execute();
            $dupQuery->store_result();
            if ($dupQuery->num_rows > 0) {
                $errors[] = "A subject with code {$subject_code} already exists for {$academic_year}.";
            }
            $dupQuery->close();
        } else {
            // if prepare fails, log and continue (but don't block)
            error_log("subjects_add.php: duplicate check prepare failed: " . $conn->error);
        }
    }

    // Insert into DB
    if (empty($errors)) {
        // Prepare insert - types:
        // subject_code (s), subject_name (s), subject_description (s),
        // department_id (i / null), department_name (s), credit_hours (d),
        // is_elective (i), semester (i / null), academic_year (s),
        // max_students (i), teacher_id (i / null), status (s)
        $stmt = $conn->prepare("INSERT INTO subjects 
            (subject_code, subject_name, subject_description, department_id, department_name, credit_hours, is_elective, semester, academic_year, max_students, teacher_id, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            $errors[] = "Database prepare failed: " . $conn->error;
        } else {
            // If department_id or teacher_id are null, keep them as null (mysqli will send as NULL)
            // Bind params: s s s i s d i i s i i s  -> "sssisdiisiis"? We'll use "sssisd iisiis"? Use exact order:
            // 1:s,2:s,3:s,4:i,5:s,6:d,7:i,8:i,9:s,10:i,11:i,12:s
            $types = "sssisd iis iis"; // just for reading — we will use the final types string below
            $types = "sssisd ii s ii s"; // readability line (not used)
            // Final concatenation without spaces:
            $bind_types = "sssisd iisiis"; // incorrect spacing; build explicitly below

            // Correct final bind types string (no spaces): subject_code(s),subject_name(s),subject_description(s),
            // department_id(i),department_name(s),credit_hours(d),is_elective(i),semester(i),
            // academic_year(s),max_students(i),teacher_id(i),status(s)
            $bind_types = "sssid diis iis"; // still messy — let's simply use the correct literal:
            // Final correct string:
            $bind_types = "sssisd iisiis"; // ARGH — building by concatenation simpler:

            // Build proper bind types by concatenation:
            $bind_types = "s" . "s" . "s" . "i" . "s" . "d" . "i" . "i" . "s" . "i" . "i" . "s";
            // Now bind
            $stmt->bind_param(
                $bind_types,
                $subject_code,
                $subject_name,
                $subject_description,
                $department_id,
                $department_name,
                $credit_hours,
                $is_elective,
                $semester,
                $academic_year,
                $max_students,
                $teacher_id,
                $status
            );

            if ($stmt->execute()) {
                $success = "Subject added successfully!";
                // Clear POST so form resets
                $_POST = [];
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}

// Fetch teachers for dropdown (Active teachers)
$teachers_result = $conn->query("
    SELECT id, staff_no, full_name 
    FROM staff 
    WHERE status = 'Active' AND (position = 'Teacher' OR position = 'AcademicTeacher')
    ORDER BY full_name ASC
");

// Fetch existing subjects for reference
$subjects_result = $conn->query("
    SELECT subject_code, subject_name, credit_hours, semester, academic_year, status 
    FROM subjects 
    ORDER BY subject_code ASC
");

// Prepare list of next 5 academic years (starting with current year)
$academic_year_options = [];
for ($i = 0; $i < 5; $i++) {
    $y = $current_year + $i;
    $academic_year_options[] = $y . '-' . ($y + 1);
}

// Helper: function to echo selected option
function optsel($val, $check) {
    return ($val === $check) ? 'selected' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject - School Management System</title>
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
            border-radius: 4px;
            margin: 0 2px;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.15);
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
            color: var(--dark);
        }

        .dropdown-item:hover {
            background-color: var(--secondary);
            color: white;
        }
        
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 30px auto;
            max-width: 1100px;
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            position: relative;
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
        
        .required-field::after {
            content: " *";
            color: var(--danger);
        }
        
        .form-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 5px solid var(--primary);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .floating-alert {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
            animation: slideInRight 0.5s ease;
        }
        
        .subject-preview {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to   { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .main-container { margin: 15px; border-radius: 15px; }
            .header-section { padding: 1.5rem; }
            .content-section { padding: 1.5rem; }
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
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPrincipal">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarPrincipal">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="principal_dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="staffDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-users-cog me-1"></i>Staff Management
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="staffDropdown">
                            <li><a class="dropdown-item" href="add_staff.php"><i class="fas fa-user-plus me-2"></i>Add Staff</a></li>
                            <li><a class="dropdown-item" href="deactivate_staff.php"><i class="fas fa-user-minus me-2"></i>Deactivate Staff</a></li>
                            <li><a class="dropdown-item" href="subject_allocation.php"><i class="fas fa-book me-2"></i>Subject Allocation</a></li>
                            <li><a class="dropdown-item active" href="subjects_add.php"><i class="fas fa-plus-circle me-2"></i>Add Subject</a></li>
                            <li><a class="dropdown-item" href="class_teacher_allocation.php"><i class="fas fa-chalkboard-teacher me-2"></i>Class Teacher Allocation</a></li>
                            <li><a class="dropdown-item" href="create_classes.php"><i class="fas fa-door-open me-2"></i>Create Classes</a></li>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?= htmlspecialchars($_SESSION['user_name'] ?? 'Principal') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Floating Alert -->
    <?php if (!empty($errors)): ?>
        <div class="floating-alert alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="floating-alert alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="container">
        <div class="main-container">
            <!-- Header Section -->
            <div class="header-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold mb-2"><i class="fas fa-book me-3"></i>Add New Subject</h1>
                        <p class="lead mb-0">Create new subjects for the academic curriculum</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-light text-dark fs-6 p-2">
                            <i class="fas fa-calendar me-2"></i>
                            <?= htmlspecialchars($default_academic_year) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="content-section">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card card-hover">
                            <div class="stats-number"><?= $subjects_result ? $subjects_result->num_rows : 0 ?></div>
                            <div class="stats-label">Total Subjects</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card card-hover">
                            <div class="stats-number"><?= $teachers_result ? $teachers_result->num_rows : 0 ?></div>
                            <div class="stats-label">Available Teachers</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card card-hover">
                            <div class="stats-number"><?= $subjects_result && $subjects_result->num_rows > 0 ? round($subjects_result->num_rows / 10) * 10 : 0 ?></div>
                            <div class="stats-label">Active Subjects</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card card-hover">
                            <div class="stats-number">8</div>
                            <div class="stats-label">Semesters</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Add Subject Form -->
                    <div class="col-lg-8">
                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-plus-circle me-2 text-success"></i>Subject Information</h4>
                            
                            <form method="POST" id="subjectForm" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-field">Subject Code</label>
                                        <div class="input-group-icon">
                                            <i class="fas fa-code"></i>
                                            <input type="text" class="form-control" name="subject_code" 
                                                   value="<?= htmlspecialchars($_POST['subject_code'] ?? '') ?>" 
                                                   placeholder="e.g., MATH101" required
                                                   pattern="[A-Za-z0-9]{2,20}" title="2-20 alphanumeric characters">
                                        </div>
                                        <small class="form-text text-muted">Unique identifier for the subject (uppercase)</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-field">Subject Name</label>
                                        <div class="input-group-icon">
                                            <i class="fas fa-book"></i>
                                            <input type="text" class="form-control" name="subject_name" 
                                                   value="<?= htmlspecialchars($_POST['subject_name'] ?? '') ?>" 
                                                   placeholder="e.g., Mathematics" required>
                                        </div>
                                        <small class="form-text text-muted">Full name of the subject</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Subject Description</label>
                                        <div class="input-group-icon">
                                            <i class="fas fa-align-left"></i>
                                            <textarea class="form-control" name="subject_description" rows="3" 
                                                      placeholder="Brief description of the subject..."><?= htmlspecialchars($_POST['subject_description'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Department Name</label>
                                        <div class="input-group-icon">
                                            <i class="fas fa-building"></i>
                                            <input type="text" class="form-control" name="department_name" 
                                                   value="<?= htmlspecialchars($_POST['department_name'] ?? '') ?>" 
                                                   placeholder="e.g., Science Department">
                                        </div>
                                        <small class="form-text text-muted">Department offering this subject</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Credit Hours</label>
                                        <div class="input-group-icon">
                                            <i class="fas fa-weight-hanging"></i>
                                            <input type="number" class="form-control" name="credit_hours" 
                                                   value="<?= htmlspecialchars($_POST['credit_hours'] ?? '0.0') ?>" 
                                                   step="0.1" min="0" max="10" placeholder="0.0">
                                        </div>
                                        <small class="form-text text-muted">Credit weight of the subject (0.0-10.0)</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Semester</label>
                                        <div class="input-group-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                            <select class="form-select" name="semester">
                                                <option value="">Select Semester</option>
                                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                                    <option value="<?= $i ?>" <?= (($_POST['semester'] ?? '') == $i) ? 'selected' : '' ?>>
                                                        Semester <?= $i ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required-field">Academic Year</label>
                                        <div class="input-group-icon">
                                            <i class="fas fa-calendar"></i>
                                            <select class="form-select" name="academic_year" required>
                                                <option value="">Select Year</option>
                                                <?php
                                                $selected_year = $_POST['academic_year'] ?? $default_academic_year;
                                                foreach ($academic_year_options as $opt) {
                                                    echo "<option value=\"" . htmlspecialchars($opt) . "\" " . optsel($opt, $selected_year) . ">" . htmlspecialchars($opt) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <small class="form-text text-muted">Choose the academic year (YYYY-YYYY)</small>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Max Students</label>
                                        <div class="input-group-icon">
                                            <i class="fas fa-users"></i>
                                            <input type="number" class="form-control" name="max_students" 
                                                   value="<?= htmlspecialchars($_POST['max_students'] ?? '0') ?>" 
                                                   min="0" placeholder="0">
                                        </div>
                                        <small class="form-text text-muted">0 for no limit</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Default Teacher</label>
                                        <div class="input-group-icon">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                            <select class="form-select" name="teacher_id">
                                                <option value="">Select Teacher (Optional)</option>
                                                <?php 
                                                if ($teachers_result && $teachers_result->num_rows > 0) {
                                                    $teachers_result->data_seek(0);
                                                    while ($teacher = $teachers_result->fetch_assoc()): ?>
                                                        <option value="<?= $teacher['id'] ?>" <?= (($_POST['teacher_id'] ?? '') == $teacher['id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($teacher['full_name']) ?> (<?= $teacher['staff_no'] ?>)
                                                        </option>
                                                    <?php endwhile;
                                                } else {
                                                    echo '<option value="">No teachers available</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <small class="form-text text-muted">Primary teacher for this subject</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check mt-4 pt-3">
                                            <input class="form-check-input" type="checkbox" name="is_elective" value="1" 
                                                   id="is_elective" <?= (($_POST['is_elective'] ?? '') == '1') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="is_elective">
                                                <i class="fas fa-star me-2"></i>This is an elective subject
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Elective subjects are optional for students</small>
                                        
                                        <!-- Status Field -->
                                        <div class="mt-3">
                                            <label class="form-label">Status</label>
                                            <div class="input-group-icon">
                                                <i class="fas fa-power-off"></i>
                                                <select class="form-select" name="status">
                                                    <option value="Active" <?= (($_POST['status'] ?? 'Active') == 'Active') ? 'selected' : '' ?>>Active</option>
                                                    <option value="Inactive" <?= (($_POST['status'] ?? '') == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Subject Preview -->
                                <div class="subject-preview mt-4">
                                    <h6><i class="fas fa-eye me-2"></i>Subject Preview</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small><strong>Code:</strong> <span id="preview-code">-</span></small><br>
                                            <small><strong>Name:</strong> <span id="preview-name">-</span></small><br>
                                            <small><strong>Credits:</strong> <span id="preview-credits">-</span></small>
                                        </div>
                                        <div class="col-md-6">
                                            <small><strong>Semester:</strong> <span id="preview-semester">-</span></small><br>
                                            <small><strong>Type:</strong> <span id="preview-type">-</span></small><br>
                                            <small><strong>Status:</strong> <span id="preview-status">-</span></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                    <button type="reset" class="btn btn-secondary me-md-2">
                                        <i class="fas fa-undo me-2"></i>Reset Form
                                    </button>
                                    <button type="submit" name="add_subject" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Add Subject
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Existing Subjects Preview -->
                    <div class="col-lg-4">
                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-list me-2 text-primary"></i>Existing Subjects</h4>
                            
                            <?php if ($subjects_result && $subjects_result->num_rows > 0): ?>
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-sm table-hover">
                                        <thead class="sticky-top bg-light">
                                            <tr>
                                                <th>Code</th>
                                                <th>Name</th>
                                                <th>Credits</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $subjects_result->data_seek(0);
                                            while ($subject = $subjects_result->fetch_assoc()): ?>
                                            <tr class="card-hover">
                                                <td><small class="fw-bold text-primary"><?= htmlspecialchars($subject['subject_code']) ?></small></td>
                                                <td><small><?= htmlspecialchars($subject['subject_name']) ?></small></td>
                                                <td><small class="badge bg-info"><?= htmlspecialchars($subject['credit_hours']) ?></small></td>
                                                <td>
                                                    <span class="badge bg-<?= $subject['status'] == 'Active' ? 'success' : 'secondary' ?>">
                                                        <small><?= htmlspecialchars($subject['status']) ?></small>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-book fa-2x mb-3"></i>
                                    <p>No subjects found</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Quick Actions -->
                        <div class="form-section mt-4">
                            <h5 class="section-title"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5>
                            <div class="d-grid gap-2">
                                <a href="subject_allocation.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-link me-2"></i>Subject Allocation
                                </a>
                                <a href="class_teacher_allocation.php" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-chalkboard-teacher me-2"></i>Teacher Allocation
                                </a>
                                <a href="principal_dashboard.php" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-tachometer-alt me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Real-time preview update
            function updatePreview() {
                document.getElementById('preview-code').textContent =
                    document.querySelector('[name="subject_code"]').value || '-';
                document.getElementById('preview-name').textContent =
                    document.querySelector('[name="subject_name"]').value || '-';
                document.getElementById('preview-credits').textContent =
                    document.querySelector('[name="credit_hours"]').value || '0.0';
                document.getElementById('preview-semester').textContent =
                    document.querySelector('[name="semester"]').value ? 'Semester ' + document.querySelector('[name="semester"]').value : '-';
                document.getElementById('preview-type').textContent =
                    document.querySelector('[name="is_elective"]').checked ? 'Elective' : 'Core';
                document.getElementById('preview-status').textContent =
                    document.querySelector('[name="status"]').value || 'Active';
            }

            // Add event listeners for preview updates
            document.querySelectorAll('#subjectForm input, #subjectForm select, #subjectForm textarea').forEach(element => {
                element.addEventListener('input', updatePreview);
                element.addEventListener('change', updatePreview);
            });

            // Initial preview update
            updatePreview();

            // Form submission handling: button spinner
            document.getElementById('subjectForm').addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding Subject...';
                submitBtn.disabled = true;
                
                // Re-enable after 6 seconds (fallback)
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                }, 6000);
            });

            // Auto-hide alerts after 6 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 6000);

            // Subject code auto-format to uppercase
            const codeInput = document.querySelector('input[name="subject_code"]');
            if (codeInput) {
                codeInput.addEventListener('input', function(e) {
                    this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                });
            }
        });
    </script>
</body>
</html>
