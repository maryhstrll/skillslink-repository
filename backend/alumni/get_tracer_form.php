<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';        // your DB connection (should provide $pdo or DB constants)
require_once __DIR__ . '/../session.php';    // make sure this sets $_SESSION['user_id'] and maybe role

$userId = $_SESSION['user_id'] ?? null;

// Check authentication
if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Get the active form
$stmt = $pdo->prepare("SELECT form_id, form_questions, form_description FROM tracer_forms WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    // Get the alumni_id from user_id
    $stmt = $pdo->prepare("SELECT alumni_id FROM alumni WHERE user_id = ?");
    $stmt->execute([$userId]);
    $alumni = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$alumni) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Alumni record not found']);
        exit;
    }
    
    $alumni_id = $alumni['alumni_id'];
    $form_id = $result['form_id'];
    
    // Check if user already submitted for this form
    $stmt = $pdo->prepare("SELECT response_id, responses FROM form_responses WHERE form_id = ? AND alumni_id = ? AND is_complete = 1");
    $stmt->execute([$form_id, $alumni_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // User has already responded
        echo json_encode([
            'already_responded' => true,
            'message' => 'You have already submitted your response to this tracer form. Thank you for your participation!',
            'form_id' => $form_id,
            'form_description' => $result['form_description'],
            'questions_json' => $result['form_questions'], // Include form structure for editing
            'existing_responses' => json_decode($existing['responses'], true)
        ]);
    } else {
        // User hasn't responded yet
        echo json_encode([
            'already_responded' => false,
            'form_id' => $form_id,
            'questions_json' => $result['form_questions'],
            'form_description' => $result['form_description']
        ]);
    }
} else {
    echo json_encode([
        'already_responded' => false,
        'form_id' => null,
        'questions_json' => '[]',
        'form_description' => '',
        'message' => 'No active tracer form available at the moment.'
    ]);
}