<?php
require_once __DIR__ . '/config.php';

echo "Checking tracer forms:\n";
echo "=====================\n";

$stmt = $pdo->prepare('SELECT form_id, form_title, form_year, is_active, created_at FROM tracer_forms ORDER BY created_at DESC');
$stmt->execute();
$forms = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($forms as $form) {
    echo "ID: {$form['form_id']}\n";
    echo "Title: {$form['form_title']}\n";
    echo "Year: {$form['form_year']}\n";
    echo "Active: " . ($form['is_active'] ? 'Yes' : 'No') . "\n";
    echo "Created: {$form['created_at']}\n";
    echo "---\n";
}

echo "\nChecking active forms:\n";
$stmt = $pdo->prepare('SELECT * FROM tracer_forms WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1');
$stmt->execute();
$activeForm = $stmt->fetch(PDO::FETCH_ASSOC);

if ($activeForm) {
    echo "Active form found:\n";
    echo "ID: {$activeForm['form_id']}\n";
    echo "Title: {$activeForm['form_title']}\n";
    echo "Questions: " . ($activeForm['form_questions'] ? 'Present' : 'None') . "\n";
} else {
    echo "No active form found!\n";
}
?>
