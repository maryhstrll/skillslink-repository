-- Migration: Fix Tracer Form Multiple Responses Issue
-- This migration allows multiple tracer forms per year and ensures alumni can respond to each form separately

-- 1. Drop the current unique constraint that prevents multiple responses per year
ALTER TABLE employment_records DROP INDEX unique_alumni_form_year;

-- 2. Add a new unique constraint that allows multiple forms per year but only one response per form
ALTER TABLE employment_records ADD CONSTRAINT unique_alumni_tracer_form UNIQUE (alumni_id, tracer_form_id);

-- 3. Update the tracer_form_id column to be NOT NULL since it's now part of the unique key
-- First, set NULL values to 0 (we'll handle this in the application)
UPDATE employment_records SET tracer_form_id = 0 WHERE tracer_form_id IS NULL;

-- 4. Make tracer_form_id NOT NULL
ALTER TABLE employment_records MODIFY COLUMN tracer_form_id INT NOT NULL;

-- 5. Similarly, update form_responses table to use form_id properly 
-- (this table already has the correct constraint: unique_form_alumni (form_id, alumni_id))

-- Note: This migration will allow:
-- - Multiple tracer forms in the same year
-- - Alumni can respond to each tracer form separately
-- - Each alumni can only respond once per specific tracer form
