<?php
// CORS headers are handled by .htaccess
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            handleGetUsers();
            break;
        case 'POST':
            handleCreateUser();
            break;
        case 'PUT':
            handleUpdateUser();
            break;
        case 'DELETE':
            handleDeleteUser();
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

function handleGetUsers() {
    global $pdo;
    
    // Get query parameters for filtering and pagination
    $role = $_GET['role'] ?? '';
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(100, max(10, intval($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;

    // Build the base query
    $query = "
        SELECT 
            u.user_id as id,
            u.email,
            u.role,
            u.approval_status,
            u.first_name,
            u.last_name,
            u.middle_name,
            u.student_id,
            u.last_login,
            u.created_at,
            u.updated_at,
            u.is_active,
            p.program_name,
            CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.middle_name, ''), ' ', COALESCE(u.last_name, '')) as full_name
        FROM users u
        LEFT JOIN programs p ON u.program_id = p.program_id
        WHERE 1=1
    ";

    $params = [];

    // Add filters
    if ($role) {
        $query .= " AND u.role = :role";
        $params['role'] = $role;
    }

    if ($status) {
        if ($status === 'active') {
            $query .= " AND u.is_active = 1";
        } elseif ($status === 'inactive') {
            $query .= " AND u.is_active = 0";
        } elseif ($status === 'pending') {
            $query .= " AND u.approval_status = 'pending'";
        } elseif ($status === 'rejected') {
            $query .= " AND u.approval_status = 'rejected'";
        }
    }

    if ($search) {
        $searchTerm = "%$search%";
        $query .= " AND (
            u.first_name LIKE :search1 
            OR u.last_name LIKE :search2 
            OR u.email LIKE :search3 
            OR u.student_id LIKE :search4
            OR CONCAT(u.first_name, ' ', u.last_name) LIKE :search5
        )";
        $params['search1'] = $searchTerm;
        $params['search2'] = $searchTerm;
        $params['search3'] = $searchTerm;
        $params['search4'] = $searchTerm;
        $params['search5'] = $searchTerm;
    }

    // Count total records for pagination
    $countQuery = "SELECT COUNT(*) " . substr($query, strpos($query, 'FROM'));
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetchColumn();

    // Add ordering and pagination
    $query .= " ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset";
    $params['limit'] = $limit;
    $params['offset'] = $offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the response data
    $formattedUsers = array_map(function($user) {
        return [
            'id' => $user['id'],
            'name' => trim($user['full_name']) ?: 'N/A',
            'username' => $user['student_id'] ?: $user['email'],
            'email' => $user['email'],
            'role' => $user['role'],
            'status' => $user['is_active'] ? 'active' : 'inactive',
            'approval_status' => $user['approval_status'],
            'program' => $user['program_name'],
            'lastLogin' => $user['last_login'],
            'createdAt' => $user['created_at'],
            'updatedAt' => $user['updated_at']
        ];
    }, $users);

    $totalPages = ceil($totalRecords / $limit);

    echo json_encode([
        'success' => true,
        'data' => $formattedUsers,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'per_page' => $limit
        ]
    ]);
}

function handleCreateUser() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $requiredFields = ['email', 'password', 'role', 'first_name', 'last_name', 'program_id'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Field '$field' is required"]);
            return;
        }
    }

    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        return;
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Email already exists']);
        return;
    }

    // Hash password
    $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);

    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users (
            email, password_hash, role, first_name, last_name, middle_name, 
            student_id, program_id, approval_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $approvalStatus = $input['role'] === 'admin' ? 'approved' : 'pending';
    
    $success = $stmt->execute([
        $input['email'],
        $passwordHash,
        $input['role'],
        $input['first_name'],
        $input['last_name'],
        $input['middle_name'] ?? null,
        $input['student_id'] ?? null,
        $input['program_id'],
        $approvalStatus
    ]);

    if ($success) {
        $userId = $pdo->lastInsertId();
        echo json_encode([
            'success' => true, 
            'message' => 'User created successfully',
            'user_id' => $userId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to create user']);
    }
}

function handleUpdateUser() {
    global $pdo;
    
    $userId = $_GET['id'] ?? null;
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'User ID is required']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    // Build update query dynamically
    $allowedFields = ['first_name', 'last_name', 'middle_name', 'student_id', 'role', 'is_active', 'approval_status'];
    $updateFields = [];
    $params = [];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "$field = :$field";
            $params[$field] = $input[$field];
        }
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No valid fields to update']);
        return;
    }
    
    $params['user_id'] = $userId;
    $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE user_id = :user_id";
    
    $stmt = $pdo->prepare($query);
    $success = $stmt->execute($params);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update user']);
    }
}

function handleDeleteUser() {
    global $pdo;
    
    $userId = $_GET['id'] ?? null;
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'User ID is required']);
        return;
    }

    // Soft delete by setting is_active to false
    $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE user_id = ?");
    $success = $stmt->execute([$userId]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'User deactivated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to deactivate user']);
    }
}
?>
