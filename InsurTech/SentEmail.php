<!-- Updated SentEmail.php -->
<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

?>
<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php
  require('include/header.php');
  ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="fs-2 fw-semibold">Send Email</div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item">
            <span>Email</span>
          </li>
          <li class="breadcrumb-item active"><span>Send Email</span></li>
        </ol>
      </nav>

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
      
      <div class="row">
        <div class="col-lg-12">
          <div class="card mb-4">
            <div class="card-body p-4">
              <div class="card-title fs-4 fw-semibold">Email Composition Form</div>
              <form action="function/send_email.php" method="POST">
                <div class="row mb-3">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="template_id" class="form-label">Select Template:</label>
                      <select class="form-select" name="template_id" id="template_id">
                        <option value="">-- Select Template --</option>
                        <?php
                        $result = $conn->query("SELECT id, name FROM email_templates ORDER BY name ASC");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                        ?>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="customer_email" class="form-label">Customer Email:</label>
                      <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                    </div>
                    
                    <div class="mb-3">
                      <label for="customer_name" class="form-label">Customer Name:</label>
                      <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <!-- Custom fields will be dynamically added here -->
                    <div id="custom-fields-container"></div>
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="email_subject" class="form-label">Email Subject:</label>
                  <input type="text" class="form-control" id="email_subject" name="email_subject" required>
                </div>
                
                <div class="mb-3">
                  <label for="email_body" class="form-label">Email Body:</label>
                  <textarea class="form-control" id="email_body" name="email_body" rows="10" required></textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                  <button type="submit" class="btn btn-success">
                    <i class="bi bi-envelope"></i> Send Email
                  </button>
                  <button type="button" class="btn btn-secondary" id="preview-btn">
                    <i class="bi bi-eye"></i> Preview Email
                  </button>
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

<!-- Email Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="previewModalLabel">Email Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <strong>To:</strong> <span id="preview-to"></span>
        </div>
        <div class="mb-3">
          <strong>Subject:</strong> <span id="preview-subject"></span>
        </div>
        <hr>
        <div id="preview-body" class="border p-3 bg-white"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // When template is selected
    $("#template_id").change(function() {
        var templateId = $(this).val();
        if (templateId !== "") {
            // Clear custom fields container
            $("#custom-fields-container").empty();
            
            // Fetch template data
            $.ajax({
                url: 'function/fetch_template_email.php',
                type: 'POST',
                data: { template_id: templateId },
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        alert(response.error);
                        return;
                    }
                    
                    // Set subject and body 
                    $("#email_subject").val(response.subject);
                    $("#email_body").val(response.body);
                    
                    // Add custom fields if any
                    if (response.custom_fields && response.custom_fields.length > 0) {
                        var customFieldsHtml = '<div class="mb-4"><h5>Custom Fields</h5>';
                        
                        response.custom_fields.forEach(function(field) {
                            customFieldsHtml += `
                                <div class="mb-3">
                                    <label for="custom_field_${field.name}" class="form-label">${field.label}:</label>
                                    <input type="text" class="form-control custom-field" 
                                        id="custom_field_${field.name}" 
                                        name="custom_field[${field.name}]" 
                                        data-var="{${field.name}}">
                                </div>
                            `;
                        });
                        
                        customFieldsHtml += '</div>';
                        $("#custom-fields-container").html(customFieldsHtml);
                    }
                    
                    // Update template fields with available values
                    updateTemplateFields();
                },
                error: function() {
                    alert("Failed to fetch template data. Please try again.");
                }
            });
        } else {
            // Clear fields if no template selected
            $("#email_subject").val("");
            $("#email_body").val("");
            $("#custom-fields-container").empty();
        }
    });
    
    // Function to update template fields with available values
    function updateTemplateFields() {
        var subject = $("#email_subject").val();
        var body = $("#email_body").val();
        
        // Update standard fields
        var customerName = $("#customer_name").val() || '';
        var vehicleNumber = $("#vehicle_number").val() || '';
        var totalPremium = $("#total_premium").val() || '';
        var link = $("#link").val() || '';
        
        subject = subject.replace(/\{customer_name\}/g, customerName)
                         .replace(/\{vehicle_number\}/g, vehicleNumber)
                         .replace(/\{total_premium\}/g, totalPremium)
                         .replace(/\{link\}/g, link);
        
        body = body.replace(/\{customer_name\}/g, customerName)
                   .replace(/\{vehicle_number\}/g, vehicleNumber)
                   .replace(/\{total_premium\}/g, totalPremium)
                   .replace(/\{link\}/g, link);
        
        // Update custom fields
        $(".custom-field").each(function() {
            var varName = $(this).data('var');
            var varValue = $(this).val() || '';
            
            subject = subject.replace(new RegExp(varName, 'g'), varValue);
            body = body.replace(new RegExp(varName, 'g'), varValue);
        });
        
        $("#email_subject").val(subject);
        $("#email_body").val(body);
    }
    
    // Listen for changes on standard fields and update template
    $("#customer_name, #vehicle_number, #total_premium, #link").on('input', function() {
        updateTemplateFields();
    });
    
    // Listen for changes on custom fields and update template
    $(document).on('input', '.custom-field', function() {
        updateTemplateFields();
    });
    
    // Preview button
    $("#preview-btn").click(function(e) {
        e.preventDefault();
        
        $("#preview-to").text($("#customer_email").val());
        $("#preview-subject").text($("#email_subject").val());
        $("#preview-body").html($("#email_body").val());
        
        $("#previewModal").modal('show');
    });
});
</script>