<?php 
session_start();
require_once 'db.php';

// Authentication (optional)
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'AcademicTeacher') {
//     header("Location: login.php");
//     exit();
// }

$academic_teacher_name = $_SESSION['full_name'] ?? 'Academic Teacher';
$error = '';
$success = '';

// 🧠 Determine academic year format (e.g. 2025-2026)
$currentYear = date('Y');
$nextYear = $currentYear + 1;
$academic_year = "{$currentYear}-{$nextYear}";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_subjects'])) {
        $class_id = $_POST['class_id'];
        $subject_ids = $_POST['subject_ids'] ?? [];
        $hours_per_week = $_POST['hours_per_week'] ?? 0;

        try {
            if (empty($subject_ids)) {
                $error = "Please select at least one subject.";
            } else {
                $success_count = 0;
                $error_count = 0;
                $errors = [];

                foreach ($subject_ids as $subject_id) {
                    // Check if mapping already exists for this academic year
                    $check_stmt = $conn->prepare("SELECT id FROM class_subjects WHERE class_id = ? AND subject_id = ? AND academic_year = ?");
                    $check_stmt->bind_param("iis", $class_id, $subject_id, $academic_year);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();

                    if ($check_result->num_rows > 0) {
                        $error_count++;
                        // Get subject name for error message
                        $subject_stmt = $conn->prepare("SELECT subject_name FROM subjects WHERE id = ?");
                        $subject_stmt->bind_param("i", $subject_id);
                        $subject_stmt->execute();
                        $subject_result = $subject_stmt->get_result();
                        $subject_name = $subject_result->fetch_assoc()['subject_name'] ?? 'Unknown';
                        $errors[] = "Subject '$subject_name' is already assigned to this class.";
                        $subject_stmt->close();
                    } else {
                        // Insert class-subject assignment
                        $stmt = $conn->prepare("
                            INSERT INTO class_subjects (class_id, subject_id, hours_per_week, academic_year)
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->bind_param("iiis", $class_id, $subject_id, $hours_per_week, $academic_year);
                        if ($stmt->execute()) {
                            $success_count++;
                        } else {
                            $error_count++;
                            $errors[] = "Error assigning subject ID $subject_id.";
                        }
                        $stmt->close();
                    }
                    $check_stmt->close();
                }

                // Prepare success/error message
                if ($success_count > 0) {
                    $success = "Successfully assigned $success_count subject(s) to class!";
                }
                if ($error_count > 0) {
                    $error = "Failed to assign $error_count subject(s). " . implode(" ", $errors);
                }
            }
        } catch (Exception $e) {
            $error = "Error assigning subjects: " . $e->getMessage();
        }
    }

    if (isset($_POST['remove_subject'])) {
        $mapping_id = $_POST['mapping_id'];
        try {
            $stmt = $conn->prepare("DELETE FROM class_subjects WHERE id = ?");
            $stmt->bind_param("i", $mapping_id);
            $stmt->execute();
            $success = "Subject removed from class successfully!";
        } catch (Exception $e) {
            $error = "Error removing subject: " . $e->getMessage();
        }
    }

    if (isset($_POST['bulk_remove'])) {
        $mapping_ids = $_POST['mapping_ids'] ?? [];
        if (!empty($mapping_ids)) {
            try {
                $placeholders = str_repeat('?,', count($mapping_ids) - 1) . '?';
                $stmt = $conn->prepare("DELETE FROM class_subjects WHERE id IN ($placeholders)");
                $stmt->bind_param(str_repeat('i', count($mapping_ids)), ...$mapping_ids);
                $stmt->execute();
                $success = "Successfully removed " . count($mapping_ids) . " subject assignment(s)!";
            } catch (Exception $e) {
                $error = "Error removing subjects: " . $e->getMessage();
            }
        } else {
            $error = "No subjects selected for removal.";
        }
    }
}

// Fetch data for dropdowns and display
try {
    // Active classes
    $classes_result = $conn->query("SELECT id, class_name FROM classes WHERE status = 'Active' ORDER BY class_name");
    $classes = $classes_result->fetch_all(MYSQLI_ASSOC);

    // Active subjects
    $subjects_result = $conn->query("SELECT id, subject_name FROM subjects WHERE status = 'Active' ORDER BY subject_name");
    $subjects = $subjects_result->fetch_all(MYSQLI_ASSOC);

    // Current mappings for this academic year
    $mappings_query = "
        SELECT cs.id, cs.class_id, cs.subject_id, cs.hours_per_week, cs.academic_year,
               c.class_name, s.subject_name
        FROM class_subjects cs
        JOIN classes c ON cs.class_id = c.id
        JOIN subjects s ON cs.subject_id = s.id
        WHERE cs.academic_year = '$academic_year'
        ORDER BY c.class_name, s.subject_name
    ";
    $mappings_result = $conn->query($mappings_query);
    $mappings = $mappings_result->fetch_all(MYSQLI_ASSOC);

    // Group mappings by class for better organization
    $mappings_by_class = [];
    foreach ($mappings as $mapping) {
        $class_name = $mapping['class_name'];
        if (!isset($mappings_by_class[$class_name])) {
            $mappings_by_class[$class_name] = [];
        }
        $mappings_by_class[$class_name][] = $mapping;
    }

} catch (Exception $e) {
    $error = "Error fetching data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Class Subjects - School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar { background-color: #2c3e50; }
        body { padding-top: 80px; background-color: #f8f9fa; }
        .card { border: none; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
        .table th { background-color: #2c3e50; color: white; }
        .subject-checkbox { margin-right: 8px; }
        .subject-item { padding: 8px 12px; border: 1px solid #dee2e6; border-radius: 4px; margin-bottom: 5px; background: white; }
        .selected-count { background: #e7f3ff; padding: 4px 8px; border-radius: 4px; font-size: 0.9em; }
        .class-section { border-left: 4px solid #2c3e50; padding-left: 15px; margin-bottom: 20px; }
        .bulk-actions { background: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .toggle-btn { cursor: pointer; }
        .collapsible { transition: all 0.3s ease; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="academic_dashboard.php">
            <i class="fas fa-user-graduate me-2"></i>Academic Teacher Dashboard
        </a>
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3">
                <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($academic_teacher_name) ?>
            </span>
            <a class="btn btn-outline-light btn-sm me-2" href="generate_timetable.php">
                <i class="fas fa-arrow-left me-1"></i> Back to Timetable
            </a>
            <a class="btn btn-outline-light btn-sm" href="academic_dashboard.php">
                <i class="fas fa-home me-1"></i> Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1">Manage Class Subjects (<?= $academic_year ?>)</h1>
            <p class="text-muted mb-0">Assign multiple subjects to classes quickly and efficiently.</p>
        </div>
    </div>

    <!-- Alerts -->
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Multi-Subject Assignment Form -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Assign Multiple Subjects</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="multiSubjectForm">
                        <div class="mb-3">
                            <label for="class_id" class="form-label">Select Class</label>
                            <select class="form-select" id="class_id" name="class_id" required>
                                <option value="">Choose a class...</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['class_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Subjects</label>
                            <div class="selected-count mb-2">
                                <span id="selectedCount">0</span> subjects selected
                            </div>
                            <div class="subject-list" style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px;">
                                <?php foreach ($subjects as $subject): ?>
                                    <div class="subject-item">
                                        <div class="form-check">
                                            <input class="form-check-input subject-checkbox" type="checkbox" 
                                                   name="subject_ids[]" value="<?= $subject['id'] ?>" 
                                                   id="subject_<?= $subject['id'] ?>">
                                            <label class="form-check-label w-100" for="subject_<?= $subject['id'] ?>">
                                                <?= htmlspecialchars($subject['subject_name']) ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllSubjects()">
                                    <i class="fas fa-check-double me-1"></i> Select All
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllSubjects()">
                                    <i class="fas fa-times me-1"></i> Deselect All
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="hours_per_week" class="form-label">Hours per Week (for all selected subjects)</label>
                            <input type="number" class="form-control" id="hours_per_week" name="hours_per_week"
                                   min="1" max="20" value="5" required>
                            <div class="form-text">Same hours will be applied to all selected subjects.</div>
                        </div>

                        <button type="submit" name="add_subjects" class="btn btn-primary w-100">
                            <i class="fas fa-layer-group me-1"></i> Assign Selected Subjects
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="generate_timetable.php" class="btn btn-outline-success">
                            <i class="fas fa-table me-1"></i> Generate Timetable
                        </a>
                        <a href="subject_allocation.php" class="btn btn-outline-info">
                            <i class="fas fa-users me-1"></i> Assign Teachers to Subjects
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Assignments -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Current Class-Subject Assignments
                    </h5>
                    <div>
                        <span class="badge bg-primary me-2"><?= count($mappings) ?> total</span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleAllSections()">
                            <i class="fas fa-expand me-1"></i> Toggle All
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($mappings)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-unlink fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No Assignments Found</h4>
                            <p class="text-muted">No subjects have been assigned for <?= $academic_year ?> yet.</p>
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
                                            onclick="return confirm('Are you sure you want to remove all selected subjects?')">
                                        <i class="fas fa-trash me-1"></i> Remove Selected
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Grouped by Class -->
                        <?php foreach ($mappings_by_class as $class_name => $class_mappings): ?>
                            <div class="class-section">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 toggle-btn" onclick="toggleSection('<?= preg_replace('/[^a-zA-Z0-9]/', '_', $class_name) ?>')">
                                        <i class="fas fa-chevron-down me-2"></i>
                                        <?= htmlspecialchars($class_name) ?>
                                        <span class="badge bg-secondary ms-2"><?= count($class_mappings) ?> subjects</span>
                                    </h6>
                                    <small class="text-muted">Click to expand/collapse</small>
                                </div>
                                
                                <div class="collapsible" id="section_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $class_name) ?>">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="40">
                                                        <input type="checkbox" class="section-select-all" 
                                                               data-section="<?= preg_replace('/[^a-zA-Z0-9]/', '_', $class_name) ?>">
                                                    </th>
                                                    <th>Subject</th>
                                                    <th>Hours/Week</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($class_mappings as $mapping): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="mapping_ids[]" 
                                                                   value="<?= $mapping['id'] ?>" 
                                                                   class="preview-checkbox"
                                                                   form="bulkForm">
                                                        </td>
                                                        <td><?= htmlspecialchars($mapping['subject_name']) ?></td>
                                                        <td>
                                                            <span class="badge bg-info"><?= $mapping['hours_per_week'] ?> hrs</span>
                                                        </td>
                                                        <td>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="mapping_id" value="<?= $mapping['id'] ?>">
                                                                <button type="submit" name="remove_subject"
                                                                        class="btn btn-sm btn-outline-danger"
                                                                        onclick="return confirm('Remove <?= htmlspecialchars($mapping['subject_name']) ?> from <?= htmlspecialchars($class_name) ?>?')">
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
<script>
    // Update selected count
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.subject-checkbox:checked');
        document.getElementById('selectedCount').textContent = checkboxes.length;
    }

    // Select all subjects
    function selectAllSubjects() {
        document.querySelectorAll('.subject-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        updateSelectedCount();
    }

    // Deselect all subjects
    function deselectAllSubjects() {
        document.querySelectorAll('.subject-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectedCount();
    }

    // Toggle section visibility
    function toggleSection(sectionId) {
        const section = document.getElementById('section_' + sectionId);
        const icon = section.previousElementSibling.querySelector('.fa-chevron-down');
        
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
        document.querySelectorAll('.toggle-btn .fa-chevron-down').forEach(icon => {
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
        
        // Also update section checkboxes
        document.querySelectorAll('.section-select-all').forEach(sectionCheckbox => {
            sectionCheckbox.checked = checkbox.checked;
        });
    }

    // Section-specific selection
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize section select-all functionality
        document.querySelectorAll('.section-select-all').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const section = this.getAttribute('data-section');
                const sectionCheckboxes = document.querySelectorAll(`#section_${section} .preview-checkbox`);
                sectionCheckboxes.forEach(item => {
                    item.checked = this.checked;
                });
            });
        });

        // Initialize checkbox event listeners
        document.querySelectorAll('.subject-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        // Initialize all sections as expanded
        document.querySelectorAll('.collapsible').forEach(section => {
            section.style.display = 'block';
        });

        updateSelectedCount();
    });

    // Form validation
    document.getElementById('multiSubjectForm').addEventListener('submit', function(e) {
        const selectedSubjects = document.querySelectorAll('.subject-checkbox:checked').length;
        const classSelected = document.getElementById('class_id').value;
        
        if (!classSelected) {
            e.preventDefault();
            alert('Please select a class.');
            return;
        }
        
        if (selectedSubjects === 0) {
            e.preventDefault();
            alert('Please select at least one subject.');
            return;
        }
    });
</script>
</body>
</html>

<?php $conn->close(); ?>