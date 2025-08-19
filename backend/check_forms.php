<?php
require_once 'config.php';

echo "Checking tracer_forms table...\n\n";

try {
    // Check if tracer_forms table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'tracer_forms'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "❌ tracer_forms table does not exist\n";
        echo "Creating tracer_forms table...\n";
        
        $sql = "CREATE TABLE tracer_forms (
            form_id INT AUTO_INCREMENT PRIMARY KEY,
            form_title VARCHAR(255) NOT NULL,
            form_year INT NOT NULL,
            form_description TEXT,
            form_questions JSON,
            deadline_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT,
            is_active BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (created_by) REFERENCES users(user_id)
        )";
        
        $pdo->exec($sql);
        echo "✓ tracer_forms table created\n\n";
    } else {
        echo "✓ tracer_forms table exists\n\n";
    }
    
    // Check existing forms
    $stmt = $pdo->query("SELECT * FROM tracer_forms ORDER BY form_year DESC");
    $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Existing forms: " . count($forms) . "\n";
    
    if (count($forms) == 0) {
        echo "No forms found. Creating a sample form...\n\n";
        
        $sampleQuestions = json_encode([
            [
                'id' => 'satisfaction',
                'label' => 'How satisfied are you with your current job?',
                'type' => 'radio',
                'options' => ['Very Satisfied', 'Satisfied', 'Neutral', 'Dissatisfied', 'Very Dissatisfied']
            ],
            [
                'id' => 'skills_used',
                'label' => 'Which skills from your program do you use most in your current role?',
                'type' => 'textarea',
                'placeholder' => 'Describe the skills you use most...'
            ]
        ]);
        
        $stmt = $pdo->prepare("INSERT INTO tracer_forms (form_title, form_year, form_description, form_questions, created_by, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'Alumni Employment Tracer 2025',
            2025,
            'This form collects employment data from our alumni to track career outcomes and improve our programs.',
            $sampleQuestions,
            1, // admin user
            1  // active
        ]);
        
        echo "✓ Sample form created\n\n";
    } else {
        foreach ($forms as $form) {
            echo "- Form ID: {$form['form_id']}\n";
            echo "  Title: {$form['form_title']}\n";
            echo "  Year: {$form['form_year']}\n";
            echo "  Active: " . ($form['is_active'] ? 'Yes' : 'No') . "\n";
            echo "  Questions: " . (json_decode($form['form_questions']) ? count(json_decode($form['form_questions'], true)) : 0) . "\n\n";
        }
    }
    
    // Check employment_records table
    echo "Checking employment_records table...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'employment_records'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM employment_records");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✓ employment_records table exists with {$result['count']} records\n";
    } else {
        echo "❌ employment_records table does not exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
