<?php
require_once 'includes/config.php';
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    header('Location: admin.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $domain = sanitize_input($_POST['domain_name']);
                $company = sanitize_input($_POST['company_name']);
                $dba = !empty($_POST['dba_name']) ? sanitize_input($_POST['dba_name']) : null;
                $stmt = $conn->prepare("INSERT INTO domain_mappings (domain_name, company_name, dba_name) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $domain, $company, $dba);
                $stmt->execute();
                break;

            case 'update':
                $id = (int)$_POST['id'];
                $domain = sanitize_input($_POST['domain_name']);
                $company = sanitize_input($_POST['company_name']);
                $dba = !empty($_POST['dba_name']) ? sanitize_input($_POST['dba_name']) : null;
                $stmt = $conn->prepare("UPDATE domain_mappings SET domain_name = ?, company_name = ?, dba_name = ? WHERE id = ?");
                $stmt->bind_param("sssi", $domain, $company, $dba, $id);
                $stmt->execute();
                break;

            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("DELETE FROM domain_mappings WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                break;
        }
    }
}

// Get all domain mappings
$result = $conn->query("SELECT * FROM domain_mappings ORDER BY domain_name");
$companyName = getCompanyName();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Management - Admin Panel</title>
    <link rel="icon" href="data:;base64,iVBORw0KGgo=">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
            margin: 0 2px;
            min-width: 70px;
        }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        #domainsTable {
            width: 100% !important;
        }
        #domainsTable th, #domainsTable td {
            vertical-align: middle;
            padding: 0.75rem;
        }
        .btn-group-sm > .btn {
            margin: 0 2px;
        }
        .input-group {
            margin-bottom: 0;
        }
        .input-group-text {
            height: calc(1.5em + 0.75rem + 2px);
            display: flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
        }
        .input-group-text i {
            font-size: 1rem;
            width: 16px;
            text-align: center;
        }
        .card {
            margin-bottom: 2rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
        .card-header h4 {
            margin-bottom: 0;
            font-size: 1.1rem;
            color: #333;
        }
        .form-control {
            height: calc(1.5em + 0.75rem + 2px);
        }
        .input-group-text {
            background-color: #f8f9fa;
        }
        .modal-header {
            background-color: #f8f9fa;
        }
        .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        /* DataTables length menu styling */
        .dataTables_length {
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }
        .dataTables_length label {
            margin-bottom: 0;
            display: inline-block;
            white-space: nowrap;
        }
        .dataTables_length select {
            min-width: 75px;
            display: inline-block;
            padding: 0.375rem 1.75rem 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            vertical-align: middle;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            margin: 0 0.5rem;
            height: calc(1.5em + 0.75rem + 2px);
        }
        /* Ensure proper spacing between text and select */
        .dataTables_length label > span,
        .dataTables_length label > select {
            margin: 0 0.5rem;
        }
        /* DataTables pagination styling */
        .dataTables_paginate {
            padding-top: 0.5rem;
        }
        .dataTables_info {
            padding-top: 0.5rem;
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
        <h2>Domain Management</h2>
        <div id="message" class="alert" style="display:none;"></div>
        <div class="mb-3">
            <a href="admin.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Admin Panel</a>
        </div>

        <!-- Add New Domain Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h4><i class="fas fa-plus"></i> Add New Domain Mapping</h4>
            </div>
            <div class="card-body">
                <form method="POST" class="row">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-globe"></i></span>
                            </div>
                            <input type="text" class="form-control" name="domain_name" placeholder="Domain Name (e.g., example.com)" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                            </div>
                            <input type="text" class="form-control" name="company_name" placeholder="Company Name" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            </div>
                            <input type="text" class="form-control" name="dba_name" placeholder="DBA Name (optional)">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Domain</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Existing Domains Table -->
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-list"></i> Existing Domain Mappings</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="domainsTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th width="25%">Domain Name</th>
                                <th width="25%">Company Name</th>
                                <th width="20%">DBA Name</th>
                                <th width="15%">Last Updated</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr data-id="<?php echo $row['id']; ?>">
                                <td><?php echo htmlspecialchars($row['domain_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                <td><?php echo $row['dba_name'] ? htmlspecialchars($row['dba_name']) : ''; ?></td>
                                <td><?php echo $row['updated_at']; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-primary action-btn" 
                                                onclick="editDomain(<?php echo $row['id']; ?>, 
                                                '<?php echo htmlspecialchars($row['domain_name']); ?>', 
                                                '<?php echo htmlspecialchars($row['company_name']); ?>',
                                                '<?php echo htmlspecialchars($row['dba_name'] ?? ''); ?>')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="btn btn-danger action-btn" 
                                                    onclick="return confirm('Are you sure you want to delete this domain mapping?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Domain Mapping</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="editForm">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label class="form-label">Domain Name</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                </div>
                                <input type="text" class="form-control" name="domain_name" id="edit_domain_name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Company Name</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                </div>
                                <input type="text" class="form-control" name="company_name" id="edit_company_name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">DBA Name (optional)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                </div>
                                <input type="text" class="form-control" name="dba_name" id="edit_dba_name">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('editForm').submit();">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
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
    $(document).ready(function() {
        var table = $('#domainsTable').DataTable({
            "order": [[ 0, "asc" ]],
            "columnDefs": [
                { "orderable": false, "targets": 4 },
                { "width": "25%", "targets": 0 },
                { "width": "25%", "targets": 1 },
                { "width": "20%", "targets": 2 },
                { "width": "15%", "targets": 3 },
                { "width": "15%", "targets": 4 }
            ],
            "autoWidth": false,
            "scrollX": false,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "language": {
                "lengthMenu": "Display _MENU_ entries per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total entries)"
            }
        });

        // Adjust column widths after table is drawn
        table.columns.adjust();
    });

        function editDomain(id, domain, company, dba) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_domain_name').value = domain;
            document.getElementById('edit_company_name').value = company;
            document.getElementById('edit_dba_name').value = dba || '';
            $('#editModal').modal('show');
        }

        function showMessage(message, type) {
            $('#message').removeClass().addClass('alert alert-' + type).text(message).show();
            setTimeout(function() {
                $('#message').fadeOut();
            }, 3000);
        }

        // Show message if form was submitted
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        $(document).ready(function() {
            showMessage('Domain mapping has been updated successfully.', 'success');
        });
        <?php endif; ?>
</script>
</body>
</html>
<?php
$conn->close();
?>
