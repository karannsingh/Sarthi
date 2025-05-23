<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $designationId = (int)($_POST['designationId'] ?? 0);
    $departmentId = ($_POST['departmentId'] !== '') ? (int)$_POST['departmentId'] : null;
    $menuAccess = $_POST['menuAccess'] ?? [];

    try {
        $conn->begin_transaction();

        // Delete existing access
        if ($departmentId === null) {
            $stmt = $conn->prepare("DELETE FROM menu_access WHERE DesignationOID = ? AND DepartmentOID IS NULL");
            $stmt->bind_param("i", $designationId);
        } else {
            $stmt = $conn->prepare("DELETE FROM menu_access WHERE DesignationOID = ? AND DepartmentOID = ?");
            $stmt->bind_param("ii", $designationId, $departmentId);
        }
        $stmt->execute();

        // Re-insert access
        if (!empty($menuAccess)) {
            $insertQuery = "INSERT INTO menu_access (MenuID, DesignationOID, DepartmentOID, HasAccess) VALUES (?, ?, ?, 1)";
            $stmt = $conn->prepare($insertQuery);

            foreach ($menuAccess as $menuId) {
                $menuId = (int)$menuId;

                if ($departmentId === null) {
                    // Apply access to all departments
                    $result = $conn->query("SELECT DepartmentOID FROM master_department");
                    while ($row = $result->fetch_assoc()) {
                        $deptOID = $row['DepartmentOID'];
                        $stmt->bind_param("iii", $menuId, $designationId, $deptOID);
                        $stmt->execute();
                    }

                    // Insert for NULL department
                    $nullDept = null;
                    $stmt->bind_param("iii", $menuId, $designationId, $nullDept);
                    $stmt->execute();
                } else {
                    $stmt->bind_param("iii", $menuId, $designationId, $departmentId);
                    $stmt->execute();
                }
            }
        }

        $conn->commit();
        $message = "Menu access updated successfully.";
        $messageType = "success";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Update failed: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Load dropdown values
$designations = $conn->query("SELECT DesignationOID, DesignationName FROM master_designation ORDER BY DesginationLevel");
$departments = $conn->query("SELECT DepartmentOID, DepartmentName FROM master_department ORDER BY DepartmentName");

// Get selected filters
$selectedDesignation = isset($_GET['designation']) ? (int)$_GET['designation'] : 0;
$selectedDepartment = $_GET['department'] ?? '';

// Load menus
$menuItems = [];
$menus = $conn->query("SELECT * FROM master_menu ORDER BY ParentID, MenuOrder");
while ($row = $menus->fetch_assoc()) {
    $menuItems[] = $row;
}

// Load current access
$currentAccess = [];
if ($selectedDesignation) {
    if ($selectedDepartment === '') {
        $stmt = $conn->prepare("SELECT MenuID FROM menu_access WHERE DesignationOID = ?");
        $stmt->bind_param("i", $selectedDesignation);
    } else {
        $stmt = $conn->prepare("SELECT MenuID FROM menu_access WHERE DesignationOID = ? AND DepartmentOID = ?");
        $stmt->bind_param("ii", $selectedDesignation, $selectedDepartment);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $currentAccess[] = $row['MenuID'];
    }
}
?>

<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
    <?php require('include/header.php'); ?>

    <div class="body flex-grow-1 px-3">
        <div class="container-lg">
            <div class="fs-2 fw-semibold">Manage Menu Access</div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><span>Menu Management</span></li>
                    <li class="breadcrumb-item active"><span>Manage Menu Access</span></li>
                </ol>
            </nav>

            <div class="card mb-4">
                <div class="card-header"><strong>Manage Menu Access</strong></div>
                <div class="card-body">

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
                    <?php endif; ?>

                    <!-- Filter Form -->
                    <form method="GET" class="form-inline mb-3">
                        <label class="mr-2">Designation:</label>
                        <select name="designation" class="form-control mr-3" required onchange="this.form.submit()">
                            <option value="">-- Select --</option>
                            <?php while ($row = $designations->fetch_assoc()): ?>
                                <option value="<?= $row['DesignationOID'] ?>" <?= $row['DesignationOID'] == $selectedDesignation ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['DesignationName']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>

                        <label class="mr-2">Department:</label>
                        <select name="department" class="form-control mr-3" onchange="this.form.submit()">
                            <option value="">-- All --</option>
                            <?php while ($row = $departments->fetch_assoc()): ?>
                                <option value="<?= $row['DepartmentOID'] ?>" <?= $row['DepartmentOID'] == $selectedDepartment ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['DepartmentName']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>

                    <!-- Access Form -->
                    <?php if ($selectedDesignation): ?>
                        <form method="POST">
                            <input type="hidden" name="designationId" value="<?= $selectedDesignation ?>">
                            <input type="hidden" name="departmentId" value="<?= $selectedDepartment ?>">

                            <table class="table table-bordered table-hover">
                                <thead class="thead-dark">
                                  <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Menu Name</th>
                                    <th>
                                      <input type="checkbox" id="selectAll" title="Select All">
                                      Access
                                    </th>
                                  </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $counter = 1;
                                    foreach ($menuItems as $menu):
                                        $parentLabel = '';
                                        if ($menu['ParentID'] != 0) {
                                            foreach ($menuItems as $parent) {
                                                if ($parent['MenuID'] == $menu['ParentID']) {
                                                    $parentLabel = $parent['MenuName'] . ' > ';
                                                    break;
                                                }
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td><?= htmlspecialchars($parentLabel . $menu['MenuName']) ?></td>
                                        <td>
                                            <input type="checkbox" name="menuAccess[]" value="<?= $menu['MenuID'] ?>"
                                                <?= in_array($menu['MenuID'], $currentAccess) ? 'checked' : '' ?>>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <button type="submit" class="btn btn-primary">Update Access</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require('include/footer.php'); ?>

<!-- JavaScript Section -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const selectAllCheckbox = document.getElementById("selectAll");
    const checkboxes = document.querySelectorAll("input[name='menuAccess[]']");

    // Hierarchy logic (auto-check parent if child is selected)
    const menuParents = {};
    <?php foreach ($menuItems as $menu): ?>
        <?php if ($menu['ParentID'] != 0): ?>
            menuParents[<?= $menu['MenuID'] ?>] = <?= $menu['ParentID'] ?>;
        <?php endif; ?>
    <?php endforeach; ?>

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            const menuId = parseInt(this.value);
            if (this.checked && menuParents[menuId]) {
                const parentCheckbox = document.querySelector("input[name='menuAccess[]'][value='" + menuParents[menuId] + "']");
                if (parentCheckbox && !parentCheckbox.checked) {
                    parentCheckbox.checked = true;
                }
            }
            updateSelectAllState();
        });
    });

    selectAllCheckbox.addEventListener('change', function () {
        const isChecked = this.checked;
        checkboxes.forEach(cb => cb.checked = isChecked);
    });

    function updateSelectAllState() {
        const total = checkboxes.length;
        const checked = Array.from(checkboxes).filter(cb => cb.checked).length;

        if (checked === total) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (checked > 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }

    updateSelectAllState(); // Initialize state
});
</script>