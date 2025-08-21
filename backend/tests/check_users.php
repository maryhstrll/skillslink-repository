<?php
require_once 'config.php';

try {
    $stmt = $pdo->query('SELECT u.user_id, u.email, a.alumni_id FROM users u JOIN alumni a ON u.user_id = a.user_id LIMIT 5');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Users with alumni records:\n";
    foreach($users as $user) {
        echo "User ID: {$user['user_id']}, Email: {$user['email']}, Alumni ID: {$user['alumni_id']}\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
