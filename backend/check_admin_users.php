<?php
require_once 'config.php';

echo "Admin users in the database:\n";

$stmt = $pdo->query("SELECT user_id, email, role, first_name, last_name FROM users WHERE role = 'admin'");
while ($row = $stmt->fetch()) {
    echo "Admin User - ID: {$row['user_id']}, Email: {$row['email']}, Role: {$row['role']}, Name: {$row['first_name']} {$row['last_name']}\n";
}

echo "\nChecking if admin users have alumni records:\n";
$stmt = $pdo->query("
    SELECT u.user_id, u.email, u.role, a.alumni_id 
    FROM users u 
    LEFT JOIN alumni a ON u.user_id = a.user_id 
    WHERE u.role = 'admin'
");
while ($row = $stmt->fetch()) {
    $hasAlumniRecord = $row['alumni_id'] ? 'YES' : 'NO';
    echo "Admin User - ID: {$row['user_id']}, Has Alumni Record: $hasAlumniRecord\n";
}
?>
