<?php
include 'includes/config.php';
session_start();
$companyName = getCompanyName();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - <?php echo htmlspecialchars($companyName); ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .policy-section {
            margin-bottom: 2rem;
        }
        .policy-section h2 {
            color: #333;
            margin-bottom: 1rem;
        }
        .important-notice {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 15px 0;
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
            <h2><i class="fas fa-shield-alt"></i> Privacy Policy</h2>
            <div>
                <a href="terms_of_service.php" class="mb-2">
                    <i class="fas fa-file-contract"></i> Terms of Service
                </a>
            </div>
        </div>

        <div class="policy-content mt-4">
            <h1>Privacy Policy</h1>
            <p class="text-muted">Last Updated: November 7, 2024</p>

            <div class="policy-section">
                <p>This Privacy Policy explains how <?php echo htmlspecialchars($companyName); ?> ("we," "us," "our") collects, uses, and protects your information when you use our services, applications, and websites (collectively, the "Services").</p>
            </div>

            <div class="policy-section important-notice">
                <h2>How End Users Consent to Receive Messages</h2>
                <p>End users provide explicit consent to receive messages, including promotional and marketing communications, by entering their phone number and opting in through our designated mechanisms. This Privacy Policy and our Terms and Conditions are always available next to any phone number entry field and on our website.</p>
            </div>

            <div class="policy-section">
                <h2>Data Protection Commitment</h2>
                <p>We are committed to protecting your personal information. Important points about our data practices:</p>
                <ul>
                    <li><strong>No Third-Party Sharing:</strong> We do not share, sell, or disclose end-user information to third parties or affiliates for marketing, promotional, lead generation, or analytics purposes.</li>
                    <li><strong>Limited Data Usage:</strong> Your information is used solely to provide our Services and communicate with you as requested.</li>
                    <li><strong>Service Provider Access:</strong> End-user opt-in data may only be shared with service providers as necessary to deliver our Services and fulfill your requests.</li>
                </ul>
            </div>

            <div class="policy-section">
                <h2>Information We Collect</h2>
                <p>We collect only the information necessary to provide our Services:</p>
                <ul>
                    <li>Contact information (name, phone number, email)</li>
                    <li>Service usage information</li>
                    <li>Device data necessary for service delivery</li>
                </ul>
            </div>

            <div class="policy-section">
                <h2>Your Rights</h2>
                <p>You may:</p>
                <ul>
                    <li>Opt out of messages by replying STOP to our messages</li>
                    <li>Request access to your personal information</li>
                    <li>Request deletion of your information</li>
                    <li>Contact us with privacy questions or concerns</li>
                </ul>
            </div>

            <div class="policy-section">
                <h2>Children's Privacy</h2>
                <p>Our Services are not directed at children under 18. We do not knowingly collect information from minors.</p>
            </div>

            <div class="policy-section">
                <h2>Updates to Privacy Policy</h2>
                <p>We may update this Privacy Policy periodically. Changes will be posted here with an updated date.</p>
            </div>

            <div class="policy-section">
                <h2>Contact Us</h2>
                <p>For privacy-related questions, please contact us through the information provided on our website.</p>
            </div>

            <a href="index.php" class="btn btn-primary mt-3">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
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