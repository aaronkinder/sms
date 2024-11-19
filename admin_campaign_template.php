<?php
// Previous PHP code remains unchanged until the form section
include 'includes/config.php';
session_start();

if (!isset($_SESSION['admin_authenticated'])) {
    header('Location: admin.php');
    exit();
}

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
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_name = 'CompanyName'");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $companyName = $row['setting_value'];
        $dbaName = $companyName;
    } else {
        $companyName = 'Company Name';
        $dbaName = 'Company Name';
    }
}

$currentDate = date('l, F j, Y');
$currentTime = date('g:i A');
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
            <?php echo htmlspecialchars($companyName); ?> DBA <?php echo htmlspecialchars($dbaName); ?>
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
                    <!-- A2P Brand -->
                    <div class="form-group">
                        <label for="companyName">Company Name:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="companyName" name="companyName" value="<?php echo htmlspecialchars($companyName); ?>" readonly>
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
                            <input type="text" class="form-control" id="dbaName" name="dbaName" value="<?php echo htmlspecialchars($dbaName); ?>" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy" type="button" onclick="copyToClipboard('dbaName')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>

                    <!-- Campaign Description -->
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <div class="input-group">
                            <textarea class="form-control" id="description" name="description" rows="10" readonly>We are <?php echo htmlspecialchars($companyName); ?> and are doing DBA as <?php echo htmlspecialchars($dbaName); ?>. This campaign sends appointment confirmations, message notifications, and offers for technical services to existing clients or new clients who have opted in to receive SMS notifications. The communications are sent via the website form.

Privacy Policy: https://<?php echo htmlspecialchars($fullDomain); ?>/privacy_policy.php
Terms of Service: https://<?php echo htmlspecialchars($fullDomain); ?>/terms_of_service.php
Subscribe and Unsubscribe options displayed to users: https://<?php echo htmlspecialchars($fullDomain); ?>/index.php
Subscribe: https://<?php echo htmlspecialchars($fullDomain); ?>/subscribe.php
Unsubscribe: https://<?php echo htmlspecialchars($fullDomain); ?>/unsubscribe.php</textarea>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy h-100" type="button" onclick="copyToClipboard('description')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>

                    <!-- Message Samples -->
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

                    <!-- End User Consent Information -->
                    <h4 class="mt-4">End User Consent Information</h4>
                    <div class="form-group">
                        <label for="userConsent">How do end-users consent to receive messages? (40-2048 characters):</label>
                        <div class="input-group">
                            <textarea class="form-control" id="userConsent" name="userConsent" rows="4" readonly>End users opt-in by visiting <?php echo htmlspecialchars($fullDomain); ?>/subscribe.php and adding their phone number. They then check a box agreeing to receive text messages from <?php echo htmlspecialchars($companyName); ?> dba <?php echo htmlspecialchars($dbaName); ?>. Opt-in occurs after end users create an account; see form at (<?php echo htmlspecialchars($fullDomain); ?>/subscribe.php).</textarea>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy h-100" type="button" onclick="copyToClipboard('userConsent')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>

                    <!-- Keywords and Messages -->
                    <div class="form-group">
                        <label for="optInKeywords">Opt-in Keywords (max 255 characters):</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="optInKeywords" value="START" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy" type="button" onclick="copyToClipboard('optInKeywords')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>

                    <!-- Opt-in Message field -->
                    <div class="form-group">
                        <label for="optInMessage">Opt-in Message (20-320 characters):</label>
                        <div class="input-group">
                            <textarea class="form-control" id="optInMessage" name="optInMessage" rows="2" readonly><?php echo htmlspecialchars($dbaName); ?>: You are now opted-in. For help, reply HELP. To opt-out, reply STOP</textarea>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy h-100" type="button" onclick="copyToClipboard('optInMessage')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <span class="copy-feedback">Copied!</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            updateMessageSamples();
            updateOptInMessage();
        });

        function updateOptInMessage() {
            const dbaName = $('#dbaName').val();
            const optInMessage = $('#optInMessage');
            optInMessage.val(`${dbaName}: You are now opted-in. For help, reply HELP. To opt-out, reply STOP`);
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
            
            feedbackElement.classList.add('show');
            setTimeout(() => {
                feedbackElement.classList.remove('show');
            }, 1000);

            const button = event.currentTarget;
            const icon = button.querySelector('i');
            const originalClass = icon.className;
            icon.className = 'fas fa-check';
            setTimeout(() => {
                icon.className = originalClass;
            }, 1000);
        }

        $('#dbaName').on('input', function() {
            updateMessageSamples();
            updateOptInMessage();
        });
    </script>
</body>
</html>
