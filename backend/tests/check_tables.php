<?php
require_once __DIR__ . '/../config.php';

try {
    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Available tables:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    // Check users table structure
    if (in_array('users', $tables)) {
        echo "\nUsers table structure:\n";
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']})\n";
        }
    }
    
    // Check employment_records table structure
    if (in_array('employment_records', $tables)) {
        echo "\nEmployment records table structure:\n";
        $stmt = $pdo->query("DESCRIBE employment_records");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']})\n";
        }
    }
    
    // Check programs table
    if (in_array('programs', $tables)) {
        echo "\nPrograms table structure:\n";
        $stmt = $pdo->query("DESCRIBE programs");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']})\n";
        }
        
        // Get some sample programs
        echo "\nSample programs:\n";
        $stmt = $pdo->query("SELECT * FROM programs LIMIT 5");
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($programs as $program) {
            echo "- {$program['program_name']} (ID: {$program['program_id']})\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
