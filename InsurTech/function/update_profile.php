<?php
require('../include/config.php');

if (!isset($_SESSION['USEROID'])) {
    die("Unauthorized Access!");
}

$employee_id = $_SESSION['USEROID'];

// Handle Update Profile
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $FirstName = mysqli_real_escape_string($conn, $_POST['FirstName']);
    $MiddleName = mysqli_real_escape_string($conn, $_POST['MiddleName']);
    $LastName = mysqli_real_escape_string($conn, $_POST['LastName']);
    $UserName = mysqli_real_escape_string($conn, $_POST['UserName']);
    $OfficeMobileNumber = mysqli_real_escape_string($conn, $_POST['OfficeMobileNumber']);

    // First update users table
    $update_users = "UPDATE users SET UserName = '$UserName' WHERE UserOID = '$employee_id'";

    // Then update user_details table
    $update_user_details = "UPDATE user_details SET 
        FirstName = '$FirstName',
        MiddleName = '$MiddleName',
        LastName = '$LastName',
        OfficeMobileNumber = '$OfficeMobileNumber' 
        WHERE UserOID = '$employee_id'";

    // Run both queries
    $result1 = mysqli_query($conn, $update_users);
    $result2 = mysqli_query($conn, $update_user_details);

    if ($result1 && $result2) {
        echo "<script>
            alert('Profile updated successfully!');
            window.location.href = '../profile.php';
        </script>";
    } else {
        echo "<script>
            alert('Error while updating profile. Try again!');
            window.location.href = '../profile.php';
        </script>";
    }
}
?>