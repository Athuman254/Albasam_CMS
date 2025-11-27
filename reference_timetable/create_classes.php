<?php
session_start();
require_once "db.php";


// ======================== HANDLE CLASS CREATION ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $class_name = trim($_POST['class_name']);
        $class_code = trim($_POST['class_code']);
        $curriculum = trim($_POST['curriculum']);
        $level = trim($_POST['level']);
        $stream = trim($_POST['stream']);
        $section = trim($_POST['section']);
        $academic_year = trim($_POST['academic_year']);
        $capacity = intval($_POST['capacity']);
        $class_teacher_id = !empty($_POST['class_teacher_id']) ? intval($_POST['class_teacher_id']) : null;
        $room_number = trim($_POST['room_number']);
        $lab_required = isset($_POST['lab_required']) ? 1 : 0;
        $subject_combination = trim($_POST['subject_combination']);
        $description = trim($_POST['description']);

        if (empty($class_name) || empty($class_code) || empty($level) || empty($academic_year)) {
            throw new Exception("Please fill in all required fields.");
        }

        $stmt = $conn->prepare("SELECT id FROM classes WHERE class_code=? AND academic_year=?");
        $stmt->bind_param("ss", $class_code, $academic_year);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) throw new Exception("Class code '$class_code' already exists for $academic_year.");
        $stmt->close();

        if ($class_teacher_id) {
            $stmt = $conn->prepare("SELECT id FROM staff WHERE id=? AND status='Active'");
            $stmt->bind_param("i", $class_teacher_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) throw new Exception("Selected class teacher not found or inactive.");
            $stmt->close();
        }

        $stmt = $conn->prepare("
            INSERT INTO classes 
            (class_name, class_code, curriculum, level, stream, section, academic_year, capacity, class_teacher_id, room_number, lab_required, subject_combination, description, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')
        ");
        $stmt->bind_param(
            "sssssssisisss",
            $class_name, $class_code, $curriculum, $level, $stream, $section,
            $academic_year, $capacity, $class_teacher_id, $room_number, $lab_required,
            $subject_combination, $description
        );

        if ($stmt->execute()) {
            $class_id = $conn->insert_id;

            if ($class_teacher_id) {
                $upd = $conn->prepare("UPDATE staff SET current_class_id=? WHERE id=?");
                $upd->bind_param("ii", $class_id, $class_teacher_id);
                $upd->execute();
                $upd->close();
            }

            $response['success'] = true;
            $response['message'] = "Class '$class_name' created successfully!";
        } else {
            throw new Exception("Failed to create class: " . $conn->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        $_SESSION[$response['success'] ? 'success_message' : 'error_message'] = $response['message'];
        header("Location: create_classes.php");
        exit;
    }
}

// ======================== FETCH DATA ========================
$teachers_result = $conn->query("SELECT id, full_name, staff_no FROM staff WHERE status='Active' AND position LIKE '%Teacher%' ORDER BY full_name ASC");
$classes_result = $conn->query("
    SELECT c.*, s.full_name as teacher_name
    FROM classes c
    LEFT JOIN staff s ON c.class_teacher_id = s.id
    ORDER BY c.level, c.stream, c.section ASC
");

$current_year = date('Y');
$default_academic_year = $current_year . '-' . ($current_year + 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Classes - School Management System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {background:#f7f8fa;font-family:Arial,sans-serif;}
.navbar {background:#343a40;}
.navbar-brand {font-weight:600;font-size:1.3rem;}
.main-container {background:#fff;border-radius:10px;box-shadow:0 0 15px rgba(0,0,0,.1);}
h2,h4 {color:#343a40;}
.table thead {background:#343a40;color:#fff;}
.table tbody tr:hover {background:#f1f1f1;}
.btn-primary {background:#007bff;border:none;}
.btn-primary:hover {background:#0069d9;}
.form-label {font-weight:500;}
.alert {margin-top:10px;}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
<div class="container-fluid">
    <a class="navbar-brand" href="principal_dashboard.php"><i class="fas fa-school me-2"></i>School Management System</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPrincipal"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navbarPrincipal">
        <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link" href="create_classes.php">Create Classes</a></li>
            <li class="nav-item"><a class="nav-link" href="principal_dashboard.php">Dashboard</a></li>
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-user-circle me-1"></i><?php echo $_SESSION['user_name'] ?? 'Principal'; ?></a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>
</nav>

<div class="container mt-5 pt-4">
    <div class="main-container p-4">
        <h2 class="mb-4"><i class="fas fa-door-open me-2"></i>Create Classes</h2>

        <!-- Messages -->
        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <!-- Create Class Form -->
        <form method="POST" id="createClassForm">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Curriculum</label>
                    <select name="curriculum" id="curriculum" class="form-select" required>
                        <option value="">Select Curriculum</option>
                        <option value="8-4-4">8-4-4</option>
                        <option value="CBC Senior Secondary">CBC Senior Secondary</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Level</label>
                    <select name="level" id="level" class="form-select" required></select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Stream</label>
                    <select name="stream" id="stream" class="form-select">
                        <option value="General">General</option>
                        <option value="Science">Science</option>
                        <option value="Humanities">Humanities</option>
                        <option value="Business">Business</option>
                        <option value="Technical">Technical</option>
                        <option value="Arts">Arts</option>
                        <option value="Sports">Sports</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Section</label>
                    <select name="section" id="section" class="form-select">
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year" class="form-select">
                        <?php for($i=-5;$i<=5;$i++){
                            $y=$current_year+$i; $n=$y+1;
                            echo "<option value='{$y}-{$n}'".($i==0?' selected':'').">{$y}-{$n}</option>";
                        } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Class Name</label>
                    <input type="text" name="class_name" id="class_name" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Class Code</label>
                    <input type="text" name="class_code" id="class_code" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Capacity</label>
                    <input type="number" name="capacity" value="45" class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Class Teacher</label>
                    <select name="class_teacher_id" class="form-select">
                        <option value="">Select Teacher</option>
                        <?php while($t=$teachers_result->fetch_assoc()): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['full_name']) ?> (<?= $t['staff_no'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Room Number</label>
                    <input type="text" name="room_number" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Lab Required</label><br>
                    <input type="checkbox" name="lab_required" value="1" class="form-check-input mt-2">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Subject Combination</label>
                    <input type="text" name="subject_combination" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
            </div>

            <button type="submit" name="create_class" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create Class</button>
        </form>

        <!-- Existing Classes Table -->
        <hr>
        <h4>Existing Classes</h4>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Code</th><th>Name</th><th>Curriculum</th><th>Level</th><th>Stream</th><th>Section</th><th>Year</th><th>Teacher</th><th>Capacity</th><th>Room</th><th>Lab</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($c=$classes_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $c['class_code'] ?></td>
                    <td><?= htmlspecialchars($c['class_name']) ?></td>
                    <td><?= $c['curriculum'] ?></td>
                    <td><?= $c['level'] ?></td>
                    <td><?= $c['stream'] ?></td>
                    <td><?= $c['section'] ?></td>
                    <td><?= $c['academic_year'] ?></td>
                    <td><?= $c['teacher_name'] ?: 'N/A' ?></td>
                    <td><?= $c['capacity'] ?></td>
                    <td><?= $c['room_number'] ?: 'N/A' ?></td>
                    <td><?= $c['lab_required'] ? 'Yes' : 'No' ?></td>
                    <td><?= $c['status'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const curriculumLevels = {
    '8-4-4':['Form 1','Form 2','Form 3','Form 4'],
    'CBC Senior Secondary':['Grade 10','Grade 11','Grade 12']
};

document.getElementById('curriculum').addEventListener('change',function(){
    const levelSelect = document.getElementById('level');
    levelSelect.innerHTML = '<option value="">Select Level</option>';
    const lvls = curriculumLevels[this.value]||[];
    lvls.forEach(l=>{levelSelect.innerHTML += `<option value="${l}">${l}</option>`;});
    updateClassDetails();
});

['level','stream','section'].forEach(id=>document.getElementById(id).addEventListener('change',updateClassDetails));

function updateClassDetails(){
    const lvl = document.getElementById('level').value;
    const str = document.getElementById('stream').value;
    const sec = document.getElementById('section').value;
    let name='', code='';
    if(lvl){name=lvl+(str&&str!=='General'?' '+str:'')+(sec?' '+sec:'');}
    if(lvl){
        if(lvl.includes('Form')) code='F'+lvl.replace('Form ','');
        else if(lvl.includes('Grade')) code='G'+lvl.replace('Grade ','');
        if(str&&str!=='General'){
            const abbr={'Science':'SCI','Humanities':'HUM','Business':'BUS','Technical':'TEC','Arts':'ART','Sports':'SPT'};
            code+='-'+(abbr[str]||str.substring(0,3).toUpperCase());
        }
        if(sec) code+='-'+sec;
    }
    document.getElementById('class_name').value=name;
    document.getElementById('class_code').value=code;
}
</script>
</body>
</html>
