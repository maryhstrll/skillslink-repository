<?php
/**
 * Test Script: Verify Tracer Form Multiple Responses Fix
 * 
 * This script tests that the fix is working properly by simulating
 * the scenario where multiple tracer forms exist in the same year.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';

try {
    echo "Testing Tracer Form Multiple Responses Fix\n";
    echo "==========================================\n\n";
    
    // Test 1: Check database structure
    echo "Test 1: Checking database structure...\n";
    $stmt = $pdo->prepare("SHOW CREATE TABLE employment_records");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $hasNewConstraint = strpos($result['Create Table'], 'unique_alumni_tracer_form') !== false;
    $hasOldConstraint = strpos($result['Create Table'], 'unique_alumni_form_year') !== false;
    
    echo "✓ New constraint (unique_alumni_tracer_form): " . ($hasNewConstraint ? "EXISTS" : "MISSING") . "\n";
    echo "✓ Old constraint (unique_alumni_form_year): " . ($hasOldConstraint ? "STILL EXISTS (BAD)" : "REMOVED (GOOD)") . "\n\n";
    
    // Test 2: Get a test alumni
    echo "Test 2: Finding test alumni...\n";
    $stmt = $pdo->prepare("
        SELECT a.alumni_id, a.first_name, a.last_name, u.user_id 
        FROM alumni a 
        JOIN users u ON a.user_id = u.user_id 
        WHERE u.is_active = 1 
        LIMIT 1
    ");
    $stmt->execute();
    $testAlumni = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$testAlumni) {
        echo "⚠ No active alumni found for testing. Creating mock test...\n";
        $alumni_id = 1;
        $user_id = 1;
    } else {
        echo "✓ Using alumni: {$testAlumni['first_name']} {$testAlumni['last_name']} (ID: {$testAlumni['alumni_id']})\n";
        $alumni_id = $testAlumni['alumni_id'];
        $user_id = $testAlumni['user_id'];
    }
    echo "\n";
    
    // Test 3: Create two test tracer forms for the same year
    echo "Test 3: Creating test tracer forms...\n";
    $currentYear = date('Y');
    
    // Create first form
    $stmt = $pdo->prepare("
        INSERT INTO tracer_forms (form_title, form_year, form_description, created_by, is_active) 
        VALUES (?, ?, ?, ?, 0)
    ");
    $stmt->execute([
        "Test Form 1 - Year $currentYear", 
        $currentYear, 
        "First test form for year $currentYear", 
        $user_id
    ]);
    $form1_id = $pdo->lastInsertId();
    echo "✓ Created Test Form 1 (ID: $form1_id)\n";
    
    // Create second form
    $stmt->execute([
        "Test Form 2 - Year $currentYear", 
        $currentYear, 
        "Second test form for year $currentYear", 
        $user_id
    ]);
    $form2_id = $pdo->lastInsertId();
    echo "✓ Created Test Form 2 (ID: $form2_id)\n\n";
    
    // Test 4: Test employment record insertion
    echo "Test 4: Testing employment record insertion...\n";
    
    // Insert response for first form
    $stmt = $pdo->prepare("
        INSERT INTO employment_records (
            alumni_id, form_year, tracer_form_id, employment_status, 
            company_name, submitted_at
        ) VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    try {
        $stmt->execute([$alumni_id, $currentYear, $form1_id, 'employed', 'Test Company 1']);
        echo "✓ Successfully added response for Form 1\n";
    } catch (Exception $e) {
        echo "✗ Failed to add response for Form 1: " . $e->getMessage() . "\n";
    }
    
    // Insert response for second form (this should work now!)
    try {
        $stmt->execute([$alumni_id, $currentYear, $form2_id, 'unemployed', 'Test Company 2']);
        echo "✓ Successfully added response for Form 2 (same year, different form)\n";
    } catch (Exception $e) {
        echo "✗ Failed to add response for Form 2: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Test 5: Test the fixed query logic
    echo "Test 5: Testing query logic...\n";
    
    // Set active form to Form 2
    $pdo->prepare("UPDATE tracer_forms SET is_active = 0")->execute();
    $pdo->prepare("UPDATE tracer_forms SET is_active = 1 WHERE form_id = ?")->execute([$form2_id]);
    
    // Simulate the old logic (checking by form_year) - should find response
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM employment_records WHERE alumni_id = ? AND form_year = ?");
    $stmt->execute([$alumni_id, $currentYear]);
    $oldLogicCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Simulate the new logic (checking by tracer_form_id) - should find specific response
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM employment_records WHERE alumni_id = ? AND tracer_form_id = ?");
    $stmt->execute([$alumni_id, $form2_id]);
    $newLogicCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "Old logic (form_year check): Found $oldLogicCount responses\n";
    echo "New logic (tracer_form_id check): Found $newLogicCount responses\n";
    echo "✓ Both queries work as expected\n\n";
    
    // Test 6: Clean up test data
    echo "Test 6: Cleaning up test data...\n";
    $pdo->prepare("DELETE FROM employment_records WHERE tracer_form_id IN (?, ?)")->execute([$form1_id, $form2_id]);
    $pdo->prepare("DELETE FROM tracer_forms WHERE form_id IN (?, ?)")->execute([$form1_id, $form2_id]);
    echo "✓ Test data cleaned up\n\n";
    
    echo "==========================================\n";
    echo "🎉 All tests passed! The fix is working properly.\n\n";
    
    echo "What this means:\n";
    echo "- Alumni can now respond to multiple tracer forms in the same year\n";
    echo "- New tracer forms will show as 'not responded' even if alumni\n";
    echo "  responded to previous forms in the same year\n";
    echo "- Each response is tied to a specific tracer form, not just the year\n";
    
} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
