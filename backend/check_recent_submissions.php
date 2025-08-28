<?php
require_once 'config.php';

echo "Checking recent form submissions:\n\n";

// Check employment_records submissions
$stmt = $pdo->query("SELECT er.*, CONCAT(a.first_name, ' ', a.last_name) as alumni_name 
                     FROM employment_records er 
                     LEFT JOIN alumni a ON er.alumni_id = a.alumni_id 
                     ORDER BY er.submitted_at DESC LIMIT 3");
$employment_records = $stmt->fetchAll();

echo "Recent employment record submissions:\n";
foreach ($employment_records as $record) {
    echo "ID: {$record['record_id']}, Alumni: {$record['alumni_name']}\n";
    echo "  Status: {$record['employment_status']}\n";
    echo "  Company: " . ($record['company_name'] ?: 'EMPTY') . "\n";
    echo "  Job Title: " . ($record['occupation'] ?: 'EMPTY') . "\n";
    echo "  Salary Range: '" . ($record['salary_range'] ?: 'EMPTY') . "'\n";
    echo "  Work Classification: " . ($record['work_classification'] ?: 'EMPTY') . "\n";
    echo "  Submitted: {$record['submitted_at']}\n";
    echo "  ---\n";
}

// Check form_responses submissions
$stmt = $pdo->query("SELECT fr.*, CONCAT(a.first_name, ' ', a.last_name) as alumni_name 
                     FROM form_responses fr 
                     LEFT JOIN alumni a ON fr.alumni_id = a.alumni_id 
                     ORDER BY fr.submitted_at DESC LIMIT 3");
$form_responses = $stmt->fetchAll();

echo "\nRecent form response submissions:\n";
foreach ($form_responses as $response) {
    echo "ID: {$response['response_id']}, Alumni: {$response['alumni_name']}\n";
    echo "  Form ID: {$response['form_id']}\n";
    echo "  Complete: " . ($response['is_complete'] ? 'Yes' : 'No') . "\n";
    
    $responses_data = json_decode($response['responses'], true);
    if ($responses_data && is_array($responses_data)) {
        echo "  Responses data:\n";
        foreach ($responses_data as $key => $value) {
            $display_value = is_array($value) ? json_encode($value) : $value;
            echo "    $key: " . ($display_value ?: 'EMPTY') . "\n";
        }
    }
    echo "  Submitted: {$response['submitted_at']}\n";
    echo "  ---\n";
}
?>
