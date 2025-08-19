<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';        // your DB connection (should provide $pdo or DB constants)
require_once __DIR__ . '/../session.php';    // make sure this sets $_SESSION['user_id'] and maybe role

// helper: get JSON body if sent
function get_json_body() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $_POST;
}

$action = $_GET['action'] ?? null;
$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'alumni';

// Add some basic debugging (remove in production)
error_log("tracer_forms.php: action=$action, userId=$userId, role=$role");

// Check if userId is valid
if (!$userId) {
    error_log("Error: No user_id in session");
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'User not authenticated']);
    exit;
}

try {
    if ($action === 'list') {
        // Admin: return all forms; Alumni: return only active latest
        if ($role === 'admin') {
            $stmt = $pdo->prepare("SELECT * FROM tracer_forms ORDER BY form_year DESC, created_at DESC");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM tracer_forms WHERE is_active = 1 ORDER BY form_year DESC LIMIT 1");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        // ensure questions are JSON-decoded for frontend convenience
        foreach ($rows as &$r) {
            $r['form_questions'] = json_decode($r['form_questions'] ?? '[]', true);
        }
        echo json_encode($rows);
        exit;
    }

    if ($action === 'get' && isset($_GET['form_id'])) {
        $form_id = (int)$_GET['form_id'];
        $stmt = $pdo->prepare("SELECT * FROM tracer_forms WHERE form_id = :id");
        $stmt->execute([':id' => $form_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['form_questions'] = json_decode($row['form_questions'] ?? '[]', true);
            echo json_encode($row);
        } else {
            echo json_encode(['success'=>false,'message'=>'Form not found']);
        }
        exit;
    }

    if ($action === 'count' && isset($_GET['form_id'])) {
        $form_id = (int)$_GET['form_id'];
        
        // Count responses from employment_records table (our hybrid system)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM employment_records WHERE tracer_form_id = :form_id");
        $stmt->execute([':form_id' => $form_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['count' => $result['count'] ?? 0]);
        exit;
    }

    // POST actions below require admin
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log("Processing POST request");
        $data = get_json_body();
        
        // Add debugging info
        error_log("POST data received: " . json_encode($data));
        
        // Check if this is a special action (duplicate, delete, activate)
        $action = $_GET['action'] ?? null;
        
        if ($action === 'duplicate' && !empty($data['form_id'])) {
            if ($role !== 'admin') { 
                error_log("Duplicate access denied: role is $role, need admin");
                http_response_code(403); 
                echo json_encode(['success'=>false,'message'=>'Access denied: admin role required']);
                exit; 
            }
            
            $id = (int)($data['form_id'] ?? null);
            error_log("Attempting to duplicate form ID: $id");
            
            $stmt = $pdo->prepare("SELECT * FROM tracer_forms WHERE form_id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) { 
                error_log("Form not found for ID: $id");
                echo json_encode(['success'=>false,'message'=>'Form not found']); 
                exit; 
            }
            
            try {
                $newTitle = $row['form_title'] . ' (Copy)';
                $stmt = $pdo->prepare("INSERT INTO tracer_forms (form_title, form_year, form_description, form_questions, deadline_date, created_by, is_active) VALUES (:title,:year,:desc,:questions,:deadline,:created_by,0)");
                $result = $stmt->execute([
                    ':title'=>$newTitle, 
                    ':year'=>$row['form_year'], 
                    ':desc'=>$row['form_description'],
                    ':questions'=>$row['form_questions'], 
                    ':deadline'=>$row['deadline_date'], 
                    ':created_by'=>$userId
                ]);
                
                if ($result) {
                    error_log("Form duplicated successfully");
                    echo json_encode(['success'=>true,'message'=>'Form duplicated successfully']);
                } else {
                    error_log("Failed to insert duplicated form");
                    echo json_encode(['success'=>false,'message'=>'Failed to create duplicate form']);
                }
            } catch (Exception $e) {
                error_log("Error duplicating form: " . $e->getMessage());
                echo json_encode(['success'=>false,'message'=>'Database error: ' . $e->getMessage()]);
            }
            exit;
        }

        if ($action === 'delete' && !empty($data['form_id'])) {
            if ($role !== 'admin') { 
                http_response_code(403); 
                echo json_encode(['success'=>false,'message'=>'Access denied: admin role required']);
                exit; 
            }
            $id = (int)($data['form_id']);
            $stmt = $pdo->prepare("DELETE FROM tracer_forms WHERE form_id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['success'=>true]);
            exit;
        }

        if ($action === 'activate' && !empty($data['form_id'])) {
            if ($role !== 'admin') { 
                http_response_code(403); 
                echo json_encode(['success'=>false,'message'=>'Access denied: admin role required']);
                exit; 
            }
            $id = (int)($data['form_id']);
            $pdo->beginTransaction();
            try {
                $pdo->prepare("UPDATE tracer_forms SET is_active = 0")->execute();
                $pdo->prepare("UPDATE tracer_forms SET is_active = 1 WHERE form_id = :id")->execute([':id' => $id]);
                $pdo->commit();
                echo json_encode(['success'=>true]);
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
                exit;
            }
        }

        // For regular form creation/update, check required fields
        if (!isset($data['form_title']) || !isset($data['form_year'])) {
            error_log("Missing required fields: form_title or form_year");
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'Missing required fields: form_title and form_year are required']);
            exit;
        }
        if ($role !== 'admin') {
            error_log("Access denied: role is $role, need admin");
            http_response_code(403);
            echo json_encode(['success'=>false,'message'=>'Access denied: admin role required']);
            exit;
        }

        error_log("Passed validation, proceeding with form save");

        // sanitize and prepare
        $form_title = trim($data['form_title']);
        $form_year  = (int)$data['form_year'];
        $form_desc  = $data['form_description'] ?? null;
        $deadline   = $data['deadline_date'] ?? null;
        $is_active  = !empty($data['is_active']) ? 1 : 0;
        // ensure questions are valid JSON string
        $questions = $data['form_questions'] ?? $data['form_questions_json'] ?? null;
        if (is_array($questions)) {
            $questions_json = json_encode($questions, JSON_UNESCAPED_UNICODE);
        } else {
            // if passed string, try decode then re-encode to sanitize
            $decoded = json_decode($questions, true);
            $questions_json = json_encode(is_array($decoded) ? $decoded : [], JSON_UNESCAPED_UNICODE);
        }

        $pdo->beginTransaction();
        try {
            // If making active, first deactivate others
            if ($is_active) {
                $pdo->prepare("UPDATE tracer_forms SET is_active = 0")->execute();
            }

            if (!empty($data['form_id'])) {
                // update
                $sql = "UPDATE tracer_forms
                        SET form_title=:title,
                            form_year=:year,
                            form_description=:desc,
                            form_questions=:questions,
                            deadline_date=:deadline,
                            is_active=:active
                        WHERE form_id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':title' => $form_title,
                    ':year' => $form_year,
                    ':desc' => $form_desc,
                    ':questions' => $questions_json,
                    ':deadline' => $deadline,
                    ':active' => $is_active,
                    ':id' => (int)$data['form_id']
                ]);
                $pdo->commit();
                echo json_encode(['success'=>true,'message'=>'Form updated']);
                exit;
            } else {
                // insert
                error_log("About to insert new form: title=$form_title, year=$form_year, created_by=$userId");
                $sql = "INSERT INTO tracer_forms
                        (form_title, form_year, form_description, form_questions, deadline_date, created_by, is_active)
                        VALUES (:title, :year, :desc, :questions, :deadline, :created_by, :active)";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([
                    ':title' => $form_title,
                    ':year' => $form_year,
                    ':desc' => $form_desc,
                    ':questions' => $questions_json,
                    ':deadline' => $deadline,
                    ':created_by' => $userId,
                    ':active' => $is_active
                ]);
                error_log("Insert result: " . ($result ? 'success' : 'failed'));
                $pdo->commit();
                echo json_encode(['success'=>true,'message'=>'Form created']);
                exit;
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Database error in tracer_forms: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success'=>false,'message'=>'Database error: ' . $e->getMessage()]);
            exit;
        }
    }

    // default: invalid request
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$ex->getMessage()]);
}
