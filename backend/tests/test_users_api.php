<?php
require_once 'config.php';

try {
    echo "Testing users API functionality...\n\n";
    
    // Test basic user query
    $stmt = $pdo->query('SELECT COUNT(*) as user_count FROM users');
    $result = $stmt->fetch();
    echo "Total users in database: " . $result['user_count'] . "\n\n";
    
    // Test detailed user query (similar to API)
    $query = "
        SELECT 
            u.user_id as id,
            u.email,
            u.role,
            u.approval_status,
            u.first_name,
            u.last_name,
            u.middle_name,
            u.student_id,
            u.last_login,
            u.created_at,
            u.updated_at,
            u.is_active,
            p.program_name,
            CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.middle_name, ''), ' ', COALESCE(u.last_name, '')) as full_name
        FROM users u
        LEFT JOIN programs p ON u.program_id = p.program_id
        WHERE u.is_active = 1
        ORDER BY u.created_at DESC
        LIMIT 5
    ";
    
    $stmt = $pdo->query($query);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Sample users:\n";
    echo "=============\n";
    
    foreach ($users as $user) {
        echo "ID: " . $user['id'] . "\n";
        echo "Name: " . trim($user['full_name']) . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Status: " . ($user['is_active'] ? 'active' : 'inactive') . "\n";
        echo "Program: " . ($user['program_name'] ?? 'N/A') . "\n";
        echo "Created: " . $user['created_at'] . "\n";
        echo "---\n";
    }
    
    echo "\nUsers API test completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
