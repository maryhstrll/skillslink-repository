<?php
require_once 'config.php';

echo "Checking employment_records table structure:\n";
$stmt = $pdo->query('DESCRIBE employment_records');
while($row = $stmt->fetch()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}

echo "\nChecking salary_range data in employment_records:\n";
$stmt = $pdo->query('SELECT record_id, alumni_id, salary_range FROM employment_records WHERE salary_range IS NOT NULL ORDER BY record_id DESC LIMIT 10');
$records = $stmt->fetchAll();

foreach($records as $record) {
    echo "Record ID: {$record['record_id']}, Alumni: {$record['alumni_id']}, Salary: '{$record['salary_range']}'\n";
}

if (empty($records)) {
    echo "No records with salary_range data found\n";
}
?>
