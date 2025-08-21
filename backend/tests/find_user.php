<?php
require 'config.php'; 

echo "Finding user with employment records:\n";

$stmt = $pdo->query('SELECT a.alumni_id, a.user_id, CONCAT(a.first_name, " ", a.last_name) as name 
                    FROM alumni a 
                    LEFT JOIN employment_records e ON a.alumni_id = e.alumni_id 
                    WHERE e.alumni_id IS NOT NULL');
                    
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Alumni ID: {$row['alumni_id']}, User ID: {$row['user_id']}, Name: {$row['name']}\n";
}
?>
