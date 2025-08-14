<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Admin privileges required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get pending users for approval
    try {
        $stmt = $pdo->prepare('
            SELECT user_id, email, role, approval_status, created_at, 
                   first_name, last_name, student_id, program_id 
            FROM users 
            WHERE approval_status = "pending" 
            ORDER BY created_at DESC
        ');
        $stmt->execute();
        $pendingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['users' => $pendingUsers]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch pending users: ' . $e->getMessage()]);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Approve or reject user
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'] ?? '';
    $action = $data['action'] ?? ''; // 'approve' or 'reject'
    
    if (empty($userId) || !in_array($action, ['approve', 'reject'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid user ID or action']);
        exit;
    }
    
    try {
        $newStatus = $action === 'approve' ? 'approved' : 'rejected';
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Update user approval status
        $stmt = $pdo->prepare('
            UPDATE users 
            SET approval_status = ?, approved_by = ?, approved_at = NOW() 
            WHERE user_id = ? AND approval_status = "pending"
        ');
        $stmt->execute([$newStatus, $_SESSION['user_id'], $userId]);
        
        if ($stmt->rowCount() > 0) {
            // Get user details for response
            $stmt = $pdo->prepare('SELECT email, role, first_name, last_name, student_id, program_id FROM users WHERE user_id = ?');
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If approving an alumni user, create alumni record
            if ($action === 'approve' && $user['role'] === 'alumni') {
                // Check if alumni record already exists
                $stmt = $pdo->prepare('SELECT alumni_id FROM alumni WHERE user_id = ?');
                $stmt->execute([$userId]);
                
                if (!$stmt->fetch()) {
                    // Use the program_id from the user's registration
                    if ($user['program_id']) {
                        // Create alumni record with basic information
                        $stmt = $pdo->prepare('
                            INSERT INTO alumni (
                                user_id, student_id, first_name, last_name, 
                                program_id, year_graduated, profile_completion_percentage
                            ) VALUES (?, ?, ?, ?, ?, YEAR(NOW()), 30)
                        ');
                        $stmt->execute([
                            $userId,
                            $user['student_id'],
                            $user['first_name'],
                            $user['last_name'],
                            $user['program_id']
                        ]);
                    }
                }
            }
            
            // Commit transaction
            $pdo->commit();
            
            echo json_encode([
                'message' => "User {$user['first_name']} {$user['last_name']} ({$user['email']}) has been {$newStatus}" . 
                           ($action === 'approve' && $user['role'] === 'alumni' ? ' and added to alumni records' : ''),
                'action' => $action,
                'user' => $user
            ]);
        } else {
            $pdo->rollback();
            http_response_code(404);
            echo json_encode(['error' => 'User not found or already processed']);
        }
        
    } catch(PDOException $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update user status: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
