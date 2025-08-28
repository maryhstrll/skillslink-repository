<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Alumni table structure:\n";
    $stmt = $pdo->query("DESCRIBE alumni");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
    echo "\nAlumni records:\n";
    $stmt = $pdo->query("SELECT * FROM alumni LIMIT 10");
    $alumni = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($alumni as $alum) {
        echo "- Alumni ID: {$alum['alumni_id']}, User ID: {$alum['user_id']}, Name: {$alum['first_name']} {$alum['last_name']}\n";
    }
    
    echo "\nAlumni with programs (through users):\n";
    $query = "
        SELECT 
            a.alumni_id,
            a.first_name,
            a.last_name,
            u.program_id,
            p.program_name
        FROM alumni a
        LEFT JOIN users u ON a.user_id = u.user_id
        LEFT JOIN programs p ON u.program_id = p.program_id
        WHERE u.program_id IS NOT NULL
        ORDER BY a.alumni_id
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        echo "- Alumni ID: {$row['alumni_id']}, Name: {$row['first_name']} {$row['last_name']}, Program: {$row['program_name']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
