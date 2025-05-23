<?php
require('include/top.php');

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Allow only Admin (1) and HR (5)
if (!in_array($_SESSION['ROLE'] ?? 0, [1, 5])) {
    header('Location: index.php');
    exit;
}

// Error messages array
$errorMessages = [
    'invalid_ip' => 'Invalid IP address format',
    'db_error' => 'Database operation failed',
    'invalid_csrf' => 'Security token mismatch',
    'no_permission' => 'You do not have permission to perform this action',
];

// Success messages array
$successMessages = [
    'added' => 'IP Address Added Successfully!',
    'updated' => 'IP Address Updated Successfully!',
    'deleted' => 'IP Address Deleted Successfully!',
    'activated' => 'IP Address Activated!',
    'deactivated' => 'IP Address Deactivated!',
];

// Initialize result status
$operationStatus = '';
$errorMessage = '';

// Handle POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: manage_ips.php?error=invalid_csrf");
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    $ip = $_POST['ip_address'] ?? '';
    $prefix_ip = $_POST['prefix_ip'] ?? '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    // Validate IP address if provided
    if ($ip && !filter_var($ip, FILTER_VALIDATE_IP)) {
        header("Location: manage_ips.php?error=invalid_ip");
        exit;
    }
    
    // Validate prefix IP if provided
    if ($prefix_ip && !preg_match('/^(\d{1,3}\.\d{1,3}\.){1,3}$/', $prefix_ip)) {
        header("Location: manage_ips.php?error=invalid_ip");
        exit;
    }
    
    // Process based on action
    try {
        switch ($action) {
            case 'add':
                if ($ip || $prefix_ip) {
                    $stmt = $conn->prepare("INSERT INTO AllowedIP (ip_address, prefix_ip, is_active) VALUES (?, ?, 1)");
                    $stmt->bind_param("ss", $ip, $prefix_ip);
                    if ($stmt->execute()) {
                        $operationStatus = 'added';
                    } else {
                        throw new Exception("Failed to add IP");
                    }
                }
                break;
                
            case 'edit':
                if ($id > 0) {
                    $stmt = $conn->prepare("UPDATE AllowedIP SET ip_address = ?, prefix_ip = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $ip, $prefix_ip, $id);
                    if ($stmt->execute()) {
                        $operationStatus = 'updated';
                    } else {
                        throw new Exception("Failed to update IP");
                    }
                }
                break;
                
            case 'delete':
                if ($id > 0) {
                    $stmt = $conn->prepare("UPDATE AllowedIP SET is_deleted = 1 WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        $operationStatus = 'deleted';
                    } else {
                        throw new Exception("Failed to delete IP");
                    }
                }
                break;
                
            case 'activate':
                if ($id > 0) {
                    $stmt = $conn->prepare("UPDATE AllowedIP SET is_active = 1 WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        $operationStatus = 'activated';
                    } else {
                        throw new Exception("Failed to activate IP");
                    }
                }
                break;
                
            case 'deactivate':
                if ($id > 0) {
                    $stmt = $conn->prepare("UPDATE AllowedIP SET is_active = 0 WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        $operationStatus = 'deactivated';
                    } else {
                        throw new Exception("Failed to deactivate IP");
                    }
                }
                break;
        }
        
        // Redirect with success message
        if ($operationStatus) {
            header("Location: manage_ips.php?msg=" . $operationStatus);
            exit;
        }
        
    } catch (Exception $e) {
        // Log error
        error_log("Error in manage_ips.php: " . $e->getMessage());
        // Set error message
        $errorMessage = 'db_error';
        header("Location: manage_ips.php?error=" . $errorMessage);
        exit;
    }
}

// Fetch IPs with prepared statement
try {
    $stmt = $conn->prepare("SELECT * FROM AllowedIP WHERE is_deleted = 0 ORDER BY id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Exception $e) {
    error_log("Error fetching IP addresses: " . $e->getMessage());
    $errorMessage = 'db_error';
}

require('include/side-navbar.php');
require('include/right-side-navbar.php');
?>
<style>
.action-buttons .btn {
    margin-right: 5px;
}
.action-buttons form {
    display: inline;
}
</style>
<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
    <?php
    require('include/header.php');
    ?>
    <div class="body flex-grow-1 px-3">
        <div class="container-lg">
            <div class="fs-2 fw-semibold">Manage IP Access Control</div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                  <li class="breadcrumb-item">
                    <span>Manage</span>
                  </li>
                  <li class="breadcrumb-item active"><span>Manage IP Access Control</span></li>
                </ol>
            </nav>
            <!-- Messages -->
            <?php if (isset($_GET['msg']) && array_key_exists($_GET['msg'], $successMessages)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($successMessages[$_GET['msg']]) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) && array_key_exists($_GET['error'], $errorMessages)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($errorMessages[$_GET['error']]) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-lg-12 text-center">
                    <!-- Add IP Form -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Add New IP Address / Prefix</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="row g-3" id="addIpForm">
                                <div class="col-md-5">
                                    <label for="ip_address" class="form-label">Full IP Address</label>
                                    <input type="text" name="ip_address" id="ip_address" class="form-control" 
                                           placeholder="Enter Full IP (e.g. 192.168.1.101)" pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$">
                                    <div class="form-text">Enter a specific IP address that should be allowed</div>
                                </div>
                                <div class="col-md-5">
                                    <label for="prefix_ip" class="form-label">IP Prefix</label>
                                    <input type="text" name="prefix_ip" id="prefix_ip" class="form-control" 
                                           placeholder="Enter Prefix IP (e.g. 192.168.1.)">
                                    <div class="form-text">Or enter an IP prefix to allow a range of IPs</div>
                                </div>
                                <div class="col-md-2 mt-5">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <label class="invisible">Add</label>
                                    <button type="submit" class="btn btn-primary">Add IP</button>
                                </div>
                            </form>
                            <div>
                                <span id="ip-loading" class="ms-2 mt-2" style="display: none;">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Loading...
                                </span>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-info text-white" onclick="autoFillIP()">
                                    <i class="bi bi-wifi"></i> Get My Current IP
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- IP List Table -->
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Allowed IP Addresses</h5>
                        </div>
                        <div class="card-body table-responsive">
                            <?php if ($errorMessage === 'db_error'): ?>
                                <div class="alert alert-danger">Error fetching IP addresses. Please try again later.</div>
                            <?php else: ?>
                                <table id="ipTable" class="table table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>IP Address</th>
                                            <th>Prefix IP</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $i = 1; 
                                        if ($result && $result->num_rows > 0):
                                            while($row = $result->fetch_assoc()): 
                                        ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= htmlspecialchars($row['ip_address'] ?: 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['prefix_ip'] ?: 'N/A') ?></td>
                                                <td>
                                                    <?php if ($row['is_active'] == 1): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d M Y h:i A', strtotime($row['created_at'])) ?></td>
                                                <td class="action-buttons">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <input type="hidden" name="action" value="<?= $row['is_active'] ? 'deactivate' : 'activate' ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                        <button type="submit" class="btn btn-sm <?= $row['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                                                            <?= $row['is_active'] ? 'Deactivate' : 'Activate' ?>
                                                        </button>
                                                    </form>

                                                    <button class="btn btn-sm btn-info text-white" 
                                                            onclick="editIP(<?= $row['id'] ?>, '<?= htmlspecialchars($row['ip_address'] ?: '') ?>', '<?= htmlspecialchars($row['prefix_ip'] ?: '') ?>')">
                                                        Edit
                                                    </button>

                                                    <form method="POST" class="d-inline delete-form">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php 
                                            endwhile; 
                                        else: 
                                        ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No IP Addresses Found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content" id="editIpForm">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit IP Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="edit_ip" class="form-label">IP Address</label>
                    <input type="text" id="edit_ip" name="ip_address" class="form-control" 
                           placeholder="Full IP Address (e.g. 192.168.1.101)" 
                           pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$">
                </div>
                <div class="mb-3">
                    <label for="edit_prefix_ip" class="form-label">Prefix IP</label>
                    <input type="text" id="edit_prefix_ip" name="prefix_ip" class="form-control" 
                           placeholder="Prefix IP (e.g. 192.168.1.)">
                    <div class="form-text">Enter either a full IP or prefix, not both</div>
                </div>
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
<?php
require('include/footer.php');
?>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#ipTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100],
        "order": [[0, "asc"]],
        "responsive": true,
        "language": {
            "emptyTable": "No IP addresses found",
            "zeroRecords": "No matching records found"
        }
    });
    
    // Form validation
    validateForms();
    
    // Confirmation for delete actions
    $('.delete-form').on('submit', function(e) {
        if (!confirm('Are you sure you want to delete this IP address?')) {
            e.preventDefault();
            return false;
        }
    });
});

// Edit IP function
function editIP(id, ip, prefix) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_ip').value = ip;
    document.getElementById('edit_prefix_ip').value = prefix;
    
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();
}

// Auto fill current IP
function autoFillIP() {
    const loadingSpinner = document.getElementById('ip-loading');
    loadingSpinner.style.display = 'inline-block';
    
    fetch('https://api.ipify.org')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(ip => {
            document.getElementById('ip_address').value = ip;
            loadingSpinner.style.display = 'none';
        })
        .catch(error => {
            console.error('Error fetching IP:', error);
            alert('Could not detect your IP. Please enter it manually.');
            loadingSpinner.style.display = 'none';
        });
}

// Form validation
function validateForms() {
    // Validate add form
    document.getElementById('addIpForm').addEventListener('submit', function(e) {
        const ipField = document.getElementById('ip_address');
        const prefixField = document.getElementById('prefix_ip');
        
        // Check if at least one field is filled
        if (ipField.value.trim() === '' && prefixField.value.trim() === '') {
            alert('Please enter either a full IP address or an IP prefix');
            e.preventDefault();
            return false;
        }
        
        // If IP is provided, validate format
        if (ipField.value.trim() !== '' && !isValidIP(ipField.value)) {
            alert('Please enter a valid IP address');
            ipField.focus();
            e.preventDefault();
            return false;
        }
        
        // If prefix is provided, validate format
        if (prefixField.value.trim() !== '' && !isValidPrefix(prefixField.value)) {
            alert('Please enter a valid IP prefix');
            prefixField.focus();
            e.preventDefault();
            return false;
        }
    });
    
    // Validate edit form
    document.getElementById('editIpForm').addEventListener('submit', function(e) {
        const ipField = document.getElementById('edit_ip');
        const prefixField = document.getElementById('edit_prefix_ip');
        
        // Check if at least one field is filled
        if (ipField.value.trim() === '' && prefixField.value.trim() === '') {
            alert('Please enter either a full IP address or an IP prefix');
            e.preventDefault();
            return false;
        }
        
        // If IP is provided, validate format
        if (ipField.value.trim() !== '' && !isValidIP(ipField.value)) {
            alert('Please enter a valid IP address');
            ipField.focus();
            e.preventDefault();
            return false;
        }
        
        // If prefix is provided, validate format
        if (prefixField.value.trim() !== '' && !isValidPrefix(prefixField.value)) {
            alert('Please enter a valid IP prefix');
            prefixField.focus();
            e.preventDefault();
            return false;
        }
    });
}

// Validate IP address format
function isValidIP(ip) {
    // Regular expression for validating an IP address
    const ipRegex = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
    return ipRegex.test(ip);
}

// Validate IP prefix format
function isValidPrefix(prefix) {
    // Regular expression for validating an IP prefix (e.g., 192.168. or 10.0.0.)
    const prefixRegex = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)){0,2}\.$/;
    return prefixRegex.test(prefix);
}
</script>