<?php
require_once 'config.php';

// Simulate a session for testing
session_start();
$_SESSION['user_id'] = 9; // Use a test user ID with alumni record

// Test the get_employment_tracer.php endpoint
echo "Testing get_employment_tracer.php endpoint:\n\n";

try {
    // Include the endpoint
    ob_start();
    include 'alumni/get_employment_tracer.php';
    $output = ob_get_clean();
    
    echo "Raw output:\n";
    echo $output . "\n\n";
    
    // Try to decode JSON
    $data = json_decode($output, true);
    if ($data) {
        echo "JSON decoded successfully:\n";
        echo "- Already responded: " . ($data['already_responded'] ? 'Yes' : 'No') . "\n";
        echo "- Form year: " . ($data['form_year'] ?? 'N/A') . "\n";
        echo "- Core questions count: " . count($data['core_questions'] ?? []) . "\n";
        echo "- Additional questions count: " . count($data['additional_questions'] ?? []) . "\n";
        
        if (!empty($data['core_questions'])) {
            echo "\nFirst 3 core questions:\n";
            for ($i = 0; $i < min(3, count($data['core_questions'])); $i++) {
                $q = $data['core_questions'][$i];
                echo "  " . ($i + 1) . ". " . $q['label'] . " (ID: " . $q['id'] . ", Maps to: " . $q['maps_to'] . ")\n";
            }
        }
    } else {
        echo "Failed to decode JSON\n";
        echo "JSON Error: " . json_last_error_msg() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
