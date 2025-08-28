<?php
require_once __DIR__ . '/../config.php';

try {
    $formId = 32; // Active form ID
    
    echo "Testing employment by program query:\n\n";
    
    // Test the exact query from the function
    $query = "
        SELECT 
            p.program_name,
            p.program_code,
            COUNT(CASE WHEN er.employment_status = 'employed' THEN 1 END) as employed_count,
            COUNT(er.alumni_id) as total_responses,
            CASE 
                WHEN COUNT(er.alumni_id) > 0 
                THEN ROUND((COUNT(CASE WHEN er.employment_status = 'employed' THEN 1 END) * 100.0 / COUNT(er.alumni_id)), 1)
                ELSE 0 
            END as employment_rate
        FROM programs p
        LEFT JOIN users u ON p.program_id = u.program_id
        LEFT JOIN employment_records er ON u.user_id = er.alumni_id AND er.tracer_form_id = ?
        WHERE p.is_active = 1
        GROUP BY p.program_id, p.program_name, p.program_code
        HAVING total_responses > 0
        ORDER BY employment_rate DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$formId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Query results:\n";
    if (empty($results)) {
        echo "No results found!\n\n";
        
        // Debug: Check the join conditions
        echo "Debugging the joins:\n";
        
        // Check programs and users join
        $debugQuery1 = "
            SELECT 
                p.program_name,
                COUNT(u.user_id) as user_count
            FROM programs p
            LEFT JOIN users u ON p.program_id = u.program_id
            WHERE p.is_active = 1
            GROUP BY p.program_id, p.program_name
            ORDER BY user_count DESC
        ";
        
        $stmt = $pdo->prepare($debugQuery1);
        $stmt->execute();
        $debug1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Programs with users:\n";
        foreach ($debug1 as $row) {
            echo "- {$row['program_name']}: {$row['user_count']} users\n";
        }
        
        // Check employment records with users
        echo "\nEmployment records with alumni info:\n";
        $debugQuery2 = "
            SELECT 
                er.alumni_id,
                er.employment_status,
                er.tracer_form_id,
                u.user_id,
                u.program_id,
                p.program_name
            FROM employment_records er
            LEFT JOIN users u ON er.alumni_id = u.user_id
            LEFT JOIN programs p ON u.program_id = p.program_id
            WHERE er.tracer_form_id = ?
            LIMIT 10
        ";
        
        $stmt = $pdo->prepare($debugQuery2);
        $stmt->execute([$formId]);
        $debug2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($debug2 as $row) {
            echo "- Alumni ID: {$row['alumni_id']}, User ID: {$row['user_id']}, Program: {$row['program_name']}, Status: {$row['employment_status']}\n";
        }
        
    } else {
        foreach ($results as $result) {
            echo "- {$result['program_name']}: {$result['employed_count']}/{$result['total_responses']} employed ({$result['employment_rate']}%)\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "SQL Error: " . $pdo->errorInfo()[2] . "\n";
}
?>
