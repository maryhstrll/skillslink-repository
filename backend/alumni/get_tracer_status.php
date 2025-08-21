<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../session.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

try {
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
    
    // Get the active form
    $stmt = $pdo->prepare("SELECT form_id, form_year, form_title FROM tracer_forms WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
    $stmt->execute();
    $form = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$form) {
        echo json_encode([
            'success' => true,
            'has_active_form' => false,
            'submitted' => false,
            'status' => 'No Active Form',
            'description' => 'No active tracer form available at the moment.',
            'form_year' => null,
            'form_title' => null
        ]);
        exit;
    }
    
    $form_id = $form['form_id'];
    $form_year = $form['form_year'];
    $form_title = $form['form_title'] ?? "Tracer Form {$form_year}";
    
    // Check if user already submitted employment data for this specific form
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM employment_records WHERE alumni_id = ? AND tracer_form_id = ?");
    $stmt->execute([$alumni_id, $form_id]);
    $employment_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Check if user has additional responses
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM form_responses WHERE form_id = ? AND alumni_id = ? AND is_complete = 1");
    $stmt->execute([$form_id, $alumni_id]);
    $additional_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Determine if the user has submitted (either employment data or form responses)
    $has_submitted = $employment_count > 0 || $additional_count > 0;
    
    if ($has_submitted) {
        $status = 'Submitted';
        $description = "Thank you for submitting your tracer form for {$form_year}.";
    } else {
        $status = 'Not Submitted';
        $description = "Please submit your tracer form for {$form_year}.";
    }
    
    echo json_encode([
        'success' => true,
        'has_active_form' => true,
        'submitted' => $has_submitted,
        'status' => $status,
        'description' => $description,
        'form_year' => $form_year,
        'form_title' => $form_title,
        'employment_submitted' => $employment_count > 0,
        'additional_submitted' => $additional_count > 0
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
}
?>
