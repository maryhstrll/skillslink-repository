<?php
require_once __DIR__ . '/../config.php';

echo "Checking which questions are needed for reports but missing from selected questions...\n";

$needed_questions = ['employment_status', 'salary_range', 'job_relevance_to_course', 'months_to_find_job'];

$stmt = $pdo->prepare('SELECT form_questions FROM tracer_forms WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1');
$stmt->execute();
$form = $stmt->fetch(PDO::FETCH_ASSOC);
$questions = json_decode($form['form_questions'], true);
$selected = $questions['selected_employment_questions'] ?? [];

echo "Needed questions: " . implode(', ', $needed_questions) . "\n";
echo "Selected questions: " . implode(', ', $selected) . "\n";

$missing = array_diff($needed_questions, $selected);
echo "Missing questions: " . implode(', ', $missing) . "\n";

if (!empty($missing)) {
    echo "\nThese missing questions need to be added to the active form!\n";
    echo "Without them, the Academic Excellence Dashboard cannot display complete data.\n";
}
?>
