<?php
require_once __DIR__ . '/config.php';

echo "Testing database connection and structure...\n\n";

try {
    // Test database connection
    echo "1. Testing database connection...\n";
    $stmt = $pdo->query("SELECT 1");
    echo "✓ Database connection successful\n\n";
    
    // Check if users table exists and has data
    echo "2. Checking users table...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Users table exists\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch()['count'];
        echo "   Users count: $count\n";
        
        if ($count > 0) {
            $stmt = $pdo->query("SELECT user_id, email, role, first_name, last_name, student_id, approval_status FROM users LIMIT 5");
            echo "   Sample users:\n";
            while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "   - ID: {$user['user_id']}, Email: {$user['email']}, Role: {$user['role']}, Status: {$user['approval_status']}\n";
            }
        }
    } else {
        echo "✗ Users table not found\n";
    }
    
    echo "\n3. Checking alumni table...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'alumni'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Alumni table exists\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM alumni");
        $count = $stmt->fetch()['count'];
        echo "   Alumni count: $count\n";
    } else {
        echo "✗ Alumni table not found\n";
    }
    
    echo "\n4. Checking programs table...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'programs'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Programs table exists\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM programs");
        $count = $stmt->fetch()['count'];
        echo "   Programs count: $count\n";
    } else {
        echo "✗ Programs table not found\n";
    }
    
    echo "\n5. Checking batches table...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'batches'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Batches table exists\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM batches");
        $count = $stmt->fetch()['count'];
        echo "   Batches count: $count\n";
    } else {
        echo "✗ Batches table not found\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}
?>
