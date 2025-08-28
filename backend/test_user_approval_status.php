<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT user_id, first_name, last_name, email, role, is_active, approval_status FROM users ORDER BY user_id LIMIT 10");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current users in database:\n";
    echo "ID | Name | Active | Approval Status\n";
    echo "---|------|--------|----------------\n";
    
    foreach($users as $user) {
        $name = trim($user['first_name'] . ' ' . $user['last_name']);
        printf("%2d | %-20s | %-6s | %s\n", 
            $user['user_id'], 
            substr($name, 0, 20),
            $user['is_active'] ? 'active' : 'inactive',
            $user['approval_status'] ?? 'NULL'
        );
    }
    
    // Let's manually set two users to rejected for testing
    echo "\nSetting two users to rejected status...\n";
    $pdo->exec("UPDATE users SET approval_status = 'rejected' WHERE user_id IN (28, 29)");
    
    echo "Updated users:\n";
    $stmt = $pdo->query("SELECT user_id, first_name, last_name, email, role, is_active, approval_status FROM users WHERE user_id IN (28, 29)");
    $updated_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($updated_users as $user) {
        $name = trim($user['first_name'] . ' ' . $user['last_name']);
        printf("%2d | %-20s | %-6s | %s\n", 
            $user['user_id'], 
            substr($name, 0, 20),
            $user['is_active'] ? 'active' : 'inactive',
            $user['approval_status']
        );
    }
    
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
