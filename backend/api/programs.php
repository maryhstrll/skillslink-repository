<?php
// Returns list of academic programs for registration dropdown
// Expected DB table structure (adjust if different):
// programs(program_id INT PK AI, program_name VARCHAR, active TINYINT(1) default 1)

header('Content-Type: application/json');

// Allow unauthenticated access for program listing (public data)
// CORS handled elsewhere (.htaccess) and preflight may call OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(200);
	exit();
}

require_once __DIR__ . '/../config.php';

try {
	if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
		http_response_code(405);
		echo json_encode(['success' => false, 'error' => 'Method not allowed']);
		exit;
	}

	// Try common column names; adjust query if schema differs
	// Prefer active programs only if column exists
	$columns = $pdo->query("SHOW COLUMNS FROM programs")->fetchAll(PDO::FETCH_COLUMN);
	$hasActive = in_array('active', $columns, true);
	$hasProgramId = in_array('program_id', $columns, true);
	$hasName = in_array('program_name', $columns, true) || in_array('name', $columns, true);

	if (!$hasProgramId || !$hasName) {
		http_response_code(500);
		echo json_encode(['success' => false, 'error' => 'Program table missing required columns']);
		exit;
	}

	$nameCol = in_array('program_name', $columns, true) ? 'program_name' : 'name';
	$activeClause = $hasActive ? 'WHERE active = 1' : '';
	$stmt = $pdo->query("SELECT program_id AS id, $nameCol AS name FROM programs $activeClause ORDER BY name ASC");
	$programs = $stmt->fetchAll();

	echo json_encode(['success' => true, 'data' => $programs]);
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
