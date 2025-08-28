<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Testing admin users...\n";
    $stmt = $pdo->prepare('SELECT user_id, email, role, first_name, last_name FROM users WHERE role = ?');
    $stmt->execute(['admin']);
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Admin users found: " . count($admins) . "\n";
    foreach ($admins as $admin) {
        echo "- {$admin['email']} ({$admin['first_name']} {$admin['last_name']})\n";
    }
    
    echo "\nTesting session...\n";
    session_start();
    echo "Session data:\n";
    var_dump($_SESSION);
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
