<?php
require_once 'config.php';

echo "Testing tracer_forms table...\n\n";

try {
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'tracer_forms'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "✗ tracer_forms table does not exist\n";
        echo "Creating tracer_forms table...\n";
        
        $createSql = "CREATE TABLE tracer_forms (
            form_id INT AUTO_INCREMENT PRIMARY KEY,
            form_title VARCHAR(100) NOT NULL,
            form_year YEAR NOT NULL,
            form_description TEXT NULL,
            form_questions JSON NULL,
            deadline_date DATE NULL,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (created_by) REFERENCES users(user_id),
            INDEX idx_form_year (form_year),
            INDEX idx_active (is_active)
        )";
        
        $pdo->exec($createSql);
        echo "✓ tracer_forms table created successfully\n";
    } else {
        echo "✓ tracer_forms table exists\n";
        
        // Check existing data
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM tracer_forms");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "Existing forms count: $count\n";
        
        if ($count > 0) {
            $stmt = $pdo->query("SELECT form_id, form_title, form_year, is_active FROM tracer_forms LIMIT 5");
            $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "Sample forms:\n";
            foreach ($forms as $form) {
                echo "  - ID: {$form['form_id']}, Title: {$form['form_title']}, Year: {$form['form_year']}, Active: " . ($form['is_active'] ? 'Yes' : 'No') . "\n";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}
