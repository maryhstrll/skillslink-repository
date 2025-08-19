<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';

// Check if user is logged in and is alumni
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'alumni') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Alumni privileges required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user_id'];
    
    // Get the social links to update
    $linkedinProfile = $data['linkedin_profile'] ?? null;
    $facebookProfile = $data['facebook_profile'] ?? null;
    
    // Validate URLs if provided
    if (!empty($linkedinProfile) && !filter_var($linkedinProfile, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid LinkedIn URL format']);
        exit;
    }
    
    if (!empty($facebookProfile) && !filter_var($facebookProfile, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid Facebook URL format']);
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update alumni table
    $stmt = $pdo->prepare('
        UPDATE alumni 
        SET linkedin_profile = ?, facebook_profile = ?
        WHERE user_id = ?
    ');
    $stmt->execute([$linkedinProfile, $facebookProfile, $userId]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Social links updated successfully'
    ]);
    
} catch (PDOException $e) {
    $pdo->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>
