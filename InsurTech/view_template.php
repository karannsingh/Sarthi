<!-- view_template.php -->
<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = 'Invalid template ID';
    $_SESSION['message_type'] = 'danger';
    header('Location: manage_templates.php');
    exit;
}

$template_id = (int)$_GET['id'];

// Fetch template data
$stmt = $conn->prepare("SELECT * FROM email_templates WHERE id = ?");
$stmt->bind_param("i", $template_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = 'Template not found';
    $_SESSION['message_type'] = 'danger';
    header('Location: manage_templates.php');
    exit;
}

$template = $result->fetch_assoc();
$custom_fields = !empty($template['custom_fields']) ? json_decode($template['custom_fields'], true) : [];
?>

<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php require('include/header.php'); ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="fs-2 fw-semibold">View Email Template</div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item">
            <span>Templates</span>
          </li>
          <li class="breadcrumb-item">
            <a href="manage_templates.php">Manage Templates</a>
          </li>
          <li class="breadcrumb-item active"><span>View Template</span></li>
        </ol>
      </nav>
      
      <div class="row">
        <div class="col-lg-12">
          <div class="card mb-4">
            <div class="card-body p-4">
              <div class="card-title fs-4 fw-semibold"><?= htmlspecialchars($template['name']) ?></div>
              
              <div class="row">
                <div class="col-md-8">
                  <div class="mb-4">
                    <h5 class="border-bottom pb-2">Template Details</h5>
                    <div class="row mb-2">
                      <div class="col-md-3 fw-bold">Created:</div>
                      <div class="col-md-9"><?= date('F j, Y, g:i a', strtotime($template['created_at'])) ?></div>
                    </div>
                    <div class="row mb-2">
                      <div class="col-md-3 fw-bold">Last Updated:</div>
                      <div class="col-md-9"><?= date('F j, Y, g:i a', strtotime($template['updated_at'])) ?></div>
                    </div>
                    <div class="row mb-2">
                      <div class="col-md-3 fw-bold">Subject:</div>
                      <div class="col-md-9"><?= htmlspecialchars($template['subject']) ?></div>
                    </div>
                    <div class="row mb-2">
                      <div class="col-md-3 fw-bold">Signature Type:</div>
                      <div class="col-md-9">
                        <?php 
                        $signatures = [
                            1 => 'Standard (Sarthi Enterprises)',
                            2 => 'Support Team',
                            3 => 'Sales Team'
                        ];
                        echo $signatures[$template['signature_type']] ?? 'Unknown';
                        ?>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-4">
                    <h5 class="border-bottom pb-2">Email Preview</h5>
                    <div class="card">
                      <div class="card-header">
                        Subject: <?= htmlspecialchars($template['subject']) ?>
                      </div>
                      <div class="card-body">
                        <?= nl2br(htmlspecialchars($template['body'])) ?>
                        
                        <?php 
                        // Display selected signature
                        $signatures = [
                            1 => "<br><br>Best Regards,<br>Sarthi Enterprises<br><a href='https://sarthii.co.in'>Visit our website</a>",
                            2 => "<br><br>Thanks & Regards,<br>Team Support<br>Contact: 1234567890",
                            3 => "<br><br>Warm Regards,<br>Sales Team<br>Your Company",
                        ];
                        $sig_type = $template['signature_type'];
                        echo $signatures[$sig_type] ?? $signatures[1];
                        ?>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-4">
                  <div class="mb-4">
                    <h5 class="border-bottom pb-2">Standard Variables</h5>
                    <div class="table-responsive">
                      <table class="table table-sm table-hover">
                        <thead>
                          <tr>
                            <th>Variable</th>
                            <th>Description</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td><code>{customer_name}</code></td>
                            <td>Full name of the customer</td>
                          </tr>
                          <tr>
                            <td><code>{vehicle_number}</code></td>
                            <td>Vehicle identification number</td>
                          </tr>
                          <tr>
                            <td><code>{total_premium}</code></td>
                            <td>Total premium amount</td>
                          </tr>
                          <tr>
                            <td><code>{link}</code></td>
                            <td>Custom link/URL</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  
                  <?php if (!empty($custom_fields)): ?>
                  <div class="mb-4">
                    <h5 class="border-bottom pb-2">Custom Variables</h5>
                    <div class="table-responsive">
                      <table class="table table-sm table-hover">
                        <thead>
                          <tr>
                            <th>Variable</th>
                            <th>Label</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($custom_fields as $field): ?>
                          <tr>
                            <td><code>{<?= htmlspecialchars($field['name']) ?>}</code></td>
                            <td><?= htmlspecialchars($field['label']) ?></td>
                          </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <?php endif; ?>
                  
                  <div class="d-flex justify-content-between">
                    <a href="edit_template.php?id=<?= $template_id ?>" class="btn btn-primary">Edit Template</a>
                    <a href="manage_templates.php" class="btn btn-secondary">Back to List</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <?php require('include/footer.php'); ?>
</div>