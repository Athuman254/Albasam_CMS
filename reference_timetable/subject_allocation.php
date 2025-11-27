<?php
session_start();
require_once "db.php"; // Secure DB connection

$success = "";
$errors = [];

// 🧠 Define production limits
define('MAX_CLASSES_PER_TEACHER', 4);
define('MAX_SUBJECTS_PER_TEACHER', 3);
define('MAX_HOURS_PER_WEEK', 25);
define('MAX_CLASSES_PER_DAY', 3);

// 🧠 Get teacher workload limits and current allocations
function getTeacherWorkload($conn, $teacher_id, $academic_year) {
    $query = "
        SELECT 
            COUNT(DISTINCT sa.class_id) as current_classes,
            COUNT(DISTINCT sa.subject_id) as current_subjects,
            SUM(sa.hours_per_week) as total_hours,
            GROUP_CONCAT(DISTINCT c.class_name) as classes,
            GROUP_CONCAT(DISTINCT s.subject_name) as subjects
        FROM subject_allocations sa
        JOIN classes c ON sa.class_id = c.id
        JOIN subjects s ON sa.subject_id = s.id
        WHERE sa.teacher_id = ? AND sa.academic_year = ? AND sa.status = 'Active'
        GROUP BY sa.teacher_id
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $teacher_id, $academic_year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0 ? $result->fetch_assoc() : [
        'current_classes' => 0,
        'current_subjects' => 0,
        'total_hours' => 0,
        'classes' => '',
        'subjects' => ''
    ];
}

// 🧠 Check if allocation would cause overload with production limits
function checkTeacherOverload($conn, $teacher_id, $new_subjects_count, $new_classes_count, $new_hours, $academic_year) {
    $current = getTeacherWorkload($conn, $teacher_id, $academic_year);
    
    $limits = [
        'max_classes' => MAX_CLASSES_PER_TEACHER,
        'max_subjects' => MAX_SUBJECTS_PER_TEACHER, 
        'max_hours' => MAX_HOURS_PER_WEEK,
        'max_classes_per_day' => MAX_CLASSES_PER_DAY
    ];
    
    $warnings = [];
    $is_overloaded = false;
    
    // Check class limit
    $total_classes = $current['current_classes'] + $new_classes_count;
    if ($total_classes > $limits['max_classes']) {
        $warnings[] = "❌ Class limit exceeded: {$current['current_classes']} current + {$new_classes_count} new = {$total_classes} (max: {$limits['max_classes']})";
        $is_overloaded = true;
    }
    
    // Check subject limit
    $total_subjects = $current['current_subjects'] + $new_subjects_count;
    if ($total_subjects > $limits['max_subjects']) {
        $warnings[] = "❌ Subject limit exceeded: {$current['current_subjects']} current + {$new_subjects_count} new = {$total_subjects} (max: {$limits['max_subjects']})";
        $is_overloaded = true;
    }
    
    // Check hours limit
    $total_new_hours = $new_hours * $new_subjects_count * $new_classes_count;
    $total_hours = $current['total_hours'] + $total_new_hours;
    if ($total_hours > $limits['max_hours']) {
        $warnings[] = "❌ Hours limit exceeded: {$current['total_hours']} current + {$total_new_hours} new = {$total_hours} (max: {$limits['max_hours']})";
        $is_overloaded = true;
    }
    
    return [
        'overloaded' => $is_overloaded,
        'warnings' => $warnings,
        'current_workload' => $current,
        'limits' => $limits,
        'projected_total' => [
            'classes' => $total_classes,
            'subjects' => $total_subjects,
            'hours' => $total_hours
        ]
    ];
}

// 🧠 Get overloaded teachers for bulk cleanup - FIXED VERSION
function getOverloadedTeachers($conn, $academic_year) {
    // Create variables for the limits to pass by reference
    $max_classes = MAX_CLASSES_PER_TEACHER;
    $max_subjects = MAX_SUBJECTS_PER_TEACHER;
    $max_hours = MAX_HOURS_PER_WEEK;
    
    $query = "
        SELECT 
            t.id,
            t.full_name,
            COUNT(DISTINCT sa.class_id) as class_count,
            COUNT(DISTINCT sa.subject_id) as subject_count,
            SUM(sa.hours_per_week) as total_hours
        FROM subject_allocations sa
        JOIN staff t ON sa.teacher_id = t.id
        WHERE sa.academic_year = ? AND sa.status = 'Active'
        GROUP BY t.id, t.full_name
        HAVING 
            class_count > ? OR 
            subject_count > ? OR 
            total_hours > ?
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return ["error" => "Prepare failed: " . $conn->error];
    }
    
    $stmt->bind_param("siii", $academic_year, $max_classes, $max_subjects, $max_hours);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $overloaded = [];
    while ($row = $result->fetch_assoc()) {
        $overloaded[] = $row;
    }
    return $overloaded;
}

// 🆕 NEW FUNCTION: Check for unallocated subjects and classes
function getUnallocatedSubjectsClasses($conn, $academic_year) {
    // Get all active classes and subjects that should have allocations
    $query = "
        SELECT 
            c.id as class_id,
            c.class_name,
            s.id as subject_id, 
            s.subject_name,
            COUNT(sa.id) as allocation_count
        FROM classes c
        CROSS JOIN subjects s
        LEFT JOIN subject_allocations sa ON 
            sa.class_id = c.id AND 
            sa.subject_id = s.id AND 
            sa.academic_year = ? AND 
            sa.status = 'Active'
        WHERE c.status = 'Active' 
        AND s.status = 'Active'
        GROUP BY c.id, s.id
        HAVING allocation_count = 0
        ORDER BY c.class_name, s.subject_name
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $academic_year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $unallocated = [];
    while ($row = $result->fetch_assoc()) {
        $unallocated[] = $row;
    }
    return $unallocated;
}

// 🆕 NEW FUNCTION: Get allocation coverage report
function getAllocationCoverageReport($conn, $academic_year) {
    $report = [
        'total_classes' => 0,
        'total_subjects' => 0,
        'total_possible_allocations' => 0,
        'current_allocations' => 0,
        'coverage_percentage' => 0,
        'unallocated_count' => 0
    ];
    
    // Get total classes and subjects
    $count_query = "
        SELECT 
            (SELECT COUNT(*) FROM classes WHERE status='Active') as total_classes,
            (SELECT COUNT(*) FROM subjects WHERE status='Active') as total_subjects,
            (SELECT COUNT(*) FROM subject_allocations WHERE academic_year=? AND status='Active') as current_allocations
    ";
    
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param("s", $academic_year);
    $stmt->execute();
    $count_result = $stmt->get_result()->fetch_assoc();
    
    $report['total_classes'] = $count_result['total_classes'];
    $report['total_subjects'] = $count_result['total_subjects'];
    $report['current_allocations'] = $count_result['current_allocations'];
    $report['total_possible_allocations'] = $report['total_classes'] * $report['total_subjects'];
    $report['unallocated_count'] = $report['total_possible_allocations'] - $report['current_allocations'];
    
    if ($report['total_possible_allocations'] > 0) {
        $report['coverage_percentage'] = round(($report['current_allocations'] / $report['total_possible_allocations']) * 100, 2);
    }
    
    return $report;
}

// 🧠 Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add_allocations'])) {
        $teacher_id = intval($_POST["teacher_id"]);
        $subject_ids = $_POST["subject_ids"] ?? [];
        $class_ids = $_POST["class_ids"] ?? [];
        $hours_per_week = intval($_POST["hours_per_week"]) ?: 5;

        if (!$teacher_id || empty($subject_ids) || empty($class_ids)) {
            $errors[] = "Please select teacher, at least one subject, and at least one class.";
        } else {
            $academic_year = date('Y') . '-' . (date('Y') + 1);

            // Check for teacher overload BEFORE creating allocations
            $overload_check = checkTeacherOverload($conn, $teacher_id, count($subject_ids), count($class_ids), $hours_per_week, $academic_year);
            
            if ($overload_check['overloaded']) {
                $errors[] = "🚨 Teacher workload would exceed production limits:";
                $errors = array_merge($errors, $overload_check['warnings']);
                $errors[] = "📊 Current workload: " . $overload_check['current_workload']['total_hours'] . " hours, " . 
                           $overload_check['current_workload']['current_classes'] . " classes, " . 
                           $overload_check['current_workload']['current_subjects'] . " subjects";
                
                // Show projected totals
                $projected = $overload_check['projected_total'];
                $errors[] = "📈 Projected: {$projected['hours']} hours, {$projected['classes']} classes, {$projected['subjects']} subjects";
            } else {
                $success_count = 0;
                $error_count = 0;
                $existing_allocations = [];

                // Prepare insert statement
                $insert = $conn->prepare("INSERT INTO subject_allocations (teacher_id, subject_id, class_id, hours_per_week, academic_year, status) VALUES (?, ?, ?, ?, ?, 'Active')");
                
                foreach ($subject_ids as $subject_id) {
                    foreach ($class_ids as $class_id) {
                        // Check if allocation already exists
                        $check_stmt = $conn->prepare("SELECT id FROM subject_allocations WHERE teacher_id = ? AND subject_id = ? AND class_id = ? AND academic_year = ?");
                        $check_stmt->bind_param("iiis", $teacher_id, $subject_id, $class_id, $academic_year);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();

                        if ($check_result->num_rows > 0) {
                            $error_count++;
                            // Get details for error message
                            $subject_stmt = $conn->prepare("SELECT subject_name FROM subjects WHERE id = ?");
                            $subject_stmt->bind_param("i", $subject_id);
                            $subject_stmt->execute();
                            $subject_name = $subject_stmt->get_result()->fetch_assoc()['subject_name'] ?? 'Unknown';
                            
                            $class_stmt = $conn->prepare("SELECT class_name FROM classes WHERE id = ?");
                            $class_stmt->bind_param("i", $class_id);
                            $class_stmt->execute();
                            $class_name = $class_stmt->get_result()->fetch_assoc()['class_name'] ?? 'Unknown';
                            
                            $existing_allocations[] = "$subject_name for $class_name";
                            $subject_stmt->close();
                            $class_stmt->close();
                        } else {
                            // Insert new allocation
                            $insert->bind_param("iiiis", $teacher_id, $subject_id, $class_id, $hours_per_week, $academic_year);
                            if ($insert->execute()) {
                                $success_count++;
                            } else {
                                $error_count++;
                            }
                        }
                        $check_stmt->close();
                    }
                }
                $insert->close();

                // Prepare success/error message
                if ($success_count > 0) {
                    $success = "✅ Successfully created $success_count allocation(s)!";
                    // Show updated workload
                    $updated_workload = getTeacherWorkload($conn, $teacher_id, $academic_year);
                    $success .= " Teacher now has {$updated_workload['total_hours']} hours across {$updated_workload['current_classes']} classes teaching {$updated_workload['current_subjects']} subjects.";
                    
                    // Show remaining capacity
                    $remaining_classes = MAX_CLASSES_PER_TEACHER - $updated_workload['current_classes'];
                    $remaining_subjects = MAX_SUBJECTS_PER_TEACHER - $updated_workload['current_subjects'];
                    $remaining_hours = MAX_HOURS_PER_WEEK - $updated_workload['total_hours'];
                    
                    if ($remaining_classes > 0 || $remaining_subjects > 0 || $remaining_hours > 0) {
                        $success .= " Remaining capacity: {$remaining_classes} classes, {$remaining_subjects} subjects, {$remaining_hours} hours.";
                    }
                }
                if ($error_count > 0) {
                    $errors[] = "⚠️ Failed to create $error_count allocation(s). ";
                    if (!empty($existing_allocations)) {
                        $errors[] = "Already exists: " . implode(", ", array_unique($existing_allocations));
                    }
                }
            }
        }
    }

    if (isset($_POST['remove_allocation'])) {
        $allocation_id = $_POST['allocation_id'];
        try {
            $stmt = $conn->prepare("DELETE FROM subject_allocations WHERE id = ?");
            $stmt->bind_param("i", $allocation_id);
            $stmt->execute();
            $success = "✅ Allocation removed successfully!";
        } catch (Exception $e) {
            $errors[] = "❌ Error removing allocation: " . $e->getMessage();
        }
    }

    if (isset($_POST['bulk_remove'])) {
        $allocation_ids = $_POST['allocation_ids'] ?? [];
        if (!empty($allocation_ids)) {
            try {
                $placeholders = str_repeat('?,', count($allocation_ids) - 1) . '?';
                $stmt = $conn->prepare("DELETE FROM subject_allocations WHERE id IN ($placeholders)");
                // Create variables for binding
                $types = str_repeat('i', count($allocation_ids));
                $params = $allocation_ids;
                array_unshift($params, $types);
                $stmt->bind_param(...$params);
                $stmt->execute();
                $success = "✅ Successfully removed " . count($allocation_ids) . " allocation(s)!";
            } catch (Exception $e) {
                $errors[] = "❌ Error removing allocations: " . $e->getMessage();
            }
        } else {
            $errors[] = "⚠️ No allocations selected for removal.";
        }
    }
    
    // 🆕 Handle bulk cleanup of overloaded teachers
    if (isset($_POST['cleanup_overloaded'])) {
        $academic_year = date('Y') . '-' . (date('Y') + 1);
        $overloaded_teachers = getOverloadedTeachers($conn, $academic_year);
        
        if (isset($overloaded_teachers['error'])) {
            $errors[] = "Database error: " . $overloaded_teachers['error'];
        } elseif (empty($overloaded_teachers)) {
            $success = "✅ No overloaded teachers found! All allocations are within production limits.";
        } else {
            $success = "🔍 Found " . count($overloaded_teachers) . " overloaded teachers. Please review and remove excess allocations manually.";
        }
    }
    
    // 🆕 NEW: Handle coverage scan
    if (isset($_POST['scan_coverage'])) {
        $academic_year = date('Y') . '-' . (date('Y') + 1);
        $coverage_report = getAllocationCoverageReport($conn, $academic_year);
        $unallocated = getUnallocatedSubjectsClasses($conn, $academic_year);
        
        if ($coverage_report['coverage_percentage'] < 80) {
            $errors[] = "❌ LOW ALLOCATION COVERAGE: Only {$coverage_report['coverage_percentage']}% of possible allocations are filled!";
            $errors[] = "📊 Coverage Report: {$coverage_report['current_allocations']}/{$coverage_report['total_possible_allocations']} allocations filled";
            $errors[] = "🔍 Found {$coverage_report['unallocated_count']} missing allocations that will cause free lessons";
            
            if (!empty($unallocated)) {
                $errors[] = "📝 Missing allocations include:";
                $sample_missing = array_slice($unallocated, 0, 5);
                foreach ($sample_missing as $missing) {
                    $errors[] = "   - {$missing['class_name']} - {$missing['subject_name']}";
                }
                if (count($unallocated) > 5) {
                    $errors[] = "   ... and " . (count($unallocated) - 5) . " more";
                }
            }
        } else {
            $success = "✅ Good allocation coverage: {$coverage_report['coverage_percentage']}% - This should prevent free lessons in timetable generation!";
        }
    }
}

// 🧩 Fetch teachers (only teachers)
$teachers = $conn->query("SELECT id, full_name FROM staff WHERE position IN ('Teacher','AcademicTeacher') ORDER BY full_name");

// 🧩 Fetch classes
$classes_result = $conn->query("SELECT id, class_name FROM classes WHERE status='Active' ORDER BY class_name ASC");
$classes = $classes_result->fetch_all(MYSQLI_ASSOC);

// 🧩 Fetch subjects via AJAX if teacher selected
if (isset($_GET['ajax_teacher_id'])) {
    $teacher_id = intval($_GET['ajax_teacher_id']);
    $result = $conn->query("
        SELECT s.id, s.subject_name
        FROM teacher_subjects ts
        JOIN subjects s ON ts.subject_id = s.id
        WHERE ts.teacher_id = $teacher_id AND ts.status='Active'
        ORDER BY s.subject_name
    ");

    if ($result->num_rows > 0) {
        echo "<option value=''>Select Subjects</option>";
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['id']}'>{$row['subject_name']}</option>";
        }
    } else {
        echo "<option value=''>No subjects found</option>";
    }
    exit;
}

// 🧩 Enhanced teacher workload display
$teacher_workloads = [];
$teachers_result = $conn->query("SELECT id, full_name FROM staff WHERE position IN ('Teacher','AcademicTeacher') ORDER BY full_name");
while ($teacher = $teachers_result->fetch_assoc()) {
    $teacher_workloads[$teacher['id']] = getTeacherWorkload($conn, $teacher['id'], date('Y') . '-' . (date('Y') + 1));
}

// 🧩 Fetch current allocations for display
$allocations_query = "
    SELECT sa.id, sa.teacher_id, sa.subject_id, sa.class_id, sa.hours_per_week, sa.academic_year,
           t.full_name as teacher_name, s.subject_name, c.class_name
    FROM subject_allocations sa
    JOIN staff t ON sa.teacher_id = t.id
    JOIN subjects s ON sa.subject_id = s.id
    JOIN classes c ON sa.class_id = c.id
    WHERE sa.status = 'Active'
    ORDER BY t.full_name, c.class_name, s.subject_name
";
$allocations_result = $conn->query($allocations_query);
$allocations = $allocations_result->fetch_all(MYSQLI_ASSOC);

// Group allocations by teacher for better organization
$allocations_by_teacher = [];
foreach ($allocations as $allocation) {
    $teacher_name = $allocation['teacher_name'];
    if (!isset($allocations_by_teacher[$teacher_name])) {
        $allocations_by_teacher[$teacher_name] = [];
    }
    $allocations_by_teacher[$teacher_name][] = $allocation;
}

// 🆕 Get overloaded teachers for the cleanup section
$academic_year = date('Y') . '-' . (date('Y') + 1);
$overloaded_teachers = getOverloadedTeachers($conn, $academic_year);

// 🆕 NEW: Get coverage data for display
$coverage_report = getAllocationCoverageReport($conn, $academic_year);
$unallocated = getUnallocatedSubjectsClasses($conn, $academic_year);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Production-Ready Teacher Allocation System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background: linear-gradient(135deg, #001f3f, #0059b3);
  color: #fff;
  margin: 0;
  padding: 30px;
}
.container {
  background: #fff;
  color: #000;
  border-radius: 10px;
  padding: 25px;
  max-width: 1600px;
  margin: 0 auto;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
h2 {
  text-align: center;
  color: #003366;
  margin-bottom: 25px;
}
.card { border: none; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
.table th { background-color: #2c3e50; color: white; }
.subject-checkbox { margin-right: 8px; }
.class-checkbox { margin-right: 8px; }
.subject-item, .class-item { padding: 8px 12px; border: 1px solid #dee2e6; border-radius: 4px; margin-bottom: 5px; background: white; }
.selected-count { background: #e7f3ff; padding: 4px 8px; border-radius: 4px; font-size: 0.9em; }
.teacher-section { border-left: 4px solid #2c3e50; padding-left: 15px; margin-bottom: 20px; }
.bulk-actions { background: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
.toggle-btn { cursor: pointer; }
.collapsible { transition: all 0.3s ease; }
.workload-info { background: #e8f4fd; border-left: 4px solid #17a2b8; padding: 10px; margin: 10px 0; border-radius: 4px; }
.workload-warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 10px 0; border-radius: 4px; }
.workload-danger { background: #f8d7da; border-left: 4px solid #dc3545; padding: 10px; margin: 10px 0; border-radius: 4px; }
.workload-success { background: #d1edff; border-left: 4px solid #28a745; padding: 10px; margin: 10px 0; border-radius: 4px; }
.teacher-option { display: flex; justify-content: space-between; }
.workload-badge { font-size: 0.8em; padding: 2px 6px; border-radius: 10px; }
.production-limits { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
.overload-alert { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; padding: 10px; margin: 10px 0; }
.capacity-meter { height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden; margin: 5px 0; }
.capacity-fill { height: 100%; border-radius: 4px; }
.coverage-excellent { background: #d4edda; border-left: 4px solid #28a745; }
.coverage-good { background: #fff3cd; border-left: 4px solid #ffc107; }
.coverage-poor { background: #f8d7da; border-left: 4px solid #dc3545; }
</style>
<script>
function loadSubjects(teacherId) {
  const subjectDropdown = document.getElementById('subject_ids_container');
  subjectDropdown.innerHTML = "<div class='text-center py-2'><i class='fas fa-spinner fa-spin'></i> Loading subjects...</div>";

  fetch("?ajax_teacher_id=" + teacherId)
    .then(res => res.text())
    .then(data => {
      subjectDropdown.innerHTML = `
        <div class="selected-count mb-2">
          <span id="selectedSubjectsCount">0</span> subjects selected
        </div>
        <div class="subject-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px;">
          ${data}
        </div>
        <div class="mt-2">
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllSubjects()">
            <i class="fas fa-check-double me-1"></i> Select All
          </button>
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllSubjects()">
            <i class="fas fa-times me-1"></i> Deselect All
          </button>
        </div>
      `;
      
      // Add checkboxes to subjects
      const subjectOptions = subjectDropdown.querySelectorAll('option');
      let checkboxHTML = '';
      subjectOptions.forEach(option => {
        if (option.value) {
          checkboxHTML += `
            <div class="subject-item">
              <div class="form-check">
                <input class="form-check-input subject-checkbox" type="checkbox" 
                       name="subject_ids[]" value="${option.value}" 
                       id="subject_${option.value}">
                <label class="form-check-label w-100" for="subject_${option.value}">
                  ${option.textContent}
                </label>
              </div>
            </div>
          `;
        }
      });
      
      subjectDropdown.querySelector('.subject-list').innerHTML = checkboxHTML;
      updateSelectedCounts();
      
      // Add event listeners to new checkboxes
      document.querySelectorAll('.subject-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCounts);
      });
      
      // Show teacher workload info
      showTeacherWorkload(teacherId);
    })
    .catch(() => {
      subjectDropdown.innerHTML = "<div class='text-danger'>Error loading subjects</div>";
    });
}

function showTeacherWorkload(teacherId) {
  const workloadInfo = document.getElementById('teacher_workload_info');
  if (!workloadInfo) return;
  
  // Get teacher workload data from PHP
  const teacherOption = document.querySelector(`#teacher_id option[value="${teacherId}"]`);
  if (teacherOption && teacherOption.dataset.workload) {
    const workload = JSON.parse(teacherOption.dataset.workload);
    const totalHours = workload.total_hours || 0;
    const currentClasses = workload.current_classes || 0;
    const currentSubjects = workload.current_subjects || 0;
    
    const hoursPercent = Math.min(100, (totalHours / <?= MAX_HOURS_PER_WEEK ?>) * 100);
    const classesPercent = Math.min(100, (currentClasses / <?= MAX_CLASSES_PER_TEACHER ?>) * 100);
    const subjectsPercent = Math.min(100, (currentSubjects / <?= MAX_SUBJECTS_PER_TEACHER ?>) * 100);
    
    let workloadClass = 'workload-success';
    if (totalHours > 20) workloadClass = 'workload-danger';
    else if (totalHours > 15) workloadClass = 'workload-warning';
    
    workloadInfo.innerHTML = `
      <div class="${workloadClass}">
        <strong>📊 Current Workload:</strong><br>
        • ${totalHours} hours (${<?= MAX_HOURS_PER_WEEK ?> - totalHours} remaining)<br>
        • ${currentClasses} classes (${<?= MAX_CLASSES_PER_TEACHER ?> - currentClasses} remaining)<br>
        • ${currentSubjects} subjects (${<?= MAX_SUBJECTS_PER_TEACHER ?> - currentSubjects} remaining)
        
        <div class="capacity-meter">
          <div class="capacity-fill bg-${totalHours > 20 ? 'danger' : totalHours > 15 ? 'warning' : 'success'}" 
               style="width: ${hoursPercent}%"></div>
        </div>
        <small>Hours: ${totalHours}/<?= MAX_HOURS_PER_WEEK ?> (${Math.round(hoursPercent)}%)</small>
      </div>
    `;
  }
}

function updateSelectedCounts() {
  const subjectCheckboxes = document.querySelectorAll('.subject-checkbox:checked');
  const classCheckboxes = document.querySelectorAll('.class-checkbox:checked');
  
  if (document.getElementById('selectedSubjectsCount')) {
    document.getElementById('selectedSubjectsCount').textContent = subjectCheckboxes.length;
  }
  if (document.getElementById('selectedClassesCount')) {
    document.getElementById('selectedClassesCount').textContent = classCheckboxes.length;
  }
  
  // Calculate and show potential workload
  const teacherSelect = document.getElementById('teacher_id');
  const hoursInput = document.getElementById('hours_per_week');
  
  if (teacherSelect.value && hoursInput.value) {
    showPotentialWorkload(teacherSelect.value, subjectCheckboxes.length, classCheckboxes.length, parseInt(hoursInput.value));
  }
}

function showPotentialWorkload(teacherId, subjectsCount, classesCount, hoursPerWeek) {
  const potentialInfo = document.getElementById('potential_workload_info');
  if (!potentialInfo) return;
  
  const totalNewHours = subjectsCount * classesCount * hoursPerWeek;
  
  if (subjectsCount > 0 && classesCount > 0) {
    // Get current workload
    const teacherOption = document.querySelector(`#teacher_id option[value="${teacherId}"]`);
    if (teacherOption && teacherOption.dataset.workload) {
      const workload = JSON.parse(teacherOption.dataset.workload);
      const currentHours = workload.total_hours || 0;
      const currentClasses = workload.current_classes || 0;
      const currentSubjects = workload.current_subjects || 0;
      
      const projectedHours = currentHours + totalNewHours;
      const projectedClasses = currentClasses + classesCount;
      const projectedSubjects = currentSubjects + subjectsCount;
      
      let alertClass = 'workload-info';
      if (projectedHours > <?= MAX_HOURS_PER_WEEK ?> || projectedClasses > <?= MAX_CLASSES_PER_TEACHER ?> || projectedSubjects > <?= MAX_SUBJECTS_PER_TEACHER ?>) {
        alertClass = 'workload-danger';
      } else if (projectedHours > 20) {
        alertClass = 'workload-warning';
      }
      
      potentialInfo.innerHTML = `
        <div class="${alertClass}">
          <strong>🔮 Projected Workload After Allocation:</strong><br>
          • Hours: ${currentHours} + ${totalNewHours} = <strong>${projectedHours}/<?= MAX_HOURS_PER_WEEK ?></strong><br>
          • Classes: ${currentClasses} + ${classesCount} = <strong>${projectedClasses}/<?= MAX_CLASSES_PER_TEACHER ?></strong><br>
          • Subjects: ${currentSubjects} + ${subjectsCount} = <strong>${projectedSubjects}/<?= MAX_SUBJECTS_PER_TEACHER ?></strong>
        </div>
      `;
    }
    potentialInfo.style.display = 'block';
  } else {
    potentialInfo.style.display = 'none';
  }
}

function selectAllSubjects() {
  document.querySelectorAll('.subject-checkbox').forEach(checkbox => {
    checkbox.checked = true;
  });
  updateSelectedCounts();
}

function deselectAllSubjects() {
  document.querySelectorAll('.subject-checkbox').forEach(checkbox => {
    checkbox.checked = false;
  });
  updateSelectedCounts();
}

function selectAllClasses() {
  document.querySelectorAll('.class-checkbox').forEach(checkbox => {
    checkbox.checked = true;
  });
  updateSelectedCounts();
}

function deselectAllClasses() {
  document.querySelectorAll('.class-checkbox').forEach(checkbox => {
    checkbox.checked = false;
  });
  updateSelectedCounts();
}

// Toggle section visibility
function toggleSection(sectionId) {
  const section = document.getElementById('section_' + sectionId);
  const icon = section.previousElementSibling.querySelector('.fa-chevron-down, .fa-chevron-right');
  
  if (section.style.display === 'none') {
    section.style.display = 'block';
    icon.classList.remove('fa-chevron-right');
    icon.classList.add('fa-chevron-down');
  } else {
    section.style.display = 'none';
    icon.classList.remove('fa-chevron-down');
    icon.classList.add('fa-chevron-right');
  }
}

// Toggle all sections
function toggleAllSections() {
  const sections = document.querySelectorAll('.collapsible');
  const allVisible = Array.from(sections).every(section => section.style.display !== 'none');
  
  sections.forEach(section => {
    section.style.display = allVisible ? 'none' : 'block';
  });
  
  // Update icons
  document.querySelectorAll('.toggle-btn .fa-chevron-down, .toggle-btn .fa-chevron-right').forEach(icon => {
    if (allVisible) {
      icon.classList.remove('fa-chevron-down');
      icon.classList.add('fa-chevron-right');
    } else {
      icon.classList.remove('fa-chevron-right');
      icon.classList.add('fa-chevron-down');
    }
  });
}

// Toggle all preview items for bulk selection
function toggleAllPreviewItems(checkbox) {
  document.querySelectorAll('.preview-checkbox').forEach(item => {
    item.checked = checkbox.checked;
  });
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('multiAllocationForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      const selectedSubjects = document.querySelectorAll('.subject-checkbox:checked').length;
      const selectedClasses = document.querySelectorAll('.class-checkbox:checked').length;
      const teacherSelected = document.getElementById('teacher_id').value;
      
      if (!teacherSelected) {
        e.preventDefault();
        alert('Please select a teacher.');
        return;
      }
      
      if (selectedSubjects === 0) {
        e.preventDefault();
        alert('Please select at least one subject.');
        return;
      }
      
      if (selectedClasses === 0) {
        e.preventDefault();
        alert('Please select at least one class.');
        return;
      }
    });
  }

  // Initialize all sections as expanded
  document.querySelectorAll('.collapsible').forEach(section => {
    section.style.display = 'block';
  });

  // Initialize class checkboxes event listeners
  document.querySelectorAll('.class-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCounts);
  });

  // Update workload when hours change
  document.getElementById('hours_per_week')?.addEventListener('input', updateSelectedCounts);
});
</script>
</head>
<body>
<div class="container">
  <h2>🏫 Production-Ready Teacher Allocation System</h2>

  <!-- Production Limits Banner -->
  <div class="production-limits">
    <div class="row text-center">
      <div class="col-md-3">
        <h4><i class="fas fa-chalkboard-teacher"></i> <?= MAX_CLASSES_PER_TEACHER ?> Classes</h4>
        <small>Max classes per teacher</small>
      </div>
      <div class="col-md-3">
        <h4><i class="fas fa-book"></i> <?= MAX_SUBJECTS_PER_TEACHER ?> Subjects</h4>
        <small>Max subjects per teacher</small>
      </div>
      <div class="col-md-3">
        <h4><i class="fas fa-clock"></i> <?= MAX_HOURS_PER_WEEK ?> Hours</h4>
        <small>Max teaching hours per week</small>
      </div>
      <div class="col-md-3">
        <h4><i class="fas fa-calendar-day"></i> <?= MAX_CLASSES_PER_DAY ?> Classes/Day</h4>
        <small>Max classes per day</small>
      </div>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i><?= implode("<br>", $errors) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="row">
    <!-- Left Column -->
    <div class="col-lg-6">
      <!-- Multi-Allocation Form -->
      <div class="card">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Create Production Allocations</h5>
        </div>
        <div class="card-body">
          <form method="POST" id="multiAllocationForm">
            <div class="mb-3">
              <label for="teacher_id" class="form-label">Select Teacher</label>
              <select class="form-select" id="teacher_id" name="teacher_id" onchange="loadSubjects(this.value)" required>
                <option value="">Choose a teacher...</option>
                <?php 
                $teachers->data_seek(0); // Reset pointer
                while ($t = $teachers->fetch_assoc()): 
                  $workload = $teacher_workloads[$t['id']] ?? ['total_hours' => 0, 'current_classes' => 0, 'current_subjects' => 0];
                  $workload_class = '';
                  if ($workload['total_hours'] > 20) $workload_class = 'workload-danger';
                  elseif ($workload['total_hours'] > 15) $workload_class = 'workload-warning';
                  else $workload_class = 'workload-success';
                ?>
                  <option value="<?= $t['id'] ?>" data-workload='<?= json_encode($workload) ?>'>
                    <?= htmlspecialchars($t['full_name']) ?> 
                    (<?= $workload['total_hours'] ?>h, <?= $workload['current_classes'] ?>c, <?= $workload['current_subjects'] ?>s)
                  </option>
                <?php endwhile; ?>
              </select>
              <div class="form-text">
                <strong>Production Limits:</strong> Max <?= MAX_HOURS_PER_WEEK ?> hours, <?= MAX_CLASSES_PER_TEACHER ?> classes, <?= MAX_SUBJECTS_PER_TEACHER ?> subjects per teacher
              </div>
            </div>

            <div id="teacher_workload_info"></div>
            <div id="potential_workload_info" style="display: none;"></div>

            <div class="mb-3">
              <label class="form-label">Select Subjects</label>
              <div id="subject_ids_container">
                <div class="text-muted">Please select a teacher first to load subjects</div>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Select Classes</label>
              <div class="selected-count mb-2">
                <span id="selectedClassesCount">0</span> classes selected
              </div>
              <div class="class-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px;">
                <?php foreach ($classes as $class): ?>
                  <div class="class-item">
                    <div class="form-check">
                      <input class="form-check-input class-checkbox" type="checkbox" 
                             name="class_ids[]" value="<?= $class['id'] ?>" 
                             id="class_<?= $class['id'] ?>">
                      <label class="form-check-label w-100" for="class_<?= $class['id'] ?>">
                        <?= htmlspecialchars($class['class_name']) ?>
                      </label>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="mt-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllClasses()">
                  <i class="fas fa-check-double me-1"></i> Select All
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllClasses()">
                  <i class="fas fa-times me-1"></i> Deselect All
                </button>
              </div>
            </div>

            <div class="mb-3">
              <label for="hours_per_week" class="form-label">Hours per Week (for all allocations)</label>
              <input type="number" class="form-control" id="hours_per_week" name="hours_per_week"
                     min="1" max="10" value="5" required>
              <div class="form-text">Same hours will be applied to all selected subject-class combinations.</div>
            </div>

            <button type="submit" name="add_allocations" class="btn btn-success w-100">
              <i class="fas fa-layer-group me-1"></i> Create Production Allocations
            </button>
          </form>
        </div>
      </div>

      <!-- Allocation Coverage Analysis -->
      <div class="card mt-4">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Allocation Coverage Analysis</h5>
        </div>
        <div class="card-body">
          <?php
          $coverage_class = 'coverage-excellent';
          if ($coverage_report['coverage_percentage'] < 60) $coverage_class = 'coverage-poor';
          elseif ($coverage_report['coverage_percentage'] < 80) $coverage_class = 'coverage-good';
          ?>
          
          <div class="<?= $coverage_class ?> p-3 rounded mb-3">
            <h6>📊 Allocation Coverage: <?= $coverage_report['coverage_percentage'] ?>%</h6>
            <div class="capacity-meter">
              <div class="capacity-fill bg-<?= $coverage_report['coverage_percentage'] < 60 ? 'danger' : ($coverage_report['coverage_percentage'] < 80 ? 'warning' : 'success') ?>" 
                   style="width: <?= $coverage_report['coverage_percentage'] ?>%"></div>
            </div>
            <small>
              <?= $coverage_report['current_allocations'] ?> / <?= $coverage_report['total_possible_allocations'] ?> possible allocations filled<br>
              <?= $coverage_report['unallocated_count'] ?> missing allocations
            </small>
          </div>

          <?php if (!empty($unallocated)): ?>
            <div class="alert alert-warning">
              <h6><i class="fas fa-exclamation-triangle me-2"></i>Missing Allocations Found</h6>
              <p class="mb-2">These subject-class combinations need teachers to avoid free lessons:</p>
              <div style="max-height: 200px; overflow-y: auto;">
                <table class="table table-sm table-bordered">
                  <thead>
                    <tr>
                      <th>Class</th>
                      <th>Subject</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach (array_slice($unallocated, 0, 10) as $missing): // Show first 10 ?>
                      <tr>
                        <td><?= htmlspecialchars($missing['class_name']) ?></td>
                        <td><?= htmlspecialchars($missing['subject_name']) ?></td>
                        <td><span class="badge bg-danger">No Teacher</span></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                <?php if (count($unallocated) > 10): ?>
                  <small class="text-muted">... and <?= count($unallocated) - 10 ?> more missing allocations</small>
                <?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <div class="alert alert-success">
              <i class="fas fa-check-circle me-2"></i>All subject-class combinations have teacher allocations!
            </div>
          <?php endif; ?>

          <form method="POST">
            <button type="submit" name="scan_coverage" class="btn btn-info w-100">
              <i class="fas fa-sync-alt me-1"></i> Rescan Coverage
            </button>
          </form>
          
          <div class="mt-3">
            <h6>🎯 Recommended Actions:</h6>
            <ul class="small">
              <li>Aim for <strong>80%+ coverage</strong> for complete timetable</li>
              <li>Assign teachers to missing subject-class combinations</li>
              <li>Balance workload across all teachers</li>
              <li>Ensure core subjects have priority allocations</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Overloaded Teachers Cleanup -->
      <div class="card mt-4">
        <div class="card-header bg-warning text-dark">
          <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Overload Detection & Cleanup</h5>
        </div>
        <div class="card-body">
          <?php if (isset($overloaded_teachers['error'])): ?>
            <div class="alert alert-danger">
              <i class="fas fa-exclamation-circle me-2"></i><?= $overloaded_teachers['error'] ?>
            </div>
          <?php elseif (!empty($overloaded_teachers)): ?>
            <div class="alert alert-danger">
              <h6><i class="fas fa-exclamation-circle me-2"></i>Found <?= count($overloaded_teachers) ?> Overloaded Teachers</h6>
              <p class="mb-2">These teachers exceed production limits and may cause timetable conflicts:</p>
              <ul class="mb-3">
                <?php foreach ($overloaded_teachers as $teacher): ?>
                  <li>
                    <strong><?= htmlspecialchars($teacher['full_name']) ?></strong>: 
                    <?= $teacher['class_count'] ?> classes, 
                    <?= $teacher['subject_count'] ?> subjects, 
                    <?= $teacher['total_hours'] ?> hours
                  </li>
                <?php endforeach; ?>
              </ul>
              <p class="mb-0"><small>Use the bulk removal feature below to fix these allocations.</small></p>
            </div>
          <?php else: ?>
            <div class="alert alert-success">
              <i class="fas fa-check-circle me-2"></i>No overloaded teachers found! All allocations are within production limits.
            </div>
          <?php endif; ?>
          
          <form method="POST">
            <button type="submit" name="cleanup_overloaded" class="btn btn-warning w-100">
              <i class="fas fa-search me-1"></i> Scan for Overloaded Teachers
            </button>
          </form>
        </div>
      </div>

      <!-- Allocation Strategy Guide -->
      <div class="card mt-4">
        <div class="card-header bg-secondary text-white">
          <h5 class="mb-0"><i class="fas fa-chess-board me-2"></i>Allocation Strategy Guide</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <h6>✅ Do This:</h6>
              <ul class="small text-success">
                <li>Start with core subjects (Math, Science, English)</li>
                <li>Assign multiple teachers to large classes</li>
                <li>Balance workload across all teachers</li>
                <li>Use subject specialists for advanced topics</li>
                <li>Ensure every class has all required subjects allocated</li>
              </ul>
            </div>
            <div class="col-md-6">
              <h6>❌ Avoid This:</h6>
              <ul class="small text-danger">
                <li>Leaving subjects without teachers</li>
                <li>Overloading few teachers (causes conflicts)</li>
                <li>Missing allocations for required subjects</li>
                <li>Ignoring coverage percentage</li>
                <li>Creating impossible teacher schedules</li>
              </ul>
            </div>
          </div>
          
          <div class="mt-3 p-3 bg-light rounded">
            <h6>📈 Minimum Coverage Targets:</h6>
            <div class="row text-center">
              <div class="col-4">
                <div class="text-danger">❌ &lt;60%</div>
                <small>Critical - Many free lessons</small>
              </div>
              <div class="col-4">
                <div class="text-warning">⚠️ 60-80%</div>
                <small>Needs improvement</small>
              </div>
              <div class="col-4">
                <div class="text-success">✅ 80-100%</div>
                <small>Good coverage</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Current Allocations -->
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>Current Teacher Allocations
          </h5>
          <div>
            <span class="badge bg-light text-dark me-2"><?= count($allocations) ?> total</span>
            <button class="btn btn-sm btn-light" onclick="toggleAllSections()">
              <i class="fas fa-expand me-1"></i> Toggle All
            </button>
          </div>
        </div>
        <div class="card-body">
          <?php if (empty($allocations)): ?>
            <div class="text-center py-5">
              <i class="fas fa-unlink fa-4x text-muted mb-3"></i>
              <h4 class="text-muted">No Allocations Found</h4>
              <p class="text-muted">No teacher allocations have been created yet.</p>
            </div>
          <?php else: ?>
            <!-- Bulk Actions -->
            <div class="bulk-actions">
              <form method="POST" id="bulkForm">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <input type="checkbox" id="selectAllPreview" onchange="toggleAllPreviewItems(this)">
                    <label for="selectAllPreview" class="form-check-label ms-2">
                      Select all for bulk removal
                    </label>
                  </div>
                  <button type="submit" name="bulk_remove" class="btn btn-sm btn-danger" 
                          onclick="return confirm('Are you sure you want to remove all selected allocations? This will help resolve timetable conflicts.')">
                    <i class="fas fa-trash me-1"></i> Remove Selected
                  </button>
                </div>
              </form>
            </div>

            <!-- Grouped by Teacher -->
            <?php foreach ($allocations_by_teacher as $teacher_name => $teacher_allocations): 
              $teacher_id = $teacher_allocations[0]['teacher_id'];
              $workload = $teacher_workloads[$teacher_id] ?? ['total_hours' => 0, 'current_classes' => 0, 'current_subjects' => 0];
              $workload_class = '';
              if ($workload['total_hours'] > 20) $workload_class = 'workload-danger';
              elseif ($workload['total_hours'] > 15) $workload_class = 'workload-warning';
              else $workload_class = 'workload-success';
            ?>
              <div class="teacher-section">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="mb-0 toggle-btn" onclick="toggleSection('<?= preg_replace('/[^a-zA-Z0-9]/', '_', $teacher_name) ?>')">
                    <i class="fas fa-chevron-down me-2"></i>
                    <?= htmlspecialchars($teacher_name) ?>
                    <span class="badge bg-secondary ms-2"><?= count($teacher_allocations) ?> allocations</span>
                  </h6>
                  <div class="<?= $workload_class ?> p-2 rounded">
                    <small>
                      <?= $workload['total_hours'] ?>h / <?= MAX_HOURS_PER_WEEK ?>h • 
                      <?= $workload['current_classes'] ?>c / <?= MAX_CLASSES_PER_TEACHER ?>c • 
                      <?= $workload['current_subjects'] ?>s / <?= MAX_SUBJECTS_PER_TEACHER ?>s
                    </small>
                  </div>
                </div>
                
                <div class="collapsible" id="section_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $teacher_name) ?>">
                  <div class="table-responsive">
                    <table class="table table-sm table-hover">
                      <thead>
                        <tr>
                          <th width="40">
                            <input type="checkbox" class="section-select-all" 
                                   data-section="<?= preg_replace('/[^a-zA-Z0-9]/', '_', $teacher_name) ?>">
                          </th>
                          <th>Class</th>
                          <th>Subject</th>
                          <th>Hours/Week</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($teacher_allocations as $allocation): ?>
                          <tr>
                            <td>
                              <input type="checkbox" name="allocation_ids[]" 
                                     value="<?= $allocation['id'] ?>" 
                                     class="preview-checkbox"
                                     form="bulkForm">
                            </td>
                            <td><?= htmlspecialchars($allocation['class_name']) ?></td>
                            <td><?= htmlspecialchars($allocation['subject_name']) ?></td>
                            <td>
                              <span class="badge bg-info"><?= $allocation['hours_per_week'] ?> hrs</span>
                            </td>
                            <td>
                              <form method="POST" class="d-inline">
                                <input type="hidden" name="allocation_id" value="<?= $allocation['id'] ?>">
                                <button type="submit" name="remove_allocation"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Remove <?= htmlspecialchars($allocation['subject_name']) ?> from <?= htmlspecialchars($allocation['class_name']) ?>? This will help prevent timetable conflicts.')">
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
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>