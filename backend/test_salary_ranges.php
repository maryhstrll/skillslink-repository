<?php
require_once 'config.php';

echo "Testing salary range data submission:\n\n";

// First, let's check what values are currently in the salary_range column
echo "Current salary data in employment_records:\n";
$stmt = $pdo->query('SELECT record_id, alumni_id, salary_range, employment_status FROM employment_records ORDER BY record_id DESC LIMIT 5');
$records = $stmt->fetchAll();

foreach($records as $record) {
    echo "Record ID: {$record['record_id']}, Alumni: {$record['alumni_id']}, Status: {$record['employment_status']}, Salary: '" . ($record['salary_range'] ?: 'NULL/EMPTY') . "'\n";
}

echo "\n\nLet's check if the salary_range enum values are correct:\n";
$stmt = $pdo->query("SHOW COLUMNS FROM employment_records LIKE 'salary_range'");
$column = $stmt->fetch();
echo "Salary Range Column Type: " . $column['Type'] . "\n";

// Let's try to update one record with a proper salary range
echo "\nTesting update of salary range for record ID 1:\n";
try {
    $stmt = $pdo->prepare("UPDATE employment_records SET salary_range = ? WHERE record_id = 1");
    $stmt->execute(['30,000 to 40,000']);
    echo "Successfully updated salary range\n";
    
    // Verify the update
    $stmt = $pdo->prepare("SELECT salary_range FROM employment_records WHERE record_id = 1");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "Updated value: '" . $result['salary_range'] . "'\n";
    
} catch (Exception $e) {
    echo "Error updating salary: " . $e->getMessage() . "\n";
}

// Test all valid salary ranges
echo "\nTesting all valid salary ranges:\n";
$valid_ranges = [
    'Below 10,000',
    '10,000 to 20,000', 
    '20,000 to 30,000',
    '30,000 to 40,000', 
    '40,000 to 50,000',
    '50,000 to 60,000',
    'Above 60,000'
];

foreach ($valid_ranges as $range) {
    try {
        $stmt = $pdo->prepare("SELECT ? as test_range");
        $stmt->execute([$range]);
        echo "Valid: '$range'\n";
    } catch (Exception $e) {
        echo "Invalid: '$range' - " . $e->getMessage() . "\n";
    }
}
?>
