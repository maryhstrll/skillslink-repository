<?php
session_start();
// CORS is handled by .htaccess
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config.php';

function validateSession() {
    global $pdo;
    
    // Check if user session exists
    if (!isset($_SESSION['user_id'])) {
        return [
            'loggedIn' => false,
            'error' => 'No active session found'
        ];
    }
    
    try {
        // Verify user still exists and is approved in database
        $stmt = $pdo->prepare('
            SELECT user_id, email, role, first_name, last_name, student_id, 
                   approval_status, last_login, created_at 
            FROM users 
            WHERE user_id = ? AND approval_status = "approved"
        ');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // User doesn't exist or is not approved anymore
            session_destroy();
            return [
                'loggedIn' => false,
                'error' => 'User account not found or not approved'
            ];
        }
        
        // Update session data with fresh database info
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['student_id'] = $user['student_id'];
        $_SESSION['approval_status'] = $user['approval_status'];
        
        return [
            'loggedIn' => true,
            'user' => [
                'id' => $user['user_id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'student_id' => $user['student_id'],
                'full_name' => trim($user['first_name'] . ' ' . $user['last_name']),
                'approval_status' => $user['approval_status'],
                'last_login' => $user['last_login'],
                'member_since' => $user['created_at']
            ],
            'session' => [
                'session_id' => session_id(),
                'session_started' => $_SESSION['login_time'] ?? null,
                'expires_in' => ini_get('session.gc_maxlifetime')
            ]
        ];
        
    } catch (PDOException $e) {
        error_log('Session validation error: ' . $e->getMessage());
        return [
            'loggedIn' => false,
            'error' => 'Database error during session validation'
        ];
    }
}

// Main logic - only execute if this file is accessed directly
if (basename($_SERVER['SCRIPT_NAME']) === 'session.php') {
    try {
        $result = validateSession();
        
        // Set appropriate HTTP status code
        if (!$result['loggedIn']) {
            http_response_code(401);
        }
        
        echo json_encode($result);
        
    } catch (Exception $e) {
        error_log('Session check error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'loggedIn' => false,
            'error' => 'Internal server error'
        ]);
    }
}
?>