<?php
// CORS headers are handled by .htaccess
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../session.php';

try {
    // Only allow GET requests
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit();
    }

    // Get all batches with their programs
    $sql = "SELECT 
                b.batch_id,
                b.batch_year,
                b.batch_name,
                b.total_graduates,
                p.program_id,
                p.program_name,
                p.program_code,
                p.department
            FROM batches b
            JOIN programs p ON b.program_id = p.program_id
            WHERE b.batch_year IS NOT NULL
            ORDER BY b.batch_year DESC, p.program_name ASC";
    
    $stmt = $pdo->query($sql);
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'batches' => $batches
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
