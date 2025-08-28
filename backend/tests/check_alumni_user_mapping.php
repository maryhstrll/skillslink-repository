<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Alumni ID to User ID mapping:\n";
    
    // Check all employment records and their corresponding user data
    $query = "
        SELECT 
            er.alumni_id,
            u.user_id,
            u.first_name,
            u.last_name,
            u.program_id,
            p.program_name,
            er.employment_status
        FROM employment_records er
        LEFT JOIN users u ON er.alumni_id = u.user_id
        LEFT JOIN programs p ON u.program_id = p.program_id
        WHERE er.tracer_form_id = 32
        ORDER BY er.alumni_id
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        echo "Alumni ID: {$row['alumni_id']} -> User ID: {$row['user_id']}, Program ID: {$row['program_id']}, Program: {$row['program_name']}, Status: {$row['employment_status']}\n";
    }
    
    echo "\nAll users with programs:\n";
    $query2 = "
        SELECT 
            u.user_id,
            u.first_name,
            u.last_name,
            u.program_id,
            p.program_name
        FROM users u
        LEFT JOIN programs p ON u.program_id = p.program_id
        WHERE u.program_id IS NOT NULL
        ORDER BY u.user_id
    ";
    
    $stmt2 = $pdo->prepare($query2);
    $stmt2->execute();
    $users = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "User ID: {$user['user_id']}, Name: {$user['first_name']} {$user['last_name']}, Program: {$user['program_name']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
