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
    
    // Get the fields to update
    $phoneNumber = $data['phone_number'] ?? null;
    $alternativePhone = $data['alternative_phone'] ?? null;
    $barangay = $data['barangay'] ?? null;
    $city = $data['city'] ?? null;
    $province = $data['province'] ?? null;
    $country = $data['country'] ?? null;
    $postalCode = $data['postal_code'] ?? null;
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update alumni table
    $stmt = $pdo->prepare('
        UPDATE alumni 
        SET phone_number = ?, alternative_phone = ?, barangay = ?, city = ?, 
            province = ?, country = ?, postal_code = ?
        WHERE user_id = ?
    ');
    $stmt->execute([
        $phoneNumber, $alternativePhone, $barangay, $city, 
        $province, $country, $postalCode, $userId
    ]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully'
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
