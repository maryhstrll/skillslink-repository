<?php
require_once 'config.php';

try {
    $stmt = $pdo->query('DESCRIBE employment_records');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Employment Records Table Columns:\n";
    foreach($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
