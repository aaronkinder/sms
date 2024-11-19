<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'includes/config.php';
include 'logging.php';
include 'includes/unsubscribe_validation.php';

$companyName = getCompanyName();
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

$message = '';
$show_confirmation = false;
$identifier = '';
$type = '';

log_message("Unsubscribe process started", 'unsubscribe.log');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    log_message("POST request received", 'unsubscribe.log');
    
    // Validate cooldown
    $cooldown_validation = validateCooldown($ip_address);
    if (!$cooldown_validation['is_valid']) {
        $message = $cooldown_validation['message'];
    } else {
        // Validate CAPTCHA
        $captcha_validation = validateCaptcha($_POST['captcha'] ?? '');
        if (!$captcha_validation['is_valid']) {
            $message = $captcha_validation['message'];
        } else {
            $identifier = sanitize_input($_POST['identifier'] ?? '');
            $type = $_POST['type'] ?? '';
            
            log_message("Processing {$type}: {$identifier}", 'unsubscribe.log');
            
            // Validate identifier based on type
            $validation_result = validateUnsubscribeRequest($identifier, $type);
            if ($validation_result['is_valid']) {
                $identifier = $validation_result['formatted_identifier'];
                log_message("Valid $type received: $identifier", 'unsubscribe.log');
                
                if (isset($_POST['confirm_unsubscribe'])) {
                    log_message("Unsubscribe confirmation received", 'unsubscribe.log');
                    // Start transaction
                    $conn->begin_transaction();
                    log_message("Transaction started", 'unsubscribe.log');

                    try {
                        // Check if the record exists
                        if ($type === 'phone') {
                            // Only check for the +1 format since we've standardized the input
                            $check_stmt = $conn->prepare("SELECT id, email, phone FROM subscribers WHERE phone = ?");
                            $check_stmt->bind_param("s", $identifier);  // $identifier already has +1 prefix
                        } else {
                            $check_stmt = $conn->prepare("SELECT id, email, phone FROM subscribers WHERE email = ?");
                            $check_stmt->bind_param("s", $identifier);
                        }
                        
                        if (!$check_stmt) {
                            throw new Exception("Error preparing check statement: " . $conn->error);
                        }
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();
                        
                        if ($check_result->num_rows > 0) {
                            $subscriber = $check_result->fetch_assoc();
                            log_message("Subscriber found with ID: " . $subscriber['id'], 'unsubscribe.log');
                            // Record exists, proceed with deletion
                            $delete_stmt = $conn->prepare("DELETE FROM subscribers WHERE id = ?");
                            if (!$delete_stmt) {
                                throw new Exception("Error preparing delete statement: " . $delete_stmt->error);
                            }
                            $delete_stmt->bind_param("i", $subscriber['id']);
                            if (!$delete_stmt->execute()) {
                                throw new Exception("Error executing delete statement: " . $delete_stmt->error);
                            }
                            $affected_rows = $delete_stmt->affected_rows;
                            $delete_stmt->close();

                            if ($affected_rows > 0) {
                                $message = "<div class='alert alert-success'>You have been successfully unsubscribed.</div>";
                                update_submission_time($ip_address);
                                log_message("Subscriber successfully unsubscribed. Affected rows: $affected_rows", 'unsubscribe.log');
                            } else {
                                throw new Exception("No records were deleted. This might be due to a database error.");
                            }
                        } else {
                            $message = "<div class='alert alert-warning'>No matching record found for unsubscription.</div>";
                            log_message("No matching record found for unsubscription", 'unsubscribe.log');
                        }
                        $check_stmt->close();

                        // Commit transaction
                        $conn->commit();
                        log_message("Transaction committed", 'unsubscribe.log');
                    } catch (Exception $e) {
                        // Rollback transaction on error
                        $conn->rollback();
                        log_message("Transaction rolled back due to error: " . $e->getMessage(), 'unsubscribe.log');
                        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
                    }
                } else {
                    $show_confirmation = true;
                    log_message("Showing confirmation page", 'unsubscribe.log');
                }
            } else {
                $message = $validation_result['message'];
            }
        }
    }
}

log_message("Unsubscribe process completed", 'unsubscribe.log');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe from <?php echo htmlspecialchars($companyName); ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .unsubscribe-tab {
            background: none;
            border: none;
            padding: 10px 20px;
            margin: 0 5px;
            opacity: 0.7;
            cursor: pointer;
        }
        .unsubscribe-tab.active {
            opacity: 1;
            border-bottom: 2px solid #007bff;
        }
        .submit-button {
            margin-top: 20px;
            margin-bottom: 20px;
            width: 100%;
            padding: 15px;
        }
        .captcha-section {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .invalid-feedback {
            display: none;
        }
        .form-control.is-invalid ~ .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <i class="fas fa-bullhorn"></i>
            <?php echo htmlspecialchars($companyName); ?>
        </a>
        <ul class="nav-links">
            <li><a href="index.php" class="nav-home"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="subscribe.php" class="nav-subscribe"><i class="fas fa-user-plus"></i> Subscribe</a></li>
            <li><a href="unsubscribe.php" class="nav-unsubscribe"><i class="fas fa-user-minus"></i> Unsubscribe</a></li>
            <?php if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']): ?>
            <li><a href="admin.php" class="nav-admin"><i class="fas fa-user-shield"></i> Admin</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="container mt-5">
        <div class="legal-notice mb-4">
            <h2><i class="fas fa-shield-alt"></i> Important Legal Information</h2>
            <div>
                <a href="privacy_policy.php" class="mb-2">
                    <i class="fas fa-user-shield"></i> Privacy Policy
                </a>
                <a href="terms_of_service.php" class="mb-2">
                    <i class="fas fa-file-contract"></i> Terms of Service
                </a>
            </div>
            <p class="mt-3 mb-0 text-muted">Our Privacy Policy and Terms of Service remain in effect during the unsubscribe process</p>
        </div>

        <h1 class="mb-4">Unsubscribe from <?php echo htmlspecialchars($companyName); ?></h1>
        <?php echo $message; ?>
        
        <?php if (!$show_confirmation): ?>
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="unsubscribe-form">
                    <form id="unsubscribeForm" method="post" novalidate>
                        <div class="unsubscribe-tabs mb-4">
                            <button type="button" class="unsubscribe-tab active" data-tab="phone">
                                <i class="fas fa-phone"></i>
                                Phone Number
                            </button>
                            <button type="button" class="unsubscribe-tab" data-tab="email">
                                <i class="fas fa-envelope"></i>
                                Email Address
                            </button>
                        </div>

                        <div class="unsubscribe-content">
                            <!-- Phone Number Input -->
                            <div id="phoneInput" class="tab-content active">
                                <div class="form-group">
                                    <label for="phone"><i class="fas fa-phone"></i> Phone Number:</label>
                                    <input type="tel" 
                                           class="form-control phone-input" 
                                           id="phone" 
                                           name="identifier"
                                           placeholder="Enter phone number"
                                           required>
                                    <div id="phoneError" class="invalid-feedback">
                                        Please enter a valid US phone number
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Enter your US phone number (e.g., 3135054496 or +13135054496)
                                    </small>
                                </div>
                            </div>

                            <!-- Email Input -->
                            <div id="emailInput" class="tab-content">
                                <div class="form-group">
                                    <label for="email"><i class="fas fa-envelope"></i> Email Address:</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="identifier"
                                           placeholder="Enter your email address">
                                    <div id="emailError" class="invalid-feedback">
                                        Please enter a valid email address
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Enter the email address you used to subscribe
                                    </small>
                                </div>
                            </div>

                            <input type="hidden" name="type" id="selectedType" value="phone">

                            <div class="legal-agreement mt-4">
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> By unsubscribing, you acknowledge that this action is governed by our 
                                    <a href="privacy_policy.php" target="_blank">Privacy Policy</a> and 
                                    <a href="terms_of_service.php" target="_blank">Terms of Service</a>.
                                </div>
                            </div>

                            <div class="captcha-section">
                                <label for="captcha">CAPTCHA: <?php echo generate_math_captcha(); ?></label>
                                <input type="number" class="form-control" id="captcha" name="captcha" required>
                                <div id="captchaError" class="invalid-feedback">
                                    Please solve the CAPTCHA
                                </div>
                            </div>

                            <button type="submit" class="btn btn-danger btn-lg submit-button">
                                <i class="fas fa-user-minus"></i> Unsubscribe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="unsubscribe-form">
                    <div class="unsubscribe-content">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Are you sure you want to unsubscribe <?php echo htmlspecialchars($identifier); ?> from <?php echo htmlspecialchars($companyName); ?> marketing messages?
                        </div>
                        <form id="confirmUnsubscribeForm" method="post">
                            <input type="hidden" name="identifier" value="<?php echo htmlspecialchars($identifier); ?>">
                            <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
                            <input type="hidden" name="captcha" value="<?php echo $_POST['captcha']; ?>">
                            <input type="hidden" name="confirm_unsubscribe" value="1">
                            <div class="legal-agreement mb-4">
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> This action is final and governed by our 
                                    <a href="privacy_policy.php" target="_blank">Privacy Policy</a> and 
                                    <a href="terms_of_service.php" target="_blank">Terms of Service</a>.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-danger btn-lg submit-button mb-3">
                                <i class="fas fa-check"></i> Confirm Unsubscribe
                            </button>
                            <a href="unsubscribe.php" class="btn btn-secondary btn-lg d-block">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <p class="mt-3 text-center">Not yet subscribed? <a href="subscribe.php"><i class="fas fa-user-plus"></i> Subscribe here</a>.</p>
    </div>

    <footer class="footer mt-auto py-3">
        <div class="container">
            <div class="row">
                <div class="col text-center">
                    <a href="privacy_policy.php" class="text-muted me-3">Privacy Policy</a>
                    <a href="terms_of_service.php" class="text-muted">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/unsubscribe-validation.js"></script>
</body>
</html>
