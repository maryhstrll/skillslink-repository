<?php
// Don't start session here since session.php will do it
header('Content-Type: application/json');

// Handle preflight requests first
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../session.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Validate session first
    $sessionResult = validateSession();
    
    if (!$sessionResult['loggedIn']) {
        http_response_code(401);
        echo json_encode([
            'success' => false, 
            'message' => 'Unauthorized',
            'debug' => 'Session validation failed',
            'session_data' => $sessionResult
        ]);
        exit();
    }
    
    // Only allow alumni to access their profile
    if ($sessionResult['user']['role'] !== 'alumni') {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'Access denied',
            'debug' => 'User role is not alumni',
            'user_role' => $sessionResult['user']['role']
        ]);
        exit();
    }
    
    $userId = $sessionResult['user']['id'];
    $studentId = $sessionResult['user']['student_id'];
    
    // Get detailed alumni profile information
    $sql = "SELECT 
                u.user_id,
                u.email,
                u.first_name,
                u.last_name,
                u.middle_name,
                u.student_id,
                u.created_at as member_since,
                a.alumni_id,
                a.phone_number,
                a.alternative_phone,
                a.date_of_birth,
                a.gender,
                a.year_graduated,
                a.graduation_date,
                a.program_id,
                a.batch_id,
                a.gpa,
                a.address,
                a.city,
                a.province,
                a.country,
                a.postal_code,
                a.linkedin_profile,
                a.facebook_profile,
                a.created_at,
                a.updated_at,
                p.program_name,
                p.program_code,
                p.department,
                b.batch_name,
                b.batch_year,
                e.employment_status,
                e.company_name as current_company,
                e.job_title as current_position,
                e.salary_range,
                e.employment_type,
                e.work_setup
            FROM users u
            LEFT JOIN alumni a ON u.student_id = a.student_id
            LEFT JOIN programs p ON a.program_id = p.program_id
            LEFT JOIN batches b ON a.batch_id = b.batch_id
            LEFT JOIN employment_records e ON a.alumni_id = e.alumni_id AND e.is_current_job = 1
            WHERE u.user_id = ? AND u.approval_status = 'approved'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profile) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Profile not found']);
        exit();
    }
    
    // Calculate profile completion percentage
    $completionFields = [
        'phone_number', 'date_of_birth', 'gender', 'address', 'city', 
        'province', 'country', 'linkedin_profile', 'facebook_profile'
    ];
    
    $completedFields = 0;
    foreach ($completionFields as $field) {
        if (!empty($profile[$field])) {
            $completedFields++;
        }
    }
    
    $completionPercentage = round(($completedFields / count($completionFields)) * 100);
    
    // Format the response
    $response = [
        'success' => true,
        'data' => [
            'user_info' => [
                'user_id' => $profile['user_id'],
                'email' => $profile['email'],
                'first_name' => $profile['first_name'],
                'last_name' => $profile['last_name'],
                'middle_name' => $profile['middle_name'],
                'full_name' => trim(($profile['first_name'] ?? '') . ' ' . ($profile['middle_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')),
                'student_id' => $profile['student_id'],
                'member_since' => $profile['member_since']
            ],
            'personal_info' => [
                'phone_number' => $profile['phone_number'],
                'alternative_phone' => $profile['alternative_phone'],
                'date_of_birth' => $profile['date_of_birth'],
                'gender' => $profile['gender']
            ],
            'address' => [
                'address' => $profile['address'],
                'city' => $profile['city'],
                'province' => $profile['province'],
                'country' => $profile['country'] ?? 'Philippines',
                'postal_code' => $profile['postal_code']
            ],
            'academic_info' => [
                'program_id' => $profile['program_id'],
                'program_name' => $profile['program_name'],
                'program_code' => $profile['program_code'],
                'department' => $profile['department'],
                'batch_id' => $profile['batch_id'],
                'batch_name' => $profile['batch_name'],
                'batch_year' => $profile['batch_year'],
                'year_graduated' => $profile['year_graduated'],
                'graduation_date' => $profile['graduation_date'],
                'gpa' => $profile['gpa']
            ],
            'social_links' => [
                'linkedin_profile' => $profile['linkedin_profile'],
                'facebook_profile' => $profile['facebook_profile']
            ],
            'employment' => [
                'status' => $profile['employment_status'],
                'company' => $profile['current_company'],
                'position' => $profile['current_position'],
                'salary_range' => $profile['salary_range'],
                'employment_type' => $profile['employment_type'],
                'work_setup' => $profile['work_setup']
            ],
            'profile_completion' => $completionPercentage,
            'created_at' => $profile['created_at'],
            'updated_at' => $profile['updated_at']
        ]
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log('Database error in get_profile.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error',
        'debug' => $e->getMessage(),
        'error_type' => 'PDOException'
    ]);
} catch (Exception $e) {
    error_log('Error in get_profile.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Internal server error',
        'debug' => $e->getMessage(),
        'error_type' => 'Exception'
    ]);
}
?>
