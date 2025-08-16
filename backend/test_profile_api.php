<?php
session_start();
require_once 'config.php';

// Simulate logging in as an alumni user for testing
$_SESSION['user_id'] = 9; // Mary May Historillo
$_SESSION['login_time'] = time();

echo "Simulated login for user ID: 9\n";
echo "Testing profile API...\n\n";

// Now test the get_profile API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/skillslink/backend/alumni/get_profile.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
curl_setopt($ch, CURLOPT_HEADER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n";
?>
