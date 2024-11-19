<?php
include 'includes/config.php';
session_start();

header('Content-Type: application/json');

function send_response($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit();
}

function check_admin_auth() {
    return isset($_SESSION['admin_authenticated']);
}

function log_error($message) {
    error_log(date('[Y-m-d H:i:s] ') . "Error: " . $message . "\n", 3, 'error.log');
}

$ip_address = $_SERVER['REMOTE_ADDR'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if (!check_admin_auth()) {
            send_response(false, "Unauthorized access");
        }
        if ($_POST['action'] == 'update') {
            $stmt = $conn->prepare("UPDATE subscribers SET first_name = ?, last_name = ?, phone = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $_POST['first_name'], $_POST['last_name'], $_POST['phone'], $_POST['email'], $_POST['id']);
            if ($stmt->execute()) {
                send_response(true, "Subscriber updated successfully.");
            } else {
                log_error("Error updating subscriber: " . $conn->error);
                send_response(false, "Error updating subscriber: " . $conn->error);
            }
        } elseif ($_POST['action'] == 'delete') {
            $stmt = $conn->prepare("DELETE FROM subscribers WHERE id = ?");
            $stmt->bind_param("i", $_POST['id']);
            if ($stmt->execute()) {
                send_response(true, "Subscriber deleted successfully.");
            } else {
                log_error("Error deleting subscriber: " . $conn->error);
                send_response(false, "Error deleting subscriber: " . $conn->error);
            }
        }
    } else {
        // Handle subscribe request
        $cooldown = check_submission_cooldown($ip_address);
        if ($cooldown > 0) {
            send_response(false, "Please wait {$cooldown} seconds before submitting again.");
        }

        if (!verify_math_captcha($_POST['captcha'])) {
            send_response(false, "CAPTCHA verification failed. Please try again.");
        }

        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $opt_in = isset($_POST['opt_in']) ? 1 : 0;

        $formatted_phone = validate_and_format_phone($phone);
        if (!$formatted_phone) {
            send_response(false, "Invalid phone number format. Please enter a 10-digit US phone number.");
        }

        if (!validate_email($email)) {
            send_response(false, "Invalid email address format.");
        }

        if ($opt_in) {
            $stmt = $conn->prepare("INSERT INTO subscribers (first_name, last_name, phone, email, ip_address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $first_name, $last_name, $formatted_phone, $email, $ip_address);

            if ($stmt->execute()) {
                update_submission_time($ip_address);
                send_response(true, "Thank you for subscribing to KRS1 marketing messages.");
                // Send confirmation message
                $message = "Thank you for subscribing to KRS1 marketing messages. Reply STOP to unsubscribe.";
                // Here you would integrate with your SMS gateway to send the message
                // For example: send_sms($formatted_phone, $message);
            } else {
                log_error("Error inserting subscriber: " . $stmt->error);
                send_response(false, "Error: " . $stmt->error);
            }
            $stmt->close();
        } else {
            send_response(false, "Please confirm that you agree to receive marketing information.");
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'get') {
    if (!check_admin_auth()) {
        log_error("Unauthorized access attempt for subscriber data");
        send_response(false, "Unauthorized access");
    }
    
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        log_error("Invalid or missing subscriber ID");
        send_response(false, "Invalid subscriber ID");
    }

    $stmt = $conn->prepare("SELECT * FROM subscribers WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    
    if (!$stmt->execute()) {
        log_error("Error executing subscriber fetch query: " . $stmt->error);
        send_response(false, "Error fetching subscriber data");
    }

    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        send_response(true, "Subscriber data fetched successfully", $row);
    } else {
        log_error("Subscriber not found for ID: " . $_GET['id']);
        send_response(false, "Subscriber not found");
    }
}

$conn->close();
?>
