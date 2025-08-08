<?php
/**
 * Database Migration Script
 * Adds user approval system to existing database
 */

require_once __DIR__ . '/config.php';

echo "Starting database migration for user approval system...\n";

try {
    // Check if approval_status column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'approval_status'");
    if ($stmt->rowCount() > 0) {
        echo "User approval system already exists. Checking for additional columns...\n";
        
        // Check if new columns exist
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'first_name'");
        if ($stmt->rowCount() === 0) {
            echo "Adding alumni information columns...\n";
            $pdo->exec("ALTER TABLE users ADD COLUMN first_name VARCHAR(50) NULL AFTER approved_at");
            echo "✓ Added first_name column\n";
            
            $pdo->exec("ALTER TABLE users ADD COLUMN last_name VARCHAR(50) NULL AFTER first_name");
            echo "✓ Added last_name column\n";
            
            $pdo->exec("ALTER TABLE users ADD COLUMN student_id VARCHAR(50) NULL AFTER last_name");
            echo "✓ Added student_id column\n";
            
            echo "Alumni information columns added successfully!\n";
        } else {
            echo "Alumni information columns already exist.\n";
        }
        exit(0);
    }

    echo "Adding approval columns to users table...\n";
    
    // Add approval_status column
    $pdo->exec("ALTER TABLE users 
                ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending' AFTER role");
    echo "✓ Added approval_status column\n";
    
    // Add approved_by column
    $pdo->exec("ALTER TABLE users 
                ADD COLUMN approved_by INT NULL AFTER approval_status");
    echo "✓ Added approved_by column\n";
    
    // Add approved_at column
    $pdo->exec("ALTER TABLE users 
                ADD COLUMN approved_at TIMESTAMP NULL AFTER approved_by");
    echo "✓ Added approved_at column\n";
    
    // Add foreign key constraint
    $pdo->exec("ALTER TABLE users 
                ADD CONSTRAINT fk_users_approved_by 
                FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL");
    echo "✓ Added foreign key constraint\n";
    
    // Add index for approval_status
    $pdo->exec("ALTER TABLE users ADD INDEX idx_approval_status (approval_status)");
    echo "✓ Added approval_status index\n";
    
    // Set existing users as approved (except those created very recently)
    $stmt = $pdo->prepare("UPDATE users SET approval_status = 'approved', approved_at = NOW() 
                          WHERE role IN ('admin', 'staff') OR created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute();
    $affected = $stmt->rowCount();
    echo "✓ Set {$affected} existing users as approved\n";
    
    echo "\nMigration completed successfully!\n";
    echo "New user registrations will now require admin approval.\n";
    
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
