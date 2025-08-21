<?php
require_once __DIR__ . '/session.php';

echo "=== Session Debug ===\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'not set') . "\n";
echo "Role: " . ($_SESSION['role'] ?? 'not set') . "\n";
echo "Alumni ID: " . ($_SESSION['alumni_id'] ?? 'not set') . "\n";
echo "Username: " . ($_SESSION['username'] ?? 'not set') . "\n";

echo "\nFull session data:\n";
print_r($_SESSION);
?>
