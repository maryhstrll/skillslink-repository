<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

try {
    $action = $_GET['action'] ?? '';

    // Allow test_connection without admin check for debugging
    if ($action === 'test_connection') {
        testDatabaseConnection();
        exit;
    }

    // DEVELOPMENT MODE - Remove this for production
    $isDevelopmentMode = true; // Set to false in production
    
    if (!$isDevelopmentMode) {
        // Check admin access for production
        $role = $_SESSION['role'] ?? 'alumni';
        if ($role !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            exit();
        }
    }

    switch ($action) {
        case 'test_connection':
            testDatabaseConnection();
            break;
        case 'employment_statistics':
            generateEmploymentStatistics();
            break;
        case 'graduation_trends':
            generateGraduationTrends();
            break;
        case 'salary_analysis':
            generateSalaryAnalysis();
            break;
        case 'geographic_distribution':
            generateGeographicDistribution();
            break;
        case 'job_search_analysis':
            generateJobSearchAnalysis();
            break;
        case 'summary_dashboard':
            generateSummaryDashboard();
            break;
        case 'academic_excellence':
            generateAcademicExcellenceDashboard();
            break;
        case 'employment_by_program':
            generateEmploymentByProgram();
            break;
        case 'salary_by_program':
            generateSalaryByProgram();
            break;
        case 'job_alignment_rate':
            generateJobAlignmentRate();
            break;
        case 'time_to_employment_by_program':
            generateTimeToEmploymentByProgram();
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function testDatabaseConnection() {
    global $pdo;
    
    try {
        // Test database connection
        $stmt = $pdo->query("SELECT 1");
        
        // Check for active forms
        $activeFormQuery = "SELECT COUNT(*) as count FROM tracer_forms WHERE is_active = 1";
        $stmt = $pdo->prepare($activeFormQuery);
        $stmt->execute();
        $activeForms = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Check for any forms
        $totalFormsQuery = "SELECT COUNT(*) as count FROM tracer_forms";
        $stmt = $pdo->prepare($totalFormsQuery);
        $stmt->execute();
        $totalForms = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Check for responses
        $responsesQuery = "SELECT COUNT(*) as count FROM form_responses";
        $stmt = $pdo->prepare($responsesQuery);
        $stmt->execute();
        $totalResponses = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Check employment records
        $employmentQuery = "SELECT COUNT(*) as count FROM employment_records";
        $stmt = $pdo->prepare($employmentQuery);
        $stmt->execute();
        $employmentRecords = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo json_encode([
            'success' => true,
            'data' => [
                'database_connected' => true,
                'active_forms' => (int)$activeForms,
                'total_forms' => (int)$totalForms,
                'total_responses' => (int)$totalResponses,
                'employment_records' => (int)$employmentRecords
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $e->getMessage()
        ]);
    }
}

function generateEmploymentStatistics() {
    global $pdo;
    
    // Get active form
    $activeFormQuery = "SELECT form_id FROM tracer_forms WHERE is_active = 1 LIMIT 1";
    $stmt = $pdo->prepare($activeFormQuery);
    $stmt->execute();
    $activeForm = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$activeForm) {
        echo json_encode(['success' => false, 'message' => 'No active tracer form found']);
        return;
    }
    
    $formId = $activeForm['form_id'];
    
    // Get employment status distribution from employment_records
    $employmentStatusQuery = "
        SELECT 
            CASE 
                WHEN employment_status = 'employed' THEN 'Employed'
                WHEN employment_status = 'unemployed' THEN 'Unemployed'
                WHEN employment_status = 'self-employed' THEN 'Self-Employed'
                ELSE UPPER(LEFT(employment_status, 1)) + LOWER(SUBSTRING(employment_status, 2))
            END as status,
            COUNT(*) as count
        FROM employment_records er
        WHERE er.tracer_form_id = ? 
        AND employment_status IS NOT NULL
        GROUP BY status
    ";
    
    $stmt = $pdo->prepare($employmentStatusQuery);
    $stmt->execute([$formId]);
    $employmentStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert to associative array
    $statusCounts = [];
    foreach ($employmentStatus as $row) {
        $statusCounts[$row['status']] = (int)$row['count'];
    }
    
    // Get employment types for employed alumni from employment_records
    $employmentTypesQuery = "
        SELECT 
            CASE 
                WHEN nature_of_employment IS NULL OR nature_of_employment = '' THEN 'Not Specified'
                ELSE nature_of_employment
            END as type,
            COUNT(*) as count
        FROM employment_records er
        WHERE er.tracer_form_id = ? 
        AND employment_status = 'employed'
        GROUP BY type
    ";
    
    $stmt = $pdo->prepare($employmentTypesQuery);
    $stmt->execute([$formId]);
    $employmentTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert to associative array
    $typeCounts = [];
    foreach ($employmentTypes as $row) {
        $typeCounts[$row['type']] = (int)$row['count'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'employment_status' => $statusCounts,
            'employment_types' => $typeCounts,
            'form_id' => $formId
        ]
    ]);
}

function generateSummaryDashboard() {
    global $pdo;
    
    // Get active form
    $activeFormQuery = "SELECT form_id FROM tracer_forms WHERE is_active = 1 LIMIT 1";
    $stmt = $pdo->prepare($activeFormQuery);
    $stmt->execute();
    $activeForm = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$activeForm) {
        echo json_encode(['success' => false, 'message' => 'No active tracer form found']);
        return;
    }
    
    $formId = $activeForm['form_id'];
    
    // Get basic counts
    $totalResponsesQuery = "SELECT COUNT(*) as count FROM employment_records WHERE tracer_form_id = ?";
    $stmt = $pdo->prepare($totalResponsesQuery);
    $stmt->execute([$formId]);
    $totalResponses = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $employedQuery = "SELECT COUNT(*) as count FROM employment_records WHERE tracer_form_id = ? AND employment_status = 'employed'";
    $stmt = $pdo->prepare($employedQuery);
    $stmt->execute([$formId]);
    $employedCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $unemployedQuery = "SELECT COUNT(*) as count FROM employment_records WHERE tracer_form_id = ? AND employment_status = 'unemployed'";
    $stmt = $pdo->prepare($unemployedQuery);
    $stmt->execute([$formId]);
    $unemployedCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $employmentRate = $totalResponses > 0 ? round(($employedCount / $totalResponses) * 100, 1) : 0;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_responses' => (int)$totalResponses,
            'employed_count' => (int)$employedCount,
            'unemployed_count' => (int)$unemployedCount,
            'employment_rate' => $employmentRate
        ]
    ]);
}

function generateSalaryAnalysis() {
    global $pdo;
    
    // Get active form
    $activeFormQuery = "SELECT form_id FROM tracer_forms WHERE is_active = 1 LIMIT 1";
    $stmt = $pdo->prepare($activeFormQuery);
    $stmt->execute();
    $activeForm = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$activeForm) {
        echo json_encode(['success' => false, 'message' => 'No active tracer form found']);
        return;
    }
    
    $formId = $activeForm['form_id'];
    
    // Get salary range distribution
    $salaryQuery = "
        SELECT 
            CASE 
                WHEN salary_range IS NULL OR salary_range = '' THEN 'Not Specified'
                ELSE salary_range
            END as salary_range,
            COUNT(*) as count
        FROM employment_records 
        WHERE tracer_form_id = ? 
        AND employment_status = 'employed'
        GROUP BY salary_range
    ";
    
    $stmt = $pdo->prepare($salaryQuery);
    $stmt->execute([$formId]);
    $salaryRanges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $salaryCounts = [];
    foreach ($salaryRanges as $row) {
        $salaryCounts[$row['salary_range']] = (int)$row['count'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'salary_distribution' => $salaryCounts,
            'form_id' => $formId
        ]
    ]);
}

function generateGeographicDistribution() {
    global $pdo;
    
    // Get active form
    $activeFormQuery = "SELECT form_id FROM tracer_forms WHERE is_active = 1 LIMIT 1";
    $stmt = $pdo->prepare($activeFormQuery);
    $stmt->execute();
    $activeForm = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$activeForm) {
        echo json_encode(['success' => false, 'message' => 'No active tracer form found']);
        return;
    }
    
    $formId = $activeForm['form_id'];
    
    // Get location distribution
    $locationQuery = "
        SELECT 
            CASE
                WHEN is_local = 'yes' OR is_local = 1 THEN 'Local'
                WHEN is_abroad = 'yes' OR is_abroad = 1 THEN 'Abroad'
                ELSE 'Not Specified'
            END as location_type,
            COUNT(*) as count
        FROM employment_records 
        WHERE tracer_form_id = ? 
        AND employment_status = 'employed'
        GROUP BY location_type
    ";
    
    $stmt = $pdo->prepare($locationQuery);
    $stmt->execute([$formId]);
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $locationCounts = [];
    foreach ($locations as $row) {
        $locationCounts[$row['location_type']] = (int)$row['count'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'geographic_distribution' => $locationCounts,
            'form_id' => $formId
        ]
    ]);
}

function generateJobSearchAnalysis() {
    global $pdo;
    
    // Get active form
    $activeFormQuery = "SELECT form_id FROM tracer_forms WHERE is_active = 1 LIMIT 1";
    $stmt = $pdo->prepare($activeFormQuery);
    $stmt->execute();
    $activeForm = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$activeForm) {
        echo json_encode(['success' => false, 'message' => 'No active tracer form found']);
        return;
    }
    
    $formId = $activeForm['form_id'];
    
    // Get job search time distribution
    $monthsQuery = "
        SELECT 
            CASE
                WHEN months_to_find_job = 0 THEN 'Immediately'
                WHEN months_to_find_job BETWEEN 1 AND 3 THEN '1-3 months'
                WHEN months_to_find_job BETWEEN 4 AND 6 THEN '4-6 months'
                WHEN months_to_find_job BETWEEN 7 AND 12 THEN '7-12 months'
                WHEN months_to_find_job > 12 THEN 'Over 12 months'
                ELSE 'Not Specified'
            END as time_range,
            COUNT(*) as count
        FROM employment_records 
        WHERE tracer_form_id = ? 
        AND employment_status = 'employed'
        GROUP BY time_range
    ";
    
    $stmt = $pdo->prepare($monthsQuery);
    $stmt->execute([$formId]);
    $monthsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $monthsCounts = [];
    foreach ($monthsData as $row) {
        $monthsCounts[$row['time_range']] = (int)$row['count'];
    }
    
    // Get average months
    $avgQuery = "
        SELECT AVG(months_to_find_job) as avg_months 
        FROM employment_records 
        WHERE tracer_form_id = ? 
        AND employment_status = 'employed'
        AND months_to_find_job IS NOT NULL
    ";
    
    $stmt = $pdo->prepare($avgQuery);
    $stmt->execute([$formId]);
    $avgMonths = $stmt->fetch(PDO::FETCH_ASSOC)['avg_months'];
    $avgMonths = $avgMonths ? round($avgMonths, 1) : 0;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'job_search_time' => $monthsCounts,
            'average_months' => $avgMonths,
            'form_id' => $formId
        ]
    ]);
}

function generateGraduationTrends() {
    global $pdo;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'message' => 'Graduation trends data not available yet',
            'trends' => []
        ]
    ]);
}

// Academic Excellence Dashboard Functions

function generateAcademicExcellenceDashboard() {
    global $pdo;
    
    // Get data for all academic excellence metrics
    $employmentByProgram = getEmploymentByProgram();
    $salaryByProgram = getSalaryByProgram();
    $jobAlignmentRate = getJobAlignmentRate();
    $timeToEmploymentByProgram = getTimeToEmploymentByProgram();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'employment_by_program' => $employmentByProgram,
            'salary_by_program' => $salaryByProgram,
            'job_alignment_rate' => $jobAlignmentRate,
            'time_to_employment_by_program' => $timeToEmploymentByProgram
        ]
    ]);
}

function generateEmploymentByProgram() {
    global $pdo;
    
    $data = getEmploymentByProgram();
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
}

function generateSalaryByProgram() {
    global $pdo;
    
    $data = getSalaryByProgram();
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
}

function generateJobAlignmentRate() {
    global $pdo;
    
    $data = getJobAlignmentRate();
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
}

function generateTimeToEmploymentByProgram() {
    global $pdo;
    
    $data = getTimeToEmploymentByProgram();
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
}

// Helper functions for Academic Excellence Dashboard

function getEmploymentByProgram() {
    global $pdo;
    
    // Get active form
    $activeFormQuery = "SELECT form_id FROM tracer_forms WHERE is_active = 1 LIMIT 1";
    $stmt = $pdo->prepare($activeFormQuery);
    $stmt->execute();
    $activeForm = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$activeForm) {
        return ['programs' => [], 'employment_rates' => []];
    }
    
    $formId = $activeForm['form_id'];
    
    // Get employment rate by program using alumni table
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
        LEFT JOIN alumni a ON p.program_id = a.program_id
        LEFT JOIN employment_records er ON a.alumni_id = er.alumni_id AND er.tracer_form_id = ?
        WHERE p.is_active = 1
        GROUP BY p.program_id, p.program_name, p.program_code
        HAVING total_responses > 0
        ORDER BY employment_rate DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$formId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $programs = [];
    $employmentRates = [];
    $employedCounts = [];
    $totalResponses = [];
    
    foreach ($results as $row) {
        $programs[] = $row['program_name'];
        $employmentRates[] = (float)$row['employment_rate'];
        $employedCounts[] = (int)$row['employed_count'];
        $totalResponses[] = (int)$row['total_responses'];
    }
    
    return [
        'programs' => $programs,
        'employment_rates' => $employmentRates,
        'employed_counts' => $employedCounts,
        'total_responses' => $totalResponses
    ];
}

function getSalaryByProgram() {
    global $pdo;
    
    // Get active form
    $activeFormQuery = "SELECT form_id FROM tracer_forms WHERE is_active = 1 LIMIT 1";
    $stmt = $pdo->prepare($activeFormQuery);
    $stmt->execute();
    $activeForm = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$activeForm) {
        return ['programs' => [], 'average_salaries' => []];
    }
    
    $formId = $activeForm['form_id'];
    
    // Get average salary by program using alumni table
    $query = "
        SELECT 
            p.program_name,
            p.program_code,
            COUNT(er.alumni_id) as total_employed,
            AVG(CASE 
                WHEN er.salary_range = 'Below 10,000' THEN 5000
                WHEN er.salary_range = '10,000 to 20,000' THEN 15000
                WHEN er.salary_range = '20,000 to 30,000' THEN 25000
                WHEN er.salary_range = '30,000 to 40,000' THEN 35000
                WHEN er.salary_range = '40,000 to 50,000' THEN 45000
                WHEN er.salary_range = '50,000 to 60,000' THEN 55000
                WHEN er.salary_range = 'Above 60,000' THEN 65000
                ELSE NULL
            END) as avg_salary
        FROM programs p
        LEFT JOIN alumni a ON p.program_id = a.program_id
        LEFT JOIN employment_records er ON a.alumni_id = er.alumni_id 
        WHERE er.tracer_form_id = ? 
        AND er.employment_status = 'employed'
        AND er.salary_range IS NOT NULL 
        AND p.is_active = 1
        GROUP BY p.program_id, p.program_name, p.program_code
        HAVING total_employed > 0
        ORDER BY avg_salary DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$formId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $programs = [];
    $averageSalaries = [];
    $totalEmployed = [];
    
    foreach ($results as $row) {
        $programs[] = $row['program_name'];
        $averageSalaries[] = round((float)$row['avg_salary'], 2);
        $totalEmployed[] = (int)$row['total_employed'];
    }
    
    return [
        'programs' => $programs,
        'average_salaries' => $averageSalaries,
        'total_employed' => $totalEmployed
    ];
}

function getJobAlignmentRate() {
    global $pdo;
    
    // Get active form
    $activeFormQuery = "SELECT form_id FROM tracer_forms WHERE is_active = 1 LIMIT 1";
    $stmt = $pdo->prepare($activeFormQuery);
    $stmt->execute();
    $activeForm = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$activeForm) {
        return ['overall_rate' => 0, 'by_program' => []];
    }
    
    $formId = $activeForm['form_id'];
    
    // Get job-course alignment rate using alumni table
    $overallQuery = "
        SELECT 
            COUNT(CASE WHEN er.job_relevance_to_course IN ('highly_relevant', 'somewhat_relevant') THEN 1 END) as aligned_jobs,
            COUNT(er.alumni_id) as total_employed,
            CASE 
                WHEN COUNT(er.alumni_id) > 0 
                THEN ROUND((COUNT(CASE WHEN er.job_relevance_to_course IN ('highly_relevant', 'somewhat_relevant') THEN 1 END) * 100.0 / COUNT(er.alumni_id)), 1)
                ELSE 0 
            END as alignment_rate
        FROM employment_records er
        WHERE er.tracer_form_id = ? 
        AND er.employment_status = 'employed'
        AND er.job_relevance_to_course IS NOT NULL
    ";
    
    $stmt = $pdo->prepare($overallQuery);
    $stmt->execute([$formId]);
    $overallResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get alignment by program
    $programQuery = "
        SELECT 
            p.program_name,
            COUNT(CASE WHEN er.job_relevance_to_course IN ('highly_relevant', 'somewhat_relevant') THEN 1 END) as aligned_jobs,
            COUNT(er.alumni_id) as total_employed,
            CASE 
                WHEN COUNT(er.alumni_id) > 0 
                THEN ROUND((COUNT(CASE WHEN er.job_relevance_to_course IN ('highly_relevant', 'somewhat_relevant') THEN 1 END) * 100.0 / COUNT(er.alumni_id)), 1)
                ELSE 0 
            END as alignment_rate
        FROM programs p
        LEFT JOIN alumni a ON p.program_id = a.program_id
        LEFT JOIN employment_records er ON a.alumni_id = er.alumni_id AND er.tracer_form_id = ?
        WHERE er.employment_status = 'employed'
        AND er.job_relevance_to_course IS NOT NULL
        AND p.is_active = 1
        GROUP BY p.program_id, p.program_name
        HAVING total_employed > 0
        ORDER BY alignment_rate DESC
    ";
    
    $stmt = $pdo->prepare($programQuery);
    $stmt->execute([$formId]);
    $programResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'overall_rate' => (float)$overallResult['alignment_rate'],
        'aligned_jobs' => (int)$overallResult['aligned_jobs'],
        'total_employed' => (int)$overallResult['total_employed'],
        'by_program' => $programResults
    ];
}

function getTimeToEmploymentByProgram() {
    global $pdo;
    
    // Get active form
    $activeFormQuery = "SELECT form_id FROM tracer_forms WHERE is_active = 1 LIMIT 1";
    $stmt = $pdo->prepare($activeFormQuery);
    $stmt->execute();
    $activeForm = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$activeForm) {
        return ['programs' => [], 'average_months' => []];
    }
    
    $formId = $activeForm['form_id'];
    
    // Get average time to employment by program using alumni table
    $query = "
        SELECT 
            p.program_name,
            p.program_code,
            COUNT(er.alumni_id) as total_employed,
            AVG(er.months_to_find_job) as avg_months_to_employment,
            MIN(er.months_to_find_job) as min_months,
            MAX(er.months_to_find_job) as max_months
        FROM programs p
        LEFT JOIN alumni a ON p.program_id = a.program_id
        LEFT JOIN employment_records er ON a.alumni_id = er.alumni_id AND er.tracer_form_id = ?
        WHERE er.employment_status = 'employed'
        AND er.months_to_find_job IS NOT NULL
        AND er.months_to_find_job > 0
        AND p.is_active = 1
        GROUP BY p.program_id, p.program_name, p.program_code
        HAVING total_employed > 0
        ORDER BY avg_months_to_employment ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$formId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $programs = [];
    $averageMonths = [];
    $minMonths = [];
    $maxMonths = [];
    $totalEmployed = [];
    
    foreach ($results as $row) {
        $programs[] = $row['program_name'];
        $averageMonths[] = round((float)$row['avg_months_to_employment'], 1);
        $minMonths[] = (int)$row['min_months'];
        $maxMonths[] = (int)$row['max_months'];
        $totalEmployed[] = (int)$row['total_employed'];
    }
    
    return [
        'programs' => $programs,
        'average_months' => $averageMonths,
        'min_months' => $minMonths,
        'max_months' => $maxMonths,
        'total_employed' => $totalEmployed
    ];
}
?>
