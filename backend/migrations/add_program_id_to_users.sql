-- Add program_id column to users table for registration
-- This allows storing the program selected during alumni registration

ALTER TABLE users 
ADD COLUMN program_id INT NULL AFTER student_id,
ADD KEY `fk_users_program_id` (`program_id`);

-- Add foreign key constraint to programs table (if programs table exists)
-- Remove the comment below if you want to enforce referential integrity
-- ALTER TABLE users 
-- ADD CONSTRAINT `fk_users_program_id` 
-- FOREIGN KEY (`program_id`) REFERENCES `programs`(`program_id`) ON DELETE SET NULL;
