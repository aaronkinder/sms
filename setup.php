<?php
session_start();

function createDatabase($conn, $db_name) {
    $sql = "CREATE DATABASE IF NOT EXISTS `$db_name`";
    return $conn->query($sql) === TRUE;
}

function createTables($conn) {
    $subscribers_sql = "CREATE TABLE IF NOT EXISTS subscribers (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(30) NOT NULL,
        last_name VARCHAR(30) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        email VARCHAR(50) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $admin_sql = "CREATE TABLE IF NOT EXISTS admin_settings (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        password_hash VARCHAR(255) NOT NULL
    )";

    $rate_limit_sql = "CREATE TABLE IF NOT EXISTS rate_limit (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        last_submission_time INT NOT NULL,
        UNIQUE KEY (ip_address)
    )";

    $settings_sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        setting_name VARCHAR(50) NOT NULL,
        setting_value TEXT NOT NULL,
        UNIQUE KEY (setting_name)
    )";

    $domain_mappings_sql = "CREATE TABLE IF NOT EXISTS domain_mappings (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        domain_name VARCHAR(255) NOT NULL,
        company_name VARCHAR(255) NOT NULL,
        dba_name VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (domain_name)
    )";

    $success = $conn->query($subscribers_sql) === TRUE 
        && $conn->query($admin_sql) === TRUE 
        && $conn->query($rate_limit_sql) === TRUE
        && $conn->query($settings_sql) === TRUE
        && $conn->query($domain_mappings_sql) === TRUE;

    if ($success) {
        // Insert company name and domain mapping
        $company_name = $_POST['company_name'] ?? 'Company Name';
        $domain_name = $_POST['domain_name'] ?? 'example.com';
        $dba_name = $_POST['dba_name'] ?? null;

        // Insert into settings
        $stmt = $conn->prepare("INSERT IGNORE INTO settings (setting_name, setting_value) VALUES ('CompanyName', ?)");
        $stmt->bind_param("s", $company_name);
        $success = $stmt->execute();
        $stmt->close();

        // Insert initial domain mapping
        if ($success) {
            $stmt = $conn->prepare("INSERT INTO domain_mappings (domain_name, company_name, dba_name) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $domain_name, $company_name, $dba_name);
            $success = $stmt->execute();
            $stmt->close();
        }
    }

    return $success;
}

function updateConfigFile($db_host, $db_name, $db_user, $db_pass) {
    $config_content = "<?php
    \$db_host = '$db_host';
    \$db_name = '$db_name';
    \$db_user = '$db_user';
    \$db_pass = '$db_pass';

    // Security settings
    \$max_submissions_per_ip = 5;
    \$submission_cooldown = 60;

    // Create connection
    \$conn = new mysqli(\$db_host, \$db_user, \$db_pass, \$db_name);

    // Check connection
    if (\$conn->connect_error) {
        die(\"Connection failed: \" . \$conn->connect_error);
    }

    // Set charset to utf8mb4
    \$conn->set_charset(\"utf8mb4\");

    // Function to get domain information
    function getDomainInfo(\$domain_name) {
        global \$conn;
        \$stmt = \$conn->prepare(\"SELECT * FROM domain_mappings WHERE domain_name = ?\");
        \$stmt->bind_param(\"s\", \$domain_name);
        \$stmt->execute();
        \$result = \$stmt->get_result();
        if (\$result->num_rows > 0) {
            return \$result->fetch_assoc();
        }
        return null;
    }

    // Function to get company name from settings
    function getCompanyName() {
        global \$conn;
        \$stmt = \$conn->prepare(\"SELECT setting_value FROM settings WHERE setting_name = 'CompanyName'\");
        \$stmt->execute();
        \$result = \$stmt->get_result();
        if (\$result->num_rows > 0) {
            \$row = \$result->fetch_assoc();
            return \$row['setting_value'];
        }
        return 'Company Name';
    }

    // Function to sanitize user input
    function sanitize_input(\$input) {
        global \$conn;
        return \$conn->real_escape_string(strip_tags(trim(\$input)));
    }

    // Function to validate email
    function validate_email(\$email) {
        return filter_var(\$email, FILTER_VALIDATE_EMAIL);
    }

    // Function to validate and format US phone number
    function validate_and_format_phone(\$phone) {
        \$phone = preg_replace('/[^0-9]/', '', \$phone);
        if (strlen(\$phone) === 10) {
            return '+1' . \$phone;
        }
        return false;
    }

    // Function to generate a simple math CAPTCHA
    function generate_math_captcha() {
        \$num1 = rand(1, 10);
        \$num2 = rand(1, 10);
        \$answer = \$num1 + \$num2;
        \$_SESSION['captcha_answer'] = \$answer;
        return \"\$num1 + \$num2 = ?\";
    }

    // Function to verify math CAPTCHA
    function verify_math_captcha(\$user_answer) {
        return isset(\$_SESSION['captcha_answer']) && intval(\$user_answer) === \$_SESSION['captcha_answer'];
    }

    // Function to check submission cooldown
    function check_submission_cooldown(\$ip_address) {
        global \$conn, \$submission_cooldown;
        \$stmt = \$conn->prepare(\"SELECT last_submission_time FROM rate_limit WHERE ip_address = ?\");
        \$stmt->bind_param(\"s\", \$ip_address);
        \$stmt->execute();
        \$result = \$stmt->get_result();
        if (\$result->num_rows > 0) {
            \$row = \$result->fetch_assoc();
            \$time_since_last_submission = time() - \$row['last_submission_time'];
            if (\$time_since_last_submission < \$submission_cooldown) {
                return \$submission_cooldown - \$time_since_last_submission;
            }
        }
        return 0;
    }

    // Function to update last submission time
    function update_submission_time(\$ip_address) {
        global \$conn;
        \$current_time = time();
        \$stmt = \$conn->prepare(\"INSERT INTO rate_limit (ip_address, last_submission_time) VALUES (?, ?) ON DUPLICATE KEY UPDATE last_submission_time = ?\");
        \$stmt->bind_param(\"sii\", \$ip_address, \$current_time, \$current_time);
        \$stmt->execute();
    }
    ?>";

    return file_put_contents('includes/config.php', $config_content) !== false;
}

$setup_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db_host = $_POST['db_host'];
    $db_name = $_POST['db_name'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $admin_password = $_POST['admin_password'];
    $company_name = trim($_POST['company_name']);
    $domain_name = trim($_POST['domain_name']);
    $dba_name = trim($_POST['dba_name']);

    if (strlen($admin_password) < 15) {
        $setup_message = "<div class='alert alert-danger'>Admin password must be at least 15 characters long.</div>";
    } elseif (empty($company_name)) {
        $setup_message = "<div class='alert alert-danger'>Company name is required.</div>";
    } elseif (empty($domain_name)) {
        $setup_message = "<div class='alert alert-danger'>Domain name is required.</div>";
    } else {
        // Create connection without database
        $conn = new mysqli($db_host, $db_user, $db_pass);

        // Check connection
        if ($conn->connect_error) {
            $setup_message = "<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>";
        } else {
            // Check if database exists
            $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
            
            if ($result->num_rows > 0) {
                if (!isset($_POST['confirm_delete'])) {
                    // Database exists, ask for confirmation
                    $setup_message = "
                    <div class='alert alert-warning'>
                        Database '$db_name' already exists. Do you want to delete its content and set up fresh?
                        <form method='post' action='setup.php' class='mt-3'>
                            <input type='hidden' name='db_host' value='$db_host'>
                            <input type='hidden' name='db_name' value='$db_name'>
                            <input type='hidden' name='db_user' value='$db_user'>
                            <input type='hidden' name='db_pass' value='$db_pass'>
                            <input type='hidden' name='admin_password' value='$admin_password'>
                            <input type='hidden' name='company_name' value='$company_name'>
                            <input type='hidden' name='domain_name' value='$domain_name'>
                            <input type='hidden' name='dba_name' value='$dba_name'>
                            <button type='submit' name='confirm_delete' value='1' class='btn btn-danger'>Yes, delete and set up fresh</button>
                            <a href='setup.php' class='btn btn-secondary'>No, cancel setup</a>
                        </form>
                    </div>";
                } elseif (isset($_POST['confirm_delete'])) {
                    // User confirmed deletion, proceed with setup
                    $conn->query("DROP DATABASE `$db_name`");
                    $setup_message .= "<div class='alert alert-info'>Existing database deleted.</div>";
                }
            }

            if (empty($setup_message) || isset($_POST['confirm_delete'])) {
                if (createDatabase($conn, $db_name)) {
                    $conn->select_db($db_name);
                    if (createTables($conn)) {
                        if (updateConfigFile($db_host, $db_name, $db_user, $db_pass)) {
                            // Hash and store admin password
                            $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("INSERT INTO admin_settings (password_hash) VALUES (?)");
                            $stmt->bind_param("s", $password_hash);
                            if ($stmt->execute()) {
                                $setup_message = "<div class='alert alert-success'>Setup completed successfully!</div>";
                            } else {
                                $setup_message = "<div class='alert alert-danger'>Error storing admin password: " . $stmt->error . "</div>";
                            }
                            $stmt->close();
                        } else {
                            $setup_message = "<div class='alert alert-danger'>Error updating configuration file.</div>";
                        }
                    } else {
                        $setup_message = "<div class='alert alert-danger'>Error creating tables: " . $conn->error . "</div>";
                    }
                } else {
                    $setup_message = "<div class='alert alert-danger'>Error creating database: " . $conn->error . "</div>";
                }
            }
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="icon" href="data:,">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Database Setup</h2>
        <?php echo $setup_message; ?>
        <form method="post" action="setup.php">
            <div class="form-group">
                <label for="company_name">Company Name:</label>
                <input type="text" class="form-control" id="company_name" name="company_name" required>
            </div>
            <div class="form-group">
                <label for="domain_name">Domain Name:</label>
                <input type="text" class="form-control" id="domain_name" name="domain_name" required placeholder="example.com">
            </div>
            <div class="form-group">
                <label for="dba_name">DBA Name (optional):</label>
                <input type="text" class="form-control" id="dba_name" name="dba_name">
            </div>
            <div class="form-group">
                <label for="db_host">Database Host:</label>
                <input type="text" class="form-control" id="db_host" name="db_host" required>
            </div>
            <div class="form-group">
                <label for="db_name">Database Name:</label>
                <input type="text" class="form-control" id="db_name" name="db_name" required>
            </div>
            <div class="form-group">
                <label for="db_user">Database Username:</label>
                <input type="text" class="form-control" id="db_user" name="db_user" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="db_pass">Database Password:</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="db_pass" name="db_pass" autocomplete="new-password">
                    <div class="input-group-append">
                        <span class="input-group-text toggle-password" data-target="db_pass">
                            <i class="fa fa-eye"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="admin_password">Admin Password (min 15 characters):</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="admin_password" name="admin_password" required minlength="15" autocomplete="new-password">
                    <div class="input-group-append">
                        <span class="input-group-text toggle-password" data-target="admin_password">
                            <i class="fa fa-eye"></i>
                        </span>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Setup Database</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('JavaScript is running');

            function togglePasswordVisibility(event) {
                const target = event.currentTarget.getAttribute('data-target');
                const passwordInput = document.getElementById(target);
                const icon = event.currentTarget.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }

            const togglePasswordIcons = document.querySelectorAll('.toggle-password');
            togglePasswordIcons.forEach(icon => {
                icon.addEventListener('click', togglePasswordVisibility);
                console.log('Added event listener to:', icon);
            });

            // Add error event listeners to all resources
            document.querySelectorAll('link, script, img').forEach(element => {
                element.addEventListener('error', function(e) {
                    console.error('Failed to load resource:', e.target.src || e.target.href);
                    console.error('Resource type:', e.target.tagName);
                    console.error('Resource attributes:', JSON.stringify(e.target.attributes));
                });
            });
        });
    </script>
</body>
</html>
