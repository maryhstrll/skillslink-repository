<?php
// Test the form_responses API endpoint directly
require_once __DIR__ . '/config.php';

// Simulate being an admin for testing
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Simulate the GET request
$_GET['action'] = 'list';
$_GET['form_id'] = '19'; // The form ID we found in the debug

echo "=== Testing API Call ===\n";
echo "Simulating admin user with role 'admin'\n";
echo "Requesting responses for form_id: 19\n\n";

// Include the form_responses.php logic
$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'alumni';

echo "Session user_id: $userId\n";
echo "Session role: $role\n\n";

$action = $_GET['action'] ?? null;

// GET list of responses for a form (admin)
if ($action === 'list' && isset($_GET['form_id'])) {
    if ($role !== 'admin') { 
        echo "ERROR: Access denied - role is not admin\n";
        exit; 
    }
    
    $form_id = (int)$_GET['form_id'];
    echo "Processing request for form_id: $form_id\n";
    
    $sql = "SELECT fr.*, 
                   CONCAT(a.first_name, ' ', a.last_name) AS alumni_name,
                   u.email AS alumni_email,
                   a.student_id AS alumni_student_id
            FROM form_responses fr
            LEFT JOIN alumni a ON fr.alumni_id = a.alumni_id
            LEFT JOIN users u ON a.user_id = u.user_id
            WHERE fr.form_id = :form_id
            ORDER BY fr.submitted_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':form_id' => $form_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Query executed successfully\n";
    echo "Number of rows returned: " . count($rows) . "\n";
    
    // Process the responses for better display
    foreach ($rows as &$r) {
        // Decode responses for readability
        $responses_data = json_decode($r['responses'] ?? '[]', true);
        $r['responses'] = $responses_data;
        
        // Ensure completion percentage is shown properly
        if (empty($r['completion_percentage'])) {
            $r['completion_percentage'] = 0;
        }
        
        // Format submitted date
        if ($r['submitted_at']) {
            $r['submitted_at'] = date('Y-m-d H:i:s', strtotime($r['submitted_at']));
        }
    }
    
    echo "Final result:\n";
    echo json_encode($rows, JSON_PRETTY_PRINT);
}
?>
