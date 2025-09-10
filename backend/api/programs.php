<?php
// CRUD operations for academic programs management
header('Content-Type: application/json');
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../config.php';

// Require admin or staff authorization for write operations
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
}

try {
    switch ($method) {
        case 'GET':
            // Get all programs with full details
            $stmt = $pdo->prepare("
                SELECT 
                    program_id,
                    program_code,
                    program_name,
                    department,
                    program_type,
                    duration,
                    description,
                    is_active,
                    created_at,
                    updated_at
                FROM programs 
                ORDER BY program_name ASC
            ");
            $stmt->execute();
            $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $programs]);
            break;

        case 'POST':
            // Create new program
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['program_code'], $input['program_name'], $input['department'], $input['program_type'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing required fields']);
                exit;
            }

            $stmt = $pdo->prepare("
                INSERT INTO programs (program_code, program_name, department, program_type, duration, description, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $input['program_code'],
                $input['program_name'],
                $input['department'],
                $input['program_type'],
                $input['duration'] ?? null,
                $input['description'] ?? null,
                $input['is_active'] ?? true
            ]);

            if ($result) {
                $program_id = $pdo->lastInsertId();
                echo json_encode(['success' => true, 'message' => 'Program created successfully', 'program_id' => $program_id]);
            } else {
                throw new Exception('Failed to create program');
            }
            break;

        case 'PUT':
            // Update existing program
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['program_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Program ID is required']);
                exit;
            }

            $stmt = $pdo->prepare("
                UPDATE programs 
                SET program_code = ?, program_name = ?, department = ?, program_type = ?, 
                    duration = ?, description = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
                WHERE program_id = ?
            ");
            
            $result = $stmt->execute([
                $input['program_code'],
                $input['program_name'],
                $input['department'],
                $input['program_type'],
                $input['duration'] ?? null,
                $input['description'] ?? null,
                $input['is_active'] ?? true,
                $input['program_id']
            ]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Program updated successfully']);
            } else {
                throw new Exception('Failed to update program');
            }
            break;

        case 'DELETE':
            // Soft delete program
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['program_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Program ID is required']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE programs SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE program_id = ?");
            $result = $stmt->execute([$input['program_id']]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Program deleted successfully']);
            } else {
                throw new Exception('Failed to delete program');
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
