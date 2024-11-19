<?php
function validateUnsubscribeRequest($identifier, $type) {
    $result = [
        'is_valid' => false,
        'message' => '',
        'formatted_identifier' => ''
    ];

    if ($type === 'phone') {
        // Remove all non-numeric characters except plus
        $cleaned_number = preg_replace('/[^0-9+]/', '', $identifier);
        log_message("Cleaned phone number: {$cleaned_number}", 'unsubscribe.log');
        
        // If number starts with +1, keep it, otherwise add it
        if (preg_match('/^\+1[0-9]{10}$/', $cleaned_number)) {
            // Number is already in correct format
            $result['is_valid'] = true;
            $result['formatted_identifier'] = $cleaned_number;
        } else if (preg_match('/^1?([0-9]{10})$/', $cleaned_number, $matches)) {
            // Number is either 10 digits or 1 + 10 digits, format it properly
            $result['is_valid'] = true;
            $result['formatted_identifier'] = '+1' . $matches[1];
        } else {
            $result['message'] = "<div class='alert alert-danger'>Please enter a valid US phone number (10 digits).</div>";
            log_message("Invalid phone number format: " . $identifier, 'unsubscribe.log');
        }
        
        if ($result['is_valid']) {
            log_message("Formatted phone number: {$result['formatted_identifier']}", 'unsubscribe.log');
        }
    } else if ($type === 'email') {
        // Use the existing validate_email function from config.php
        if (validate_email($identifier)) {
            $result['is_valid'] = true;
            $result['formatted_identifier'] = $identifier;
            log_message("Valid email format", 'unsubscribe.log');
        } else {
            $result['message'] = "<div class='alert alert-danger'>Please enter a valid email address.</div>";
            log_message("Invalid email format", 'unsubscribe.log');
        }
    }

    return $result;
}

function validateCaptcha($captcha) {
    if (!verify_math_captcha($captcha)) {
        log_message("CAPTCHA verification failed", 'unsubscribe.log');
        return [
            'is_valid' => false,
            'message' => "<div class='alert alert-danger'>CAPTCHA verification failed. Please try again.</div>"
        ];
    }
    return ['is_valid' => true, 'message' => ''];
}

function validateCooldown($ip_address) {
    $cooldown = check_submission_cooldown($ip_address);
    if ($cooldown > 0) {
        log_message("Cooldown active: $cooldown seconds remaining", 'unsubscribe.log');
        return [
            'is_valid' => false,
            'message' => "<div class='alert alert-warning'>Please wait {$cooldown} seconds before submitting again.</div>"
        ];
    }
    return ['is_valid' => true, 'message' => ''];
}
