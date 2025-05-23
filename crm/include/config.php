<?php 

session_start();

$server = "127.0.0.1";
$user = "u766188297_insurance_v02";
$pass = "qyTUU]Q;8y";
$database = "u766188297_insurance_v02";

$conn = mysqli_connect($server, $user, $pass, $database);

if (!$conn) {
    die("<script>alert('Connection Failed.')</script>");
}
date_default_timezone_set('Asia/Kolkata');
?>