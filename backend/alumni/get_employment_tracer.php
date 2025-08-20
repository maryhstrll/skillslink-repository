<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../session.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

try {
    // Get the alumni_id from user_id
    $stmt = $pdo->prepare("SELECT alumni_id FROM alumni WHERE user_id = ?");
    $stmt->execute([$userId]);
    $alumni = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$alumni) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Alumni record not found']);
        exit;
    }
    
    $alumni_id = $alumni['alumni_id'];
    
    // Get the active form
    $stmt = $pdo->prepare("SELECT * FROM tracer_forms WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
    $stmt->execute();
    $form = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$form) {
        echo json_encode([
            'already_responded' => false,
            'form_id' => null,
            'form_year' => null,
            'form_description' => '',
            'core_questions' => [],
            'additional_questions' => [],
            'message' => 'No active tracer form available at the moment.'
        ]);
        exit;
    }
    
    $form_id = $form['form_id'];
    $form_year = $form['form_year'];
    
    // Check if user already submitted employment data for this form/year
    $stmt = $pdo->prepare("SELECT * FROM employment_records WHERE alumni_id = ? AND form_year = ?");
    $stmt->execute([$alumni_id, $form_year]);
    $existing_employment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user has additional responses
    $stmt = $pdo->prepare("SELECT responses FROM form_responses WHERE form_id = ? AND alumni_id = ? AND is_complete = 1");
    $stmt->execute([$form_id, $alumni_id]);
    $existing_additional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Parse form questions
    $form_questions_data = json_decode($form['form_questions'] ?? '[]', true);
    
    // Handle both old format (array) and new format (object with additional_questions and selected_employment_questions)
    if (is_array($form_questions_data)) {
        // Check if it's the new format with additional_questions key
        if (isset($form_questions_data['additional_questions'])) {
            $all_questions = $form_questions_data['additional_questions'];
            $selected_employment_questions = $form_questions_data['selected_employment_questions'] ?? [];
        } else {
            // Old format - treat as additional questions only
            $all_questions = $form_questions_data;
            $selected_employment_questions = ['employment_status']; // Default to employment status only
        }
    } else {
        $all_questions = [];
        $selected_employment_questions = ['employment_status'];
    }
    
    // Define all available core employment questions
    $all_core_questions = [
        [
            'id' => 'employment_status',
            'label' => 'Current Employment Status',
            'type' => 'radio',
            'required' => true,
            'options' => ['Employed', 'Unemployed', 'Further Studies', 'Not Looking'],
            'maps_to' => 'employment_status'
        ],
        [
            'id' => 'date_employed',
            'label' => 'Date Employed',
            'type' => 'date',
            'required' => false,
            'conditional' => 'employment_status == "employed"',
            'maps_to' => 'date_employed'
        ],
        [
            'id' => 'company_name',
            'label' => 'Company Name',
            'type' => 'text',
            'required' => false,
            'conditional' => 'employment_status == "employed"',
            'maps_to' => 'company_name'
        ],
        [
            'id' => 'job_title',
            'label' => 'Job Title/Position',
            'type' => 'text',
            'required' => false,
            'conditional' => 'employment_status == "employed"',
            'maps_to' => 'job_title' // Frontend uses job_title, maps to occupation in DB
        ],
        [
            'id' => 'job_description',
            'label' => 'Brief Job Description',
            'type' => 'textarea',
            'required' => false,
            'conditional' => 'employment_status == "employed"',
            'maps_to' => 'job_description'
        ],
        [
            'id' => 'salary_range',
            'label' => 'Monthly Salary Range',
            'type' => 'select',
            'required' => false,
            'options' => ['Below 10,000', '10,000 to 20,000', '20,000 to 30,000', '30,000 to 40,000', '40,000 to 50,000', '50,000 to 60,000', 'Above 60,000'],
            'conditional' => 'employment_status == "employed"',
            'maps_to' => 'salary_range'
        ],
        [
            'id' => 'work_classification',
            'label' => 'Work Classification',
            'type' => 'radio',
            'required' => false,
            'options' => ['Wage and Salary Workers', 'Self-employed without any paid employee', 'Employer in Own Family-Operated Farm or Business', 'Work Without Pay in Own-Family-Operated Farm or Business'],
            'conditional' => 'employment_status == "employed"',
            'maps_to' => 'work_classification'
        ],
        [
            'id' => 'employment_type',
            'label' => 'Nature of Employment',
            'type' => 'radio',
            'required' => false,
            'options' => ['Permanent', 'Casual', 'Contractual', 'Seasonal or Short Term'],
            'conditional' => 'employment_status == "employed"',
            'maps_to' => 'employment_type' // Frontend uses employment_type, maps to nature_of_employment in DB
        ],
        [
            'id' => 'industry',
            'label' => 'Industry/Sector',
            'type' => 'text',
            'required' => false,
            'conditional' => 'employment_status == "employed"',
            'maps_to' => 'industry'
        ],
        [
            'id' => 'work_location',
            'label' => 'Work Location (City)',
            'type' => 'text',
            'required' => false,
            'conditional' => 'employment_status == "employed"',
            'maps_to' => 'work_location'
        ],
        [
            'id' => 'is_local',
            'label' => 'Local',
            'type' => 'radio',
            'required' => false,
            'options' => ['Yes', 'No'],
            'conditional' => 'employment_status == "employed"',
            'maps_to' => 'is_local'
        ],
        [
            'id' => 'is_abroad',
            'label' => 'Abroad',
            'type' => 'radio',
            'required' => false,
            'options' => ['Yes', 'No'],
            'conditional' => 'employment_status == "employed"',
            'maps_to' => 'is_abroad'
        ],
        [
            'id' => 'months_to_find_job',
            'label' => 'How many months did it take to find your first job after graduation?',
            'type' => 'number',
            'required' => false,
            'min' => 0,
            'max' => 60,
            'conditional' => 'employment_status == "employed"',
            'maps_to' => 'months_to_find_job'
        ]
    ];
    
    // Filter core questions based on selected employment questions
    $core_questions = array_filter($all_core_questions, function($question) use ($selected_employment_questions) {
        return in_array($question['id'], $selected_employment_questions);
    });
    
    // Re-index the array
    $core_questions = array_values($core_questions);
    
    // Additional questions are the custom questions from the form
    $additional_questions = is_array($all_questions) ? $all_questions : [];
    
    if ($existing_employment || $existing_additional) {
        // User has already responded
        $existing_employment_data = [];
        if ($existing_employment) {
            // Map database fields to form format
            $existing_employment_data = [
                'employment_status' => $existing_employment['employment_status'],
                'company_name' => $existing_employment['company_name'],
                'job_title' => $existing_employment['occupation'], // Database column is 'occupation'
                'job_description' => $existing_employment['job_description'],
                'salary_range' => $existing_employment['salary_range'],
                'employment_type' => $existing_employment['nature_of_employment'], // Database column is 'nature_of_employment'
                'work_classification' => $existing_employment['work_classification'],
                'industry' => $existing_employment['industry'],
                'work_location' => $existing_employment['work_location'],
                'is_local' => $existing_employment['is_local'],
                'is_abroad' => $existing_employment['is_abroad'],
                'date_employed' => $existing_employment['date_employed'],
                'months_to_find_job' => $existing_employment['months_to_find_job']
            ];
        }
        
        $existing_additional_data = [];
        if ($existing_additional) {
            $existing_additional_data = json_decode($existing_additional['responses'], true) ?: [];
        }
        
        echo json_encode([
            'already_responded' => true,
            'message' => 'You have already submitted your employment tracer form for ' . $form_year . '. Thank you for your participation!',
            'form_id' => $form_id,
            'form_year' => $form_year,
            'form_description' => $form['form_description'],
            'core_questions' => $core_questions,
            'additional_questions' => $additional_questions,
            'existing_employment_data' => $existing_employment_data,
            'existing_additional_data' => $existing_additional_data
        ]);
    } else {
        // User hasn't responded yet
        echo json_encode([
            'already_responded' => false,
            'form_id' => $form_id,
            'form_year' => $form_year,
            'form_description' => $form['form_description'],
            'core_questions' => $core_questions,
            'additional_questions' => $additional_questions
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error in get_employment_tracer: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
