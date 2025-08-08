<?php
// CORS is handled by .htaccess
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

$request_method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// Remove empty elements and reindex array
$uri = array_values(array_filter($uri));

// Find the 'api' segment in the URI
$api_index = array_search('api', $uri);

// Check if we have a valid API request
if ($api_index !== false && isset($uri[$api_index + 1])) {
    $endpoint = $uri[$api_index + 1];
    
    switch ($endpoint) {
        case 'test':
            if ($request_method === 'GET') {
                echo json_encode([
                    'message' => 'SkillsLink API is working!',
                    'version' => '1.0.0',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'endpoints' => [
                        'test' => 'GET /api/test',
                        'login' => 'POST /api/login',
                        'logout' => 'POST /api/logout',
                        'session' => 'GET /api/session'
                    ]
                ]);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'login':
            // Check if login.php exists before including it
            if (file_exists('auth/login.php')) {
                require_once 'auth/login.php';
            } else {
                http_response_code(501);
                echo json_encode(['error' => 'Login endpoint not implemented yet']);
            }
            break;
            
        case 'logout':
            // Check if logout.php exists before including it
            if (file_exists('auth/logout.php')) {
                require_once 'auth/logout.php';
            } else {
                http_response_code(501);
                echo json_encode(['error' => 'Logout endpoint not implemented yet']);
            }
            break;
            
        case 'session':
            // Check session status
            if (file_exists('session.php')) {
                require_once 'session.php';
            } else {
                http_response_code(501);
                echo json_encode(['error' => 'Session endpoint not implemented yet']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'API endpoint not specified']);
}

?>