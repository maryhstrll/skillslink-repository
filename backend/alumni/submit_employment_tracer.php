<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../session.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'alumni';

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

try {
    error_log("=== EMPLOYMENT TRACER SUBMISSION START ===");
    error_log("User ID: $userId, Role: $role");
    error_log("Received data: " . json_encode($data));
    
    // Get alumni_id from user_id
    $stmt = $pdo->prepare("SELECT alumni_id FROM alumni WHERE user_id = ?");
    $stmt->execute([$userId]);
    $alumni = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$alumni) {
        error_log("Alumni record not found for user_id: $userId");
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Alumni record not found']);
        exit;
    }
    
    $alumni_id = $alumni['alumni_id'];
    error_log("Alumni ID found: $alumni_id");
    
    // Get active form
    $stmt = $pdo->prepare("SELECT form_id, form_year FROM tracer_forms WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
    $stmt->execute();
    $form = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$form) {
        error_log("No active form found");
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No active form found']);
        exit;
    }

    $form_id = $form['form_id'];
    $form_year = $form['form_year'];
    
    error_log("Active form found: form_id=$form_id, form_year=$form_year");
    
    $pdo->beginTransaction();
    
    // 1. Save CORE employment data to employment_records
    $employment_data = $data['employment_data'] ?? [];
    error_log("Employment data: " . json_encode($employment_data));
    
    $stmt = $pdo->prepare("
        INSERT INTO employment_records (
            alumni_id, form_year, tracer_form_id, employment_status, 
            company_name, job_title, job_description, salary_range, employment_type, 
            company_size, industry, work_location, work_setup, start_date,
            job_relevance_to_course, skills_used, months_to_find_job, 
            job_search_method, additional_comments, submitted_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            employment_status = VALUES(employment_status),
            company_name = VALUES(company_name),
            job_title = VALUES(job_title),
            job_description = VALUES(job_description),
            salary_range = VALUES(salary_range),
            employment_type = VALUES(employment_type),
            company_size = VALUES(company_size),
            industry = VALUES(industry),
            work_location = VALUES(work_location),
            work_setup = VALUES(work_setup),
            start_date = VALUES(start_date),
            job_relevance_to_course = VALUES(job_relevance_to_course),
            skills_used = VALUES(skills_used),
            months_to_find_job = VALUES(months_to_find_job),
            job_search_method = VALUES(job_search_method),
            additional_comments = VALUES(additional_comments),
            submitted_at = VALUES(submitted_at),
            updated_at = CURRENT_TIMESTAMP
    ");
    
    $result = $stmt->execute([
        $alumni_id,
        $form_year,
        $form_id,
        $employment_data['employment_status'] ?? null,
        $employment_data['company_name'] ?? null,
        $employment_data['job_title'] ?? null,
        $employment_data['job_description'] ?? null,
        $employment_data['salary_range'] ?? null,
        $employment_data['employment_type'] ?? null,
        $employment_data['company_size'] ?? null,
        $employment_data['industry'] ?? null,
        $employment_data['work_location'] ?? null,
        $employment_data['work_setup'] ?? null,
        $employment_data['start_date'] ?? null,
        $employment_data['job_relevance_to_course'] ?? null,
        $employment_data['skills_used'] ?? null,
        $employment_data['months_to_find_job'] ?? null,
        $employment_data['job_search_method'] ?? null,
        $employment_data['additional_comments'] ?? null
    ]);
    
    if (!$result) {
        error_log("Failed to insert/update employment record");
        throw new Exception("Failed to save employment data");
    }
    
    error_log("Employment data saved successfully");
    
    // 2. Save ADDITIONAL responses to form_responses (if any)
    $additional_responses = $data['additional_responses'] ?? [];
    
    if (!empty($additional_responses)) {
        error_log("Saving additional responses: " . json_encode($additional_responses));
        
        $stmt = $pdo->prepare("
            INSERT INTO form_responses (form_id, alumni_id, responses, submitted_at, is_complete, completion_percentage)
            VALUES (?, ?, ?, NOW(), 1, 100)
            ON DUPLICATE KEY UPDATE
                responses = VALUES(responses),
                submitted_at = VALUES(submitted_at),
                updated_at = CURRENT_TIMESTAMP
        ");
        
        $result = $stmt->execute([
            $form_id,
            $alumni_id,
            json_encode($additional_responses, JSON_UNESCAPED_UNICODE)
        ]);
        
        if (!$result) {
            error_log("Failed to save additional responses");
            throw new Exception("Failed to save additional responses");
        }
        
        error_log("Additional responses saved successfully");
    }
    
    $pdo->commit();
    
    error_log("=== EMPLOYMENT TRACER SUBMISSION SUCCESS ===");
    echo json_encode([
        'success' => true, 
        'message' => 'Employment tracer submitted successfully',
        'data' => [
            'form_year' => $form_year,
            'has_additional_responses' => !empty($additional_responses)
        ]
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("=== EMPLOYMENT TRACER SUBMISSION ERROR ===");
    error_log("Error: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
}
?>
