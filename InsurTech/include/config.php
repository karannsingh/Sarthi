<?php
// Security headers – must be sent before any output
/*header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("Permissions-Policy: geolocation=(), microphone=(), camera=(), interest-cohort=()");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; object-src 'none';");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");*/

// Start session
ob_start();
session_start();

// DB connection
$server = "127.0.0.1";
$user = "u766188297_DBInsurTech";
$pass = "R]a*qD;s9";
$database = "u766188297_InsurTech";

$conn = mysqli_connect($server, $user, $pass, $database);
if (!$conn) {
    die("<script>alert('Connection Failed.')</script>");
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// API Constants
define('GROQ_API_KEY', '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');
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
?>