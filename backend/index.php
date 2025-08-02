<?php
header('Content-Type: application/json');
require_once 'config.php';

$request_method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// Remove empty elements and reindex array
$uri = array_values(array_filter($uri));

// Find the 'api' segment in the URI
$api_index = array_search('api', $uri);

if ($api_index !== false && isset($uri[$api_index + 1]) && $uri[$api_index + 1] === 'test') {
    if ($request_method === 'GET') {
        echo json_encode(['message' => 'SkillsLink API is working!']);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
}
?>