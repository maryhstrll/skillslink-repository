-- Migration: Add user approval system
-- Add approval columns to users table

ALTER TABLE users 
ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending' AFTER role,
ADD COLUMN approved_by INT NULL AFTER approval_status,
ADD COLUMN approved_at TIMESTAMP NULL AFTER approved_by,
ADD COLUMN first_name VARCHAR(50) NULL AFTER approved_at,
ADD COLUMN last_name VARCHAR(50) NULL AFTER first_name,
ADD COLUMN student_id VARCHAR(50) NULL AFTER last_name;

-- Add foreign key for approved_by
ALTER TABLE users 
ADD CONSTRAINT fk_users_approved_by 
FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL;

-- Add index for approval_status
ALTER TABLE users ADD INDEX idx_approval_status (approval_status);

-- Set existing users as approved (except pending registrations)
UPDATE users SET approval_status = 'approved', approved_at = NOW() 
WHERE role IN ('admin', 'staff') OR created_at < NOW();

-- Update existing alumni to approved if they should be grandfathered in
-- UPDATE users SET approval_status = 'approved', approved_at = NOW() 
-- WHERE role = 'alumni' AND created_at < '2025-08-08 00:00:00';
