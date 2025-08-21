<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../session.php';

// Helper function to get JSON body
function get_json_body() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $_POST;
}

$action = $_GET['action'] ?? null;
$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'alumni';

// Check if user is authenticated
if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

try {
    // Get notifications for the current user
    if ($action === 'get' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare("
            SELECT notification_id, type, title, message, status, priority, category, 
                   created_at, scheduled_at, sent_at, read_at
            FROM notifications 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $stmt->execute([':user_id' => $userId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'notifications' => $notifications]);
        exit;
    }
    
    // Get unread notification count
    if ($action === 'count' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as unread_count 
            FROM notifications 
            WHERE user_id = :user_id AND status IN ('pending', 'sent', 'delivered')
        ");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'unread_count' => (int)$result['unread_count']]);
        exit;
    }
    
    // Mark notification as read
    if ($action === 'mark_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = get_json_body();
        $notification_id = $data['notification_id'] ?? null;
        
        if (!$notification_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
            exit;
        }
        
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET status = 'read', read_at = NOW() 
            WHERE notification_id = :id AND user_id = :user_id
        ");
        $result = $stmt->execute([
            ':id' => $notification_id,
            ':user_id' => $userId
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update notification']);
        }
        exit;
    }
    
    // Mark all notifications as read
    if ($action === 'mark_all_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET status = 'read', read_at = NOW() 
            WHERE user_id = :user_id AND status IN ('pending', 'sent', 'delivered')
        ");
        $result = $stmt->execute([':user_id' => $userId]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update notifications']);
        }
        exit;
    }
    
    // Create notification (admin only)
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($role !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied: admin role required']);
            exit;
        }
        
        $data = get_json_body();
        
        $required_fields = ['user_ids', 'title', 'message'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
                exit;
            }
        }
        
        $user_ids = $data['user_ids']; // Array of user IDs
        $type = $data['type'] ?? 'in_app';
        $title = $data['title'];
        $message = $data['message'];
        $priority = $data['priority'] ?? 'normal';
        $category = $data['category'] ?? 'announcement';
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, type, title, message, status, priority, category) 
                VALUES (:user_id, :type, :title, :message, 'sent', :priority, :category)
            ");
            
            $success_count = 0;
            foreach ($user_ids as $user_id) {
                $result = $stmt->execute([
                    ':user_id' => $user_id,
                    ':type' => $type,
                    ':title' => $title,
                    ':message' => $message,
                    ':priority' => $priority,
                    ':category' => $category
                ]);
                
                if ($result) {
                    $success_count++;
                }
            }
            
            $pdo->commit();
            echo json_encode([
                'success' => true, 
                'message' => "Notifications sent to $success_count users",
                'sent_count' => $success_count
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    // Invalid request
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
