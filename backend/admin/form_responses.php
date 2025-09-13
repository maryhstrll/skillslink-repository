<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../session.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'alumni';

function get_json_body(){ $raw=file_get_contents('php://input'); $d=json_decode($raw,true); return is_array($d)?$d:$_POST; }

try {
    $action = $_GET['action'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Submit a response (alumni only or admin impersonation)
        $data = get_json_body();
        $form_id = (int)($data['form_id'] ?? 0);
        // use session alumni id (do NOT trust client)
        $alumni_id = $_SESSION['alumni_id'] ?? $userId; // adapt: if alumni id stored in session, otherwise user id
        if (!$form_id || !$alumni_id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Missing']); exit; }

        // validate form exists and active (optional)
        $stmt = $pdo->prepare("SELECT * FROM tracer_forms WHERE form_id = :id");
        $stmt->execute([':id'=>$form_id]);
        $form = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$form) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Form not found']); exit; }

        $responses = $data['responses'] ?? $data['responses_json'] ?? null;
        if (is_array($responses)) $responses_json = json_encode($responses, JSON_UNESCAPED_UNICODE);
        else {
            $decoded = json_decode($responses, true);
            $responses_json = json_encode(is_array($decoded) ? $decoded : [], JSON_UNESCAPED_UNICODE);
        }

        // simple completion logic
        $is_complete = !empty($data['is_complete']) ? 1 : 1;
        $completion_percentage = isset($data['completion_percentage']) ? (int)$data['completion_percentage'] : 100;

        // upsert using ON DUPLICATE KEY (unique_form_alumni(form_id, alumni_id))
        $sql = "INSERT INTO form_responses (form_id, alumni_id, responses, is_complete, completion_percentage, submitted_at)
                VALUES (:form_id, :alumni_id, :responses, :is_complete, :completion, NOW())
                ON DUPLICATE KEY UPDATE
                  responses = VALUES(responses),
                  is_complete = VALUES(is_complete),
                  completion_percentage = VALUES(completion_percentage),
                  submitted_at = VALUES(submitted_at),
                  updated_at = CURRENT_TIMESTAMP";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':form_id' => $form_id,
            ':alumni_id' => $alumni_id,
            ':responses' => $responses_json,
            ':is_complete' => $is_complete,
            ':completion' => $completion_percentage
        ]);
        echo json_encode(['success'=>true,'message'=>'Response saved']);
        exit;
    }

    // GET list of responses for a form (admin)
    if ($action === 'list' && isset($_GET['form_id'])) {
        if ($role !== 'admin') { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }
        $form_id = (int)$_GET['form_id'];
        
        // Get traditional form responses
        $sql = "SELECT fr.*, 
                       CONCAT(a.first_name, ' ', a.last_name) AS alumni_name,
                       u.email AS alumni_email,
                       a.student_id AS alumni_student_id,
                       p.program_name AS program_name,
                       'form_response' as response_type
                FROM form_responses fr
                LEFT JOIN alumni a ON fr.alumni_id = a.alumni_id
                LEFT JOIN users u ON a.user_id = u.user_id
                LEFT JOIN programs p ON a.program_id = p.program_id
                WHERE fr.form_id = :form_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':form_id' => $form_id]);
        $form_responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get employment tracer responses (from employment_records table)
        $employment_sql = "SELECT er.*, 
                                  CONCAT(a.first_name, ' ', a.last_name) AS alumni_name,
                                  u.email AS alumni_email,
                                  a.student_id AS alumni_student_id,
                                  p.program_name AS program_name,
                                  'employment_record' as response_type,
                                  er.record_id as response_id,
                                  er.created_at as submitted_at,
                                  100 as completion_percentage,
                                  1 as is_complete
                           FROM employment_records er
                           LEFT JOIN alumni a ON er.alumni_id = a.alumni_id
                           LEFT JOIN users u ON a.user_id = u.user_id
                           LEFT JOIN programs p ON a.program_id = p.program_id
                           WHERE er.tracer_form_id = :form_id";
        $stmt = $pdo->prepare($employment_sql);
        $stmt->execute([':form_id' => $form_id]);
        $employment_responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert employment records to response format
        foreach ($employment_responses as &$emp) {
            $emp['responses'] = [
                'employment_status' => $emp['employment_status'],
                'company_name' => $emp['company_name'],
                'occupation' => $emp['occupation'],
                'job_description' => $emp['job_description'],
                'salary_range' => $emp['salary_range'],
                'work_classification' => $emp['work_classification'],
                'company_size' => $emp['company_size'],
                'industry' => $emp['industry'],
                'work_location' => $emp['work_location'],
                'is_local' => ($emp['is_local'] === 'yes' || $emp['is_local'] === 1) ? 'Yes' : 'No',
                'is_abroad' => ($emp['is_abroad'] === 'yes' || $emp['is_abroad'] === 1) ? 'Yes' : 'No',
                'employment_type' => $emp['nature_of_employment'],
                'date_employed' => $emp['date_employed'],
                'job_relevance_to_course' => $emp['job_relevance_to_course'],
                'skills_used' => $emp['skills_used'],
                'months_to_find_job' => $emp['months_to_find_job'],
                'job_search_method' => $emp['job_search_method'],
                'additional_comments' => $emp['additional_comments']
            ];
            
            // Remove null and empty values
            $emp['responses'] = array_filter($emp['responses'], function($value) {
                return $value !== null && $value !== '' && $value !== '0000-00-00';
            });
        }
        
        // Merge responses from same alumni into single entries
        $merged_responses = [];
        
        // Process form responses first
        foreach ($form_responses as $form_resp) {
            $alumni_key = $form_resp['alumni_id'];
            $responses_data = json_decode($form_resp['responses'] ?? '[]', true);
            
            $merged_responses[$alumni_key] = [
                'response_id' => $form_resp['response_id'],
                'form_id' => $form_resp['form_id'],
                'alumni_id' => $form_resp['alumni_id'],
                'alumni_name' => $form_resp['alumni_name'],
                'alumni_email' => $form_resp['alumni_email'],
                'alumni_student_id' => $form_resp['alumni_student_id'],
                'program_name' => $form_resp['program_name'],
                'responses' => $responses_data ?: [],
                'is_complete' => $form_resp['is_complete'],
                'completion_percentage' => $form_resp['completion_percentage'] ?: 0,
                'submitted_at' => $form_resp['submitted_at'],
                'response_type' => 'combined_response',
                'has_form_data' => true,
                'has_employment_data' => false
            ];
        }
        
        // Merge employment responses into existing entries or create new ones
        foreach ($employment_responses as $emp_resp) {
            $alumni_key = $emp_resp['alumni_id'];
            
            if (isset($merged_responses[$alumni_key])) {
                // Merge employment data into existing form response
                $merged_responses[$alumni_key]['responses'] = array_merge(
                    $merged_responses[$alumni_key]['responses'],
                    $emp_resp['responses']
                );
                $merged_responses[$alumni_key]['has_employment_data'] = true;
                
                // Use employment program name if form response doesn't have one
                if (empty($merged_responses[$alumni_key]['program_name']) && !empty($emp_resp['program_name'])) {
                    $merged_responses[$alumni_key]['program_name'] = $emp_resp['program_name'];
                }
                
                // Update completion percentage if employment data is more recent or complete
                if ($emp_resp['completion_percentage'] > $merged_responses[$alumni_key]['completion_percentage']) {
                    $merged_responses[$alumni_key]['completion_percentage'] = $emp_resp['completion_percentage'];
                }
                
                // Use the more recent submission date
                if (strtotime($emp_resp['submitted_at']) > strtotime($merged_responses[$alumni_key]['submitted_at'])) {
                    $merged_responses[$alumni_key]['submitted_at'] = $emp_resp['submitted_at'];
                }
            } else {
                // Create new entry with only employment data
                $merged_responses[$alumni_key] = [
                    'response_id' => $emp_resp['response_id'],
                    'form_id' => $form_id,
                    'alumni_id' => $emp_resp['alumni_id'],
                    'alumni_name' => $emp_resp['alumni_name'],
                    'alumni_email' => $emp_resp['alumni_email'],
                    'alumni_student_id' => $emp_resp['alumni_student_id'],
                    'program_name' => $emp_resp['program_name'],
                    'responses' => $emp_resp['responses'],
                    'is_complete' => $emp_resp['is_complete'],
                    'completion_percentage' => $emp_resp['completion_percentage'] ?: 100,
                    'submitted_at' => $emp_resp['submitted_at'],
                    'response_type' => 'combined_response',
                    'has_form_data' => false,
                    'has_employment_data' => true
                ];
            }
        }
        
        // Convert back to indexed array
        $rows = array_values($merged_responses);
        
        // Process the merged responses for better display
        foreach ($rows as &$r) {
            // Remove null and empty values from responses
            if (is_array($r['responses'])) {
                $r['responses'] = array_filter($r['responses'], function($value) {
                    return $value !== null && $value !== '' && $value !== '0000-00-00';
                });
            }
            
            // Ensure completion percentage is shown properly
            if (empty($r['completion_percentage'])) {
                $r['completion_percentage'] = 0;
            }
            
            // Ensure program name has a fallback
            if (empty($r['program_name'])) {
                $r['program_name'] = 'Unknown Program';
            }
            
            // Format submitted date
            if ($r['submitted_at']) {
                $r['submitted_at'] = date('Y-m-d H:i:s', strtotime($r['submitted_at']));
            }
        }
        
        // Sort by submitted date (most recent first)
        usort($rows, function($a, $b) {
            return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
        });
        
        echo json_encode($rows);
        exit;
    }

    // GET count of responses for a form (admin)
    if ($action === 'count' && isset($_GET['form_id'])) {
        if ($role !== 'admin') { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }
        $form_id = (int)$_GET['form_id'];
        
        // Count unique alumni who have submitted responses (either form or employment data)
        $sql = "SELECT COUNT(DISTINCT alumni_id) as count FROM (
                    SELECT alumni_id FROM form_responses WHERE form_id = ?
                    UNION
                    SELECT alumni_id FROM employment_records WHERE tracer_form_id = ?
                ) AS combined_responses";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$form_id, $form_id]);
        $unique_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo json_encode(['count' => (int)$unique_count]);
        exit;
    }
    
    // Get counts of responses for all forms
    if ($action === 'counts') {
        if ($role !== 'admin') { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }
        
        // Get counts per form from form_responses
        $sql = "SELECT form_id, COUNT(DISTINCT alumni_id) as response_count 
                FROM form_responses 
                GROUP BY form_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $form_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Get counts per form from employment_records
        $sql = "SELECT tracer_form_id as form_id, COUNT(DISTINCT alumni_id) as response_count 
                FROM employment_records 
                GROUP BY tracer_form_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $emp_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Merge both counts
        $counts = [];
        foreach ($form_counts as $form_id => $count) {
            $counts[$form_id] = (int)$count;
        }
        
        foreach ($emp_counts as $form_id => $count) {
            if (isset($counts[$form_id])) {
                // Get the union count of unique alumni
                $sql = "SELECT COUNT(DISTINCT alumni_id) as count FROM (
                            SELECT alumni_id FROM form_responses WHERE form_id = ?
                            UNION
                            SELECT alumni_id FROM employment_records WHERE tracer_form_id = ?
                        ) AS combined_responses";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$form_id, $form_id]);
                $counts[$form_id] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
            } else {
                $counts[$form_id] = (int)$count;
            }
        }
        
        echo json_encode($counts);
        exit;
    }
    
    // Get statistics for a specific form
    if ($action === 'stats' && isset($_GET['form_id'])) {
        if ($role !== 'admin') { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }
        $form_id = (int)$_GET['form_id'];
        
        // Get total alumni count
        $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'alumni'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $alumni_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get total responses for this form (unique alumni)
        $sql = "SELECT COUNT(DISTINCT alumni_id) as count FROM (
                    SELECT alumni_id FROM form_responses WHERE form_id = ?
                    UNION
                    SELECT alumni_id FROM employment_records WHERE tracer_form_id = ?
                ) AS combined_responses";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$form_id, $form_id]);
        $total_responses = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get complete responses (both form and employment data)
        $sql = "SELECT COUNT(DISTINCT fr.alumni_id) as complete_count
                FROM form_responses fr
                JOIN employment_records er ON fr.alumni_id = er.alumni_id AND fr.form_id = er.tracer_form_id
                WHERE fr.form_id = :form_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':form_id' => $form_id]);
        $complete_responses = $stmt->fetch(PDO::FETCH_ASSOC)['complete_count'];
        
        // Calculate incomplete responses
        $incomplete_responses = $total_responses - $complete_responses;
        
        // Calculate response rate
        $response_rate = $alumni_count > 0 ? ($total_responses / $alumni_count) * 100 : 0;
        
        $stats = [
            'total_alumni' => (int) $alumni_count,
            'total_responses' => (int) $total_responses,
            'complete' => (int) $complete_responses,
            'incomplete' => (int) $incomplete_responses,
            'response_rate' => round($response_rate, 2)
        ];
        
        echo json_encode($stats);
        exit;
    }

    // Get count of responses for a form
    if ($action === 'count' && isset($_GET['form_id'])) {
        if ($role !== 'admin') { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }
        $form_id = (int)$_GET['form_id'];
        
        try {
            // Get unique alumni respondents count
            $sql = "SELECT COUNT(DISTINCT alumni_id) as count FROM (
                        SELECT alumni_id FROM form_responses WHERE form_id = ?
                        UNION
                        SELECT alumni_id FROM employment_records WHERE tracer_form_id = ?
                    ) AS combined_responses";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$form_id, $form_id]);
            $count = (int) $stmt->fetchColumn();
            
            echo json_encode(['success' => true, 'count' => $count]);
        } catch (Exception $countEx) {
            // If an error occurs, return a safe fallback value
            echo json_encode(['success' => false, 'count' => 0, 'error' => 'Count error', 'debug' => $countEx->getMessage()]);
        }
        exit;
    }
    
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
