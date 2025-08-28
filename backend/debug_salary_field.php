<?php
require_once 'config.php';

echo "=== Checking salary_range field structure ===\n";

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Check field structure
    $stmt = $pdo->query('DESCRIBE employment_records');
    while ($row = $stmt->fetch()) {
        if ($row['Field'] === 'salary_range') {
            echo "Field: " . $row['Field'] . "\n";
            echo "Type: " . $row['Type'] . "\n";
            echo "Null: " . $row['Null'] . "\n";
            echo "Default: " . $row['Default'] . "\n";
            echo "\n";
            break;
        }
    }
    
    // Check if there are any records with non-empty salary_range
    echo "=== Checking existing salary_range values ===\n";
    
    // First check what columns exist
    echo "Table structure:\n";
    $stmt = $pdo->query('DESCRIBE employment_records');
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . " | " . $row['Type'] . "\n";
    }
    echo "\n";
    
    $stmt = $pdo->query("SELECT alumni_id, salary_range, company_name, occupation, created_at FROM employment_records WHERE salary_range IS NOT NULL AND salary_range != '' ORDER BY created_at DESC LIMIT 5");
    $count = 0;
    while ($row = $stmt->fetch()) {
        $count++;
        echo "Alumni: {$row['alumni_id']}, Salary: '{$row['salary_range']}', Company: {$row['company_name']}, Job: {$row['occupation']}, Created: {$row['created_at']}\n";
    }
    
    if ($count === 0) {
        echo "No records found with non-empty salary_range values.\n";
    }
    
    // Check total records vs empty salary_range
    $totalStmt = $pdo->query("SELECT COUNT(*) as total FROM employment_records");
    $total = $totalStmt->fetch()['total'];
    
    $emptyStmt = $pdo->query("SELECT COUNT(*) as empty FROM employment_records WHERE salary_range IS NULL OR salary_range = ''");
    $empty = $emptyStmt->fetch()['empty'];
    
    echo "\nTotal employment records: $total\n";
    echo "Records with empty salary_range: $empty\n";
    echo "Records with salary_range data: " . ($total - $empty) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
