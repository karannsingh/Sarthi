<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$data = [
    'employee_id' => '',
    'basic_salary' => '',
    'hra' => '',
    'special_allowance' => '',
    'pf_employer_contribution' => '',
    'pf_employee_contribution' => '',
    'professional_tax' => '',
    'account_number' => '',
    'bank_name' => '',
    'bank_branch' => '',
    'ifsc_code' => ''
];

if ($isEdit) {
    $res = mysqli_query($conn, "SELECT * FROM employee_salary WHERE id = $id AND IsDeleted = 0");
    if (mysqli_num_rows($res) > 0) {
        $data = mysqli_fetch_assoc($res);
    } else {
        header("Location: BankDetails.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'];
    $basic_salary = $_POST['basic_salary'];
    $hra = $_POST['hra'];
    $special_allowance = $_POST['special_allowance'];
    $pf_employer = $_POST['pf_employer'];
    $pf_employee = $_POST['pf_employee'];
    $pt = $_POST['pt'];
    $account_number = $_POST['account_number'];
    $bank_name = $_POST['bank_name'];
    $bank_branch = $_POST['bank_branch'];
    $bank_IFSC = $_POST['bank_IFSC'];

    if ($isEdit) {
        // Update query
        $query = "UPDATE employee_salary SET 
            basic_salary = '$basic_salary',
            hra = '$hra',
            special_allowance = '$special_allowance',
            pf_employer_contribution = '$pf_employer',
            pf_employee_contribution = '$pf_employee',
            professional_tax = '$pt',
            account_number = '$account_number',
            bank_name = '$bank_name',
            bank_branch = '$bank_branch',
            ifsc_code = '$bank_IFSC'
            WHERE id = $id";
    } else {
        // Insert query
        $query = "INSERT INTO employee_salary (employee_id, basic_salary, hra, special_allowance, pf_employer_contribution, pf_employee_contribution, professional_tax, account_number, bank_name, bank_branch, ifsc_code, IsDeleted)
            VALUES ('$employee_id', '$basic_salary', '$hra', '$special_allowance', '$pf_employer', '$pf_employee', '$pt', '$account_number', '$bank_name', '$bank_branch', '$bank_IFSC', 0)";
    }

    mysqli_query($conn, $query);
    header("Location: BankDetails.php");
    exit;
}
?>

<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php require('include/header.php'); ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="fs-2 fw-semibold"><?= $isEdit ? 'Update' : 'Add' ?> Salary/Bank Details</div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item"><span>Home</span></li>
          <li class="breadcrumb-item active"><span><?= $isEdit ? 'Update' : 'Add' ?> Salary/Bank Details</span></li>
        </ol>
      </nav>

      <div class="row">
        <div class="col-md-12">
          <div class="card mb-4">
            <div class="card-header"><strong><?= $isEdit ? 'Update' : 'Add' ?> Salary/Bank Details</strong></div>
            <div class="card-body">
              <form method="post">
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label class="form-label">Select Employee</label>
                    <select name="employee_id" class="form-select" <?= $isEdit ? 'disabled' : 'required' ?>>
                      <option value="">Select</option>
                      <?php
                      if ($isEdit) {
                          // Load current user
                          $empRes = mysqli_query($conn, "SELECT UserOID, UserName FROM users WHERE UserOID = {$data['employee_id']}");
                      } else {
                          // Load users not in salary table
                          $empRes = mysqli_query($conn, "SELECT UserOID, UserName FROM users WHERE Status = 1 AND Designation <> 1 AND UserOID NOT IN (SELECT employee_id FROM employee_salary WHERE IsDeleted = 0)");
                      }

                      while ($emp = mysqli_fetch_assoc($empRes)) {
                          $selected = ($emp['UserOID'] == $data['employee_id']) ? 'selected' : '';
                          echo "<option value='{$emp['UserOID']}' $selected>{$emp['UserName']}</option>";
                      }
                      ?>
                    </select>
                    <?php if ($isEdit): ?>
                      <input type="hidden" name="employee_id" value="<?= $data['employee_id'] ?>">
                    <?php endif; ?>
                  </div>
                </div>

                <?php
                function inputField($label, $name, $value, $required = true) {
                  $requiredAttr = $required ? 'required' : '';
                  return "
                    <div class='col-md-6'>
                      <label class='form-label'>{$label}</label>
                      <input type='text' step='0.01' name='{$name}' value='{$value}' class='form-control' {$requiredAttr}>
                    </div>";
                }
                ?>

                <div class="row mb-3">
                  <?= inputField('Basic Salary', 'basic_salary', $data['basic_salary']) ?>
                  <?= inputField('HRA', 'hra', $data['hra']) ?>
                </div>
                <div class="row mb-3">
                  <?= inputField('Special Allowance', 'special_allowance', $data['special_allowance']) ?>
                  <?= inputField('Employer PF', 'pf_employer', $data['pf_employer_contribution'], false) ?>
                </div>
                <div class="row mb-3">
                  <?= inputField('Employee PF', 'pf_employee', $data['pf_employee_contribution'], false) ?>
                  <?= inputField('Professional Tax', 'pt', $data['professional_tax'], false) ?>
                </div>
                <div class="row mb-3">
                  <?= inputField('Account Number', 'account_number', $data['account_number']) ?>
                  <?= inputField('Bank Name', 'bank_name', $data['bank_name']) ?>
                </div>
                <div class="row mb-3">
                  <?= inputField('Bank Branch', 'bank_branch', $data['bank_branch'], false) ?>
                  <?= inputField('IFSC Code', 'bank_IFSC', $data['ifsc_code'], false) ?>
                </div>

                <div class="text-center">
                  <button type="submit" class="btn btn-success me-2"><?= $isEdit ? 'Update' : 'Save' ?></button>
                  <a href="BankDetails.php" class="btn btn-secondary">Cancel</a>
                </div>
              </form>
            </div> <!-- card-body -->
          </div> <!-- card -->
        </div>
      </div>
    </div>
  </div>
</div>

<?php require('include/footer.php'); ?>