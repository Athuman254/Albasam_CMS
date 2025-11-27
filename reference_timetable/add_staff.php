<?php
session_start();
require_once "db.php"; // secure mysqli connection in $conn

$errors = [];
$success = "";

/**
 * Generate a new staff number STF0001, STF0002...
 * This looks up the numeric part of the highest existing staff_no and increments it.
 * More robust than blindly using ORDER BY id DESC because we parse staff_no format.
 */
function generateStaffNumber($conn) {
    $prefix = "STF";
    $pad = 4;

    // Find the maximum numeric portion from staff_no values that match the pattern STF\d+
    $res = $conn->query("SELECT staff_no FROM staff WHERE staff_no LIKE 'STF%'");
    $maxNum = 0;
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $code = $row['staff_no'];
            // extract trailing digits
            if (preg_match('/^STF0*([0-9]+)$/i', $code, $m)) {
                $num = intval($m[1]);
                if ($num > $maxNum) $maxNum = $num;
            }
        }
        $res->free();
    }

    $next = $maxNum + 1;
    return $prefix . str_pad($next, $pad, "0", STR_PAD_LEFT);
}

// Precompute auto staff no for the form
$auto_staff_no = generateStaffNumber($conn);

// Determine current academic year format YYYY-YYYY+1 (e.g., 2025-2026)
$currentYear = (int)date('Y');
$default_academic_year = $currentYear . '-' . ($currentYear + 1);

// Positions list (drop-down)
$positions = [
    "Teacher",
    "AcademicTeacher",
    "Head of Department",
    "Deputy Principal",
    "Principal",
    "Lab Technician",
    "Accountant",
    "Secretary",
    "Librarian"
];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Take posted staff_no if present (should be auto-generated readonly in the form)
    $staff_no = trim($_POST['staff_no'] ?? '');
    if ($staff_no === '') {
        $staff_no = generateStaffNumber($conn);
    }

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $status = trim($_POST['status'] ?? 'Active');
    $subjects = $_POST['subjects'] ?? []; // array of subject IDs (may be empty)
    // optional: you might want to accept class association per teacher_subjects in future

    // Validation
    if ($full_name === '') $errors[] = "Full name is required.";
    if ($email === '') $errors[] = "Email is required.";
    if ($position === '' || !in_array($position, $positions)) $errors[] = "A valid position is required.";
    if (!in_array($status, ['Active','Inactive'])) $status = 'Active';
    if ($gender !== '' && !in_array($gender, ['Male','Female'])) $gender = '';

    // Basic email format check
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email address is invalid.";
    }

    // If everything ok so far, check duplicates and save
    if (empty($errors)) {
        // Duplicate email check
        $check = $conn->prepare("SELECT id FROM staff WHERE email = ? LIMIT 1");
        if (!$check) {
            $errors[] = "Database error (prepare): " . $conn->error;
        } else {
            $check->bind_param('s', $email);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $errors[] = "A staff member with this email already exists.";
            }
            $check->close();
        }
    }

    if (empty($errors)) {
        // Insert staff
        $ins = $conn->prepare("
            INSERT INTO staff (staff_no, full_name, email, phone, gender, position, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$ins) {
            $errors[] = "Database error (prepare insert): " . $conn->error;
        } else {
            $ins->bind_param("sssssss", $staff_no, $full_name, $email, $phone, $gender, $position, $status);
            if ($ins->execute()) {
                $staff_id = $ins->insert_id;

                // If there are subject combinations, insert into teacher_subjects
                if (!empty($subjects) && is_array($subjects)) {
                    // Prepare once
                    // teacher_subjects structure (as per your schema):
                    // id, teacher_id, subject_id, class_id, academic_year, status, created_at
                    // We'll insert class_id as NULL (not supplied from this form) and created_at = NOW()
                    $ts_sql = "INSERT INTO teacher_subjects (teacher_id, subject_id, class_id, academic_year, status, created_at)
                               VALUES (?, ?, NULL, ?, 'Active', NOW())";
                    $ts = $conn->prepare($ts_sql);
                    if (!$ts) {
                        // Non-fatal: still proceed but log error
                        error_log("teacher_subjects prepare failed: " . $conn->error);
                    } else {
                        // Use YYYY-YYYY+1 academic year (same as default)
                        $year = (int)date('Y');
                        $academic_year_str = $year . '-' . ($year + 1);

                        // Bind and execute for each subject
                        foreach ($subjects as $sub_id_raw) {
                            $sub_id = intval($sub_id_raw);
                            if ($sub_id <= 0) continue;
                            $ts->bind_param('iis', $staff_id, $sub_id, $academic_year_str);
                            $ts->execute();
                            // optional: check errors per execute
                        }
                        $ts->close();
                    }
                }

                $success = "Staff member added successfully (Staff No: {$staff_no}).";
                // Reset POST to clear form
                $_POST = [];
                // regenerate next staff number
                $auto_staff_no = generateStaffNumber($conn);
            } else {
                $errors[] = "Database error (insert staff): " . $ins->error;
            }
            $ins->close();
        }
    }
}

// Fetch all active subjects for selection
$subjects_result = $conn->query("SELECT id, subject_name FROM subjects WHERE status = 'Active' ORDER BY subject_name ASC");

// If query fails, create an empty array to avoid warnings in the template
if (!$subjects_result) {
    $subjects_result = new class {
        function fetch_assoc(){ return false; }
        public $num_rows = 0;
        function data_seek($n){}
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Staff - School Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg,#001f3f,#0059b3); padding: 40px; color:#fff; }
.container-card { max-width:900px; margin:0 auto; background:#fff; color:#000; border-radius:12px; padding:22px; box-shadow:0 6px 20px rgba(0,0,0,0.25); }
h2 { color:#003366; margin-bottom:16px; text-align:center; }
input, select, textarea { border-radius:6px; border:1px solid #ccc; padding:10px; font-size:15px; }
label { font-weight:600; }
button { background:#0059b3; color:#fff; border:none; padding:10px 18px; border-radius:8px; }
button:hover { background:#003f80; }
.success { color:green; font-weight:600; }
.error-list { color:#e74c3c; }
.small-muted { font-size:0.9rem; color:#666; }
.form-note { font-size:0.9rem; color:#666; margin-top:6px; }
</style>
</head>
<body>
<div class="container-card">
  <h2>Add New Staff Member</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" novalidate>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Full Name <span class="text-danger">*</span></label>
        <input class="form-control" type="text" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Staff Number (Auto-generated)</label>
        <input class="form-control" type="text" name="staff_no" value="<?= htmlspecialchars($auto_staff_no) ?>" readonly>
        <div class="form-note">System generated. You may override if you must (avoid duplicates).</div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input class="form-control" type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Gender</label>
        <select class="form-select" name="gender">
          <option value="">Select</option>
          <option value="Male" <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
          <option value="Female" <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Position <span class="text-danger">*</span></label>
        <select class="form-select" name="position" required>
          <option value="">Select Position</option>
          <?php foreach ($positions as $pos): ?>
            <option value="<?= htmlspecialchars($pos) ?>" <?= (($_POST['position'] ?? '') === $pos) ? 'selected' : '' ?>><?= htmlspecialchars($pos) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="Active" <?= (($_POST['status'] ?? '') === 'Active') ? 'selected' : '' ?>>Active</option>
          <option value="Inactive" <?= (($_POST['status'] ?? '') === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Subjects (hold CTRL / CMD to select multiple)</label>
        <select class="form-select" name="subjects[]" multiple size="7">
          <?php
          // reset pointer and render subjects
          if ($subjects_result && property_exists($subjects_result, 'num_rows') && $subjects_result->num_rows > 0) {
              $subjects_result->data_seek(0);
              while ($s = $subjects_result->fetch_assoc()) {
                  $sel = (isset($_POST['subjects']) && in_array($s['id'], (array)$_POST['subjects'])) ? 'selected' : '';
                  echo '<option value="' . (int)$s['id'] . '" ' . $sel . '>' . htmlspecialchars($s['subject_name']) . '</option>';
              }
          } else {
              echo '<option disabled>No subjects available</option>';
          }
          ?>
        </select>
        <div class="form-note">Assign subjects the teacher can teach — helps the allocation engine.</div>
      </div>

      <div class="col-12 text-end">
        <button type="submit" class="btn">Add Staff</button>
      </div>
    </div>
  </form>
</div>

</body>
</html>
