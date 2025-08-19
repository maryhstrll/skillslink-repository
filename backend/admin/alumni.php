<?php
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            getAllAlumni();
            break;
        case 'POST':
            createAlumni($input);
            break;
        case 'PUT':
            updateAlumni($input);
            break;
        case 'DELETE':
            deleteAlumni($input);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
}

function getAllAlumni() {
    global $pdo;
    
    try {
        $sql = "SELECT 
                    a.alumni_id,
                    a.student_id,
                    a.first_name,
                    a.last_name,
                    a.middle_name,
                    a.phone_number,
                    a.year_graduated,
                    a.graduation_date,
                    a.program_id,
                    a.batch_id,
                    a.gpa,
                    a.barangay,
                    a.city,
                    a.province,
                    a.linkedin_profile,
                    a.facebook_profile,
                    a.created_at,
                    a.updated_at,
                    p.program_name,
                    p.program_code,
                    p.department,
                    b.batch_name,
                    b.batch_year
                FROM alumni a
                LEFT JOIN programs p ON a.program_id = p.program_id
                LEFT JOIN batches b ON a.batch_id = b.batch_id
                WHERE a.is_active = 1
                ORDER BY a.last_name, a.first_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $alumni = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $alumni,
            'count' => count($alumni)
        ]);
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

function createAlumni($data) {
    global $pdo;
    
    // Validate required fields
    $required_fields = ['student_id', 'first_name', 'last_name', 'program_id', 'year_graduated'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        // First, create a user record (alumni are also users)
        $userSql = "INSERT INTO users (email, password_hash, role, first_name, last_name, student_id, approval_status) 
                    VALUES (?, ?, 'alumni', ?, ?, ?, 'approved')";
        
        // Generate a temporary email if not provided
        $email = $data['email'] ?? $data['student_id'] . '@alumni.temp';
        $password_hash = password_hash('temp123', PASSWORD_DEFAULT); // Temporary password
        
        $userStmt = $pdo->prepare($userSql);
        $userStmt->execute([
            $email,
            $password_hash,
            $data['first_name'],
            $data['last_name'],
            $data['student_id']
        ]);
        
        $user_id = $pdo->lastInsertId();
        
        // Then create the alumni record
        $alumniSql = "INSERT INTO alumni (
            user_id, student_id, first_name, last_name, middle_name, 
            phone_number, program_id, year_graduated, graduation_date,
            barangay, city, province, linkedin_profile, facebook_profile
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $alumniStmt = $pdo->prepare($alumniSql);
        $alumniStmt->execute([
            $user_id,
            $data['student_id'],
            $data['first_name'],
            $data['last_name'],
            $data['middle_name'] ?? null,
            $data['phone_number'] ?? null,
            $data['program_id'],
            $data['year_graduated'],
            $data['graduation_date'] ?? null,
            $data['barangay'] ?? null,
            $data['city'] ?? null,
            $data['province'] ?? null,
            $data['linkedin_profile'] ?? null,
            $data['facebook_profile'] ?? null
        ]);
        
        $alumni_id = $pdo->lastInsertId();
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Alumni created successfully',
            'data' => ['alumni_id' => $alumni_id, 'user_id' => $user_id]
        ]);
    } catch (PDOException $e) {
        $pdo->rollback();
        if ($e->getCode() == 23000) { // Duplicate entry
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Student ID already exists']);
        } else {
            throw new Exception('Database error: ' . $e->getMessage());
        }
    }
}

function updateAlumni($data) {
    global $pdo;
    
    if (empty($data['alumni_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Alumni ID is required']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update alumni record
        $alumniSql = "UPDATE alumni SET 
            first_name = ?, last_name = ?, middle_name = ?, 
            phone_number = ?, program_id = ?, year_graduated = ?,
            graduation_date = ?, barangay = ?, city = ?, province = ?,
            linkedin_profile = ?, facebook_profile = ?, updated_at = CURRENT_TIMESTAMP
            WHERE alumni_id = ?";
        
        $alumniStmt = $pdo->prepare($alumniSql);
        $alumniStmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['middle_name'] ?? null,
            $data['phone_number'] ?? null,
            $data['program_id'],
            $data['year_graduated'],
            $data['graduation_date'] ?? null,
            $data['barangay'] ?? null,
            $data['city'] ?? null,
            $data['province'] ?? null,
            $data['linkedin_profile'] ?? null,
            $data['facebook_profile'] ?? null,
            $data['alumni_id']
        ]);
        
        // Also update the corresponding user record
        $userSql = "UPDATE users u 
                    JOIN alumni a ON u.user_id = a.user_id 
                    SET u.first_name = ?, u.last_name = ?, u.updated_at = CURRENT_TIMESTAMP
                    WHERE a.alumni_id = ?";
        
        $userStmt = $pdo->prepare($userSql);
        $userStmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['alumni_id']
        ]);
        
        $pdo->commit();
        
        if ($alumniStmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Alumni updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Alumni not found or no changes made']);
        }
    } catch (PDOException $e) {
        $pdo->rollback();
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

function deleteAlumni($data) {
    global $pdo;
    
    if (empty($data['alumni_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Alumni ID is required']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Soft delete - set is_active to 0 instead of actually deleting
        $alumniSql = "UPDATE alumni SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE alumni_id = ?";
        $alumniStmt = $pdo->prepare($alumniSql);
        $alumniStmt->execute([$data['alumni_id']]);
        
        // Also deactivate the user account
        $userSql = "UPDATE users u 
                    JOIN alumni a ON u.user_id = a.user_id 
                    SET u.is_active = 0, u.updated_at = CURRENT_TIMESTAMP
                    WHERE a.alumni_id = ?";
        $userStmt = $pdo->prepare($userSql);
        $userStmt->execute([$data['alumni_id']]);
        
        $pdo->commit();
        
        if ($alumniStmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Alumni deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Alumni not found']);
        }
    } catch (PDOException $e) {
        $pdo->rollback();
        throw new Exception('Database error: ' . $e->getMessage());
    }
}
?>
