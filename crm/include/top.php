<?php
	require('include/config.php');
	require('include/functions.inc.php');

	if (isset($_SESSION['LAST_ACTIVE_TIME'])) {
	  if ((time()-$_SESSION['LAST_ACTIVE_TIME'])>1800) {
	    header('location:logout.php');
	    die();
	  }
	}
	$_SESSION['LAST_ACTIVE_TIME']=time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<base href="./">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
	<meta name="description" content="CoreUI - Bootstrap Admin Template">
	<meta name="author" content="Łukasz Holeczek">
	<meta name="keyword" content="Bootstrap,Admin,Template,SCSS,HTML,RWD,Dashboard">
	<title>AK Insurance | Sarthi Enterprises</title>
	<link rel="apple-touch-icon" sizes="57x57" href="assets/img/logo/AK.png">
	<link rel="apple-touch-icon" sizes="60x60" href="assets/img/logo/AK.png">
	<link rel="apple-touch-icon" sizes="72x72" href="assets/img/logo/AK.png">
	<link rel="apple-touch-icon" sizes="76x76" href="assets/img/logo/AK.png">
	<link rel="apple-touch-icon" sizes="114x114" href="assets/img/logo/AK.png">
	<link rel="apple-touch-icon" sizes="120x120" href="assets/img/logo/AK.png">
	<link rel="apple-touch-icon" sizes="144x144" href="assets/img/logo/AK.png">
	<link rel="apple-touch-icon" sizes="152x152" href="assets/img/logo/AK.png">
	<link rel="apple-touch-icon" sizes="180x180" href="assets/img/logo/AK.png">
	<link rel="icon" type="image/png" sizes="192x192" href="assets/img/logo/AK.png">
	<link rel="icon" type="image/png" sizes="32x32" href="assets/img/logo/AK.png">
	<link rel="icon" type="image/png" sizes="96x96" href="assets/img/logo/AK.png">
	<link rel="icon" type="image/png" sizes="16x16" href="assets/img/logo/AK.png">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="assets/img/logo/AK.png">
	<meta name="theme-color" content="#ffffff">
	<!-- Vendors styles-->
	<link rel="stylesheet" href="vendors/simplebar/css/simplebar.css">
	<link rel="stylesheet" href="css/vendors/simplebar.css">
	<!-- Main styles for this application-->
	<link href="css/style.css" rel="stylesheet">
	<!-- We use those styles to show code examples, you should remove them in your application.-->
	<link href="css/examples.css" rel="stylesheet">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
	<style type="text/css">
		.sidebar {
    		--cui-sidebar-width: 12rem;
    	}
    	.sidebar:not(.sidebar-end) ~ * {
		    --cui-sidebar-occupy-start: 12rem;
		}
		.sidebar-narrow-unfoldable:not(.sidebar-end) ~ * {
		    --cui-sidebar-occupy-start: 4rem;
		}
	</style>
</head>
<body>