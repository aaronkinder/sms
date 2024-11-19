<?php
session_start();
require_once 'includes/config.php';

// Login handling
if (isset($_POST['login'])) {
    $admin_username = 'admin';
    $admin_password = '03bc95c2b560a';

    if ($_POST['username'] === $admin_username && $_POST['password'] === $admin_password) {
        $_SESSION['admin'] = true;
    } else {
        $login_error = "Invalid credentials";
    }
}

// Logout handling
if (isset($_GET['logout'])) {
    unset($_SESSION['admin']);
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Function to execute command and capture output
function execute_command($command, &$output, &$errors) {
    $descriptors = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );
    
    $process = proc_open($command, $descriptors, $pipes);
    
    if (is_resource($process)) {
        fclose($pipes[0]);
        
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        return proc_close($process);
    }
    
    return false;
}

// Function to backup database
function backup_database($db_host, $db_user, $db_pass, $db_name) {
    $backup_file = 'backup_' . date("Y-m-d_H-i-s") . '.sql';
    $backup_dir = 'backups/';
    
    // Create backups directory if it doesn't exist
    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    // Create .htaccess to prevent direct access to backup files
    $htaccess_content = "Deny from all";
    file_put_contents($backup_dir . '.htaccess', $htaccess_content);
    
    // Use Ubuntu's MySQL bin path
    $mysqldump_path = '/usr/bin/mysqldump';
    
    // Build command
    $command = sprintf(
        '%s -h %s -u %s %s %s',
        $mysqldump_path,
        escapeshellarg($db_host),
        escapeshellarg($db_user),
        empty($db_pass) ? '' : '-p' . escapeshellarg($db_pass),
        escapeshellarg($db_name)
    );
    
    // Execute command and capture output
    $output = '';
    $errors = '';
    $return_var = execute_command($command, $output, $errors);
    
    if ($return_var === 0 && !empty($output)) {
        // Write the output to file
        if (file_put_contents($backup_dir . $backup_file, $output) !== false) {
            return ['success' => true, 'message' => 'Database backup created successfully: ' . $backup_file];
        }
    }
    
    $error_msg = !empty($errors) ? $errors : 'Unknown error occurred';
    return ['success' => false, 'message' => 'Error creating backup: ' . $error_msg];
}

// Function to restore database
function restore_database($db_host, $db_user, $db_pass, $db_name, $backup_file) {
    $backup_dir = 'backups/';
    $full_path = $backup_dir . basename($backup_file);
    
    if (!file_exists($full_path)) {
        return ['success' => false, 'message' => 'Backup file not found'];
    }
    
    // Get backup file content
    $sql_content = file_get_contents($full_path);
    if ($sql_content === false) {
        return ['success' => false, 'message' => 'Could not read backup file'];
    }
    
    // Use Ubuntu's MySQL bin path
    $mysql_path = '/usr/bin/mysql';
    
    // Build command
    $command = sprintf(
        '%s -h %s -u %s %s %s',
        $mysql_path,
        escapeshellarg($db_host),
        escapeshellarg($db_user),
        empty($db_pass) ? '' : '-p' . escapeshellarg($db_pass),
        escapeshellarg($db_name)
    );
    
    // Create a temporary file for the SQL input
    $temp_file = tempnam('/tmp', 'mysql_restore_');
    file_put_contents($temp_file, $sql_content);
    
    // Append input redirection to command
    $command .= ' < ' . escapeshellarg($temp_file);
    
    // Execute command and capture output
    $output = '';
    $errors = '';
    $return_var = execute_command($command, $output, $errors);
    
    // Clean up temp file
    unlink($temp_file);
    
    if ($return_var === 0) {
        return ['success' => true, 'message' => 'Database restored successfully'];
    }
    
    $error_msg = !empty($errors) ? $errors : 'Unknown error occurred';
    return ['success' => false, 'message' => 'Error restoring database: ' . $error_msg];
}

// Handle form submissions for backup/restore
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'backup':
                $result = backup_database($db_host, $db_user, $db_pass, $db_name);
                $message = $result['message'];
                $success = $result['success'];
                break;
                
            case 'restore':
                if (isset($_POST['confirm_restore']) && isset($_POST['backup_file'])) {
                    $result = restore_database($db_host, $db_user, $db_pass, $db_name, $_POST['backup_file']);
                    $message = $result['message'];
                    $success = $result['success'];
                } else {
                    $message = 'Please confirm database restore';
                    $success = false;
                }
                break;
        }
    }
}

// Get list of backup files
$backup_files = [];
$backup_dir = 'backups/';
if (file_exists($backup_dir)) {
    $backup_files = array_filter(scandir($backup_dir), function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'sql';
    });
}

// Get company name from config
$companyName = getCompanyName();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Manager</title>
    <link rel="icon" href="data:;base64,iVBORw0KGgo=">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
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
            <?php if (isset($_SESSION['admin']) && $_SESSION['admin']): ?>
            <li><a href="admin.php" class="nav-admin"><i class="fas fa-user-shield"></i> Admin</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="container mt-5">
        <?php if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true): ?>
            <!-- Login Form -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-lock"></i> Admin Login</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($login_error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($login_error); ?>
                        </div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="form-group">
                            <label for="username"><i class="fas fa-user"></i> Username:</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password"><i class="fas fa-key"></i> Password:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="mb-3">
                <a href="admin.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Admin Panel
                </a>
                <a href="?logout" class="btn btn-danger float-right">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-database"></i> Database Manager</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($message)): ?>
                        <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
                            <i class="fas fa-<?php echo $success ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Backup Database -->
                    <section class="mb-4">
                        <h3><i class="fas fa-download"></i> Backup Database</h3>
                        <form method="post">
                            <input type="hidden" name="action" value="backup">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Backup
                            </button>
                        </form>
                    </section>

                    <!-- Restore Database -->
                    <section>
                        <h3><i class="fas fa-upload"></i> Restore Database</h3>
                        <?php if (!empty($backup_files)): ?>
                            <form method="post" onsubmit="return confirm('WARNING: This will overwrite the current database. Are you sure you want to proceed?');">
                                <input type="hidden" name="action" value="restore">
                                <div class="form-group">
                                    <select name="backup_file" class="form-control" required>
                                        <?php foreach ($backup_files as $file): ?>
                                            <?php if ($file !== '.' && $file !== '..' && $file !== '.htaccess'): ?>
                                                <option value="<?php echo htmlspecialchars($file); ?>">
                                                    <?php echo htmlspecialchars($file); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="confirm_restore" name="confirm_restore" required>
                                        <label class="custom-control-label" for="confirm_restore">
                                            I confirm I want to restore this backup
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-sync-alt"></i> Restore Database
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No backup files available.
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
