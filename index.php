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
    <title><?php echo htmlspecialchars($companyName); ?> - Subscription Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
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
        <div class="legal-notice text-center">
            <h2><i class="fas fa-shield-alt"></i> Important Legal Information</h2>
            <div>
                <a href="privacy_policy.php" class="mb-2">
                    <i class="fas fa-user-shield"></i> Privacy Policy
                </a>
                <a href="terms_of_service.php" class="mb-2">
                    <i class="fas fa-file-contract"></i> Terms of Service
                </a>
            </div>
            <p class="mt-3 mb-0 text-muted">Please review our Privacy Policy and Terms of Service before proceeding</p>
        </div>

        <div class="subscription-box">
            <h1 class="text-center mb-2"><?php echo htmlspecialchars($companyName); ?></h1>
            <h2 class="text-center mb-5">Subscription Management</h2>
            <div class="row justify-content-center mb-4">
                <div class="col-md-6 mb-3">
                    <a href="subscribe.php" class="btn btn-subscribe btn-lg btn-block w-100 py-3">
                        <i class="fas fa-user-plus"></i> Subscribe
                    </a>
                </div>
                <div class="col-md-6 mb-3">
                    <a href="unsubscribe.php" class="btn btn-unsubscribe btn-lg btn-block w-100 py-3">
                        <i class="fas fa-user-minus"></i> Unsubscribe
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="footer mt-auto py-3">
        <div class="container">
            <div class="row">
                <div class="col text-center">
                    <a href="privacy_policy.php" class="text-muted me-3">Privacy Policy</a>
                    <a href="terms_of_service.php" class="text-muted">Terms of Service</a>
                    <div class="mt-2">
                        <a href="setup.php" class="btn btn-setup btn-sm me-2">Setup</a>
                        <a href="admin.php" class="btn btn-admin btn-sm">Admin</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
