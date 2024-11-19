<?php
include 'includes/config.php';
session_start();

function authenticate($password) {
    global $conn;
    $stmt = $conn->prepare("SELECT password_hash FROM admin_settings LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['admin_authenticated'] = true;
            return true;
        }
    }
    return false;
}

function exportCSV($conn) {
    $query = "SELECT * FROM subscribers";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $filename = "subscribers_" . date('Y-m-d') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, array('ID', 'First Name', 'Last Name', 'Phone', 'Email', 'IP Address', 'Subscribed At'));

        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit();
    }
}

// Detect Operating System
function getOperatingSystem() {
    $os = php_uname('s');
    return (stripos($os, 'Ubuntu') !== false) ? 'Ubuntu' : 'Windows';
}

// Logout handler
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_authenticated']);
    session_destroy();
    header('Location: admin.php');
    exit();
}

// Check if the user is trying to log in
if (isset($_POST['login'])) {
    $password = $_POST['password'];
    if (authenticate($password)) {
        header('Location: admin.php');
        exit();
    } else {
        $error = "Invalid password";
    }
}

// Check if the user is authenticated
if (!isset($_SESSION['admin_authenticated'])) {
    // If not authenticated, show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
        <link rel="icon" href="data:;base64,iVBORw0KGgo=">
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/style.css">
        <style>
            .visually-hidden {
                position: absolute !important;
                height: 1px;
                width: 1px;
                overflow: hidden;
                clip: rect(1px 1px 1px 1px); /* IE6, IE7 */
                clip: rect(1px, 1px, 1px, 1px);
                white-space: nowrap; /* added line */
            }
        </style>
    </head>
    <body>
        <div class="container mt-5">
            <h2>Admin Login</h2>
            <?php if (isset($error)) { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>
            <form method="post">
                <div class="form-group visually-hidden">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" value="admin" autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                        <div class="input-group-append">
                            <span class="input-group-text toggle-password" data-target="password">
                                <i class="fa fa-eye"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <button type="submit" name="login" class="btn btn-primary">Login</button>
            </form>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
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

                const togglePasswordIcon = document.querySelector('.toggle-password');
                if (togglePasswordIcon) {
                    togglePasswordIcon.addEventListener('click', togglePasswordVisibility);
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}

if (isset($_GET['export'])) {
    exportCSV($conn);
}

$query = "SELECT * FROM subscribers";
$result = $conn->query($query);
$companyName = getCompanyName();
$currentOS = getOperatingSystem();
$dbManagerFile = ($currentOS === 'Ubuntu') ? 'db_manager_ubuntu.php' : 'db_manager.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="icon" href="data:;base64,iVBORw0KGgo=">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .action-btn {
            width: 80px;
            margin: 2px;
            display: inline-block !important;
        }
        #subscribersTable_wrapper .col-md-6:eq(1) {
            display: none;
        }
        #subscribersTable th:last-child,
        #subscribersTable td:last-child {
            display: table-cell !important;
            visibility: visible !important;
            white-space: nowrap;
        }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
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

    <div class="container-fluid mt-5">
        <h2>Admin Panel</h2>
        <div id="message" class="alert" style="display:none;"></div>
        <div class="mb-3">
            <a href="?export=1" class="btn btn-success">Export to CSV</a>
            <a href="admin_domains.php" class="btn btn-primary">Manage Domains</a>
            <a href="<?php echo $dbManagerFile; ?>" class="btn btn-info">Database Manager</a>
            <a href="admin_campaign_template.php" class="btn btn-warning"><i class="fas fa-file-alt"></i> A2P 10DLC Template</a>
            <a href="?logout=1" class="btn btn-danger float-right">Logout</a>
        </div>
        <div class="table-responsive">
            <table id="subscribersTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>IP Address</th>
                        <th>Subscribed At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr data-id="<?php echo $row['id']; ?>">
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo $row['ip_address']; ?></td>
                        <td><?php echo $row['subscribed_at']; ?></td>
                        <td>
                            <button type="button" class="btn btn-primary btn-sm action-btn edit-btn">Edit</button>
                            <button type="button" class="btn btn-danger btn-sm action-btn delete-btn">Delete</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Subscriber</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="form-group">
                            <label for="edit_first_name">First Name:</label>
                            <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_last_name">Last Name:</label>
                            <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_phone">Phone:</label>
                            <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email:</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateSubscriberBtn">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this subscriber?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    <script>
        console.log('Script loaded');
        // Log all script and stylesheet URLs
        $('script, link[rel="stylesheet"]').each(function() {
            console.log('Resource URL:', $(this).attr('src') || $(this).attr('href'));
        });

        $(document).ready(function() {
            console.log('Document ready');
            var table = $('#subscribersTable').DataTable({
                "order": [[ 0, "desc" ]],
                "columnDefs": [
                    { "orderable": false, "targets": 7 }
                ],
                "scrollX": true,
                "autoWidth": false,
                "columns": [
                    { "width": "5%" },
                    { "width": "10%" },
                    { "width": "10%" },
                    { "width": "15%" },
                    { "width": "20%" },
                    { "width": "10%" },
                    { "width": "15%" },
                    { "width": "15%" }
                ],
                "drawCallback": function(settings) {
                    console.log('Table redrawn');
                    console.log('Table HTML:', $('#subscribersTable').html());
                    $('.action-btn').each(function() {
                        var styles = window.getComputedStyle(this);
                        console.log('Button styles:', $(this).attr('class'), {
                            display: styles.display,
                            visibility: styles.visibility,
                            width: styles.width,
                            height: styles.height,
                            position: styles.position
                        });
                    });
                    var actionsColumn = $('#subscribersTable th:last-child, #subscribersTable td:last-child');
                    var styles = window.getComputedStyle(actionsColumn[0]);
                    console.log('Actions column styles:', {
                        display: styles.display,
                        visibility: styles.visibility,
                        width: styles.width,
                        position: styles.position
                    });
                }
            });

            // Edit button click event
            $('#subscribersTable').on('click', '.edit-btn', function() {
                var id = $(this).closest('tr').data('id');
                console.log('Edit button clicked for ID:', id);
                editSubscriber(id);
            });

            // Delete button click event
            $('#subscribersTable').on('click', '.delete-btn', function() {
                var id = $(this).closest('tr').data('id');
                console.log('Delete button clicked for ID:', id);
                confirmDelete(id);
            });

            // Update subscriber button click event
            $('#updateSubscriberBtn').click(function() {
                updateSubscriber();
            });
        });

        function editSubscriber(id) {
            $.ajax({
                url: 'process.php',
                type: 'GET',
                data: { action: 'get', id: id },
                dataType: 'json',
                success: function(response) {
                    console.log('Received response:', response);
                    if (response.success) {
                        var data = response.data;
                        $('#edit_id').val(data.id);
                        $('#edit_first_name').val(data.first_name);
                        $('#edit_last_name').val(data.last_name);
                        $('#edit_phone').val(data.phone);
                        $('#edit_email').val(data.email);
                        $('#editModal').modal('show');
                    } else {
                        showMessage(response.message, 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    console.error('Response Text:', xhr.responseText);
                    showMessage('Error loading subscriber data: ' + error, 'danger');
                }
            });
        }

        function updateSubscriber() {
            console.log('Updating subscriber');
            $.ajax({
                url: 'process.php',
                type: 'POST',
                data: $('#editForm').serialize() + '&action=update',
                dataType: 'json',
                success: function(response) {
                    console.log('Received update response:', response);
                    $('#editModal').modal('hide');
                    showMessage(response.message, response.success ? 'success' : 'danger');
                    if (response.success) {
                        // Update the table row with new data
                        var id = $('#edit_id').val();
                        var row = $('#subscribersTable').DataTable().row('tr[data-id="' + id + '"]');
                        var rowData = row.data();
                        rowData[1] = $('#edit_first_name').val();
                        rowData[2] = $('#edit_last_name').val();
                        rowData[3] = $('#edit_phone').val();
                        rowData[4] = $('#edit_email').val();
                        row.data(rowData).draw();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    console.error('Response Text:', xhr.responseText);
                    showMessage('Error updating subscriber: ' + error, 'danger');
                }
            });
        }

        function confirmDelete(id) {
            $('#confirmDeleteBtn').data('id', id);
            $('#confirmModal').modal('show');
        }

        $('#confirmDeleteBtn').click(function() {
            var id = $(this).data('id');
            console.log('Deleting subscriber with ID:', id);
            $.ajax({
                url: 'process.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    $('#confirmModal').modal('hide');
                    showMessage(response.message, response.success ? 'success' : 'danger');
                    if (response.success) {
                        // Remove the row from the table
                        $('#subscribersTable').DataTable().row('tr[data-id="' + id + '"]').remove().draw();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    console.error('Response Text:', xhr.responseText);
                    showMessage('Error deleting subscriber: ' + error, 'danger');
                }
            });
        });

        function showMessage(message, type) {
            console.log('Showing message:', message, 'Type:', type);
            $('#message').removeClass().addClass('alert alert-' + type).text(message).show();
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>
