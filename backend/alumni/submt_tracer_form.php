<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '../config.php';        // your DB connection (should provide $pdo or DB constants)
require_once __DIR__ . '../session.php';    // make sure this sets $_SESSION['user_id'] and maybe role

$data = json_decode(file_get_contents("php://input"), true);

$form_id = 1; // or pass from frontend
$answers_json = json_encode($data['answers']);

$stmt = $conn->prepare("INSERT INTO tracer_responses (form_id, alumni_id, answers_json) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $form_id, $user_id, $answers_json);

if ($stmt->execute()) {
  echo json_encode(['success' => true]);
} else {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to save response.']);
}