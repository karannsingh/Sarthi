<?php
// Start session and include necessary files
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

$editing = false;
$menuItem = [
    'MenuName' => '',
    'MenuIcon' => '',
    'MenuLink' => '',
    'ParentID' => 0,
    'MenuOrder' => 0,
    'IsActive' => 1
];

$message = '';
$messageType = '';

// Determine mode (add/edit)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editing = true;
    $menuId = (int)$_GET['id'];

    // Fetch menu details for editing
    $stmt = $conn->prepare("SELECT * FROM master_menu WHERE MenuID = ?");
    $stmt->bind_param('i', $menuId);
    $stmt->execute();
    $result = $stmt->get_result();
    $menuItem = $result->fetch_assoc();
    if (!$menuItem) {
        header("Location: manage_menu_items.php");
        exit;
    }
    $stmt->close();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $menuName = trim($_POST['menuName']);
        $menuIcon = trim($_POST['menuIcon']);
        $menuLink = trim($_POST['menuLink']);
        $parentID = (int)$_POST['parentID'];
        $menuOrder = (int)$_POST['menuOrder'];
        $isActive = isset($_POST['isActive']) ? 1 : 0;

        if (empty($menuName)) {
            throw new Exception("Menu name is required.");
        }

        if ($editing && $parentID == $menuId) {
            throw new Exception("A menu item cannot be its own parent.");
        }

        if ($editing) {
            // Update
            $stmt = $conn->prepare("UPDATE master_menu SET MenuName=?, MenuIcon=?, MenuLink=?, ParentID=?, MenuOrder=?, IsActive=? WHERE MenuID=?");
            $stmt->bind_param('sssiisi', $menuName, $menuIcon, $menuLink, $parentID, $menuOrder, $isActive, $menuId);
            $stmt->execute();
            $message = "Menu updated successfully.";
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO master_menu (MenuName, MenuIcon, MenuLink, ParentID, MenuOrder, IsActive) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssiis', $menuName, $menuIcon, $menuLink, $parentID, $menuOrder, $isActive);
            $stmt->execute();
            $newMenuId = $conn->insert_id;

            // Assign default access to admin
            $stmt2 = $conn->prepare("INSERT INTO menu_access (MenuID, DesignationOID, HasAccess) VALUES (?, 1, 1)");
            $stmt2->bind_param('i', $newMenuId);
            $stmt2->execute();
            $stmt2->close();

            $message = "Menu added successfully.";
        }

        $messageType = "success";
        $stmt->close();
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch parent menus
$parentMenus = [];
$excludeID = $editing ? $menuId : 0;
$stmt = $conn->prepare("SELECT MenuID, MenuName FROM master_menu WHERE ParentID = 0 AND MenuID != ? ORDER BY MenuOrder");
$stmt->bind_param('i', $excludeID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $parentMenus[] = $row;
}
$stmt->close();
?>

<div class="wrapper d-flex flex-column min-vh-100 bg-light">
    <?php include('include/header.php'); ?>
    <div class="body flex-grow-1 px-3">
        <div class="container-lg">
            <div class="fs-4 fw-bold mb-3"><?= $editing ? "Edit Menu Item" : "Add Menu Item" ?></div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Menu Name <span class="text-danger">*</span></label>
                        <input type="text" name="menuName" class="form-control" required value="<?= htmlspecialchars($menuItem['MenuName']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Menu Icon</label>
                        <input type="text" name="menuIcon" class="form-control" placeholder="e.g. cil-speedometer" value="<?= htmlspecialchars($menuItem['MenuIcon']) ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Menu Link</label>
                        <input type="text" name="menuLink" class="form-control" placeholder="e.g. index.php or #" value="<?= htmlspecialchars($menuItem['MenuLink']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Parent Menu</label>
                        <select name="parentID" class="form-select">
                            <option value="0">None (Main Menu)</option>
                            <?php foreach ($parentMenus as $parent): ?>
                                <option value="<?= $parent['MenuID'] ?>" <?= $menuItem['ParentID'] == $parent['MenuID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($parent['MenuName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Menu Order</label>
                        <input type="number" name="menuOrder" class="form-control" value="<?= $menuItem['MenuOrder'] ?>" min="0">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="isActive" <?= $menuItem['IsActive'] ? 'checked' : '' ?>>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary"><?= $editing ? 'Update' : 'Add' ?> Menu</button>
                    <a href="manage_menu_items.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('include/footer.php'); ?>
