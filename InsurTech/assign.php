<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] != 1) {
    header("Location: index.php");
    exit();
}
?>
<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
    <?php
    require('include/header.php');
    ?>
    <div class="body flex-grow-1 px-3">
        <div class="container-lg">
            <div class="fs-2 fw-semibold">User Mapping</div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item">
                        <span>Users</span>
                    </li>
                    <li class="breadcrumb-item active"><span>User Mapping</span></li>
                </ol>
            </nav>
            <div class="card mb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="roleTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="manager-tab" data-bs-toggle="tab" data-bs-target="#manager" type="button">Assign Manager</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tl-tab" data-bs-toggle="tab" data-bs-target="#tl" type="button">Assign Team Leader</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="employee-tab" data-bs-toggle="tab" data-bs-target="#employee" type="button">Assign Employee</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="col-md-12">
                        <div class="tab-content mt-3" id="roleTabsContent">
                            <!-- Manager Assignment Tab -->
                            <div class="tab-pane fade show active" id="manager" role="tabpanel">
                                <form id="managerForm" class="needs-validation" novalidate>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Manager:</label>
                                                <select class="form-select" name="manager" id="managerSelect" required>
                                                    <option value="">-- Select Manager --</option>
                                                    <?php
                                                    $res = mysqli_query($conn, "SELECT u.UserOID, u.UserName FROM users u 
                                                                            WHERE u.Designation=2 AND u.IsDeleted=0 AND u.Status=1 
                                                                            ORDER BY u.UserName ASC");
                                                    while ($row = mysqli_fetch_assoc($res)) {
                                                        echo "<option value='" . htmlspecialchars($row['UserOID']) . "'>" . htmlspecialchars($row['UserName']) . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                                <div class="invalid-feedback">Please select a manager</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Company:</label>
                                                <select class="form-select" name="company" id="companySelect" required disabled>
                                                    <option value="">-- Select Manager First --</option>
                                                </select>
                                                <div class="invalid-feedback">Please select a company</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-person-check me-1"></i> Assign Manager
                                        </button>
                                    </div>
                                </form>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" id="searchManager" class="form-control" placeholder="Search...">
                                        </div>
                                    </div>
                                </div>
                                <div id="managerTable" class="table-responsive"></div>
                            </div>

                            <!-- Team Leader Assignment Tab -->
                            <div class="tab-pane fade" id="tl" role="tabpanel">
                                <form id="tlForm" class="needs-validation" novalidate>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Department:</label>
                                                <select class="form-select" name="department" id="tlDepartmentSelect" required>
                                                    <option value="">-- Select Department --</option>
                                                    <?php
                                                    $res = mysqli_query($conn, "SELECT DepartmentOID, DepartmentName FROM master_department ORDER BY DepartmentName ASC");
                                                    while ($row = mysqli_fetch_assoc($res)) {
                                                        echo "<option value='" . htmlspecialchars($row['DepartmentOID']) . "'>" . htmlspecialchars($row['DepartmentName']) . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                                <div class="invalid-feedback">Please select a department</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Manager:</label>
                                                <select class="form-select" name="manager" id="tlManagerSelect" required disabled>
                                                    <option value="">-- Select Department First --</option>
                                                </select>
                                                <div class="invalid-feedback">Please select a manager</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Team Leader:</label>
                                                <select class="form-select" name="teamleader" id="teamLeaderSelect" required disabled>
                                                    <option value="">-- Select Manager First --</option>
                                                </select>
                                                <div class="invalid-feedback">Please select a team leader</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-person-check me-1"></i> Assign Team Leader
                                        </button>
                                    </div>
                                </form>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" id="searchTL" class="form-control" placeholder="Search...">
                                        </div>
                                    </div>
                                </div>
                                <div id="tlTable" class="table-responsive"></div>
                            </div>

                            <!-- Employee Assignment Tab -->
                            <div class="tab-pane fade" id="employee" role="tabpanel">
                                <form id="empForm" class="needs-validation" novalidate>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Department:</label>
                                                <select class="form-select" name="department" id="empDepartmentSelect" required>
                                                    <option value="">-- Select Department --</option>
                                                    <?php
                                                    $res = mysqli_query($conn, "SELECT DepartmentOID, DepartmentName FROM master_department ORDER BY DepartmentName ASC");
                                                    while ($row = mysqli_fetch_assoc($res)) {
                                                        echo "<option value='" . htmlspecialchars($row['DepartmentOID']) . "'>" . htmlspecialchars($row['DepartmentName']) . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                                <div class="invalid-feedback">Please select a department</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Team Leader:</label>
                                                <select class="form-select" name="teamleader" id="empTeamLeaderSelect" required disabled>
                                                    <option value="">-- Select Department First --</option>
                                                </select>
                                                <div class="invalid-feedback">Please select a team leader</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Employee:</label>
                                                <select class="form-select" name="employee" id="employeeSelect" required disabled>
                                                    <option value="">-- Select Team Leader First --</option>
                                                </select>
                                                <div class="invalid-feedback">Please select an employee</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-person-check me-1"></i> Assign Employee
                                        </button>
                                    </div>
                                </form>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" id="searchEmp" class="form-control" placeholder="Search...">
                                        </div>
                                    </div>
                                </div>
                                <div id="empTable" class="table-responsive"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    require('include/footer.php');
    ?>
</div>

<!-- Add Bootstrap Icons for better UI -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<script>
// Function to display loading animation
function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = `
            <div class="d-flex justify-content-center my-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
    }
}

// Function to load tables with loading indicator
function loadTables() {
    showLoading("managerTable");
    showLoading("tlTable");
    showLoading("empTable");
    
    $("#managerTable").load("AssignRole/load_manager_assignments.php");
    $("#tlTable").load("AssignRole/load_tl_assignments.php");
    $("#empTable").load("AssignRole/load_emp_assignments.php");
}

// Custom toast notification
function showToast(message, type = 'success') {
    const toastId = 'custom-toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
        autohide: true,
        delay: 3000
    });
    toastElement.show();
    
    // Remove toast from DOM after it's hidden
    document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// Form validation
function validateForm(form) {
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return false;
    }
    return true;
}

$(document).ready(function() {
    // Initialize the page
    loadTables();

    // Validation for all forms
    document.querySelectorAll('.needs-validation').forEach(form => {
        form.addEventListener('submit', event => {
            if (!validateForm(form)) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });

    $('#managerSelect').on('change', function () {
    const managerId = $(this).val();
    const companySelect = $('#companySelect');

    if (managerId) {
        companySelect.prop('disabled', true).html('<option value="">Loading...</option>');

        $.ajax({
            url: 'AssignRole/get_companies_by_manager.php',
            type: 'POST',
            data: { manager_oid: managerId },
            dataType: 'json',
            success: function (data) {
                if (data.status === 'success') {
                    let options = '<option value="">-- Select Company --</option>';
                    data.companies.forEach(company => {
                        options += `<option value="${company.id}">${company.name}</option>`;
                    });
                    companySelect.html(options).prop('disabled', false);
                } else {
                    showToast(data.message || 'No companies found', 'danger');
                    companySelect.html('<option value="">-- No companies available --</option>').prop('disabled', true);
                }
            },
            error: function (xhr) {
                showToast("Server Error: " + xhr.status, 'danger');
                companySelect.html('<option value="">-- Error loading companies --</option>').prop('disabled', true);
            }
        });
    } else {
        companySelect.html('<option value="">-- Select Company --</option>').prop('disabled', true);
    }
});

    // Manager assignment form submission
    $("#managerForm").submit(function(e) {
        e.preventDefault();
        if (!validateForm(this)) return;
        
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');

        $.ajax({
            url: "AssignRole/assign_manager.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (data) {
                btn.prop('disabled', false).html(originalText);

                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    $("#managerForm")[0].reset();
                    $("#managerForm").removeClass('was-validated');
                    loadTables();
                } else {
                    showToast(data.message || "An error occurred", 'danger');
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).html(originalText);
                console.error("AJAX error", xhr.status, xhr.responseText);
                showToast("Server Error: " + xhr.status, 'danger');
            }
        });
    });

    // Team Leader Tab - Department change
    $("#tlDepartmentSelect").change(function() {
        const departmentId = $(this).val();
        if (departmentId) {
            $("#tlManagerSelect").prop('disabled', true).html('<option value="">Loading...</option>');
            $.get("AssignRole/get_managers_by_department.php", { department_id: departmentId }, function(response) {
                try {
                    const data = response;
                    let options = '<option value="">-- Select Manager --</option>';
                    if (data.status === 'success' && data.managers.length > 0) {
                        data.managers.forEach(manager => {
                            options += `<option value="${manager.UserOID}">${manager.UserName}</option>`;
                        });
                    } else {
                        showToast("No managers found for this department", 'danger');
                    }
                    $("#tlManagerSelect").html(options).prop('disabled', data.managers.length === 0);
                    $("#teamLeaderSelect").html('<option value="">-- Select Manager First --</option>').prop('disabled', true);
                } catch (e) {
                    showToast("Error loading managers: " + e.message, 'danger');
                    $("#tlManagerSelect").html('<option value="">-- Error Loading Managers --</option>').prop('disabled', true);
                }
            });
        } else {
            $("#tlManagerSelect").html('<option value="">-- Select Department First --</option>').prop('disabled', true);
            $("#teamLeaderSelect").html('<option value="">-- Select Manager First --</option>').prop('disabled', true);
        }
    });

    // Team Leader Tab - Manager change
    $("#tlManagerSelect").change(function() {
        const managerId = $(this).val();
        const departmentId = $("#tlDepartmentSelect").val();
        if (managerId && departmentId) {
            $("#teamLeaderSelect").prop('disabled', true).html('<option value="">Loading...</option>');
            $.get("AssignRole/get_teamleaders_by_department.php", { 
                manager_id: managerId,
                department_id: departmentId 
            }, function(response) {
                try {
                    const data = response;
                    let options = '<option value="">-- Select Team Leader --</option>';
                    if (data.status === 'success' && data.teamleaders.length > 0) {
                        data.teamleaders.forEach(tl => {
                            options += `<option value="${tl.UserOID}">${tl.UserName}</option>`;
                        });
                    } else {
                        showToast("No available team leaders found for this department", 'danger');
                    }
                    $("#teamLeaderSelect").html(options).prop('disabled', data.teamleaders.length === 0);
                } catch (e) {
                    showToast("Error loading team leaders: " + e.message, 'danger');
                    $("#teamLeaderSelect").html('<option value="">-- Error Loading Team Leaders --</option>').prop('disabled', true);
                }
            });
        } else {
            $("#teamLeaderSelect").html('<option value="">-- Select Manager First --</option>').prop('disabled', true);
        }
    });

    // Team Leader form submission
    $("#tlForm").submit(function(e) {
        e.preventDefault();
        if (!validateForm(this)) return;
        
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
        
        $.ajax({
            url: "AssignRole/assign_tl.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (data) {
                btn.prop('disabled', false).html(originalText);
                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    $("#tlForm")[0].reset();
                    $("#tlForm").removeClass('was-validated');
                    $("#tlManagerSelect").prop('disabled', true).html('<option value="">-- Select Department First --</option>');
                    $("#teamLeaderSelect").prop('disabled', true).html('<option value="">-- Select Manager First --</option>');
                    loadTables();
                } else {
                    showToast(data.message || "An error occurred", 'danger');
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).html(originalText);
                console.error("AJAX error", xhr.status, xhr.responseText);
                showToast("Server Error: " + xhr.status, 'danger');
            }
        });
    });

    // Employee Tab - Department change
    $("#empDepartmentSelect").change(function() {
        const departmentId = $(this).val();
        if (departmentId) {
            $("#empTeamLeaderSelect").prop('disabled', true).html('<option value="">Loading...</option>');
            $.get("AssignRole/get_teamleaders_by_department.php", { 
                department_id: departmentId,
                assigned_only: true
            }, function(response) {
                try {
                    const data = response;
                    let options = '<option value="">-- Select Team Leader --</option>';
                    if (data.status === 'success' && data.teamleaders.length > 0) {
                        data.teamleaders.forEach(tl => {
                            options += `<option value="${tl.UserOID}">${tl.UserName}</option>`;
                        });
                    } else {
                        showToast("No team leaders found for this department", 'danger');
                    }
                    $("#empTeamLeaderSelect").html(options).prop('disabled', data.teamleaders.length === 0);
                    $("#employeeSelect").html('<option value="">-- Select Team Leader First --</option>').prop('disabled', true);
                } catch (e) {
                    showToast("Error loading team leaders: " + e.message, 'danger');
                    $("#empTeamLeaderSelect").html('<option value="">-- Error Loading Team Leaders --</option>').prop('disabled', true);
                }
            });
        } else {
            $("#empTeamLeaderSelect").html('<option value="">-- Select Department First --</option>').prop('disabled', true);
            $("#employeeSelect").html('<option value="">-- Select Team Leader First --</option>').prop('disabled', true);
        }
    });

    // Employee Tab - Team Leader change
    $("#empTeamLeaderSelect").change(function() {
        const teamLeaderId = $(this).val();
        const departmentId = $("#empDepartmentSelect").val();
        if (teamLeaderId && departmentId) {
            $("#employeeSelect").prop('disabled', true).html('<option value="">Loading...</option>');
            $.get("AssignRole/get_employees_by_department.php", { 
                teamleader_id: teamLeaderId,
                department_id: departmentId 
            }, function(response) {
                try {
                    const data = response;
                    let options = '<option value="">-- Select Employee --</option>';
                    if (data.status === 'success' && data.employees.length > 0) {
                        data.employees.forEach(emp => {
                            options += `<option value="${emp.UserOID}">${emp.UserName}</option>`;
                        });
                    } else {
                        showToast("No available employees found for this department", 'danger');
                    }
                    $("#employeeSelect").html(options).prop('disabled', data.employees.length === 0);
                } catch (e) {
                    showToast("Error loading employees: " + e.message, 'danger');
                    $("#employeeSelect").html('<option value="">-- Error Loading Employees --</option>').prop('disabled', true);
                }
            });
        } else {
            $("#employeeSelect").html('<option value="">-- Select Team Leader First --</option>').prop('disabled', true);
        }
    });

    // Employee form submission
    $("#empForm").submit(function(e) {
        e.preventDefault();
        if (!validateForm(this)) return;
        
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
        
        $.ajax({
            url: "AssignRole/assign_emp.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (data) {
                btn.prop('disabled', false).html(originalText);
                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    $("#empForm")[0].reset();
                    $("#empForm").removeClass('was-validated');
                    $("#empTeamLeaderSelect").prop('disabled', true).html('<option value="">-- Select Department First --</option>');
                    $("#employeeSelect").prop('disabled', true).html('<option value="">-- Select Team Leader First --</option>');
                    loadTables();
                } else {
                    showToast(data.message || "An error occurred", 'danger');
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).html(originalText);
                console.error("AJAX error", xhr.status, xhr.responseText);
                showToast("Server Error: " + xhr.status, 'danger');
            }
        });
    });

    // Search functionality with debounce
    function debounce(func, wait = 300) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    $("#searchManager").on("keyup", debounce(function() {
        let val = encodeURIComponent($(this).val());
        showLoading("managerTable");
        $("#managerTable").load("AssignRole/load_manager_assignments.php?search=" + val);
    }));

    $("#searchTL").on("keyup", debounce(function() {
        let val = encodeURIComponent($(this).val());
        showLoading("tlTable");
        $("#tlTable").load("AssignRole/load_tl_assignments.php?search=" + val);
    }));

    $("#searchEmp").on("keyup", debounce(function() {
        let val = encodeURIComponent($(this).val());
        showLoading("empTable");
        $("#empTable").load("AssignRole/load_emp_assignments.php?search=" + val);
    }));

    // Listen for tab changes to refresh data
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        loadTables();
    });
});

// Delete mapping function
function deleteMapping(type, id) {
    if (!confirm("Are you sure you want to delete this " + type + " assignment?")) {
        return;
    }
    
    $.post("AssignRole/delete_mapping.php", { type: type, id: id }, function(response) {
        try {
            const data = JSON.parse(response);
            showToast(data.message, data.status === 'success' ? 'success' : 'danger');
            if (data.status === 'success') {
                loadTables();
            }
        } catch (e) {
            showToast("Error: " + response, 'danger');
        }
    });
}
</script>