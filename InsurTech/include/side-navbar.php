<!-- include/side-navbar.php -->
<?php
if (!isset($_SESSION['LOGIN']) || $_SESSION['LOGIN'] !== true) {
  header('Location: logout.php');
  exit;
}
function getUserMenuItems($conn, $designation, $department) {
    try {
        $query = "
            SELECT m.* 
            FROM master_menu m
            JOIN menu_access ma ON m.MenuID = ma.MenuID
            WHERE ma.DesignationOID = ? 
            AND ma.HasAccess = 1
            AND (ma.DepartmentOID IS NULL OR ma.DepartmentOID = ?)
            AND m.IsActive = 1
            ORDER BY m.ParentID, m.MenuOrder ASC
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $designation, $department);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $menuItems = [];
        while ($row = $result->fetch_assoc()) {
            $menuItems[] = $row;
        }
        
        return $menuItems;
    } catch (Exception $e) {
        error_log("Database Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Build a hierarchical menu structure from flat menu items
 * 
 * @param array $menuItems Flat array of menu items
 * @param int $parentId Parent ID to filter by
 * @return array Hierarchical menu structure
 */
function buildMenuTree($menuItems, $parentId = 0) {
    $branch = [];
    
    foreach ($menuItems as $item) {
        if ($item['ParentID'] == $parentId) {
            $children = buildMenuTree($menuItems, $item['MenuID']);
            if ($children) {
                $item['children'] = $children;
            }
            $branch[] = $item;
        }
    }
    
    return $branch;
}

/**
 * Render the menu HTML
 * 
 * @param array $menuItems Hierarchical menu structure
 * @return string HTML for the menu
 */
function renderMenu($menuItems) {
    $html = '';
    
    foreach ($menuItems as $item) {
        // Check if this is a title only item (no link)
        if (empty($item['MenuLink']) && empty($item['children'])) {
            $html .= '<li class="nav-title">' . htmlspecialchars($item['MenuName']) . '</li>';
            continue;
        }
        
        // Check if this is a menu with children
        if (!empty($item['children'])) {
            $html .= '<li class="nav-group">';
            $html .= '<a class="nav-link nav-group-toggle" href="#">';
            
            // Add icon if available
            if (!empty($item['MenuIcon'])) {
                $html .= '<svg class="nav-icon"><use xlink:href="assets/@coreui/icons/svg/free.svg#' . htmlspecialchars($item['MenuIcon']) . '"></use></svg>';
            }
            
            $html .= htmlspecialchars($item['MenuName']) . '</a>';
            $html .= '<ul class="nav-group-items">';
            
            // Render child items
            foreach ($item['children'] as $child) {
                $html .= '<li class="nav-item">';
                $html .= '<a class="nav-link" href="' . htmlspecialchars($child['MenuLink']) . '">';
                
                // Add icon if available
                if (!empty($child['MenuIcon'])) {
                    $html .= '<svg class="nav-icon"><use xlink:href="assets/@coreui/icons/svg/free.svg#' . htmlspecialchars($child['MenuIcon']) . '"></use></svg>';
                } else {
                    $html .= '<span class="nav-icon"></span>';
                }
                
                $html .= htmlspecialchars($child['MenuName']) . '</a></li>';
            }
            
            $html .= '</ul></li>';
        } else {
            // This is a regular menu item
            $html .= '<li class="nav-item">';
            $html .= '<a class="nav-link" href="' . htmlspecialchars($item['MenuLink']) . '">';
            
            // Add icon if available
            if (!empty($item['MenuIcon'])) {
                $html .= '<svg class="nav-icon"><use xlink:href="assets/@coreui/icons/svg/free.svg#' . htmlspecialchars($item['MenuIcon']) . '"></use></svg>';
            }
            
            $html .= htmlspecialchars($item['MenuName']) . '</a></li>';
        }
    }
    
    return $html;
}

// Get menu items for the current user
$userDesignation = $_SESSION['ROLE'] ?? 0;
$userDepartment = $_SESSION['DEPARTMENT'] ?? 0;
$menuItems = getUserMenuItems($conn, $userDesignation, $userDepartment);
$menuTree = buildMenuTree($menuItems);
$menuHtml = renderMenu($menuTree);
?>

<style type="text/css">
  .nav-item .nav-link{
    padding-left:1.5rem!important;
  }
</style>
<div class="sidebar sidebar-dark sidebar-fixed bg-dark-gradient" id="sidebar">
    <div class="sidebar-brand d-none d-md-flex">
        <img src="assets/img/logo/SE.jpg" width="56" height="46" alt="InsurTech" class="sidebar-brand-full">
        <img src="assets/img/logo/Sarthi-white.png" width="46" height="46" alt="InsurTech" class="sidebar-brand-narrow">
        <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
    </div>
    <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
        <?= $menuHtml ?>
    </ul>
</div>