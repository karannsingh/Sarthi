<?php
// manage_menu_items.php
require('include/top.php');

$message = '';
$messageType = '';

// Handle delete or toggle actions
if (isset($_GET['action'], $_GET['id']) && is_numeric($_GET['id'])) {
    $menuId = (int)$_GET['id'];

    if ($_GET['action'] === 'delete') {
        $conn->begin_transaction();
        try {
            $stmt1 = $conn->prepare("DELETE FROM menu_access WHERE MenuID = ?");
            $stmt1->bind_param("i", $menuId);
            $stmt1->execute();

            $stmt2 = $conn->prepare("DELETE FROM master_menu WHERE MenuID = ?");
            $stmt2->bind_param("i", $menuId);
            $stmt2->execute();

            $conn->commit();
            // Store message in session
            $_SESSION['message'] = "Menu item deleted successfully.";
            $_SESSION['messageType'] = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['message'] = "Error deleting menu item.";
            $_SESSION['messageType'] = "danger";
        }
        header("Location: manage_menu_items.php");
        exit;
    } elseif ($_GET['action'] === 'toggle') {
        $stmt = $conn->prepare("UPDATE master_menu SET IsActive = 1 - IsActive WHERE MenuID = ?");
        $stmt->bind_param("i", $menuId);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Menu item status updated.";
            $_SESSION['messageType'] = "success";
        } else {
            $_SESSION['message'] = "Error updating status.";
            $_SESSION['messageType'] = "danger";
        }
        header("Location: manage_menu_items.php");
        exit;
    }
}

require('include/side-navbar.php');
require('include/right-side-navbar.php');

// Fetch all menu items with parent name
$query = "
    SELECT m.MenuID, m.MenuName, m.MenuIcon, m.MenuLink, m.ParentID, m.MenuOrder, m.IsActive,
           p.MenuName AS ParentName
    FROM master_menu m
    LEFT JOIN master_menu p ON m.ParentID = p.MenuID
    ORDER BY m.ParentID, m.MenuOrder
";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$menuItems = $result->fetch_all(MYSQLI_ASSOC);
?>

<!-- HTML Content -->
<div class="wrapper d-flex flex-column min-vh-100 bg-light">
    <?php require('include/header.php'); ?>

    <div class="body flex-grow-1 px-3">
        <div class="container-lg">
            <div class="fs-2 fw-semibold">Manage Menu Items</div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Menu Management</li>
                </ol>
            </nav>

            <?php
            if (!empty($_SESSION['message'])):
                $message = $_SESSION['message'];
                $messageType = $_SESSION['messageType'];
                unset($_SESSION['message'], $_SESSION['messageType']);
            ?>
                <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">All Menu Items</h4>
                <a href="menu_item_form.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add New
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Menu Name</th>
                                <th>Icon</th>
                                <th>Link</th>
                                <th>Parent Menu</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($menuItems)): ?>
                            <?php foreach ($menuItems as $item): ?>
                                <tr>
                                    <td><?= $item['MenuID'] ?></td>
                                    <td><?= htmlspecialchars($item['MenuName']) ?></td>
                                    <td><?= htmlspecialchars($item['MenuIcon'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($item['MenuLink'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($item['ParentName'] ?: 'Main Menu') ?></td>
                                    <td><?= $item['MenuOrder'] ?></td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                   id="toggle_<?= $item['MenuID'] ?>"
                                                   <?= $item['IsActive'] ? 'checked' : '' ?>
                                                   onchange="toggleStatus(<?= $item['MenuID'] ?>)">
                                            <label class="form-check-label" for="toggle_<?= $item['MenuID'] ?>">
                                                <?= $item['IsActive'] ? 'Active' : 'Inactive' ?>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="menu_item_form.php?id=<?= $item['MenuID'] ?>"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Edit"><i class="bi bi-pencil-square"></i></a>

                                        <a href="manage_menu_items.php?action=delete&id=<?= $item['MenuID'] ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this menu item? This will also remove its access permissions.')">
                                           <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center">No menu items found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Toggle JS -->
<script>
function toggleStatus(menuId) {
    if (confirm("Are you sure you want to toggle the status of this menu item?")) {
        window.location.href = "manage_menu_items.php?action=toggle&id=" + menuId;
    } else {
        const checkbox = document.getElementById("toggle_" + menuId);
        checkbox.checked = !checkbox.checked;
    }
}
</script>

<?php include 'include/footer.php'; ?>