<!-- create_template.php -->
<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');
?>

<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php require('include/header.php'); ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="fs-2 fw-semibold">Create Email Template</div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item">
            <span>Templates</span>
          </li>
          <li class="breadcrumb-item active"><span>Create Template</span></li>
        </ol>
      </nav>
      
      <div class="row">
        <div class="col-lg-12">
          <div class="card mb-4">
            <div class="card-body p-4">
              <div class="card-title fs-4 fw-semibold">Email Template Form</div>
              
              <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
                  <?= $_SESSION['message'] ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php 
                  unset($_SESSION['message']); 
                  unset($_SESSION['message_type']);
                endif; 
                ?>
              
              <form action="function/save_template.php" method="POST">
                <div class="mb-3">
                  <label>Template Name:</label>
                  <input type="text" class="form-control" name="template_name" required>
                </div>
                
                <div class="mb-3">
                  <label>Email Subject:</label>
                  <input type="text" class="form-control" name="subject" required>
                  <small class="form-text text-muted">You can use variables like {customer_name} in the subject</small>
                </div>
                
                <div class="mb-3">
                  <label>Email Body:</label>
                  <textarea class="form-control" name="body" id="email_body" rows="10" required></textarea>
                  <small class="form-text text-muted">Standard variables: {customer_name}, {vehicle_number}, {total_premium}, {link}</small>
                </div>
                
                <div class="mb-3">
                  <label>Signature Type:</label>
                  <select class="form-control" name="signature_type">
                    <option value="1">Standard (Sarthi Enterprises)</option>
                    <option value="2">Support Team</option>
                    <option value="3">Sales Team</option>
                  </select>
                </div>
                
                <div class="custom-fields mb-4">
                  <label>Add Custom Variables (Optional):</label>
                  <div id="custom-fields-container">
                    <!-- Custom fields will be added here -->
                  </div>
                  <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-custom-field">
                    <i class="bi bi-plus-circle"></i> Add Custom Variable
                  </button>
                </div>
                
                <div class="d-flex justify-content-between">
                  <button type="submit" class="btn btn-primary">Save Template</button>
                  <a href="manage_templates.php" class="btn btn-secondary">Cancel</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <?php require('include/footer.php'); ?>
</div>

<script>
  $(document).ready(function() {
    // Initialize rich text editor (if available)
    if(typeof ClassicEditor !== 'undefined') {
      ClassicEditor
        .create(document.querySelector('#email_body'))
        .catch(error => {
          console.error(error);
        });
    }
    
    // Custom fields manager
    let fieldCounter = 0;
    
    $('#add-custom-field').click(function() {
      fieldCounter++;
      const fieldHTML = `
        <div class="row custom-field-row mb-2">
          <div class="col-md-5">
            <input type="text" class="form-control" name="custom_vars[${fieldCounter}][name]" placeholder="Variable name (without {})">
          </div>
          <div class="col-md-5">
            <input type="text" class="form-control" name="custom_vars[${fieldCounter}][label]" placeholder="Display label">
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm remove-field">Remove</button>
          </div>
        </div>
      `;
      $('#custom-fields-container').append(fieldHTML);
    });
    
    // Remove custom field
    $(document).on('click', '.remove-field', function() {
      $(this).closest('.custom-field-row').remove();
    });
  });
</script>