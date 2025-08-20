<?php
require 'config.php'; 

echo "Employment records table structure:\n";
$stmt = $pdo->query('DESCRIBE employment_records');
while($row = $stmt->fetch()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}
?>
