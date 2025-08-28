<?php
require_once 'config.php';

echo "=== Checking employment_status enum values ===\n";

try {
    $stmt = $pdo->query('SHOW COLUMNS FROM employment_records LIKE "employment_status"');
    $row = $stmt->fetch();
    echo "Type: " . $row['Type'] . "\n";
    
    echo "\n=== Checking work_classification enum values ===\n";
    $stmt = $pdo->query('SHOW COLUMNS FROM employment_records LIKE "work_classification"');
    $row = $stmt->fetch();
    echo "Type: " . $row['Type'] . "\n";
    
    echo "\n=== Checking nature_of_employment enum values ===\n";
    $stmt = $pdo->query('SHOW COLUMNS FROM employment_records LIKE "nature_of_employment"');
    $row = $stmt->fetch();
    echo "Type: " . $row['Type'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
