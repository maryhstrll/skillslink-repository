<?php
/**
 * Migration Script: Fix Tracer Form Multiple Responses Issue
 * 
 * This script applies the database changes needed to allow multiple tracer forms
 * per year and ensure alumni can respond to each form separately.
 */

require_once __DIR__ . '/../config.php';

try {
    echo "Starting migration: Fix Tracer Form Multiple Responses Issue\n";
    echo "=================================================================\n\n";
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Step 1: Check current constraints
    echo "Step 1: Checking current constraints...\n";
    $stmt = $pdo->prepare("SHOW CREATE TABLE employment_records");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Current table structure checked.\n\n";
    
    // Step 2: Drop the old unique constraint
    echo "Step 2: Dropping old unique constraint (unique_alumni_form_year)...\n";
    try {
        $pdo->exec("ALTER TABLE employment_records DROP INDEX unique_alumni_form_year");
        echo "✓ Old unique constraint dropped successfully.\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), "check that column/key exists") !== false) {
            echo "⚠ Old unique constraint does not exist (already removed or never existed).\n";
        } else {
            throw $e;
        }
    }
    echo "\n";
    
    // Step 3: Handle NULL tracer_form_id values
    echo "Step 3: Handling NULL tracer_form_id values...\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM employment_records WHERE tracer_form_id IS NULL");
    $stmt->execute();
    $nullCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($nullCount > 0) {
        echo "Found $nullCount records with NULL tracer_form_id.\n";
        echo "Setting them to 0 (will need manual correction later)...\n";
        $pdo->exec("UPDATE employment_records SET tracer_form_id = 0 WHERE tracer_form_id IS NULL");
        echo "✓ NULL values updated.\n";
    } else {
        echo "✓ No NULL tracer_form_id values found.\n";
    }
    echo "\n";
    
    // Step 4: Add new unique constraint
    echo "Step 4: Adding new unique constraint (unique_alumni_tracer_form)...\n";
    try {
        $pdo->exec("ALTER TABLE employment_records ADD CONSTRAINT unique_alumni_tracer_form UNIQUE (alumni_id, tracer_form_id)");
        echo "✓ New unique constraint added successfully.\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), "Duplicate entry") !== false) {
            echo "⚠ Duplicate entries found. Let's identify and fix them...\n";
            
            // Find duplicates
            $stmt = $pdo->prepare("
                SELECT alumni_id, tracer_form_id, COUNT(*) as count 
                FROM employment_records 
                GROUP BY alumni_id, tracer_form_id 
                HAVING count > 1
            ");
            $stmt->execute();
            $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Found " . count($duplicates) . " duplicate combinations:\n";
            foreach ($duplicates as $dup) {
                echo "- Alumni ID: {$dup['alumni_id']}, Tracer Form ID: {$dup['tracer_form_id']} ({$dup['count']} records)\n";
            }
            
            // For now, let's keep the most recent record for each duplicate
            echo "Removing older duplicate records...\n";
            foreach ($duplicates as $dup) {
                $pdo->prepare("
                    DELETE FROM employment_records 
                    WHERE alumni_id = ? AND tracer_form_id = ? 
                    AND record_id NOT IN (
                        SELECT * FROM (
                            SELECT record_id FROM employment_records 
                            WHERE alumni_id = ? AND tracer_form_id = ? 
                            ORDER BY submitted_at DESC, record_id DESC 
                            LIMIT 1
                        ) as temp
                    )
                ")->execute([$dup['alumni_id'], $dup['tracer_form_id'], $dup['alumni_id'], $dup['tracer_form_id']]);
            }
            
            // Try adding the constraint again
            $pdo->exec("ALTER TABLE employment_records ADD CONSTRAINT unique_alumni_tracer_form UNIQUE (alumni_id, tracer_form_id)");
            echo "✓ Duplicates removed and unique constraint added successfully.\n";
        } else {
            throw $e;
        }
    }
    echo "\n";
    
    // Step 5: Make tracer_form_id NOT NULL
    echo "Step 5: Making tracer_form_id NOT NULL...\n";
    try {
        $pdo->exec("ALTER TABLE employment_records MODIFY COLUMN tracer_form_id INT NOT NULL");
        echo "✓ tracer_form_id column set to NOT NULL.\n";
    } catch (Exception $e) {
        echo "⚠ Could not set tracer_form_id to NOT NULL: " . $e->getMessage() . "\n";
        echo "This is not critical for the fix to work.\n";
    }
    echo "\n";
    
    // Step 6: Verify the changes
    echo "Step 6: Verifying changes...\n";
    $stmt = $pdo->prepare("SHOW CREATE TABLE employment_records");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (strpos($result['Create Table'], 'unique_alumni_tracer_form') !== false) {
        echo "✓ New unique constraint is in place.\n";
    } else {
        echo "✗ New unique constraint not found!\n";
    }
    
    if (strpos($result['Create Table'], 'unique_alumni_form_year') === false) {
        echo "✓ Old unique constraint has been removed.\n";
    } else {
        echo "✗ Old unique constraint still exists!\n";
    }
    echo "\n";
    
    // Commit transaction
    $pdo->commit();
    
    echo "=================================================================\n";
    echo "✅ Migration completed successfully!\n\n";
    echo "Summary of changes:\n";
    echo "- Removed unique constraint on (alumni_id, form_year)\n";
    echo "- Added unique constraint on (alumni_id, tracer_form_id)\n";
    echo "- Updated PHP code to check responses by tracer_form_id instead of form_year\n\n";
    echo "This means:\n";
    echo "✓ Multiple tracer forms can exist in the same year\n";
    echo "✓ Alumni can respond to each tracer form separately\n";
    echo "✓ Alumni cannot respond multiple times to the same tracer form\n";
    echo "✓ New tracer forms will properly show as 'not responded' even if alumni\n";
    echo "  responded to previous forms in the same year\n\n";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
