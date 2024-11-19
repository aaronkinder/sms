<?php
include 'includes/config.php';
session_start();

$companyName = getCompanyName();
$ip_address = $_SERVER['REMOTE_ADDR'];
$cooldown = check_submission_cooldown($ip_address);

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($cooldown > 0) {
        $message = "<div class='alert alert-warning'>Please wait {$cooldown} seconds before submitting again.</div>";
    } else {
        if (!verify_math_captcha($_POST['captcha'])) {
            $message = "<div class='alert alert-danger'>CAPTCHA verification failed. Please try again.</div>";
        } else {
            $first_name = sanitize_input($_POST['first_name']);
            $last_name = sanitize_input($_POST['last_name']);
            $phone = sanitize_input($_POST['phone']);
            $email = sanitize_input($_POST['email']);
            $notification_consent = isset($_POST['notification_consent']) ? 1 : 0;
            $marketing_consent = isset($_POST['marketing_consent']) ? 1 : 0;
            $opt_in = ($notification_consent || $marketing_consent) ? 1 : 0;

            $formatted_phone = validate_and_format_phone($phone);
            if (!$formatted_phone) {
                $message = "<div class='alert alert-danger'>Invalid phone number format. Please enter a 10-digit US phone number.</div>";
            } elseif (!validate_email($email)) {
                $message = "<div class='alert alert-danger'>Invalid email address format.</div>";
            } elseif ($opt_in) {
                // Check if max submissions per IP has been reached
                $stmt = $conn->prepare("SELECT COUNT(*) FROM subscribers WHERE ip_address = ?");
                $stmt->bind_param("s", $ip_address);
                $stmt->execute();
                $result = $stmt->get_result();
                $count = $result->fetch_row()[0];
                $stmt->close();

                if ($count >= $max_submissions_per_ip) {
                    $message = "<div class='alert alert-danger'>Maximum number of submissions reached for this IP address.</div>";
                } else {
                    $stmt = $conn->prepare("INSERT INTO subscribers (first_name, last_name, phone, email, ip_address) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $first_name, $last_name, $formatted_phone, $email, $ip_address);

                    if ($stmt->execute()) {
                        $message = "<div class='alert alert-success'>Thank you for subscribing to " . htmlspecialchars($companyName) . " messages.</div>";
                        update_submission_time($ip_address);
                    } else {
                        $message = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
                    }
                    $stmt->close();
                }
            } else {
                $message = "<div class='alert alert-danger'>Please select at least one type of consent to continue.</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe to <?php echo htmlspecialchars($companyName); ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .consent-box {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }
        .consent-header {
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #dee2e6;
        }
        .consent-icon {
            font-size: 1.2em;
            margin-right: 8px;
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
        <div class="legal-notice">
            <h2><i class="fas fa-shield-alt"></i> Important Legal Information</h2>
            <div>
                <a href="privacy_policy.php" class="mb-2">
                    <i class="fas fa-user-shield"></i> Privacy Policy
                </a>
                <a href="terms_of_service.php" class="mb-2">
                    <i class="fas fa-file-contract"></i> Terms of Service
                </a>
            </div>
            <p class="mt-3 mb-0 text-muted">Please review our Privacy Policy and Terms of Service before subscribing</p>
        </div>

        <h1 class="mb-4">Subscribe to <?php echo htmlspecialchars($companyName); ?></h1>
        <?php echo $message; ?>
        <form id="subscriptionForm" method="post" class="unsubscribe-form">
            <div class="unsubscribe-content">
                <div class="form-group">
                    <label for="first_name"><i class="fas fa-user"></i> First Name:</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Enter your first name as it appears on official documents
                    </small>
                </div>

                <div class="form-group">
                    <label for="last_name"><i class="fas fa-user"></i> Last Name:</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Enter your last name as it appears on official documents
                    </small>
                </div>

                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Mobile Phone Number:</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">+1</span>
                        </div>
                        <input type="tel" 
                               class="form-control phone-input" 
                               id="phone" 
                               name="phone"
                               pattern="[0-9]{10}"
                               maxlength="10"
                               placeholder="Enter 10 digit number"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               required>
                    </div>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Enter your 10-digit US phone number without spaces or special characters (e.g., 1234567890)
                    </small>
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email Address:</label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email"
                           placeholder="Enter your email address"
                           required>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Enter a valid email address where you can receive updates
                    </small>
                </div>

                <div class="legal-agreement">
                    <div class="consent-box">
                        <div class="consent-header">
                            <i class="fas fa-bell consent-icon text-primary"></i>
                            Service Updates & Important Notifications
                        </div>
                        <div class="form-group form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="notification_consent" name="notification_consent">
                            <label class="form-check-label" for="notification_consent">
                                By clicking this box, you are authorizing us to send you text messages and notifications including service updates and important announcements. Message and data rates apply. Reply STOP to unsubscribe to a message sent from us. Reply HELP to get guidance.
                            </label>
                        </div>
                    </div>

                    <div class="consent-box">
                        <div class="consent-header">
                            <i class="fas fa-bullhorn consent-icon text-success"></i>
                            Marketing & Promotional Messages
                        </div>
                        <div class="form-group form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="marketing_consent" name="marketing_consent">
                            <label class="form-check-label" for="marketing_consent">
                                By clicking this box, you are authorizing us to send you text messages and notifications including promotional and marketing messages. Message and data rates apply. Reply STOP to unsubscribe to a message sent from us. Reply HELP to get guidance.
                            </label>
                        </div>
                    </div>

                    <div class="form-text">
                        <i class="fas fa-info-circle"></i> By subscribing, you acknowledge that you have read and agree to our 
                        <a href="privacy_policy.php" target="_blank">Privacy Policy</a> and 
                        <a href="terms_of_service.php" target="_blank">Terms of Service</a>.
                    </div>
                </div>

                <div class="form-group mt-4">
                    <label for="captcha"><i class="fas fa-robot"></i> CAPTCHA: <?php echo generate_math_captcha(); ?></label>
                    <input type="number" class="form-control" id="captcha" name="captcha" required>
                </div>

                <button type="submit" class="btn btn-success btn-lg btn-block mt-4">
                    <i class="fas fa-user-plus"></i> Subscribe
                </button>
            </div>
        </form>
        <p class="mt-3">Already subscribed? <a href="unsubscribe.php"><i class="fas fa-user-minus"></i> Unsubscribe here</a>.</p>
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
</body>
</html>
