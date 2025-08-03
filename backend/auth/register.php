<?php 
header('Content-type: application.json');
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $email = $data['email'] ?? '';
    $role = 'staff'; // only admins can register other admins

    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'Username or email already exist']);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role, email) VALUES (?, ?, ?, ?)');
    $stmt->execute([$username, $password_hash, $role, $email]);

    echo json_encode(['message' => 'Registration successful']);
} else { 
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

?>