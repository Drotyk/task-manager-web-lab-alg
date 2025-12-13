<?php
// Router for PHP built-in server.
// Run: php -S 127.0.0.1:8000 -t public public/router.php

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$file = __DIR__ . $path;

// If the request is for an existing file (e.g., /styles.css), let the server serve it.
if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
