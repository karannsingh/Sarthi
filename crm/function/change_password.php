<?php
require('../include/config.php');

if (!isset($_SESSION['USEROID'])) {
    die("Unauthorized Access!");
}

$employee_id = $_SESSION['USEROID'];

// Check Old Password via AJAX
if (isset($_POST['old_password_check'])) {
    $old_password = mysqli_real_escape_string($conn, $_POST['old_password_check']);
    $result = mysqli_query($conn, "SELECT Password FROM users WHERE UserOID = '$employee_id'");
    $row = mysqli_fetch_assoc($result);
    
    if ($row && $row['Password'] == $old_password) {
        echo "valid";
    } else {
        echo "invalid";
    }
    exit;
}

// Handle Password Change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    $result = mysqli_query($conn, "SELECT Password FROM users WHERE UserOID = '$employee_id'");
    $row = mysqli_fetch_assoc($result);
    $db_password = $row['Password'];

    if ($current_password !== $db_password) {
        echo "<script>alert('Current password is incorrect!');
        window.location.href = '../ChangePassword.php';
        </script>";
    } elseif ($new_password !== $confirm_password) {
        echo "<script>alert('New password and confirm password do not match!');
        window.location.href = '../ChangePassword.php';
        </script>";
    } else {
        $update_query = "UPDATE users SET Password = '$new_password' WHERE UserOID = '$employee_id'";
        if (mysqli_query($conn, $update_query)) {
            echo "<script>
                alert('Password changed successfully!');
                window.location.href = '../ChangePassword.php';
              </script>";
        } else {
            echo "<script>
                alert('Error updating password. Try again!');
                window.location.href = '../ChangePassword.php';
              </script>";
        }
    }
}
?>