<?php

declare(strict_types=1);

function WEB()
{
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    $devDomains = ['localhost', '127.0.0.1', 'dev.', 'test.', 'staging.'];

    foreach ($devDomains as $devDomain) {
        if (strpos($host, $devDomain) !== false) {
            return 'off';
        }
    }

    return 'on';
}

if (WEB() === "off") {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 0);
    error_reporting(E_ALL);
}


session_start();
date_default_timezone_set("Asia/Tehran");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: no-referrer-when-downgrade");


function auto_loader($classes)
{

    $app = "../app/";
    $file = $app . "core/" . $classes . ".php";
    if (file_exists($file)) {
        require_once $file;
    }

    $routes = [
        $app . "core/Helpers.php"
    ];

    foreach ($routes as $path) {
        if (file_exists($path)) {
            require_once $path;
        }
    }
}

spl_autoload_register("auto_loader");
