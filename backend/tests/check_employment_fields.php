<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Employment records data analysis:\n\n";
    
    $query = "
        SELECT 
            er.employment_status,
            er.salary_range,
            er.job_relevance_to_course,
            er.months_to_find_job,
            a.first_name,
            a.last_name,
            p.program_name
        FROM employment_records er
        LEFT JOIN alumni a ON er.alumni_id = a.alumni_id
        LEFT JOIN programs p ON a.program_id = p.program_id
        WHERE er.tracer_form_id = 32
        ORDER BY p.program_name, er.employment_status
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $record) {
        echo "Program: {$record['program_name']}\n";
        echo "  Name: {$record['first_name']} {$record['last_name']}\n";
        echo "  Status: {$record['employment_status']}\n";
        echo "  Salary Range: " . ($record['salary_range'] ?: 'NULL') . "\n";
        echo "  Job Relevance: " . ($record['job_relevance_to_course'] ?: 'NULL') . "\n";
        echo "  Months to Find Job: " . ($record['months_to_find_job'] ?: 'NULL') . "\n";
        echo "  ---\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
