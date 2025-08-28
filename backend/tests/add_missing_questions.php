<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Adding missing questions to the active tracer form...\n";
    
    // Get current form data
    $stmt = $pdo->prepare('SELECT * FROM tracer_forms WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1');
    $stmt->execute();
    $form = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$form) {
        echo "No active form found!\n";
        exit;
    }
    
    echo "Found active form: ID {$form['form_id']}, Year {$form['form_year']}\n";
    
    $questions = json_decode($form['form_questions'], true);
    $current_selected = $questions['selected_employment_questions'] ?? [];
    
    echo "Current selected questions: " . implode(', ', $current_selected) . "\n";
    
    // Add the missing questions
    $missing_questions = ['job_relevance_to_course', 'months_to_find_job'];
    $updated_selected = array_merge($current_selected, $missing_questions);
    
    // Remove duplicates
    $updated_selected = array_unique($updated_selected);
    
    echo "Updated selected questions: " . implode(', ', $updated_selected) . "\n";
    
    // Update the form questions
    $questions['selected_employment_questions'] = $updated_selected;
    $updated_form_questions = json_encode($questions);
    
    // Update the database
    $stmt = $pdo->prepare('UPDATE tracer_forms SET form_questions = ? WHERE form_id = ?');
    $result = $stmt->execute([$updated_form_questions, $form['form_id']]);
    
    if ($result) {
        echo "Successfully updated the active tracer form!\n";
        echo "The following questions are now included:\n";
        echo "- job_relevance_to_course (for Job-Course Alignment Rate chart)\n";
        echo "- months_to_find_job (for Time to Employment chart)\n";
        echo "\nAlumni can now fill out these fields in the tracer form.\n";
    } else {
        echo "Failed to update the form.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
