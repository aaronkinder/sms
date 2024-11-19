<?php
// Start the session
session_start();

// Set REMOTE_ADDR for testing
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

include 'includes/config.php';
include 'logging.php';

log_message("Test unsubscribe process started", 'test_unsubscribe.log');

// Function to insert a test subscriber
function insert_test_subscriber($conn, $first_name, $last_name, $email, $phone, $ip_address) {
    $stmt = $conn->prepare("INSERT INTO subscribers (first_name, last_name, email, phone, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $ip_address);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Function to check if a subscriber exists
function subscriber_exists($conn, $identifier) {
    $query = "SELECT id FROM subscribers WHERE email = ? OR phone = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    log_message("Checking subscriber existence. Query: $query, Params: $identifier, Result: " . ($exists ? "exists" : "does not exist"), 'test_unsubscribe.log');
    return $exists;
}

// Function to check database contents
function check_database_contents($conn) {
    $query = "SELECT * FROM subscribers";
    $result = $conn->query($query);
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    log_message("Database contents: " . print_r($rows, true), 'test_unsubscribe.log');
}

// Test data
$test_first_name = 'John';
$test_last_name = 'Doe';
$test_email = 'test@example.com';
$test_phone = '+11234567890';
$test_ip_address = '127.0.0.1';

// Step 1: Insert test subscriber
echo "Inserting test subscriber...\n";
log_message("Inserting test subscriber", 'test_unsubscribe.log');
if (insert_test_subscriber($conn, $test_first_name, $test_last_name, $test_email, $test_phone, $test_ip_address)) {
    echo "Test subscriber inserted successfully.\n";
    log_message("Test subscriber inserted successfully", 'test_unsubscribe.log');
} else {
    echo "Failed to insert test subscriber: " . $conn->error . "\n";
    log_message("Failed to insert test subscriber: " . $conn->error, 'test_unsubscribe.log');
    exit;
}

// Step 2: Verify subscriber exists
echo "Verifying subscriber exists...\n";
log_message("Verifying subscriber exists", 'test_unsubscribe.log');
if (subscriber_exists($conn, $test_phone)) {
    echo "Subscriber exists in the database.\n";
    log_message("Subscriber exists in the database", 'test_unsubscribe.log');
} else {
    echo "Subscriber not found in the database.\n";
    log_message("Subscriber not found in the database", 'test_unsubscribe.log');
    exit;
}

// Step 3: Simulate unsubscribe process
echo "Simulating unsubscribe process...\n";
log_message("Simulating unsubscribe process", 'test_unsubscribe.log');

// First request: Get the CAPTCHA
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['bypass_cooldown'] = '1';
ob_start();
include 'unsubscribe.php';
ob_end_clean();

// Extract CAPTCHA answer from session
$captcha_answer = $_SESSION['captcha_answer'];
log_message("CAPTCHA answer: $captcha_answer", 'test_unsubscribe.log');

// Second request: Submit the form with CAPTCHA
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['bypass_cooldown'] = '1';
$_POST['identifier'] = '1234567890'; // Test with 10-digit number
$_POST['captcha'] = $captcha_answer;

ob_start();
include 'unsubscribe.php';
$output = ob_get_clean();
log_message("First POST request output: " . strip_tags($output), 'test_unsubscribe.log');

// Third request: Confirm unsubscription
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['bypass_cooldown'] = '1';
$_POST['identifier'] = '1234567890'; // Test with 10-digit number
$_POST['captcha'] = $captcha_answer;
$_POST['confirm_unsubscribe'] = '1';

ob_start();
include 'unsubscribe.php';
$output .= ob_get_clean();
log_message("Second POST request output: " . strip_tags($output), 'test_unsubscribe.log');

// Add a delay to ensure database operations are completed
sleep(2);

// Check database contents
check_database_contents($conn);

// Step 4: Verify subscriber was removed
echo "Verifying subscriber was removed...\n";
log_message("Verifying subscriber was removed", 'test_unsubscribe.log');
if (!subscriber_exists($conn, $test_phone)) {
    echo "Subscriber was successfully removed from the database.\n";
    log_message("Subscriber was successfully removed from the database", 'test_unsubscribe.log');
} else {
    echo "Failed to remove subscriber from the database.\n";
    log_message("Failed to remove subscriber from the database", 'test_unsubscribe.log');
}

// Output any messages from unsubscribe.php
echo "Unsubscribe process output:\n";
echo strip_tags($output) . "\n";

log_message("Test unsubscribe process completed", 'test_unsubscribe.log');

// Close the database connection
$conn->close();
?>
