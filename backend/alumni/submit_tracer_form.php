<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../session.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'alumni';

// Check authentication
if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['answers'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing answers data']);
    exit;
}

try {
    // Log initial data
    error_log("=== FORM SUBMISSION START ===");
    error_log("User ID: $userId, Role: $role");
    error_log("Received data: " . json_encode($data));
    
    // Get the alumni_id from user_id
    $stmt = $pdo->prepare("SELECT alumni_id FROM alumni WHERE user_id = ?");
    $stmt->execute([$userId]);
    $alumni = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$alumni) {
        error_log("Alumni record not found for user_id: $userId");
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Alumni record not found']);
        exit;
    }
    
    $alumni_id = $alumni['alumni_id'];
    error_log("Alumni ID found: $alumni_id");
    
    // Get the active form ID
    $stmt = $pdo->prepare("SELECT form_id FROM tracer_forms WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
    $stmt->execute();
    $form = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$form) {
        error_log("No active form found");
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No active form found']);
        exit;
    }

    $form_id = $form['form_id'];
    $answers_json = json_encode($data['answers']);

    // Log the data being processed for debugging
    error_log("Active form found: form_id=$form_id");
    error_log("Answers JSON: $answers_json");

    // Check if user already submitted for this form
    error_log("Checking for existing response...");
    $stmt = $pdo->prepare("SELECT response_id FROM form_responses WHERE form_id = ? AND alumni_id = ?");
    $stmt->execute([$form_id, $alumni_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        error_log("Updating existing response: response_id=" . $existing['response_id']);
        // Update existing response
        $stmt = $pdo->prepare("UPDATE form_responses SET responses = ?, submitted_at = NOW(), is_complete = 1 WHERE response_id = ?");
        $stmt->execute([$answers_json, $existing['response_id']]);
        error_log("Update successful");
    } else {
        error_log("Creating new response...");
        // Create new response
        $stmt = $pdo->prepare("INSERT INTO form_responses (form_id, alumni_id, responses, submitted_at, is_complete) VALUES (?, ?, ?, NOW(), 1)");
        $stmt->execute([$form_id, $alumni_id, $answers_json]);
        error_log("Insert successful, new response_id: " . $pdo->lastInsertId());
    }

    error_log("=== FORM SUBMISSION SUCCESS ===");
    echo json_encode(['success' => true, 'message' => 'Response submitted successfully']);

} catch (Exception $e) {
    error_log("=== FORM SUBMISSION ERROR ===");
    error_log("Error submitting tracer form: " . $e->getMessage());
    error_log("Error code: " . $e->getCode());
    error_log("Exception trace: " . $e->getTraceAsString());
    error_log("Data: userId=$userId, alumni_id=" . ($alumni_id ?? 'null') . ", form_id=" . ($form_id ?? 'null') . ", answers_json=" . ($answers_json ?? 'null'));
    error_log("PDO Error Info: " . json_encode($pdo->errorInfo()));
    error_log("=== END ERROR ===");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
}
?>
