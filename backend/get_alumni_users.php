<?php
require_once 'config.php';

echo "Approved alumni users:\n\n";

$stmt = $pdo->query('
    SELECT u.email, u.first_name, u.last_name, u.student_id, a.alumni_id
    FROM users u 
    LEFT JOIN alumni a ON u.student_id = a.student_id
    WHERE u.role = "alumni" AND u.approval_status = "approved" 
    LIMIT 5
');

while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Email: {$user['email']}\n";
    echo "Name: {$user['first_name']} {$user['last_name']}\n";
    echo "Student ID: {$user['student_id']}\n";
    echo "Alumni ID: {$user['alumni_id']}\n";
    echo "---\n";
}
?>
