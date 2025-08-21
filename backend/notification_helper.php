<?php
/**
 * Notification Helper Functions
 * This file contains utility functions for creating and managing notifications
 */

require_once __DIR__ . '/config.php';

/**
 * Create notifications for multiple users
 * 
 * @param PDO $pdo Database connection
 * @param array $user_ids Array of user IDs to notify
 * @param string $type Notification type (in_app, email, sms)
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $priority Priority level (low, normal, high)
 * @param string $category Category (reminder, announcement, system, employment_update)
 * @return array Result array with success status and count
 */
function createNotifications($pdo, $user_ids, $type, $title, $message, $priority = 'normal', $category = 'announcement') {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, status, priority, category, sent_at) 
            VALUES (:user_id, :type, :title, :message, 'sent', :priority, :category, NOW())
        ");
        
        $success_count = 0;
        foreach ($user_ids as $user_id) {
            $result = $stmt->execute([
                ':user_id' => $user_id,
                ':type' => $type,
                ':title' => $title,
                ':message' => $message,
                ':priority' => $priority,
                ':category' => $category
            ]);
            
            if ($result) {
                $success_count++;
            }
        }
        
        $pdo->commit();
        return [
            'success' => true,
            'sent_count' => $success_count,
            'message' => "Notifications sent to $success_count users"
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'success' => false,
            'sent_count' => 0,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

/**
 * Get all active alumni user IDs
 * 
 * @param PDO $pdo Database connection
 * @return array Array of alumni user IDs
 */
function getAlumniUserIds($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT u.user_id 
            FROM users u 
            WHERE u.role = 'alumni' 
            AND u.is_active = 1 
            AND u.approval_status = 'approved'
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $result ?: [];
    } catch (Exception $e) {
        error_log("Error getting alumni user IDs: " . $e->getMessage());
        return [];
    }
}

/**
 * Create notification for new tracer form
 * 
 * @param PDO $pdo Database connection
 * @param string $form_title The title of the new tracer form
 * @param int $form_year The year of the tracer form
 * @param string $deadline The deadline date (optional)
 * @return array Result array with success status
 */
function notifyAlumniNewTracerForm($pdo, $form_title, $form_year, $deadline = null) {
    $alumni_ids = getAlumniUserIds($pdo);
    
    if (empty($alumni_ids)) {
        return [
            'success' => false,
            'sent_count' => 0,
            'message' => 'No alumni found to notify'
        ];
    }
    
    $title = "New Tracer Form Available";
    
    $message = "A new tracer form '$form_title' for year $form_year has been created and is now available for you to complete.";
    
    if ($deadline) {
        $formatted_deadline = date('F j, Y', strtotime($deadline));
        $message .= " Please complete it before the deadline: $formatted_deadline.";
    } else {
        $message .= " Please complete it at your earliest convenience.";
    }
    
    $message .= " You can access the form from your dashboard.";
    
    return createNotifications(
        $pdo, 
        $alumni_ids, 
        'in_app', 
        $title, 
        $message, 
        'high', 
        'announcement'
    );
}

/**
 * Create notification for tracer form activation
 * 
 * @param PDO $pdo Database connection
 * @param string $form_title The title of the activated tracer form
 * @param int $form_year The year of the tracer form
 * @param string $deadline The deadline date (optional)
 * @return array Result array with success status
 */
function notifyAlumniTracerFormActivated($pdo, $form_title, $form_year, $deadline = null) {
    $alumni_ids = getAlumniUserIds($pdo);
    
    if (empty($alumni_ids)) {
        return [
            'success' => false,
            'sent_count' => 0,
            'message' => 'No alumni found to notify'
        ];
    }
    
    $title = "Tracer Form Now Active";
    
    $message = "The tracer form '$form_title' for year $form_year is now active and ready for completion.";
    
    if ($deadline) {
        $formatted_deadline = date('F j, Y', strtotime($deadline));
        $message .= " Deadline: $formatted_deadline.";
    }
    
    $message .= " Please complete it from your dashboard as soon as possible.";
    
    return createNotifications(
        $pdo, 
        $alumni_ids, 
        'in_app', 
        $title, 
        $message, 
        'high', 
        'reminder'
    );
}

/**
 * Log notification activity for debugging
 * 
 * @param string $action The action performed
 * @param array $result The result of the notification operation
 */
function logNotificationActivity($action, $result) {
    $status = $result['success'] ? 'SUCCESS' : 'FAILED';
    $count = $result['sent_count'] ?? 0;
    $message = $result['message'] ?? 'No message';
    
    error_log("NOTIFICATION [$action] $status: Sent to $count users - $message");
}
?>
