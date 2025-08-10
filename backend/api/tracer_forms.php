<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '../config.php';        // your DB connection (should provide $pdo or DB constants)
require_once __DIR__ . '../session.php';    // make sure this sets $_SESSION['user_id'] and maybe role

$stmt = $pdo->query("SELECT * FROM tracer_forms");

// helper: get JSON body if sent
function get_json_body() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $_POST;
}

$action = $_GET['action'] ?? null;
$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'alumni';

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

    // POST actions below require admin
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = get_json_body();

        // create or update form
        if (!isset($data['form_title']) || !isset($data['form_year'])) {
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'Missing required fields']);
            exit;
        }
        if ($role !== 'admin') {
            http_response_code(403);
            echo json_encode(['success'=>false,'message'=>'Forbidden']);
            exit;
        }

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
                $sql = "INSERT INTO tracer_forms
                        (form_title, form_year, form_description, form_questions, deadline_date, created_by, is_active)
                        VALUES (:title, :year, :desc, :questions, :deadline, :created_by, :active)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':title' => $form_title,
                    ':year' => $form_year,
                    ':desc' => $form_desc,
                    ':questions' => $questions_json,
                    ':deadline' => $deadline,
                    ':created_by' => $userId,
                    ':active' => $is_active
                ]);
                $pdo->commit();
                echo json_encode(['success'=>true,'message'=>'Form created']);
                exit;
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
            exit;
        }
    }

    // Additional POST actions that require admin: duplicate, delete, activate
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
        $act = $_GET['action'];
        if ($act === 'duplicate' && !empty($_POST['form_id'] ?: null)) {
            if ($role !== 'admin') { http_response_code(403); exit; }
            $id = (int)($_POST['form_id'] ?? null);
            $stmt = $pdo->prepare("SELECT * FROM tracer_forms WHERE form_id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) { echo json_encode(['success'=>false,'message'=>'Form not found']); exit; }
            $newTitle = $row['form_title'] . ' (Copy)';
            $stmt = $pdo->prepare("INSERT INTO tracer_forms (form_title, form_year, form_description, form_questions, deadline_date, created_by, is_active) VALUES (:title,:year,:desc,:questions,:deadline,:created_by,0)");
            $stmt->execute([
                ':title'=>$newTitle, ':year'=>$row['form_year'], ':desc'=>$row['form_description'],
                ':questions'=>$row['form_questions'], ':deadline'=>$row['deadline_date'], ':created_by'=>$userId
            ]);
            echo json_encode(['success'=>true]);
            exit;
        }

        if ($act === 'delete' && !empty($_POST['form_id'])) {
            if ($role !== 'admin') { http_response_code(403); exit; }
            $id = (int)($_POST['form_id']);
            $stmt = $pdo->prepare("DELETE FROM tracer_forms WHERE form_id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['success'=>true]);
            exit;
        }

        if ($act === 'activate' && !empty($_POST['form_id'])) {
            if ($role !== 'admin') { http_response_code(403); exit; }
            $id = (int)($_POST['form_id']);
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
    }

    // default: invalid request
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$ex->getMessage()]);
}
