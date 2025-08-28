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
    error_log("Salary range specifically: " . json_encode($employment_data['salary_range'] ?? 'NOT_SET'));
    
    // Log all individual employment data fields
    error_log("Employment status: " . json_encode($employment_data['employment_status'] ?? 'NOT_SET'));
    error_log("Job title: " . json_encode($employment_data['job_title'] ?? 'NOT_SET'));
    error_log("Company name: " . json_encode($employment_data['company_name'] ?? 'NOT_SET'));
    
    $stmt = $pdo->prepare("
        INSERT INTO employment_records (
            alumni_id, form_year, tracer_form_id, employment_status, 
            company_name, occupation, job_description, salary_range, work_classification, 
            company_size, industry, work_location, is_local, is_abroad, nature_of_employment, date_employed,
            job_relevance_to_course, skills_used, months_to_find_job, 
            job_search_method, additional_comments, submitted_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            employment_status = VALUES(employment_status),
            company_name = VALUES(company_name),
            occupation = VALUES(occupation),
            job_description = VALUES(job_description),
            salary_range = VALUES(salary_range),
            work_classification = VALUES(work_classification),
            company_size = VALUES(company_size),
            industry = VALUES(industry),
            work_location = VALUES(work_location),
            is_local = VALUES(is_local),
            is_abroad = VALUES(is_abroad),
            nature_of_employment = VALUES(nature_of_employment),
            date_employed = VALUES(date_employed),
            job_relevance_to_course = VALUES(job_relevance_to_course),
            skills_used = VALUES(skills_used),
            months_to_find_job = VALUES(months_to_find_job),
            job_search_method = VALUES(job_search_method),
            additional_comments = VALUES(additional_comments),
            submitted_at = VALUES(submitted_at),
            updated_at = CURRENT_TIMESTAMP
    ");
    
    // Transform job relevance values to match database enum
    $job_relevance = null;
    if (isset($employment_data['job_relevance_to_course'])) {
        switch ($employment_data['job_relevance_to_course']) {
            case 'Highly Relevant':
                $job_relevance = 'highly_relevant';
                break;
            case 'Somewhat Relevant':
                $job_relevance = 'somewhat_relevant';
                break;
            case 'Not Relevant':
                $job_relevance = 'not_relevant';
                break;
            default:
                // If it's already in the correct format, keep it
                if (in_array($employment_data['job_relevance_to_course'], ['highly_relevant', 'somewhat_relevant', 'not_relevant'])) {
                    $job_relevance = $employment_data['job_relevance_to_course'];
                }
                break;
        }
    }
    
    $result = $stmt->execute([
        $alumni_id,
        $form_year,
        $form_id,
        $employment_data['employment_status'] ?? null,
        $employment_data['company_name'] ?? null,
        $employment_data['job_title'] ?? null, // Maps to occupation column
        $employment_data['job_description'] ?? null,
        $employment_data['salary_range'] ?? null,
        $employment_data['work_classification'] ?? null,
        $employment_data['company_size'] ?? null,
        $employment_data['industry'] ?? null,
        $employment_data['work_location'] ?? null,
        $employment_data['is_local'] ?? null,
        $employment_data['is_abroad'] ?? null,
        $employment_data['employment_type'] ?? null, // Maps to nature_of_employment
        $employment_data['date_employed'] ?? null,
        $job_relevance, // Use transformed value
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
