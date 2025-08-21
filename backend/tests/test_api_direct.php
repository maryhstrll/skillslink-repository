<?php
// Simple test for the notification API
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing Notification API...\n\n";

// Test the API by including it directly
$_GET['action'] = 'count';
$_SESSION['user_id'] = 9; // Test with a known alumni user ID
$_SESSION['role'] = 'alumni';

try {
    ob_start();
    include 'api/notifications.php';
    $output = ob_get_clean();
    
    echo "API Response:\n";
    echo $output . "\n";
    
    $decoded = json_decode($output, true);
    if ($decoded) {
        echo "\nParsed JSON:\n";
        print_r($decoded);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
