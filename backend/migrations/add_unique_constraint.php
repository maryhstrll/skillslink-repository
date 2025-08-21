<?php
/**
 * Simple Migration: Add unique constraint on (alumni_id, tracer_form_id)
 */

require_once __DIR__ . '/../config.php';

try {
    echo "Adding unique constraint on (alumni_id, tracer_form_id)...\n";
    
    // Handle NULL tracer_form_id values first
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM employment_records WHERE tracer_form_id IS NULL");
    $stmt->execute();
    $nullCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($nullCount > 0) {
        echo "Found $nullCount records with NULL tracer_form_id.\n";
        
        // Get the first available tracer form for each year
        $stmt = $pdo->prepare("
            SELECT er.record_id, er.form_year, 
                   (SELECT tf.form_id FROM tracer_forms tf WHERE tf.form_year = er.form_year LIMIT 1) as form_id
            FROM employment_records er 
            WHERE er.tracer_form_id IS NULL
        ");
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($records as $record) {
            if ($record['form_id']) {
                echo "Linking record {$record['record_id']} to form {$record['form_id']}\n";
                $updateStmt = $pdo->prepare("UPDATE employment_records SET tracer_form_id = ? WHERE record_id = ?");
                $updateStmt->execute([$record['form_id'], $record['record_id']]);
            } else {
                echo "No form found for year {$record['form_year']}, deleting record {$record['record_id']}\n";
                $deleteStmt = $pdo->prepare("DELETE FROM employment_records WHERE record_id = ?");
                $deleteStmt->execute([$record['record_id']]);
            }
        }
    }
    
    // Check for duplicates
    $stmt = $pdo->prepare("
        SELECT alumni_id, tracer_form_id, COUNT(*) as count 
        FROM employment_records 
        WHERE tracer_form_id IS NOT NULL
        GROUP BY alumni_id, tracer_form_id 
        HAVING count > 1
    ");
    $stmt->execute();
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) > 0) {
        echo "Found duplicates, cleaning up...\n";
        foreach ($duplicates as $dup) {
            echo "Removing duplicates for alumni {$dup['alumni_id']}, form {$dup['tracer_form_id']}\n";
            $pdo->prepare("
                DELETE FROM employment_records 
                WHERE alumni_id = ? AND tracer_form_id = ? 
                AND record_id NOT IN (
                    SELECT record_id FROM (
                        SELECT record_id FROM employment_records 
                        WHERE alumni_id = ? AND tracer_form_id = ? 
                        ORDER BY submitted_at DESC, record_id DESC 
                        LIMIT 1
                    ) as temp
                )
            ")->execute([$dup['alumni_id'], $dup['tracer_form_id'], $dup['alumni_id'], $dup['tracer_form_id']]);
        }
    }
    
    // Add the unique constraint
    try {
        $pdo->exec("ALTER TABLE employment_records ADD CONSTRAINT unique_alumni_tracer_form UNIQUE (alumni_id, tracer_form_id)");
        echo "✓ Unique constraint added successfully!\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), "Duplicate key name") !== false) {
            echo "✓ Unique constraint already exists.\n";
        } else {
            throw $e;
        }
    }
    
    echo "\n✅ Migration completed successfully!\n";
    echo "Now alumni can respond to multiple tracer forms in the same year.\n";
    
} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
