-- Add selected_employment_questions column to tracer_forms table
-- This will store which employment questions are selected for each form
ALTER TABLE tracer_forms 
ADD COLUMN selected_employment_questions JSON NULL AFTER form_questions;

-- Update existing forms to have default employment questions selected
UPDATE tracer_forms 
SET selected_employment_questions = '["employment_status", "company_name", "job_title", "salary_range", "employment_type"]' 
WHERE selected_employment_questions IS NULL;
