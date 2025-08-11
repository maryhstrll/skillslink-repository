<?php
session_start();
// CORS is handled by .htaccess
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $login = $data['login'] ?? ''; // Can be email or student_id
    $password = $data['password'] ?? '';

    // Validate the inputs
    if (empty($login) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email/Student ID and password are required']);
        exit;
    }

    // Fetch user with approval status - check both email and student_id
    $stmt = $pdo->prepare('SELECT user_id, email, password_hash, role, approval_status, first_name, last_name, student_id FROM users WHERE email = ? OR student_id = ?');
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Check if user is approved
        if ($user['approval_status'] !== 'approved') {
            http_response_code(403);
            $message = 'Your account is not approved for access.';
            if ($user['approval_status'] === 'pending') {
                $message = 'Your account is pending for approval by the administrator. Please wait for the confirmation.';
            } elseif ($user['approval_status'] === 'rejected') {
                $message = 'Your account has been rejected. Please contact the administrator.';
            }
            echo json_encode(['error' => $message]);
            exit;
        }

        // Start session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['student_id'] = $user['student_id'];
        $_SESSION['approval_status'] = $user['approval_status'];
        
        // Update last login
        $stmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE user_id = ?');
        $stmt->execute([$user['user_id']]);
        
        echo json_encode([
            'message' => 'Login successfully', 
            'user' => [
                'email' => $user['email'],
                'role' => $user['role'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'student_id' => $user['student_id'],
                'full_name' => trim($user['first_name'] . ' ' . $user['last_name'])
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>