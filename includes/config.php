<?php
    $db_host = 'localhost';
    $db_name = 'sms';
    $db_user = 'root';
    $db_pass = '';

    // Security settings
    $max_submissions_per_ip = 5;
    $submission_cooldown = 60;

    // Create connection
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");

    // Function to get company name based on current domain
    function getCompanyName() {
        global $conn;
        $current_domain = $_SERVER['HTTP_HOST'];
        
        // First try to get company name and DBA from domain mappings
        $stmt = $conn->prepare("SELECT company_name, dba_name FROM domain_mappings WHERE domain_name = ?");
        $stmt->bind_param("s", $current_domain);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // If DBA exists, append it to company name
            if (!empty($row['dba_name'])) {
                return $row['company_name'] . ' dba ' . $row['dba_name'];
            }
            return $row['company_name'];
        }
        
        // Fallback to settings table if no domain mapping found
        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_name = 'CompanyName'");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['setting_value'];
        }
        
        return 'Company Name'; // Default fallback
    }

    // Function to sanitize user input
    function sanitize_input($input) {
        global $conn;
        return $conn->real_escape_string(strip_tags(trim($input)));
    }

    // Function to validate email
    function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Function to validate and format US phone number
    function validate_and_format_phone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 10) {
            return '+1' . $phone;
        }
        return false;
    }

    // Function to generate a simple math CAPTCHA
    function generate_math_captcha() {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $answer = $num1 + $num2;
        $_SESSION['captcha_answer'] = $answer;
        return "$num1 + $num2 = ?";
    }

    // Function to verify math CAPTCHA
    function verify_math_captcha($user_answer) {
        return isset($_SESSION['captcha_answer']) && intval($user_answer) === $_SESSION['captcha_answer'];
    }

    // Function to check submission cooldown
    function check_submission_cooldown($ip_address) {
        global $conn, $submission_cooldown;
        $stmt = $conn->prepare("SELECT last_submission_time FROM rate_limit WHERE ip_address = ?");
        $stmt->bind_param("s", $ip_address);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $time_since_last_submission = time() - $row['last_submission_time'];
            if ($time_since_last_submission < $submission_cooldown) {
                return $submission_cooldown - $time_since_last_submission;
            }
        }
        return 0;
    }

    // Function to update last submission time
    function update_submission_time($ip_address) {
        global $conn;
        $current_time = time();
        $stmt = $conn->prepare("INSERT INTO rate_limit (ip_address, last_submission_time) VALUES (?, ?) ON DUPLICATE KEY UPDATE last_submission_time = ?");
        $stmt->bind_param("sii", $ip_address, $current_time, $current_time);
        $stmt->execute();
    }
    ?>