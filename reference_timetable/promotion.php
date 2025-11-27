<?php
require_once 'db.php';

// Promotion Controller Class
class PromotionController {
    private $conn;
    private $studentModel;
    private $classModel;
    private $academicHistory;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->studentModel = new StudentModel($conn);
        $this->classModel = new ClassModel($conn);
        $this->academicHistory = new AcademicHistory($conn);
    }

    public function processAnnualPromotion($confirmation = false) {
        $currentMonth = date('n');
        if ($currentMonth != 12) {
            return ['success' => false, 'message' => 'Promotion can only be processed in December for the Kenyan curriculum'];
        }

        if (!$confirmation) {
            return [
                'success' => false, 
                'message' => 'Please confirm promotion process',
                'needs_confirmation' => true,
                'preview' => $this->getPromotionPreview()
            ];
        }

        $results = [
            'promoted' => 0,
            'graduated' => 0,
            'repeated' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        $this->conn->begin_transaction();
        
        try {
            $students_result = $this->studentModel->getActiveStudents(1, 10000);
            $students = $students_result['students'];
            
            while ($student = $students->fetch_assoc()) {
                try {
                    $promotionResult = $this->promoteStudent($student);
                    
                    switch ($promotionResult['status']) {
                        case 'graduated':
                            $results['graduated']++;
                            break;
                        case 'promoted':
                            $results['promoted']++;
                            break;
                        case 'repeated':
                            $results['repeated']++;
                            break;
                        default:
                            $results['skipped']++;
                    }
                    
                } catch (Exception $e) {
                    $results['errors'][] = "Student {$student['student_id']}: " . $e->getMessage();
                }
            }

            $this->conn->commit();
            
            $results['success'] = true;
            $results['message'] = "Promotion completed: {$results['promoted']} promoted, {$results['graduated']} graduated, {$results['repeated']} repeated, {$results['skipped']} skipped";
            return $results;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'success' => false, 
                'message' => 'Promotion failed: ' . $e->getMessage(),
                'errors' => $results['errors']
            ];
        }
    }

    private function getPromotionPreview() {
        $preview = [
            'to_graduate' => [],
            'to_promote' => [],
            'to_repeat' => []
        ];

        $students_result = $this->studentModel->getActiveStudents(1, 10000);
        $students = $students_result['students'];
        
        while ($student = $students->fetch_assoc()) {
            $currentClass = $this->classModel->getClassById($student['class_id']);
            
            if (!$currentClass) {
                continue;
            }

            if ($student['promotion_pending'] === 'Repeat') {
                $preview['to_repeat'][] = [
                    'name' => $student['full_name'],
                    'current_class' => $currentClass['class_name'],
                    'reason' => 'Marked for repetition'
                ];
                continue;
            }

            if ($this->classModel->isFinalClass($currentClass['level'])) {
                $preview['to_graduate'][] = [
                    'name' => $student['full_name'],
                    'current_class' => $currentClass['class_name'],
                    'reason' => 'Final class completion'
                ];
                continue;
            }

            $nextClass = $this->classModel->getNextClass($currentClass['level'], $student['stream']);
            if ($nextClass) {
                $preview['to_promote'][] = [
                    'name' => $student['full_name'],
                    'current_class' => $currentClass['class_name'],
                    'next_class' => $nextClass['class_name'],
                    'reason' => 'Regular promotion'
                ];
            } else {
                $preview['to_repeat'][] = [
                    'name' => $student['full_name'],
                    'current_class' => $currentClass['class_name'],
                    'reason' => 'No next class available'
                ];
            }
        }

        return $preview;
    }

    private function promoteStudent($student) {
        if ($student['current_status'] !== 'Active') {
            return ['status' => 'skipped', 'reason' => 'Student not active'];
        }

        $currentClass = $this->classModel->getClassById($student['class_id']);
        if (!$currentClass) {
            return ['status' => 'skipped', 'reason' => 'Current class not found'];
        }

        if ($student['promotion_pending'] === 'Repeat') {
            $this->academicHistory->recordRepetition($student['student_id'], $student['class_id']);
            $this->studentModel->updateStudentClass($student['student_id'], $student['class_id'], $currentClass['class_name']);
            return ['status' => 'repeated', 'reason' => 'Student repeated class'];
        }

        if ($this->classModel->isFinalClass($currentClass['level'])) {
            $this->studentModel->graduateStudent($student['student_id']);
            $this->academicHistory->recordPromotion($student['student_id'], $student['class_id'], null, 'Graduation');
            return ['status' => 'graduated', 'reason' => 'Student graduated'];
        }

        $nextClass = $this->classModel->getNextClass($currentClass['level'], $student['stream']);
        if ($nextClass) {
            $this->studentModel->updateStudentClass(
                $student['student_id'], 
                $nextClass['id'], 
                $nextClass['class_name']
            );
            
            $this->academicHistory->recordPromotion(
                $student['student_id'], 
                $student['class_id'], 
                $nextClass['id']
            );

            return [
                'status' => 'promoted', 
                'reason' => "Promoted from {$currentClass['class_name']} to {$nextClass['class_name']}"
            ];
        }

        return ['status' => 'skipped', 'reason' => 'No next class available'];
    }
}

// Student Model Class
class StudentModel {
    private $conn;
    private $table_name = "students";

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($data) {
        $this->conn->begin_transaction();
        
        try {
            $query = "INSERT INTO " . $this->table_name . "
                    SET admission_number=?, 
                        password_hash=?,
                        full_name=?,
                        gender=?,
                        date_of_birth=?,
                        county=?,
                        sub_county=?,
                        ward=?,
                        address=?,
                        birth_certificate_number=?,
                        parent_name=?,
                        parent_phone=?,
                        parent_email=?,
                        relationship_to_student=?,
                        previous_school_name=?,
                        kcpe_index_number=?,
                        kcpe_marks=?,
                        year_of_completion=?,
                        class_id=?,
                        stream=?,
                        current_class=?,
                        admission_date=?,
                        current_status=?,
                        academic_year=?,
                        promotion_pending=?";

            $stmt = $this->conn->prepare($query);
            
            $stmt->bind_param("sssssssssssssssiissssssss",
                $data['admission_number'],
                $data['password_hash'],
                $data['full_name'],
                $data['gender'],
                $data['date_of_birth'],
                $data['county'],
                $data['sub_county'],
                $data['ward'],
                $data['address'],
                $data['birth_certificate_number'],
                $data['parent_name'],
                $data['parent_phone'],
                $data['parent_email'],
                $data['relationship_to_student'],
                $data['previous_school_name'],
                $data['kcpe_index_number'],
                $data['kcpe_marks'],
                $data['year_of_completion'],
                $data['class_id'],
                $data['stream'],
                $data['current_class'],
                $data['admission_date'],
                $data['current_status'],
                $data['academic_year'],
                $data['promotion_pending']
            );

            if(!$stmt->execute()) {
                throw new Exception("Failed to create student record: " . $stmt->error);
            }
            
            $student_id = $stmt->insert_id;
            
            $this->recordInitialAcademicHistory($student_id, $data['class_id']);
            
            $this->conn->commit();
            return $student_id;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Student creation failed: " . $e->getMessage());
            return false;
        }
    }

    private function recordInitialAcademicHistory($student_id, $class_id) {
        $query = "INSERT INTO student_academic_history 
                 SET student_id=?, 
                     old_class_id=NULL,
                     new_class_id=?,
                     action_type=?,
                     promoted_on=?";

        $stmt = $this->conn->prepare($query);
        $current_date = date('Y-m-d H:i:s');
        $action_type = 'Initial Enrollment';
        
        $stmt->bind_param("iiss", $student_id, $class_id, $action_type, $current_date);
        
        if(!$stmt->execute()) {
            throw new Exception("Failed to record academic history: " . $stmt->error);
        }
    }

    public function getActiveStudents($page = 1, $records_per_page = 10) {
        $offset = ($page - 1) * $records_per_page;
        
        $query = "SELECT SQL_CALC_FOUND_ROWS s.*, c.class_name, c.level 
                 FROM " . $this->table_name . " s
                 LEFT JOIN classes c ON s.class_id = c.id
                 WHERE s.current_status = 'Active' 
                 ORDER BY full_name
                 LIMIT ?, ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $offset, $records_per_page);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $count_result = $this->conn->query("SELECT FOUND_ROWS() as total");
        $total_rows = $count_result->fetch_assoc()['total'];
        
        return [
            'students' => $result,
            'total_pages' => ceil($total_rows / $records_per_page),
            'current_page' => $page,
            'total_records' => $total_rows
        ];
    }

    public function getStudentById($student_id) {
        $query = "SELECT s.*, c.class_name, c.level 
                 FROM " . $this->table_name . " s
                 LEFT JOIN classes c ON s.class_id = c.id
                 WHERE s.student_id = ? 
                 LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();

        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return false;
    }

    public function updateStudentClass($student_id, $new_class_id, $new_class_name) {
        $query = "UPDATE " . $this->table_name . "
                 SET class_id = ?,
                     current_class = ?,
                     last_updated = CURRENT_TIMESTAMP
                 WHERE student_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isi", $new_class_id, $new_class_name, $student_id);

        return $stmt->execute();
    }

    public function markForRepetition($student_id) {
        $query = "UPDATE " . $this->table_name . "
                 SET promotion_pending = 'Repeat',
                     last_updated = CURRENT_TIMESTAMP
                 WHERE student_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $student_id);

        return $stmt->execute();
    }

    public function graduateStudent($student_id) {
        $query = "UPDATE " . $this->table_name . "
                 SET current_status = 'Graduated',
                     graduation_date = CURDATE(),
                     promotion_pending = 'No',
                     last_updated = CURRENT_TIMESTAMP
                 WHERE student_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $student_id);

        return $stmt->execute();
    }

    public function generateAdmissionNumber() {
        $this->conn->begin_transaction();
        
        try {
            $year = date('Y');
            $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                     WHERE YEAR(admission_date) = ? FOR UPDATE";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $year);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $count = $row['count'] + 1;
            
            $this->conn->commit();
            
            return "ADM" . $year . str_pad($count, 4, '0', STR_PAD_LEFT);
            
        } catch (Exception $e) {
            $this->conn->rollback();
            $year = date('Y');
            $count = $this->countStudentsByYear($year) + 1;
            return "ADM" . $year . str_pad($count, 4, '0', STR_PAD_LEFT);
        }
    }

    private function countStudentsByYear($year) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE YEAR(admission_date) = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $year);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function countActiveStudents() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE current_status = 'Active'";
        
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        return $row['count'];
    }
}

// Class Model Class
class ClassModel {
    private $conn;
    private $table_name = "classes";

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllClasses() {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE status = 'Active' 
                 ORDER BY 
                    FIELD(level, 'Form 1', 'Form 2', 'Form 3', 'Form 4', 'Grade 5'),
                    stream, section";
        
        $result = $this->conn->query($query);
        return $result;
    }

    public function getClassById($class_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE id = ? 
                 LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $class_id);
        $stmt->execute();

        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return false;
    }

    public function getNextClass($current_level, $stream = null) {
        $level_sequence = [
            'Form 1' => 'Form 2',
            'Form 2' => 'Form 3', 
            'Form 3' => 'Form 4',
            'Form 4' => 'Graduated',
            'Grade 5' => 'Graduated'
        ];

        $next_level = isset($level_sequence[$current_level]) ? $level_sequence[$current_level] : null;
        
        if($next_level && $next_level !== 'Graduated') {
            $query = "SELECT * FROM " . $this->table_name . " 
                     WHERE level = ? AND status = 'Active'";
            
            if ($stream) {
                $query .= " AND stream = ?";
            }
            
            $query .= " LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            
            if ($stream) {
                $stmt->bind_param("ss", $next_level, $stream);
            } else {
                $stmt->bind_param("s", $next_level);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            $query = "SELECT * FROM " . $this->table_name . " 
                     WHERE level = ? AND status = 'Active' 
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $next_level);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        }
        return false;
    }

    public function isFinalClass($level) {
        $final_classes = ['Form 4', 'Grade 5'];
        return in_array($level, $final_classes);
    }

    public function countActiveClasses() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE status = 'Active'";
        
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        return $row['count'];
    }
}

// Academic History Model Class
class AcademicHistory {
    private $conn;
    private $table_name = "student_academic_history";

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function recordPromotion($student_id, $old_class_id, $new_class_id, $action_type = 'Promotion') {
        $query = "INSERT INTO " . $this->table_name . "
                 SET student_id = ?,
                     old_class_id = ?,
                     new_class_id = ?,
                     action_type = ?,
                     promoted_on = NOW()";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiis", $student_id, $old_class_id, $new_class_id, $action_type);

        return $stmt->execute();
    }

    public function recordRepetition($student_id, $class_id) {
        return $this->recordPromotion($student_id, $class_id, $class_id, 'Repetition');
    }

    public function getStudentHistory($student_id) {
        $query = "SELECT h.*, 
                         old_c.class_name as old_class,
                         new_c.class_name as new_class
                 FROM " . $this->table_name . " h
                 LEFT JOIN classes old_c ON h.old_class_id = old_c.id
                 LEFT JOIN classes new_c ON h.new_class_id = new_c.id
                 WHERE h.student_id = ?
                 ORDER BY h.promoted_on DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();

        return $stmt->get_result();
    }
}

// Student Controller Class
class StudentController {
    private $studentModel;
    private $classModel;

    public function __construct($conn) {
        $this->studentModel = new StudentModel($conn);
        $this->classModel = new ClassModel($conn);
    }

    public function registerStudent($data) {
        $validation = $this->validateStudentData($data);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        $temp_password = $this->generateStrongPassword();
        
        $studentData = [
            'admission_number' => $this->studentModel->generateAdmissionNumber(),
            'password_hash' => password_hash($temp_password, PASSWORD_DEFAULT),
            'full_name' => $this->sanitizeInput($data['full_name']),
            'gender' => $this->sanitizeInput($data['gender']),
            'date_of_birth' => $data['date_of_birth'],
            'county' => isset($data['county']) ? $this->sanitizeInput($data['county']) : null,
            'sub_county' => isset($data['sub_county']) ? $this->sanitizeInput($data['sub_county']) : null,
            'ward' => isset($data['ward']) ? $this->sanitizeInput($data['ward']) : null,
            'address' => isset($data['address']) ? $this->sanitizeInput($data['address']) : null,
            'birth_certificate_number' => isset($data['birth_certificate_number']) ? $this->sanitizeInput($data['birth_certificate_number']) : null,
            'parent_name' => $this->sanitizeInput($data['parent_name']),
            'parent_phone' => $this->sanitizeInput($data['parent_phone']),
            'parent_email' => isset($data['parent_email']) ? $this->sanitizeInput($data['parent_email']) : null,
            'relationship_to_student' => isset($data['relationship_to_student']) ? $this->sanitizeInput($data['relationship_to_student']) : null,
            'previous_school_name' => isset($data['previous_school_name']) ? $this->sanitizeInput($data['previous_school_name']) : null,
            'kcpe_index_number' => isset($data['kcpe_index_number']) ? $this->sanitizeInput($data['kcpe_index_number']) : null,
            'kcpe_marks' => isset($data['kcpe_marks']) ? intval($data['kcpe_marks']) : null,
            'year_of_completion' => isset($data['year_of_completion']) ? intval($data['year_of_completion']) : null,
            'class_id' => intval($data['class_id']),
            'stream' => isset($data['stream']) ? $this->sanitizeInput($data['stream']) : null,
            'current_class' => null,
            'admission_date' => date('Y-m-d'),
            'current_status' => 'Active',
            'academic_year' => date('Y'),
            'promotion_pending' => 'No'
        ];

        $class_info = $this->classModel->getClassById($data['class_id']);
        $studentData['current_class'] = $class_info ? $class_info['class_name'] : null;

        $student_id = $this->studentModel->create($studentData);

        if ($student_id) {
            return [
                'success' => true, 
                'message' => 'Student registered successfully',
                'admission_number' => $studentData['admission_number'],
                'student_id' => $student_id,
                'temp_password' => $temp_password
            ];
        } else {
            return ['success' => false, 'message' => 'Unable to register student'];
        }
    }

    private function validateStudentData($data) {
        $required = ['full_name', 'gender', 'date_of_birth', 'parent_name', 'parent_phone', 'class_id'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['valid' => false, 'message' => "Please fill all required fields. Missing: $field"];
            }
        }
        
        if (!$this->validateDate($data['date_of_birth'])) {
            return ['valid' => false, 'message' => 'Invalid date of birth format'];
        }
        
        if (!$this->validatePhone($data['parent_phone'])) {
            return ['valid' => false, 'message' => 'Invalid phone number format'];
        }
        
        if (!empty($data['parent_email']) && !$this->validateEmail($data['parent_email'])) {
            return ['valid' => false, 'message' => 'Invalid email address format'];
        }
        
        return ['valid' => true, 'message' => 'Validation successful'];
    }

    private function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    private function validatePhone($phone) {
        return preg_match('/^[0-9\-\+\s\(\)]{10,15}$/', $phone);
    }
    
    private function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    private function sanitizeInput($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    private function generateStrongPassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?';
        $password = '';
        $charsLength = strlen($chars) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $charsLength)];
        }
        
        return $password;
    }

    public function getAllStudents($page = 1) {
        return $this->studentModel->getActiveStudents($page);
    }

    public function getStudentDetails($student_id) {
        return $this->studentModel->getStudentById($student_id);
    }
    
    public function markStudentForRepetition($student_id) {
        return $this->studentModel->markForRepetition($student_id);
    }
}

// Student Details Display Function
function displayStudentDetails($conn) {
    if (isset($_GET['id'])) {
        $student_id = intval($_GET['id']);
        $studentModel = new StudentModel($conn);
        $student = $studentModel->getStudentById($student_id);
        
        if ($student) {
            echo '
            <div class="row">
                <div class="col-md-6">
                    <h6>Personal Information</h6>
                    <p><strong>Full Name:</strong> ' . htmlspecialchars($student['full_name']) . '</p>
                    <p><strong>Admission Number:</strong> ' . htmlspecialchars($student['admission_number']) . '</p>
                    <p><strong>Gender:</strong> ' . htmlspecialchars($student['gender']) . '</p>
                    <p><strong>Date of Birth:</strong> ' . htmlspecialchars($student['date_of_birth']) . '</p>
                    <p><strong>Birth Certificate:</strong> ' . htmlspecialchars($student['birth_certificate_number'] ?? 'N/A') . '</p>
                </div>
                <div class="col-md-6">
                    <h6>Contact Information</h6>
                    <p><strong>Parent Name:</strong> ' . htmlspecialchars($student['parent_name']) . '</p>
                    <p><strong>Parent Phone:</strong> ' . htmlspecialchars($student['parent_phone']) . '</p>
                    <p><strong>Parent Email:</strong> ' . htmlspecialchars($student['parent_email'] ?? 'N/A') . '</p>
                    <p><strong>Relationship:</strong> ' . htmlspecialchars($student['relationship_to_student'] ?? 'N/A') . '</p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <h6>Academic Information</h6>
                    <p><strong>Class:</strong> ' . htmlspecialchars($student['class_name'] . ' ' . $student['level']) . '</p>
                    <p><strong>Stream:</strong> ' . htmlspecialchars($student['stream'] ?? 'N/A') . '</p>
                    <p><strong>Admission Date:</strong> ' . htmlspecialchars($student['admission_date']) . '</p>
                    <p><strong>Status:</strong> <span class="badge bg-success">' . htmlspecialchars($student['current_status']) . '</span></p>
                </div>
                <div class="col-md-6">
                    <h6>Previous School</h6>
                    <p><strong>School Name:</strong> ' . htmlspecialchars($student['previous_school_name'] ?? 'N/A') . '</p>
                    <p><strong>KCPE Marks:</strong> ' . htmlspecialchars($student['kcpe_marks'] ?? 'N/A') . '</p>
                    <p><strong>KCPE Index:</strong> ' . htmlspecialchars($student['kcpe_index_number'] ?? 'N/A') . '</p>
                    <p><strong>Year of Completion:</strong> ' . htmlspecialchars($student['year_of_completion'] ?? 'N/A') . '</p>
                </div>
            </div>';
        } else {
            echo '<div class="alert alert-danger">Student not found.</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Invalid request.</div>';
    }
}
?>