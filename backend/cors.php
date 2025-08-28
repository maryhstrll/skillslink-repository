<?php
// CORS Configuration for SkillsLink Backend
// Note: CORS headers are now handled by .htaccess
function setCORSHeaders() {
    // CORS headers are handled by .htaccess, so we don't need to set them here
    // This function is kept for backward compatibility but does nothing
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}
?>
