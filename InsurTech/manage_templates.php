<!-- manage_templates.php -->
<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');
?>

<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php require('include/header.php'); ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="fs-2 fw-semibold">Manage Email Templates</div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item">
            <span>Templates</span>
          </li>
          <li class="breadcrumb-item active"><span>Manage Templates</span></li>
        </ol>
      </nav>
      
      <div class="row mb-3">
        <div class="col-md-6">
          <a href="create_template.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Template
          </a>
        </div>
        <div class="col-md-6">
          <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Search templates..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit" class="btn btn-outline-secondary">Search</button>
          </form>
        </div>
      </div>
      
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
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Template Name</th>
                      <th>Subject</th>
                      <th>Created</th>
                      <th>Last Updated</th>
                      <th>Custom Fields</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    // Prepare search condition
                    $search_condition = "";
                    $params = [];
                    $types = "";
                    
                    if(isset($_GET['search']) && !empty($_GET['search'])) {
                      $search = "%{$_GET['search']}%";
                      $search_condition = " WHERE name LIKE ? OR subject LIKE ?";
                      $params = [$search, $search];
                      $types = "ss";
                    }
                    
                    // Pagination
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $limit = 10;
                    $offset = ($page - 1) * $limit;
                    
                    // Count total records
                    $count_query = "SELECT COUNT(*) as total FROM email_templates" . $search_condition;
                    $stmt = $conn->prepare($count_query);
                    if(!empty($params)) {
                      $stmt->bind_param($types, ...$params);
                    }
                    $stmt->execute();
                    $total_records = $stmt->get_result()->fetch_assoc()['total'];
                    $total_pages = ceil($total_records / $limit);
                    
                    // Get templates
                    $query = "SELECT id, name, subject, created_at, updated_at, custom_fields FROM email_templates" . $search_condition . " ORDER BY created_at DESC LIMIT ?, ?";
                    
                    $stmt = $conn->prepare($query);
                    if(!empty($params)) {
                      $final_types = $types . "ii";
                      $final_params = array_merge($params, [$offset, $limit]);
                      $stmt->bind_param($final_types, ...$final_params);
                    } else {
                      $stmt->bind_param("ii", $offset, $limit);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if($result->num_rows > 0) {
                      while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
                        echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                        echo "<td>" . date('M d, Y', strtotime($row['updated_at'])) . "</td>";
                        
                        // Display custom fields count
                        $custom_fields = !empty($row['custom_fields']) ? json_decode($row['custom_fields'], true) : [];
                        $field_count = count($custom_fields);
                        echo "<td>" . ($field_count > 0 ? $field_count : 'None') . "</td>";
                        
                        echo "<td>
                          <div class='btn-group'>
                            <a href='edit_template.php?id={$row['id']}' class='btn btn-sm btn-primary'>Edit</a>
                            <a href='view_template.php?id={$row['id']}' class='btn btn-sm btn-info'>View</a>
                            <button type='button' class='btn btn-sm btn-danger delete-template' data-id='{$row['id']}' data-name='" . htmlspecialchars($row['name']) . "'>Delete</button>
                          </div>
                        </td>";
                        echo "</tr>";
                      }
                    } else {
                      echo "<tr><td colspan='7' class='text-center'>No templates found</td></tr>";
                    }
                    ?>
                  </tbody>
                </table>
              </div>
              
              <?php if($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                  <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                      <a class="page-link" href="?page=<?= $page-1 ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?>">Previous</a>
                    </li>
                    
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                      <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?>"><?= $i ?></a>
                      </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                      <a class="page-link" href="?page=<?= $page+1 ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?>">Next</a>
                    </li>
                  </ul>
                </nav>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <?php require('include/footer.php'); ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteTemplateModal" tabindex="-1" aria-labelledby="deleteTemplateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteTemplateModalLabel">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete the template: <span id="template-name"></span>?</p>
        <p class="text-danger">This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form id="delete-form" method="POST" action="function/delete_template.php">
          <input type="hidden" name="template_id" id="delete-template-id">
          <button type="submit" class="btn btn-danger">Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    // Delete template confirmation
    $('.delete-template').click(function() {
      const templateId = $(this).data('id');
      const templateName = $(this).data('name');
      
      $('#delete-template-id').val(templateId);
      $('#template-name').text(templateName);
      $('#deleteTemplateModal').modal('show');
    });
  });
</script>