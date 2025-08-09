<?php
require_once 'config.php';

try {
    // Insert sample programs
    $programs = [
        ['BSCS', 'Bachelor of Science in Computer Science', 'Computer Science Department', 'degree', 4.0],
        ['BSIT', 'Bachelor of Science in Information Technology', 'Information Technology Department', 'degree', 4.0],
        ['BSIS', 'Bachelor of Science in Information Systems', 'Information Technology Department', 'degree', 4.0],
        ['CERT-WEB', 'Certificate in Web Development', 'Computer Science Department', 'certificate', 1.0],
        ['CERT-NET', 'Certificate in Network Administration', 'Information Technology Department', 'certificate', 1.0]
    ];

    $programStmt = $pdo->prepare("INSERT INTO programs (program_code, program_name, department, program_type, duration_years) VALUES (?, ?, ?, ?, ?)");
    
    echo "Inserting sample programs...\n";
    foreach ($programs as $program) {
        try {
            $programStmt->execute($program);
            echo "Inserted program: {$program[1]}\n";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo "Program {$program[1]} already exists, skipping...\n";
            } else {
                throw $e;
            }
        }
    }

    // Insert sample batches
    $batches = [
        [1, 2020, 'BSCS Batch 2020'],
        [1, 2021, 'BSCS Batch 2021'],
        [1, 2022, 'BSCS Batch 2022'],
        [1, 2023, 'BSCS Batch 2023'],
        [2, 2020, 'BSIT Batch 2020'],
        [2, 2021, 'BSIT Batch 2021'],
        [2, 2022, 'BSIT Batch 2022'],
        [2, 2023, 'BSIT Batch 2023']
    ];

    $batchStmt = $pdo->prepare("INSERT INTO batches (program_id, batch_year, batch_name) VALUES (?, ?, ?)");
    
    echo "\nInserting sample batches...\n";
    foreach ($batches as $batch) {
        try {
            $batchStmt->execute($batch);
            echo "Inserted batch: {$batch[2]}\n";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo "Batch {$batch[2]} already exists, skipping...\n";
            } else {
                throw $e;
            }
        }
    }

    echo "\nSample data insertion completed successfully!\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
