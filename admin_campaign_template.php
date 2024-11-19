<?php
include 'includes/config.php';
session_start();

// Check if the user is authenticated
if (!isset($_SESSION['admin_authenticated'])) {
    header('Location: admin.php');
    exit();
}

// Get company and DBA names separately
$current_domain = $_SERVER['HTTP_HOST'];
$stmt = $conn->prepare("SELECT company_name, dba_name FROM domain_mappings WHERE domain_name = ?");
$stmt->bind_param("s", $current_domain);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $companyName = $row['company_name'];
    $dbaName = $row['dba_name'];
} else {
    // Fallback to settings table if no domain mapping found
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_name = 'CompanyName'");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $companyName = $row['setting_value'];
        $dbaName = $companyName; // Default DBA to company name if not found
    } else {
        $companyName = 'Company Name'; // Default fallback
        $dbaName = 'Company Name'; // Default fallback
    }
}

// Get current date and time
$currentDate = date('l, F j, Y');
$currentTime = date('g:i A');

// Get the current domain
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$fullDomain = $_SERVER['HTTP_HOST'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A2P 10DLC Campaign Template</title>
    <link rel="icon" href="data:;base64,iVBORw0KGgo=">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .input-group-append .btn-copy {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .copy-feedback {
            position: absolute;
            right: -80px;
            top: 50%;
            transform: translateY(-50%);
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .copy-feedback.show {
            opacity: 1;
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>A2P 10DLC Campaign Template</h2>
            <a href="admin.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Admin</a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h3 class="card-title">Campaign Information</h3>
                <form id="campaignForm">
                    <div class="form-group">
                        <label for="companyName">Company Name:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="companyName" name="companyName" value="<?php echo htmlspecialchars($companyName); ?>" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy" type="button" onclick="copyToClipboard('companyName')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="dbaName">DBA Company Name:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dbaName" name="dbaName" value="<?php echo htmlspecialchars($dbaName); ?>" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy" type="button" onclick="copyToClipboard('dbaName')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="domain">Domain:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="domain" name="domain" value="<?php echo htmlspecialchars($fullDomain); ?>" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy" type="button" onclick="copyToClipboard('domain')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>

                    <h4 class="mt-4">Message Samples</h4>
                    <div class="form-group">
                        <label for="sample1">Message Sample #1 (Appointment Confirmation):</label>
                        <div class="input-group">
                            <textarea class="form-control" id="sample1" name="sample1" rows="3" readonly>David, it's Kelly from [DBA Company Name]. Thanks for opting in to receive SMS notifications. I just saved a time for you on <?php echo $currentDate; ?>, at <?php echo $currentTime; ?>, and I'll see you then! If anything changes, just let me know. If you need to opt out, reply STOP.</textarea>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy h-100" type="button" onclick="copyToClipboard('sample1')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="sample2">Message Sample #2 (Promotional Offer):</label>
                        <div class="input-group">
                            <textarea class="form-control" id="sample2" name="sample2" rows="3" readonly>David, it's Kelly from [DBA Company Name]. Thanks for opting in to receive messages. Today, we are giving out a few vouchers to our past clients for a free cyber security scan. Would you like one? If you need to opt out, reply STOP.</textarea>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy h-100" type="button" onclick="copyToClipboard('sample2')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="sample3">Message Sample #3 (Consultation Offer):</label>
                        <div class="input-group">
                            <textarea class="form-control" id="sample3" name="sample3" rows="3" readonly>David, it's Kelly from [DBA Company Name]. Thanks for opting in to receive our messages. We're offering a free consultation to help optimize your business. Would you like to schedule a time? If you need to opt out, reply STOP.</textarea>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy h-100" type="button" onclick="copyToClipboard('sample3')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="sample4">Message Sample #4 (Security Update):</label>
                        <div class="input-group">
                            <textarea class="form-control" id="sample4" name="sample4" rows="3" readonly>David, it's Kelly from [DBA Company Name]. Thanks for subscribing to our updates! We've noticed an increase in cyber threats this month. Would you like a free guide on how to protect your business? If you need to opt out, reply STOP.</textarea>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy h-100" type="button" onclick="copyToClipboard('sample4')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="sample5">Message Sample #5 (New Service Announcement):</label>
                        <div class="input-group">
                            <textarea class="form-control" id="sample5" name="sample5" rows="3" readonly>David, it's Kelly from [DBA Company Name]. Thanks for opting in to receive SMS alerts! We just launched a new service for automated backup solutions. Can I send you more info? If you need to opt out, reply STOP.</textarea>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy h-100" type="button" onclick="copyToClipboard('sample5')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>

                    <h4 class="mt-4">Generated URLs</h4>
                    <div class="form-group">
                        <label>Privacy Policy URL:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="privacyUrl" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy" type="button" onclick="copyToClipboard('privacyUrl')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Terms of Service URL:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="tosUrl" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy" type="button" onclick="copyToClipboard('tosUrl')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Subscribe/Unsubscribe Options URL:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="optionsUrl" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy" type="button" onclick="copyToClipboard('optionsUrl')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Subscribe URL:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="subscribeUrl" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy" type="button" onclick="copyToClipboard('subscribeUrl')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Unsubscribe URL:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="unsubscribeUrl" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy" type="button" onclick="copyToClipboard('unsubscribeUrl')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>

                    <h4 class="mt-4">End User Consent Information</h4>
                    <div class="form-group">
                        <label>Opt-out Message:</label>
                        <div class="input-group">
                            <textarea class="form-control" id="optOutMessage" rows="2" readonly>You have successfully been unsubscribed. You will not receive any more messages from this number. Reply START to resubscribe.</textarea>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy h-100" type="button" onclick="copyToClipboard('optOutMessage')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Opt-out Keywords:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="optOutKeywords" value="CANCEL, END, QUIT, UNSUBSCRIBE, STOP, STOPALL" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy" type="button" onclick="copyToClipboard('optOutKeywords')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Help Message:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="helpMessage" value="Reply STOP to unsubscribe. Msg&Data Rates May Apply." readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy" type="button" onclick="copyToClipboard('helpMessage')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Help Keywords:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="helpKeywords" value="HELP, INFO" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy" type="button" onclick="copyToClipboard('helpKeywords')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Generate Template</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Call updateUrls on page load to initialize URLs
        $(document).ready(function() {
            updateUrls();
            updateMessageSamples();
        });

        function updateUrls() {
            const domain = $('#domain').val();
            if (domain) {
                $('#privacyUrl').val(`https://${domain}/privacy_policy.php`);
                $('#tosUrl').val(`https://${domain}/terms_of_service.php`);
                $('#optionsUrl').val(`https://${domain}/index.php`);
                $('#subscribeUrl').val(`https://${domain}/subscribe.php`);
                $('#unsubscribeUrl').val(`https://${domain}/unsubscribe.php`);
            }
        }

        function updateMessageSamples() {
            const dbaName = $('#dbaName').val();
            const samples = document.querySelectorAll('textarea[id^="sample"]');
            samples.forEach(sample => {
                sample.value = sample.value.replace('[DBA Company Name]', dbaName);
            });
        }

        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const feedbackElement = element.parentElement.querySelector('.copy-feedback');
            
            // Create a temporary textarea for copying if the element is not an input/textarea
            if (element.tagName.toLowerCase() !== 'textarea' && element.tagName.toLowerCase() !== 'input') {
                const textarea = document.createElement('textarea');
                textarea.value = element.textContent;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
            } else {
                element.select();
                document.execCommand('copy');
            }
            
            // Show feedback
            feedbackElement.classList.add('show');
            setTimeout(() => {
                feedbackElement.classList.remove('show');
            }, 1000);

            // Update button icon temporarily
            const button = event.currentTarget;
            const icon = button.querySelector('i');
            const originalClass = icon.className;
            icon.className = 'fas fa-check';
            setTimeout(() => {
                icon.className = originalClass;
            }, 1000);
        }

        $('#domain').on('input', updateUrls);
        $('#dbaName').on('input', updateMessageSamples);

        $('#campaignForm').on('submit', function(e) {
            e.preventDefault();
            const companyName = $('#companyName').val();
            const dbaName = $('#dbaName').val();
            const domain = $('#domain').val();

            // Generate the template text
            const template = `A2P 10DLC Campaign Template
Campaign Information
Description
We are ${companyName} and are doing DBA as ${dbaName}. This campaign sends appointment confirmations, message notifications, and offers for technical services to existing clients or new clients who have opted in to receive SMS notifications. The communications are sent via the website form.

Privacy Policy: https://${domain}/privacy_policy.php
Terms of Service: https://${domain}/terms_of_service.php
Subscribe and Unsubscribe options displayed to users: https://${domain}/index.php
Subscribe: https://${domain}/subscribe.php
Unsubscribe: https://${domain}/unsubscribe.php

Sending messages with embedded links?
No

Sending messages with embedded phone numbers?
No

Sending messages with age-gated content?
No

Sending messages with content related to direct lending or other loan arrangements?
No

Message Samples
Message Sample #1
David, it's Kelly from ${dbaName}. Thanks for opting in to receive SMS notifications. I just saved a time for you on <?php echo $currentDate; ?>, at <?php echo $currentTime; ?>, and I'll see you then! If anything changes, just let me know. If you need to opt out, reply STOP.

Message Sample #2
David, it's Kelly from ${dbaName}. Thanks for opting in to receive messages. Today, we are giving out a few vouchers to our past clients for a free cyber security scan. Would you like one? If you need to opt out, reply STOP.

Message Sample #3
David, it's Kelly from ${dbaName}. Thanks for opting in to receive our messages. We're offering a free consultation to help optimize your business. Would you like to schedule a time? If you need to opt out, reply STOP.

Message Sample #4
David, it's Kelly from ${dbaName}. Thanks for subscribing to our updates! We've noticed an increase in cyber threats this month. Would you like a free guide on how to protect your business? If you need to opt out, reply STOP.

Message Sample #5
David, it's Kelly from ${dbaName}. Thanks for opting in to receive SMS alerts! We just launched a new service for automated backup solutions. Can I send you more info? If you need to opt out, reply STOP.

End User Consent
Twilio manages opt-out and help keywords for you by default.

How do end-users consent to receive messages?
End users complete a form at https://${domain}/ that states OPT IN CONSENT: "By submitting your phone number, you are authorizing us to send you text messages and notifications. Message and data rates apply. Reply STOP to unsubscribe to a message sent from us." End users can unsubscribe via the same page by entering their phone number or replying STOP.

Opt-out Message
You have successfully been unsubscribed. You will not receive any more messages from this number. Reply START to resubscribe.

Opt-out Keywords
CANCEL, END, QUIT, UNSUBSCRIBE, STOP, STOPALL

Help Message
Reply STOP to unsubscribe. Msg&Data Rates May Apply.

Help Keywords
HELP, INFO`;

            // Create a temporary textarea to copy the template
            const textarea = document.createElement('textarea');
            textarea.value = template;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);

            alert('Template has been generated and copied to clipboard!');
        });
    </script>
</body>
</html>
