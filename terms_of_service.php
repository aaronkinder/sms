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
    <title>Terms of Service - <?php echo htmlspecialchars($companyName); ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .terms-section {
            margin-bottom: 2rem;
        }
        .terms-section h2 {
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
            <h2><i class="fas fa-file-contract"></i> Terms of Service</h2>
            <div>
                <a href="privacy_policy.php" class="mb-2">
                    <i class="fas fa-user-shield"></i> Privacy Policy
                </a>
            </div>
        </div>

        <div class="policy-content mt-4">
            <h1>Terms of Service</h1>
            <p class="text-muted">Last Updated: November 7, 2024</p>

            <div class="terms-section">
                <p>This Agreement ("Terms") between you and <?php echo htmlspecialchars($companyName); ?> ("we," "us," "our") governs your use of our services, applications, and websites (the "Services"). By using our Services, you agree to these Terms and our Privacy Policy.</p>
            </div>

            <div class="terms-section important-notice">
                <h2>Messaging Consent</h2>
                <p>By providing your phone number, you expressly consent to receive messages, including marketing and promotional messages, from our 10DLC number. You may opt out at any time by replying STOP to any message. Message frequency varies. Message and data rates may apply.</p>
            </div>

            <div class="terms-section">
                <h2>Eligibility and Use</h2>
                <ul>
                    <li>You must be at least 18 years old to use our Services</li>
                    <li>You agree to use the Services only for lawful purposes</li>
                    <li>You are responsible for maintaining the confidentiality of your account</li>
                    <li>You must comply with all applicable laws and our usage policies</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>Privacy and Data Protection</h2>
                <ul>
                    <li>We protect your information as described in our Privacy Policy</li>
                    <li>We do not share your information with third parties for marketing purposes</li>
                    <li>Your opt-in data is only shared with service providers necessary to deliver our Services</li>
                    <li>We maintain appropriate security measures to protect your information</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>Service Terms</h2>
                <ul>
                    <li>Services are provided "as is" without warranties</li>
                    <li>We may modify the Services or these Terms with notice</li>
                    <li>Fees, if applicable, are specified at checkout and must be paid in advance</li>
                    <li>We reserve the right to refuse service or terminate accounts for violation of these Terms</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>Dispute Resolution</h2>
                <p>Any disputes will be resolved through binding arbitration in accordance with the American Arbitration Association rules. Class actions and class arbitrations are not permitted. You may opt out of arbitration within 30 days of accepting these Terms by notifying us in writing.</p>
            </div>

            <div class="terms-section">
                <h2>Intellectual Property</h2>
                <p>All content and intellectual property in the Services belongs to us or our licensors and is protected by applicable laws.</p>
            </div>

            <div class="terms-section">
                <h2>Termination</h2>
                <ul>
                    <li>You may terminate these Terms by discontinuing use of our Services</li>
                    <li>We may terminate or suspend your access for violations of these Terms</li>
                    <li>All applicable provisions survive termination</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>Contact Us</h2>
                <p>For questions about these Terms, please contact us through the information provided on our website.</p>
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