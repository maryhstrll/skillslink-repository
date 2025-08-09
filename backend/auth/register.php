<?php 
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

// Alumni registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $firstName = $data['firstName'] ?? '';
    $lastName = $data['lastName'] ?? '';
    $studentId = $data['studentId'] ?? '';
    $role = $data['role'] ?? 'alumni'; // default to alumni

    // validate inputs
    if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email, password, first name, and last name are required']);
        exit;
    }

    // Additional validation for alumni
    if ($role === 'alumni' && empty($studentId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Student ID is required for alumni registration']);
        exit;
    }

    // Check if the email or student ID already exists
    $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ? OR student_id = ?');
    $stmt->execute([$email, $studentId]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'Email or student ID already exists']);
        exit;
    }

    // hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role, first_name, last_name, student_id) VALUES (?, ?, ?, ?, ?, ?)');
    try {
        $stmt->execute([$email, $password_hash, $role, $firstName, $lastName, $studentId]);
        echo json_encode([
            'message' => 'Registration successful! Your account is pending approval by an administrator. You will receive confirmation once approved.'
        ]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
    }
} else { 
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

?>