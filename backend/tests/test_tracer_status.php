<?php
// Test script for tracer status
require_once 'config.php';

// Mock session for testing
$_SESSION['user_id'] = 26; // This should be the user_id from the alumni table that has the employment record
$_SESSION['role'] = 'alumni';

echo "Testing tracer status API...\n";

// Capture output
ob_start();
include 'alumni/get_tracer_status.php';
$output = ob_get_clean();

echo "API Response:\n";
echo $output . "\n";

// Decode and display nicely
$data = json_decode($output, true);
if ($data && $data['success']) {
    echo "\nParsed Response:\n";
    echo "Has Active Form: " . ($data['has_active_form'] ? 'Yes' : 'No') . "\n";
    echo "Status: " . $data['status'] . "\n";
    echo "Description: " . $data['description'] . "\n";
    echo "Form Year: " . ($data['form_year'] ?? 'N/A') . "\n";
    echo "Form Title: " . ($data['form_title'] ?? 'N/A') . "\n";
    echo "Submitted: " . ($data['submitted'] ? 'Yes' : 'No') . "\n";
    echo "Employment Submitted: " . ($data['employment_submitted'] ? 'Yes' : 'No') . "\n";
    echo "Additional Submitted: " . ($data['additional_submitted'] ? 'Yes' : 'No') . "\n";
} else {
    echo "API Error: " . ($data['message'] ?? 'Unknown error') . "\n";
}
?>
