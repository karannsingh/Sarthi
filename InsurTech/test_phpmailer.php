<?php
// Save this as test_phpmailer.php in your root directory

// Show PHP version
echo "PHP Version: " . PHP_VERSION . "<br>";

// Check if autoloader exists
$autoloaderPath = __DIR__ . '/vendor/autoload.php';
echo "Autoloader path: " . $autoloaderPath . "<br>";
echo "Autoloader exists: " . (file_exists($autoloaderPath) ? 'Yes' : 'No') . "<br>";

// Try to include the autoloader
try {
    require $autoloaderPath;
    echo "Autoloader included successfully<br>";
} catch (Exception $e) {
    echo "Failed to include autoloader: " . $e->getMessage() . "<br>";
}

// Check if PHPMailer classes exist
echo "PHPMailer class exists: " . (class_exists('PHPMailer\PHPMailer\PHPMailer') ? 'Yes' : 'No') . "<br>";
echo "SMTP class exists: " . (class_exists('PHPMailer\PHPMailer\SMTP') ? 'Yes' : 'No') . "<br>";
echo "Exception class exists: " . (class_exists('PHPMailer\PHPMailer\Exception') ? 'Yes' : 'No') . "<br>";

// Check PHPMailer installation path
$phpmailerPath = __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
echo "PHPMailer.php path: " . $phpmailerPath . "<br>";
echo "PHPMailer.php exists: " . (file_exists($phpmailerPath) ? 'Yes' : 'No') . "<br>";

// Try to manually include PHPMailer files
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "Trying manual include of PHPMailer files<br>";
    
    try {
        if (file_exists($phpmailerPath)) {
            require $phpmailerPath;
            require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
            require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
            
            echo "Manual include successful<br>";
            echo "PHPMailer class now exists: " . (class_exists('PHPMailer\PHPMailer\PHPMailer') ? 'Yes' : 'No') . "<br>";
        } else {
            echo "Could not find PHPMailer files to include manually<br>";
        }
    } catch (Exception $e) {
        echo "Failed to manually include PHPMailer: " . $e->getMessage() . "<br>";
    }
}

// Get detailed information about installed packages
echo "<h3>Installed Packages:</h3>";
if (file_exists(__DIR__ . '/vendor/composer/installed.json')) {
    $installedJson = file_get_contents(__DIR__ . '/vendor/composer/installed.json');
    $installed = json_decode($installedJson, true);
    
    if (isset($installed['packages'])) {
        foreach ($installed['packages'] as $package) {
            echo "Package: " . $package['name'] . " - Version: " . $package['version'] . "<br>";
        }
    } else if (is_array($installed)) {
        foreach ($installed as $package) {
            echo "Package: " . $package['name'] . " - Version: " . $package['version'] . "<br>";
        }
    }
} else {
    echo "No installed.json file found<br>";
}
?>