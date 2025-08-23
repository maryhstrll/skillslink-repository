<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../session.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Only admin users can access employment data for other alumni
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin access required.']);
    exit;
}

try {
    $alumni_id = $_GET['alumni_id'] ?? null;
    
    if (!$alumni_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Alumni ID is required']);
        exit;
    }
    
    // Get the latest employment record for this alumni
    $stmt = $pdo->prepare("
        SELECT er.*, tf.form_year 
        FROM employment_records er
        LEFT JOIN tracer_forms tf ON er.tracer_form_id = tf.form_id
        WHERE er.alumni_id = ? 
        ORDER BY er.created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$alumni_id]);
    $employment_record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($employment_record) {
        // Format the data similar to how it's returned in get_employment_tracer.php
        $employment_data = [
            'employment_status' => $employment_record['employment_status'],
            'company_name' => $employment_record['company_name'],
            'job_title' => $employment_record['occupation'], // Database column is 'occupation'
            'job_description' => $employment_record['job_description'],
            'salary_range' => $employment_record['salary_range'],
            'employment_type' => $employment_record['nature_of_employment'], // Database column is 'nature_of_employment'
            'work_classification' => $employment_record['work_classification'],
            'company_size' => $employment_record['company_size'],
            'industry' => $employment_record['industry'],
            'work_location' => $employment_record['work_location'],
            'is_local' => $employment_record['is_local'],
            'is_abroad' => $employment_record['is_abroad'],
            'date_employed' => $employment_record['date_employed'],
            'job_relevance_to_course' => $employment_record['job_relevance_to_course'],
            'skills_used' => $employment_record['skills_used'],
            'months_to_find_job' => $employment_record['months_to_find_job'],
            'job_search_method' => $employment_record['job_search_method'],
            'additional_comments' => $employment_record['additional_comments'],
            'form_year' => $employment_record['form_year'],
            'submitted_at' => $employment_record['submitted_at']
        ];
        
        echo json_encode([
            'success' => true,
            'employment' => $employment_data
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'employment' => null,
            'message' => 'No employment data found for this alumni'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error in get_alumni_employment: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
