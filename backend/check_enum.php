<?php
require_once 'config.php';

try {
    $stmt = $pdo->query('SHOW COLUMNS FROM employment_records LIKE "employment_status"');
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Employment Status Column Type: " . $col['Type'] . "\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
