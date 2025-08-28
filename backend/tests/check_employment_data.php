<?php
require_once __DIR__ . '/../config.php';

try {
    // Check what employment records exist
    echo "Employment records count:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employment_records");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total employment records: {$count['count']}\n\n";
    
    // Check tracer forms
    echo "Active tracer forms:\n";
    $stmt = $pdo->query("SELECT form_id, form_title, is_active FROM tracer_forms ORDER BY form_id DESC LIMIT 5");
    $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($forms as $form) {
        echo "- ID: {$form['form_id']}, Title: {$form['form_title']}, Active: {$form['is_active']}\n";
    }
    
    // Check employment records by tracer_form_id
    echo "\nEmployment records by tracer form:\n";
    $stmt = $pdo->query("
        SELECT 
            er.tracer_form_id, 
            tf.form_title,
            COUNT(*) as count,
            COUNT(CASE WHEN er.employment_status = 'employed' THEN 1 END) as employed
        FROM employment_records er 
        LEFT JOIN tracer_forms tf ON er.tracer_form_id = tf.form_id
        GROUP BY er.tracer_form_id
        ORDER BY count DESC
    ");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($records as $record) {
        echo "- Form {$record['tracer_form_id']} ({$record['form_title']}): {$record['count']} total, {$record['employed']} employed\n";
    }
    
    // Check users with program_id
    echo "\nUsers by program:\n";
    $stmt = $pdo->query("
        SELECT 
            p.program_name,
            COUNT(u.user_id) as user_count
        FROM programs p
        LEFT JOIN users u ON p.program_id = u.program_id
        WHERE p.is_active = 1
        GROUP BY p.program_id, p.program_name
        ORDER BY user_count DESC
        LIMIT 10
    ");
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($programs as $program) {
        echo "- {$program['program_name']}: {$program['user_count']} users\n";
    }
    
    // Sample employment records
    echo "\nSample employment records:\n";
    $stmt = $pdo->query("
        SELECT 
            er.employment_status,
            er.tracer_form_id,
            er.alumni_id,
            u.first_name,
            u.last_name,
            p.program_name
        FROM employment_records er
        LEFT JOIN users u ON er.alumni_id = u.user_id
        LEFT JOIN programs p ON u.program_id = p.program_id
        ORDER BY er.record_id DESC
        LIMIT 5
    ");
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($samples as $sample) {
        echo "- {$sample['first_name']} {$sample['last_name']} ({$sample['program_name']}) - {$sample['employment_status']} (Form: {$sample['tracer_form_id']})\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
