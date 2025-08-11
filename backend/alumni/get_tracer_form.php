<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '../config.php';        // your DB connection (should provide $pdo or DB constants)
require_once __DIR__ . '../session.php';    // make sure this sets $_SESSION['user_id'] and maybe role

$stmt = $conn->prepare("SELECT id, form_questions FROM tracer_forms WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
  'form_id' => $result['id'],
  'questions_json' => $result['form_questions']
]);