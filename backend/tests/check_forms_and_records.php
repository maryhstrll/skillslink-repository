<?php
require_once __DIR__ . '/config.php';

echo "Existing tracer forms:\n";
$stmt = $pdo->prepare('SELECT form_id, form_title, form_year FROM tracer_forms ORDER BY form_id');
$stmt->execute();
$forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($forms as $form) {
    echo "Form ID: {$form['form_id']}, Title: {$form['form_title']}, Year: {$form['form_year']}\n";
}

echo "\nEmployment records with NULL tracer_form_id:\n";
$stmt = $pdo->prepare('SELECT record_id, alumni_id, form_year FROM employment_records WHERE tracer_form_id IS NULL');
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($records as $record) {
    echo "Record ID: {$record['record_id']}, Alumni ID: {$record['alumni_id']}, Form Year: {$record['form_year']}\n";
}
?>
