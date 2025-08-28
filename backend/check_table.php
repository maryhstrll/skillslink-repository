<?php
require_once 'config.php';

echo "Checking users table structure:\n";
$stmt = $pdo->query('DESCRIBE users');
while($row = $stmt->fetch()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}
?>
