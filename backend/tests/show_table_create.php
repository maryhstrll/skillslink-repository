<?php
require_once __DIR__ . '/config.php';

$stmt = $pdo->prepare('SHOW CREATE TABLE employment_records');
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo $result['Create Table'];
?>
