<?php
// Simple test to simulate API call to users endpoint
session_start();

// Mock an admin session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Set request method
$_SERVER['REQUEST_METHOD'] = 'GET';

// Capture output
ob_start();

// Include the API
require_once 'api/users.php';

// Get the output
$output = ob_get_clean();

echo "API Response:\n";
echo "=============\n";
echo $output . "\n";

// Try to decode JSON
$data = json_decode($output, true);
if ($data) {
    echo "\nParsed Response:\n";
    echo "================\n";
    echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    if (isset($data['data'])) {
        echo "Users count: " . count($data['data']) . "\n";
        echo "Total records: " . ($data['pagination']['total_records'] ?? 'unknown') . "\n";
    }
    if (isset($data['error'])) {
        echo "Error: " . $data['error'] . "\n";
    }
} else {
    echo "Failed to parse JSON response\n";
}
?>
