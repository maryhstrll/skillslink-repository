<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Updating employment records to use correct user IDs...\n";
    
    // Update employment records to use existing user IDs that have programs
    $updates = [
        // Update alumni_id to match existing users with programs
        ['old_id' => 2, 'new_id' => 22], // Associate in Computer Technology
        ['old_id' => 6, 'new_id' => 24], // Software Engineering  
        ['old_id' => 7, 'new_id' => 26], // Diploma in Information Technology
        ['old_id' => 8, 'new_id' => 27], // Bachelor of Science in Computer Science
        ['old_id' => 12, 'new_id' => 28] // Computer Science
    ];
    
    foreach ($updates as $update) {
        $query = "UPDATE employment_records SET alumni_id = ? WHERE alumni_id = ? AND tracer_form_id = 32";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([$update['new_id'], $update['old_id']]);
        echo "Updated alumni_id {$update['old_id']} to {$update['new_id']}: " . ($result ? "Success" : "Failed") . "\n";
    }
    
    echo "\nVerifying updates:\n";
    $query = "
        SELECT 
            er.alumni_id,
            u.first_name,
            u.last_name,
            p.program_name,
            er.employment_status
        FROM employment_records er
        LEFT JOIN users u ON er.alumni_id = u.user_id
        LEFT JOIN programs p ON u.program_id = p.program_id
        WHERE er.tracer_form_id = 32
        ORDER BY er.alumni_id
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        echo "- {$row['first_name']} {$row['last_name']} ({$row['program_name']}) - {$row['employment_status']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
