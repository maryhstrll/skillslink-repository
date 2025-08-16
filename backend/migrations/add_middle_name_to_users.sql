-- Add middle_name column to users table
ALTER TABLE users 
ADD COLUMN middle_name VARCHAR(50) NULL AFTER last_name;
