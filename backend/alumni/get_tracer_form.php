<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';        // your DB connection (should provide $pdo or DB constants)
require_once __DIR__ . '/../session.php';    // make sure this sets $_SESSION['user_id'] and maybe role

$stmt = $pdo->prepare("SELECT form_id, form_questions, form_description FROM tracer_forms WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    echo json_encode([
        'form_id' => $result['form_id'],
        'questions_json' => $result['form_questions'],
        'form_description' => $result['form_description']
    ]);
} else {
    echo json_encode([
        'form_id' => null,
        'questions_json' => '[]',
        'form_description' => ''
    ]);
}