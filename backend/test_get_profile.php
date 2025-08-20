<?php
// Test script for get_profile.php
require_once 'config.php';

// Mock session for testing  
$_SESSION['user_id'] = 26;
$_SESSION['role'] = 'alumni';
$_SERVER['REQUEST_METHOD'] = 'GET'; // Add this for CLI testing

echo "Testing get_profile.php endpoint...\n";
echo "Session data: user_id={$_SESSION['user_id']}, role={$_SESSION['role']}\n";

// Capture output and errors
ob_start();
$old_error_handler = set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "PHP Error ($errno): $errstr in $errfile on line $errline\n";
});

try {
    include 'alumni/get_profile.php';
} catch (Exception $e) {
    echo "Exception caught: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

$output = ob_get_clean();
restore_error_handler();

echo "API Output:\n";
echo $output . "\n";

// Try to decode JSON
$json = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "\nParsed JSON:\n";
    print_r($json);
} else {
    echo "\nJSON decode error: " . json_last_error_msg() . "\n";
}
?>
