<?php
require_once __DIR__ . '/config.php';

echo "=== Debugging Form Responses ===\n\n";

// Check tracer_forms table
echo "1. Checking tracer_forms table:\n";
$stmt = $pdo->query("SELECT form_id, form_title, form_year FROM tracer_forms ORDER BY form_id");
$forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($forms as $form) {
    echo "Form ID: {$form['form_id']}, Title: {$form['form_title']}, Year: {$form['form_year']}\n";
}

echo "\n2. Checking form_responses table:\n";
$stmt = $pdo->query("SELECT form_id, alumni_id, response_id, is_complete, completion_percentage, submitted_at FROM form_responses ORDER BY form_id, alumni_id");
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($responses)) {
    echo "No responses found in form_responses table\n";
} else {
    foreach ($responses as $response) {
        echo "Response ID: {$response['response_id']}, Form ID: {$response['form_id']}, Alumni ID: {$response['alumni_id']}, Complete: {$response['is_complete']}, Percentage: {$response['completion_percentage']}, Submitted: {$response['submitted_at']}\n";
    }
}

echo "\n3. Checking alumni table:\n";
$stmt = $pdo->query("SELECT alumni_id, user_id, first_name, last_name, student_id FROM alumni ORDER BY alumni_id");
$alumni = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($alumni)) {
    echo "No alumni found in alumni table\n";
} else {
    foreach ($alumni as $alumnus) {
        echo "Alumni ID: {$alumnus['alumni_id']}, User ID: {$alumnus['user_id']}, Name: {$alumnus['first_name']} {$alumnus['last_name']}, Student ID: {$alumnus['student_id']}\n";
    }
}

echo "\n4. Testing the API query:\n";
if (!empty($forms)) {
    $form_id = $forms[0]['form_id'];
    echo "Testing with form_id: $form_id\n";
    
    $sql = "SELECT fr.*, 
                   CONCAT(a.first_name, ' ', a.last_name) AS alumni_name,
                   u.email AS alumni_email,
                   a.student_id AS alumni_student_id
            FROM form_responses fr
            LEFT JOIN alumni a ON fr.alumni_id = a.alumni_id
            LEFT JOIN users u ON a.user_id = u.user_id
            WHERE fr.form_id = :form_id
            ORDER BY fr.submitted_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':form_id' => $form_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Query returned " . count($rows) . " rows:\n";
    foreach ($rows as $row) {
        echo "- Response ID: {$row['response_id']}, Alumni: {$row['alumni_name']}, Email: {$row['alumni_email']}\n";
    }
}
?>
