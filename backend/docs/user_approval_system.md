# User Approval System Implementation

## Overview
This implementation adds a user approval system where newly registered users need admin confirmation before they can access the system.

## Changes Made

### Database Changes
- Added `approval_status` column to users table (pending, approved, rejected)
- Added `approved_by` column to track which admin approved the user
- Added `approved_at` timestamp for approval date
- Added foreign key constraint and index for approval status

### Backend Changes
1. **register.php**: Updated to inform users about pending approval status
2. **login.php**: Added approval status check - prevents unapproved users from logging in
3. **session.php**: Includes approval status in session data
4. **user_approvals.php**: New admin endpoint for managing user approvals

### Frontend Changes
1. **RegisterModal.vue**: Updated to show approval pending message
2. **UserApprovalManager.vue**: New component for admins to approve/reject users
3. **Users.vue**: Integrated approval manager for admin users
4. **userApproval.js**: New service for approval API calls

## How It Works

### For New Users
1. User registers through the registration form
2. Account is created with `approval_status = 'pending'`
3. User receives message about pending approval
4. User cannot login until approved by admin

### For Admins
1. Admin can see pending users in the Users management page
2. Admin can approve or reject users with one click
3. Approved users can immediately login
4. Rejected users cannot login

## Migration
Run the migration script to add approval columns to existing database:
```bash
php backend/migrate_user_approval.php
```

## Configuration
- Default approval status for new registrations: 'pending'
- Existing users are automatically set to 'approved' during migration
- Admin and staff users are automatically approved

## API Endpoints

### GET /admin/user_approvals.php
Returns list of pending users for approval

### POST /admin/user_approvals.php
Approve or reject a user
```json
{
  "user_id": 123,
  "action": "approve" // or "reject"
}
```

## Security Notes
- Only admins can access the approval endpoints
- User sessions include approval status validation
- Foreign key constraints ensure data integrity
- Approval tracking for audit purposes
