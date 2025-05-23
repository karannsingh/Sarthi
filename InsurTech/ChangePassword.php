<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
    <?php require('include/header.php'); ?>

    <div class="body flex-grow-1 px-3">
        <div class="container-lg">
            <div class="fs-2 fw-semibold">Change Password</div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><span><?php echo isset($_SESSION['USERNAME']) ? $_SESSION['USERNAME'] : ''; ?></span></li>
                    <li class="breadcrumb-item active"><span>Change Password</span></li>
                </ol>
            </nav>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h4 class="card-title text-center fw-bold">Change Password</h4>
                            <p class="text-center text-muted">
                                <?php echo isset($_SESSION['USERNAME']) ? 'Hi, ' . $_SESSION['USERNAME'] : ''; ?>
                            </p>
                            <form method="post" action="function/change_password.php">
                                
                                <!-- Current Password -->
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                                        <span class="input-group-text toggle-password" data-target="current_password">
                                            <svg class="svg-inline--fa fa-eye" id="eyeIcon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="eye" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="20" height="20">
                                                <path fill="currentColor" d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"></path>
                                            </svg>
                                        </span>
                                    </div>
                                    <small id="password_status" class="text-danger"></small>
                                </div>

                                <!-- New Password -->
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-key"></i></span>
                                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                                        <span class="input-group-text toggle-password" data-target="new_password">
                                            <svg class="svg-inline--fa fa-eye" id="eyeIcon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="eye" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="20" height="20">
                                                <path fill="currentColor" d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"></path>
                                            </svg>
                                        </span>
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div class="mb-3">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-check-circle"></i></span>
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                        <span class="input-group-text toggle-password" data-target="confirm_password">
                                            <svg class="svg-inline--fa fa-eye" id="eyeIcon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="eye" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="20" height="20">
                                                <path fill="currentColor" d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"></path>
                                            </svg>
                                        </span>
                                    </div>
                                    <small id="confirm_status" class="text-danger"></small>
                                </div>

                                <!-- Submit Button -->
                                <div class="text-center">
                                    <button type="submit" name="change_password" class="btn btn-primary w-100 text-white">
                                        <i class="fa fa-save"></i> Change Password
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

<!-- Bootstrap & jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>

<script>
    $(document).ready(function () {
    // Initialize submit button as disabled
    $("button[type='submit']").prop("disabled", true).addClass("opacity-50");
    
    // Toggle password visibility
    $(".toggle-password").click(function () {
        var inputId = $(this).attr("data-target"); 
        var input = $("#" + inputId);
        var icon = $(this).find("svg");
    
        if (input.attr("type") === "password") {
            input.attr("type", "text");
            icon.attr("data-icon", "eye-slash"); 
            icon.html('<path fill="currentColor" d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L525.6 386.7c39.6-40.6 66.4-86.1 79.9-118.4c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C465.5 68.8 400.8 32 320 32c-68.2 0-125 26.3-169.3 60.8L38.8 5.1zM223.1 149.5C248.6 126.2 282.7 112 320 112c79.5 0 144 64.5 144 144c0 24.9-6.3 48.3-17.4 68.7L408 294.5c8.4-19.3 10.6-41.4 4.8-63.3c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3c0 10.2-2.4 19.8-6.6 28.3l-90.3-70.8zM373 389.9c-16.4 6.5-34.3 10.1-53 10.1c-79.5 0-144-64.5-144-144c0-6.9 .5-13.6 1.4-20.2L83.1 161.5C60.3 191.2 44 220.8 34.5 243.7c-3.3 7.9-3.3 16.7 0 24.6c14.9 35.7 46.2 87.7 93 131.1C174.5 443.2 239.2 480 320 480c47.8 0 89.9-12.9 126.2-32.5L373 389.9z"></path>');
        } else {
            input.attr("type", "password");
            icon.attr("data-icon", "eye");
            icon.html('<path fill="currentColor" d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"></path>');
        }
    });
    
    // Validate Old Password on Keyup
    $("#current_password").on("keyup", function () {
        var oldPass = $(this).val();
        if (oldPass.length > 0) {
            $.ajax({
                type: "POST",
                url: "function/change_password.php",
                data: { old_password_check: oldPass },
                success: function (response) {
                    if (response.trim() === "valid") {
                        $("#password_status").html("<span class='text-success'><i class='fa fa-check-circle'></i> Correct</span>");
                    } else {
                        $("#password_status").html("<span class='text-danger'><i class='fa fa-times-circle'></i> Incorrect</span>");
                    }
                    // Check form validity after response
                    checkFormValidity();
                }
            });
        } else {
            $("#password_status").html("");
            checkFormValidity();
        }
    });

    // Add password requirements container after the new password field
    $("#new_password").parent().after(
        '<div id="password_requirements" class="mt-2 border p-3 rounded bg-light" style="display: none;">' +
        '  <div class="fw-bold mb-2">Password Requirements:</div>' +
        '  <div class="row">' +
        '    <div class="col-md-6">' +
        '      <small id="length_check" class="d-block text-danger mb-1"><i class="fa fa-times-circle"></i> Minimum 8 characters</small>' +
        '      <small id="uppercase_check" class="d-block text-danger mb-1"><i class="fa fa-times-circle"></i> One capital letter</small>' +
        '    </div>' +
        '    <div class="col-md-6">' +
        '      <small id="number_check" class="d-block text-danger mb-1"><i class="fa fa-times-circle"></i> One number</small>' +
        '      <small id="special_check" class="d-block text-danger mb-1"><i class="fa fa-times-circle"></i> One special character</small>' +
        '    </div>' +
        '  </div>' +
        '  <div class="mt-2">' +
        '    <div class="d-flex align-items-center">' +
        '      <span class="me-2">Strength:</span>' +
        '      <div class="progress flex-grow-1" style="height: 8px;">' +
        '        <div id="strength_indicator" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>' +
        '      </div>' +
        '      <span class="ms-2" id="strength_text">Very Weak</span>' +
        '    </div>' +
        '  </div>' +
        '</div>'
    );

    // Validate New Password Strength
    $("#new_password").on("focus keyup", function() {
        validatePassword($(this).val());
    });
    
    // Hide requirements when focus leaves if all requirements are met
    $("#new_password").on("blur", function() {
        if (window.passwordValid) {
            $("#password_requirements").slideUp();
        }
    });
    
    // Show requirements when clicking the password field
    $("#new_password").on("click", function() {
        if ($(this).val().length > 0) {
            validatePassword($(this).val());
        } else {
            $("#password_requirements").slideDown();
        }
    });

    // Function to validate password strength
    function validatePassword(password) {
        // Initialize requirements checks
        let lengthValid = password.length >= 8;
        let uppercaseValid = /[A-Z]/.test(password);
        let numberValid = /[0-9]/.test(password);
        let specialValid = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
        
        // Update requirement indicators
        updateRequirement("length_check", lengthValid, "Minimum 8 characters");
        updateRequirement("uppercase_check", uppercaseValid, "One capital letter");
        updateRequirement("number_check", numberValid, "One number");
        updateRequirement("special_check", specialValid, "One special character");
        
        // Calculate strength
        let strength = 0;
        if (password.length > 0) strength += 10;
        if (lengthValid) strength += 25;
        if (uppercaseValid) strength += 25;
        if (numberValid) strength += 25;
        if (specialValid) strength += 25;
        
        // Update strength meter
        let strengthClass = "bg-danger";
        let strengthText = "Weak";
        
        if (strength >= 100) {
            strengthClass = "bg-success";
            strengthText = "Strong";
        } else if (strength >= 75) {
            strengthClass = "bg-info";
            strengthText = "Good";
        } else if (strength >= 50) {
            strengthClass = "bg-warning";
            strengthText = "Medium";
        } else if (strength >= 25) {
            strengthClass = "bg-danger";
            strengthText = "Weak";
        } else {
            strengthText = "Very Weak";
        }
        
        $("#strength_indicator")
            .removeClass("bg-danger bg-warning bg-info bg-success")
            .addClass(strengthClass)
            .css("width", strength + "%")
            .attr("aria-valuenow", strength);
            
        $("#strength_text").text(strengthText);
        
        // Store validation result to use when form submits
        window.passwordValid = lengthValid && uppercaseValid && numberValid && specialValid;
        
        // Check form validity
        checkFormValidity();
        
        // Hide requirements panel if all requirements are met
        if (window.passwordValid && password.length > 0) {
            $("#password_requirements").slideUp();
        } else {
            $("#password_requirements").slideDown();
        }
        
        return window.passwordValid;
    }
    
    // Helper function to check if form is valid and toggle button state
    function checkFormValidity() {
        var newPass = $("#new_password").val();
        var confirmPass = $("#confirm_password").val();
        var currentPass = $("#current_password").val();
        var currentPassValid = $("#password_status").text().includes("Correct");
        
        // Check if all validations pass
        var formValid = window.passwordValid && 
                       (newPass === confirmPass) && 
                       (confirmPass.length > 0) && 
                       (currentPass.length > 0) && 
                       currentPassValid;
        
        // Toggle button state
        $("button[type='submit']").prop("disabled", !formValid)
                                  .toggleClass("opacity-50", !formValid);
        
        return formValid;
    }
    
    // Update button state on any input change
    $("#new_password, #confirm_password, #current_password").on("keyup change", function() {
        checkFormValidity();
    });

    // Helper function to update requirement indicators
    function updateRequirement(id, isValid, text) {
        if (isValid) {
            $("#" + id)
                .html('<i class="fa fa-check-circle"></i> ' + text)
                .removeClass("text-muted text-danger")
                .addClass("text-success");
        } else {
            $("#" + id)
                .html('<i class="fa fa-times-circle"></i> ' + text)
                .removeClass("text-muted text-success")
                .addClass("text-danger");
        }
    }

    // Validate New Password and Confirm Password Match
    $("#confirm_password, #new_password").on("keyup", function () {
        var newPass = $("#new_password").val();
        var confirmPass = $("#confirm_password").val();
        
        if (confirmPass.length > 0) {
            if (newPass === confirmPass) {
                $("#confirm_status").html("<span class='text-success'><i class='fa fa-check-circle'></i> Passwords match</span>");
            } else {
                $("#confirm_status").html("<span class='text-danger'><i class='fa fa-times-circle'></i> Passwords do not match</span>");
            }
        } else {
            $("#confirm_status").html("");
        }
        
        // Check form validity
        checkFormValidity();
    });
    
    // Form submission validation
    $("form").on("submit", function(e) {
        var newPass = $("#new_password").val();
        var confirmPass = $("#confirm_password").val();
        
        // Validate password strength
        if (!validatePassword(newPass)) {
            e.preventDefault();
            $("#password_requirements").slideDown();
            alert("Your new password doesn't meet all the security requirements.");
            return false;
        }
        
        // Validate passwords match
        if (newPass !== confirmPass) {
            e.preventDefault();
            alert("New password and confirmation password do not match.");
            return false;
        }
    });
});
</script>