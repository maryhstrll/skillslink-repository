<?php
// CORS headers are handled by .htaccess
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

echo json_encode([
    'success' => true,
    'message' => 'CORS test endpoint working',
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD']
]);
?>
