<?php 
session_start();
header('Content-type: application.json');
require_once '../config.php';

// Only admin can register new users
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $email = $data['email'] ?? '';
    $role = $data['role'] ?? 'staff'; // default to staff

    // validate inputs
    if (empty($username) || empty($password) || empty($email)) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are requiered']);
        exit;
    }

    // Check if the username or email exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'Username or email already exist']);
        exit;
    }

    // hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role, email) VALUES (?, ?, ?, ?)');
    try {
        $stmt->execute([$username, $password_hash, $role, $email]);
        echo json_encode(['message' => 'Registration successful']);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
    }
} else { 
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

?>