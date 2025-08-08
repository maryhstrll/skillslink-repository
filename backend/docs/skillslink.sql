-- 1. Users Table (Admin, Staff, Alumni)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'alumni') NOT NULL DEFAULT 'alumni',
    approval_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    first_name VARCHAR(50) NULL,
    last_name VARCHAR(50) NULL,
    student_id VARCHAR(50) NULL,
    last_login TIMESTAMP NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    phone_verified BOOLEAN DEFAULT FALSE,
    password_reset_token VARCHAR(255) NULL,
    password_reset_expires TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    UNIQUE KEY `unique_student_id` (`student_id`),
    KEY `idx_email` (`email`),
    KEY `idx_role` (`role`),
    KEY `idx_active` (`is_active`),
    KEY `fk_users_approved_by` (`approved_by`),
    KEY `idx_approval_status` (`approval_status`)
);

-- 2. Programs Table
CREATE TABLE programs (
    program_id INT AUTO_INCREMENT PRIMARY KEY,
    program_code VARCHAR(20) UNIQUE NOT NULL,
    program_name VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    program_type ENUM('certificate', 'diploma', 'degree') NOT NULL,
    duration_years DECIMAL(2,1) DEFAULT 2.0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_program_code (program_code),
    INDEX idx_department (department),
    INDEX idx_active (is_active)
);

-- 3. Batches Table
CREATE TABLE batches (
    batch_id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    batch_year YEAR NOT NULL,
    batch_name VARCHAR(100) NOT NULL,
    total_graduates INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(program_id) ON DELETE CASCADE,
    UNIQUE KEY unique_program_year (program_id, batch_year),
    INDEX idx_batch_year (batch_year)
);

-- 4. Alumni Table
CREATE TABLE alumni (
    alumni_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    student_id VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50) NULL,
    phone_number VARCHAR(20) NULL,
    alternative_phone VARCHAR(20) NULL,
    date_of_birth DATE NULL,
    gender ENUM('male', 'female', 'other') NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    province VARCHAR(100) NULL,
    country VARCHAR(100) DEFAULT 'Philippines',
    postal_code VARCHAR(20) NULL,
    program_id INT NOT NULL,
    batch_id INT NULL,
    year_graduated YEAR NOT NULL,
    graduation_date DATE NULL,
    gpa DECIMAL(3,2) NULL,
    profile_completion_percentage INT DEFAULT 0,
    profile_image VARCHAR(255) NULL,
    linkedin_profile VARCHAR(255) NULL,
    facebook_profile VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    KEY `user_id` (`user_id`),
    KEY `student_id` (`student_id`),
    KEY `batch_id` (`batch_id`),
    KEY `idx_student_id` (`student_id`),
    KEY `idx_year_graduated` (`year_graduated`),
    KEY `idx_program_id` (`program_id`),
    KEY `idx_name` (`last_name`, `first_name`),
    KEY `idx_active` (`is_active`)
);

-- 5. Employment Records Table
CREATE TABLE employment_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    alumni_id INT NOT NULL,
    employment_status ENUM('employed', 'unemployed', 'self_employed', 'further_studies', 'not_looking') NOT NULL,
    company_name VARCHAR(100) NULL,
    job_title VARCHAR(100) NULL,
    job_description TEXT NULL,
    salary_range ENUM('below_15k', '15k_25k', '25k_35k', '35k_50k', '50k_75k', '75k_100k', 'above_100k', 'prefer_not_to_say') NULL,
    employment_type ENUM('full_time', 'part_time', 'contract', 'freelance', 'internship') NULL,
    company_size ENUM('startup', 'small', 'medium', 'large', 'government') NULL,
    industry VARCHAR(100) NULL,
    work_location VARCHAR(100) NULL,
    work_setup ENUM('onsite', 'remote', 'hybrid') NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    is_current_job BOOLEAN DEFAULT TRUE,
    job_relevance_to_course ENUM('highly_relevant', 'somewhat_relevant', 'not_relevant') NULL,
    skills_used TEXT NULL,
    record_year YEAR NOT NULL,
    months_to_find_job INT NULL,
    job_search_method VARCHAR(100) NULL,
    additional_comments TEXT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (alumni_id) REFERENCES alumni(alumni_id) ON DELETE CASCADE,
    UNIQUE KEY unique_alumni_year (alumni_id, record_year),
    INDEX idx_alumni_id (alumni_id),
    INDEX idx_record_year (record_year),
    INDEX idx_employment_status (employment_status),
    INDEX idx_current_job (is_current_job)
);

-- 6. Tracer Forms Table
CREATE TABLE tracer_forms (
    form_id INT AUTO_INCREMENT PRIMARY KEY,
    form_title VARCHAR(100) NOT NULL,
    form_year YEAR NOT NULL,
    form_description TEXT NULL,
    form_questions JSON NULL,
    deadline_date DATE NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    INDEX idx_form_year (form_year),
    INDEX idx_active (is_active)
);

-- 7. Form Responses Table
CREATE TABLE form_responses (
    response_id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT NOT NULL,
    alumni_id INT NOT NULL,
    responses JSON NULL,
    is_complete BOOLEAN DEFAULT FALSE,
    completion_percentage INT DEFAULT 0,
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (form_id) REFERENCES tracer_forms(form_id) ON DELETE CASCADE,
    FOREIGN KEY (alumni_id) REFERENCES alumni(alumni_id) ON DELETE CASCADE,
    UNIQUE KEY unique_form_alumni (form_id, alumni_id),
    INDEX idx_form_id (form_id),
    INDEX idx_alumni_id (alumni_id),
    INDEX idx_complete (is_complete)
);

-- 8. Notifications Table
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('email', 'sms', 'in_app') NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'sent', 'delivered', 'read', 'failed') DEFAULT 'pending',
    priority ENUM('low', 'normal', 'high') DEFAULT 'normal',
    category ENUM('reminder', 'announcement', 'system', 'employment_update') DEFAULT 'reminder',
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    retry_count INT DEFAULT 0,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_type (type)
);

-- 9. User Sessions Table
CREATE TABLE user_sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    device_info VARCHAR(255) NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_token (session_token),
    INDEX idx_active (is_active),
    INDEX idx_last_activity (last_activity)
);

-- 10. Reports Table
CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    report_title VARCHAR(100) NOT NULL,
    report_type ENUM('employment_summary', 'graduation_rates', 'program_analysis', 'alumni_tracking', 'custom') NOT NULL,
    report_description TEXT NULL,
    parameters JSON NULL,
    file_path VARCHAR(255) NULL,
    file_size INT NULL,
    generated_by INT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    download_count INT DEFAULT 0,
    is_public BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (generated_by) REFERENCES users(user_id),
    INDEX idx_report_type (report_type),
    INDEX idx_generated_by (generated_by),
    INDEX idx_generated_at (generated_at)
);

-- 11. System Logs Table
CREATE TABLE system_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action_type ENUM('create', 'update', 'delete', 'login', 'logout', 'export', 'import', 'email_sent', 'sms_sent') NOT NULL,
    table_affected VARCHAR(50) NULL,
    record_id INT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_table_affected (table_affected),
    INDEX idx_timestamp (timestamp)
);

-- 12. Settings Table
CREATE TABLE settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    updated_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(user_id),
    INDEX idx_setting_key (setting_key),
    INDEX idx_public (is_public)
);

-- 13. Email Templates Table
CREATE TABLE email_templates (
    template_id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) UNIQUE NOT NULL,
    template_type ENUM('welcome', 'reminder', 'password_reset', 'employment_update', 'announcement') NOT NULL,
    subject VARCHAR(200) NOT NULL,
    body_html TEXT NOT NULL,
    body_text TEXT NULL,
    variables JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    INDEX idx_template_type (template_type),
    INDEX idx_active (is_active)
);

-- 14. Alumni Skills Table (Optional - for detailed tracking)
CREATE TABLE alumni_skills (
    skill_id INT AUTO_INCREMENT PRIMARY KEY,
    alumni_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    skill_level ENUM('beginner', 'intermediate', 'advanced', 'expert') NOT NULL,
    skill_category VARCHAR(50) NULL,
    acquired_date DATE NULL,
    certified BOOLEAN DEFAULT FALSE,
    certificate_name VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (alumni_id) REFERENCES alumni(alumni_id) ON DELETE CASCADE,
    INDEX idx_alumni_id (alumni_id),
    INDEX idx_skill_name (skill_name),
    INDEX idx_skill_category (skill_category)
);