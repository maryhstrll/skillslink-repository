<?php
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../session.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

// Check authentication
if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Get alumni_id from users table if user is alumni
$alumni_id = null;
if ($role === 'alumni') {
    try {
        $stmt = $pdo->prepare("SELECT alumni_id FROM alumni WHERE user_id = ?");
        $stmt->execute([$userId]);
        $alumni_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$alumni_data) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Alumni record not found']);
            exit;
        }
        $alumni_id = $alumni_data['alumni_id'];
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            handleGetRequests();
            break;
        case 'POST':
            handleCreateRequest();
            break;
        case 'PUT':
            handleUpdateRequest();
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function handleGetRequests() {
    global $pdo, $alumni_id, $role, $userId;
    
    try {
        if ($role === 'alumni') {
            // Alumni can only see their own requests
            $stmt = $pdo->prepare("
                SELECT dr.*, 
                       CONCAT(a.first_name, ' ', a.last_name) as full_name
                FROM document_requests dr
                JOIN alumni a ON dr.alumni_id = a.alumni_id
                WHERE dr.alumni_id = ?
                ORDER BY dr.request_date DESC
            ");
            $stmt->execute([$alumni_id]);
        } else {
            // Admin and staff can see all requests
            $stmt = $pdo->prepare("
                SELECT dr.*, 
                       CONCAT(a.first_name, ' ', a.last_name) as full_name,
                       a.student_id,
                       p.program_name
                FROM document_requests dr
                JOIN alumni a ON dr.alumni_id = a.alumni_id
                JOIN programs p ON a.program_id = p.program_id
                ORDER BY dr.request_date DESC
            ");
            $stmt->execute();
        }
        
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $requests
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleCreateRequest() {
    global $pdo, $alumni_id, $role;
    
    // Only alumni can create requests for themselves
    if ($role !== 'alumni') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only alumni can create document requests']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($data['document_type'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Document type is required']);
        exit;
    }
    
    // Validate document type
    $valid_types = ['Transcript of Records', 'Transcript of Competency', 'Diploma', 'Certificate of Training'];
    if (!in_array($data['document_type'], $valid_types)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid document type']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO document_requests (alumni_id, document_type, purpose, status)
            VALUES (?, ?, ?, 'Pending')
        ");
        
        $stmt->execute([
            $alumni_id,
            $data['document_type'],
            $data['purpose'] ?? null
        ]);
        
        $request_id = $pdo->lastInsertId();
        
        // Get the created request details
        $stmt = $pdo->prepare("
            SELECT dr.*, 
                   CONCAT(a.first_name, ' ', a.last_name) as full_name
            FROM document_requests dr
            JOIN alumni a ON dr.alumni_id = a.alumni_id
            WHERE dr.request_id = ?
        ");
        $stmt->execute([$request_id]);
        $created_request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Document request submitted successfully',
            'data' => $created_request
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleUpdateRequest() {
    global $pdo, $role;
    
    // Only admin and staff can update request status
    if (!in_array($role, ['admin', 'staff'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['request_id']) || empty($data['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Request ID and status are required']);
        exit;
    }
    
    // Validate status
    $valid_statuses = ['Pending', 'Processing', 'Ready for Pickup', 'Completed'];
    if (!in_array($data['status'], $valid_statuses)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE document_requests 
            SET status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE request_id = ?
        ");
        
        $stmt->execute([$data['status'], $data['request_id']]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Request not found']);
            exit;
        }
        
        // Get updated request details
        $stmt = $pdo->prepare("
            SELECT dr.*, 
                   CONCAT(a.first_name, ' ', a.last_name) as full_name,
                   a.student_id,
                   p.program_name
            FROM document_requests dr
            JOIN alumni a ON dr.alumni_id = a.alumni_id
            JOIN programs p ON a.program_id = p.program_id
            WHERE dr.request_id = ?
        ");
        $stmt->execute([$data['request_id']]);
        $updated_request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Request status updated successfully',
            'data' => $updated_request
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
