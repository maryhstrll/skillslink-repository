# SkillsLink Database Schema
- **users**: Stores admin/staff accounts (id, username, password_hash, role, email, created_at).
- **alumni**: Stores alumni details (id, name, email, phone, graduation_year, course, status, created_at).
- **employment_history**: Stores employment records (id, alumni_id, company, position, start_date, end_date).
- **form_responses**: Stores form submissions (id, alumni_id, response_data, submission_date).