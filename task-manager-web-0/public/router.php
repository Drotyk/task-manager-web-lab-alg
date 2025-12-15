cat > public/router.php <<'EOF'
<?php
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$file = __DIR__ . $path;

if ($path !== '/' && file_exists($file) && !is_dir($file)) {
    return false; // віддати статичний файл (css)
}

require __DIR__ . '/index.php';
EOF
