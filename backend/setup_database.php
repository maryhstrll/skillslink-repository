<?php
require_once 'config.php';

try {
    echo "Setting up SkillsLink database with new schema...\n";
    
    // Read and execute the SQL file
    $sql_file = __DIR__ . '/docs/skillslink.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: $sql_file");
    }
    
    $sql_content = file_get_contents($sql_file);
    
    // Split the SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql_content)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Continue if table already exists
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "✓ Database schema created successfully\n";
    
    // Insert default admin user if not exists
    $admin_username = 'admin';
    $admin_password = 'admin123'; // Change this in production!
    $admin_email = 'admin@skillslink.com';
    $admin_password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

    $check_admin = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
    $check_admin->execute([$admin_username]);
    
    if (!$check_admin->fetch()) {
        $insert_admin = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, email_verified, is_active) 
            VALUES (?, ?, ?, 'admin', TRUE, TRUE)
        ");
        $insert_admin->execute([$admin_username, $admin_email, $admin_password_hash]);
        echo "✓ Default admin user created\n";
        echo "  Username: admin\n";
        echo "  Password: admin123\n";
        echo "  Email: admin@skillslink.com\n";
    } else {
        echo "✓ Admin user already exists\n";
    }
    
    // Insert sample staff user
    $staff_username = 'staff';
    $staff_password = 'staff123';
    $staff_email = 'staff@skillslink.com';
    $staff_password_hash = password_hash($staff_password, PASSWORD_DEFAULT);

    $check_staff = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
    $check_staff->execute([$staff_username]);
    
    if (!$check_staff->fetch()) {
        $insert_staff = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, email_verified, is_active) 
            VALUES (?, ?, ?, 'staff', TRUE, TRUE)
        ");
        $insert_staff->execute([$staff_username, $staff_email, $staff_password_hash]);
        echo "✓ Default staff user created\n";
        echo "  Username: staff\n";
        echo "  Password: staff123\n";
        echo "  Email: staff@skillslink.com\n";
    } else {
        echo "✓ Staff user already exists\n";
    }
    
    // Insert sample programs
    $sample_programs = [
        ['CS101', 'Computer Science', 'Information Technology', 'degree', 4.0],
        ['IT101', 'Information Technology', 'Information Technology', 'degree', 4.0],
        ['SE101', 'Software Engineering', 'Information Technology', 'degree', 4.0]
    ];
    
    foreach ($sample_programs as $program) {
        $check_program = $pdo->prepare("SELECT program_id FROM programs WHERE program_code = ?");
        $check_program->execute([$program[0]]);
        
        if (!$check_program->fetch()) {
            $insert_program = $pdo->prepare("
                INSERT INTO programs (program_code, program_name, department, program_type, duration_years) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $insert_program->execute($program);
        }
    }
    echo "✓ Sample programs inserted\n";
    
    // Insert sample alumni user
    $alumni_username = 'alumni';
    $alumni_password = 'alumni123';
    $alumni_email = 'alumni@example.com';
    $alumni_password_hash = password_hash($alumni_password, PASSWORD_DEFAULT);

    $check_alumni_user = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
    $check_alumni_user->execute([$alumni_username]);
    
    if (!$check_alumni_user->fetch()) {
        $insert_alumni_user = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, email_verified, is_active) 
            VALUES (?, ?, ?, 'alumni', TRUE, TRUE)
        ");
        $insert_alumni_user->execute([$alumni_username, $alumni_email, $alumni_password_hash]);
        
        $alumni_user_id = $pdo->lastInsertId();
        
        // Get a program ID for the alumni
        $program_result = $pdo->query("SELECT program_id FROM programs LIMIT 1");
        $program = $program_result->fetch();
        
        if ($program) {
            // Insert alumni profile
            $insert_alumni_profile = $pdo->prepare("
                INSERT INTO alumni (
                    user_id, student_id, first_name, last_name, 
                    program_id, year_graduated, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, TRUE)
            ");
            $insert_alumni_profile->execute([
                $alumni_user_id, 
                'STU2023001', 
                'John', 
                'Doe', 
                $program['program_id'], 
                2023
            ]);
        }
        
        echo "✓ Sample alumni user created\n";
        echo "  Username: alumni\n";
        echo "  Password: alumni123\n";
        echo "  Email: alumni@example.com\n";
    } else {
        echo "✓ Alumni user already exists\n";
    }
    
    echo "\n=== Database Setup Complete! ===\n";
    echo "You can now login with these accounts:\n\n";
    echo "ADMIN ACCESS:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n\n";
    echo "STAFF ACCESS:\n";
    echo "Username: staff\n";
    echo "Password: staff123\n\n";
    echo "ALUMNI ACCESS:\n";
    echo "Username: alumni\n";
    echo "Password: alumni123\n\n";
    echo "⚠️  IMPORTANT: Change all default passwords in production!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
