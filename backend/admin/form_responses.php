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
        
        echo json_encode($rows);
        exit;
    }

    // GET count of responses for a form (admin)
    if ($action === 'count' && isset($_GET['form_id'])) {
        if ($role !== 'admin') { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }
        $form_id = (int)$_GET['form_id'];
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM form_responses WHERE form_id = :form_id");
        $stmt->execute([':form_id' => $form_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['count' => (int)$result['count']]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
