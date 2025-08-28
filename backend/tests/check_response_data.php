<?php
require_once __DIR__ . '/../config.php';

echo "Checking form responses data...\n";
$stmt = $pdo->prepare('SELECT form_id, responses FROM form_responses LIMIT 3');
$stmt->execute();
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($responses as $response) {
    echo "Form ID: " . $response['form_id'] . "\n";
    echo "Responses: " . $response['responses'] . "\n";
    echo "---\n";
}

echo "\nChecking employment_records data...\n";
$stmt = $pdo->prepare('SELECT tracer_form_id, employment_status, nature_of_employment FROM employment_records LIMIT 3');
$stmt->execute();
$employment = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($employment as $emp) {
    echo "Tracer Form ID: " . $emp['tracer_form_id'] . "\n";
    echo "Employment Status: " . $emp['employment_status'] . "\n"; 
    echo "Nature: " . $emp['nature_of_employment'] . "\n";
    echo "---\n";
}
?>
