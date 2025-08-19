-- Update employment_records table for hybrid tracer form system
-- Fix syntax errors and add missing columns

-- First, let's recreate the employment_records table with proper structure
DROP TABLE IF EXISTS employment_records;

CREATE TABLE employment_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    alumni_id INT NOT NULL,
    form_year YEAR NOT NULL,
    tracer_form_id INT NULL,
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
    months_to_find_job INT NULL,
    job_search_method VARCHAR(100) NULL,
    additional_comments TEXT NULL,
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (alumni_id) REFERENCES alumni(alumni_id) ON DELETE CASCADE,
    FOREIGN KEY (tracer_form_id) REFERENCES tracer_forms(form_id) ON DELETE SET NULL,
    UNIQUE KEY unique_alumni_form_year (alumni_id, form_year),
    INDEX idx_alumni_id (alumni_id),
    INDEX idx_form_year (form_year),
    INDEX idx_employment_status (employment_status),
    INDEX idx_current_job (is_current_job),
    INDEX idx_tracer_form (tracer_form_id)
);
