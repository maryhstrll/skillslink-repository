<?php
require_once __DIR__ . '/../config.php';

$stmt = $pdo->prepare('SELECT form_questions FROM tracer_forms WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1');
$stmt->execute();
$form = $stmt->fetch(PDO::FETCH_ASSOC);
$questions = json_decode($form['form_questions'] ?? '[]', true);

echo "Form questions structure:\n";
print_r($questions);

if (isset($questions['selected_employment_questions'])) {
    echo "\nSelected employment questions:\n";
    print_r($questions['selected_employment_questions']);
} else {
    echo "\nNo selected_employment_questions found - this might be the issue!\n";
}
?>
