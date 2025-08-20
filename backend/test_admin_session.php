<?php
require_once 'config.php';
require_once 'session.php';

// Test admin session
// Simulate logging in as admin
$_SESSION['user_id'] = 1;
$_SESSION['email'] = 'admin@skillslink.com';
$_SESSION['role'] = 'admin';

echo "Testing admin session:\n";
echo "Session data:\n";
print_r($_SESSION);

echo "\nTesting validateSession() function:\n";
$result = validateSession();
print_r($result);
?>
