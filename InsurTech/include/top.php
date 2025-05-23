<?php
	require('include/config.php');
	require('include/functions.inc.php');
	
	$userIdleTimeout = $_SESSION['idle_timeout_minutes'] ?? 10; // default 10 minutes
	if (isset($_SESSION['LAST_ACTIVE_TIME'])) {
	    if ((time() - $_SESSION['LAST_ACTIVE_TIME']) > $userIdleTimeout * 60) {
	        header('location:logout.php');
	        exit;
	    }
	}
	$_SESSION['LAST_ACTIVE_TIME'] = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<base href="./">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
	<meta name="description" content="Sarthi Enterprises">
	<meta name="author" content="Sarthi Enterprises">
	<meta name="keyword" content="Sarthi Enterprises">
	<title>InsurTech | Sarthi Enterprises</title>
	<link rel="apple-touch-icon" sizes="57x57" href="assets/img/logo/Sarthi.png">
	<link rel="apple-touch-icon" sizes="60x60" href="assets/img/logo/Sarthi.png">
	<link rel="apple-touch-icon" sizes="72x72" href="assets/img/logo/Sarthi.png">
	<link rel="apple-touch-icon" sizes="76x76" href="assets/img/logo/Sarthi.png">
	<link rel="apple-touch-icon" sizes="114x114" href="assets/img/logo/Sarthi.png">
	<link rel="apple-touch-icon" sizes="120x120" href="assets/img/logo/Sarthi.png">
	<link rel="apple-touch-icon" sizes="144x144" href="assets/img/logo/Sarthi.png">
	<link rel="apple-touch-icon" sizes="152x152" href="assets/img/logo/Sarthi.png">
	<link rel="apple-touch-icon" sizes="180x180" href="assets/img/logo/Sarthi.png">
	<link rel="icon" type="image/png" sizes="192x192" href="assets/img/logo/Sarthi.png">
	<link rel="icon" type="image/png" sizes="32x32" href="assets/img/logo/Sarthi.png">
	<link rel="icon" type="image/png" sizes="96x96" href="assets/img/logo/Sarthi.png">
	<link rel="icon" type="image/png" sizes="16x16" href="assets/img/logo/Sarthi.png">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="assets/img/logo/Sarthi.png">
	<meta name="theme-color" content="#ffffff">
	<!-- Main styles for this application-->
	<link href="css/style.css" rel="stylesheet">
	<!-- We use those styles to show code examples, you should remove them in your application.-->
	<link href="css/examples.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
	<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
  	<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
	<style type="text/css">
		.body{
			background-color: #f2f2f2;
			padding-top: 1rem;
			padding-bottom: 2rem;
		}
		.sidebar {
    		--cui-sidebar-width: 12rem;
    	}
    	.sidebar:not(.sidebar-end) ~ * {
		    --cui-sidebar-occupy-start: 12rem;
		}
		.sidebar-narrow-unfoldable:not(.sidebar-end) ~ * {
		    --cui-sidebar-occupy-start: 4rem;
		}
		.header-sticky{
			box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.1), 0 2px 10px 0 rgba(0, 0, 0, 0.05);
		}
		.mini-header{
			background-color: #ffffff00;
			box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05), 0 10px 30px 0 rgba(0, 0, 0, 0.05);
			z-index: 1028;
		}
		/*.sidebar-nav .nav-title {
			color: black;
		}
		.sidebar-nav {
			background-color: white;
		}
		.sidebar-nav .nav-link{
			color: black!important;
		}
		.sidebar-nav .nav-group .nav-link .nav-icon::after{
			color: black!important;
		}
		.sidebar-nav .nav-link .nav-icon{
			color: black!important;
		}
		.sidebar-nav .nav-link .active{
			color: black!important;
		}*/
	</style>
	<script>
	    let idleTime = 0;
	    const idleLimit = <?php echo ($_SESSION['idle_timeout_minutes'] ?? 10); ?>; // in minutes

	    function resetIdleTimer() {
	        idleTime = 0;
	    }

	    // Increment the idle time counter every minute.
	    const idleInterval = setInterval(() => {
	        idleTime++;
	        if (idleTime >= idleLimit) {
	            // Auto logout by redirecting
	            window.location.href = 'logout.php';
	        }
	    }, 60000); // 1 minute

	    // Reset idle timer on user activity
	    ['mousemove', 'keypress', 'mousedown', 'touchstart'].forEach(function(event) {
	        document.addEventListener(event, resetIdleTimer, false);
	    });
	</script>
</head>
<body>