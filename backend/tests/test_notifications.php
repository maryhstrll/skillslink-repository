<?php
/**
 * Test script for the notification system
 * Run this to test if notifications are created correctly when a tracer form is activated
 */

header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/notification_helper.php';

echo "=== Testing Notification System ===\n\n";

try {
    // Test 1: Check if we can get alumni user IDs
    echo "1. Testing getAlumniUserIds()...\n";
    $alumni_ids = getAlumniUserIds($pdo);
    echo "   Found " . count($alumni_ids) . " alumni users: " . implode(', ', $alumni_ids) . "\n\n";
    
    if (empty($alumni_ids)) {
        echo "   WARNING: No alumni users found. Make sure you have alumni users with approved status.\n\n";
    }
    
    // Test 2: Create a test notification for new tracer form
    echo "2. Testing notifyAlumniNewTracerForm()...\n";
    $result = notifyAlumniNewTracerForm($pdo, "Test Tracer Form 2024", 2024, "2024-12-31");
    echo "   Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    echo "   Message: " . $result['message'] . "\n";
    echo "   Sent to: " . $result['sent_count'] . " users\n\n";
    
    // Test 3: Check if notifications were created in database
    echo "3. Checking notifications in database...\n";
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE title = 'New Tracer Form Available' 
        AND message LIKE '%Test Tracer Form 2024%'
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
    ");
    $stmt->execute();
    $count_result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Found " . $count_result['count'] . " recent test notifications in database\n\n";
    
    // Test 4: Test notification activation
    echo "4. Testing notifyAlumniTracerFormActivated()...\n";
    $result2 = notifyAlumniTracerFormActivated($pdo, "Test Activated Form 2024", 2024, "2024-11-30");
    echo "   Result: " . ($result2['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    echo "   Message: " . $result2['message'] . "\n";
    echo "   Sent to: " . $result2['sent_count'] . " users\n\n";
    
    // Test 5: Check API endpoint
    echo "5. Testing notification API (count endpoint)...\n";
    
    // We can't directly test the API endpoint here since it requires session authentication
    // But we can check if the file exists and is accessible
    $api_file = __DIR__ . '/api/notifications.php';
    if (file_exists($api_file)) {
        echo "   ✓ API file exists: " . $api_file . "\n";
    } else {
        echo "   ✗ API file missing: " . $api_file . "\n";
    }
    
    // Test 6: Show recent notifications for debugging
    echo "\n6. Recent notifications in database:\n";
    $stmt = $pdo->prepare("
        SELECT n.notification_id, n.user_id, n.title, n.message, n.status, n.created_at,
               u.email, u.role
        FROM notifications n
        JOIN users u ON n.user_id = u.user_id
        ORDER BY n.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recent_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($recent_notifications)) {
        echo "   No notifications found in database.\n";
    } else {
        echo "   ID | User | Title | Status | Created\n";
        echo "   ---|------|-------|--------|---------\n";
        foreach ($recent_notifications as $notif) {
            echo "   " . $notif['notification_id'] . " | " . 
                 $notif['email'] . " | " . 
                 substr($notif['title'], 0, 20) . "... | " . 
                 $notif['status'] . " | " . 
                 $notif['created_at'] . "\n";
        }
    }
    
    echo "\n=== Test Complete ===\n";
    echo "If you see any failures above, check:\n";
    echo "1. Database connection is working\n";
    echo "2. You have alumni users with approved status\n";
    echo "3. Notifications table exists with correct structure\n";
    echo "4. API endpoints are accessible\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
