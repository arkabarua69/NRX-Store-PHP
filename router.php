<?php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve existing static files directly
if ($uri !== '/' && file_exists(__DIR__ . $uri) && is_file(__DIR__ . $uri)) {
    $filePath = realpath(__DIR__ . $uri);
    $webRoot = realpath(__DIR__);

    // Prevent path traversal outside the web root
    if ($filePath === false || strpos($filePath, $webRoot . DIRECTORY_SEPARATOR) !== 0) {
        require __DIR__ . '/index.php';
        return;
    }

    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    // Never serve PHP files as static assets — let the application handle them
    if ($ext === 'php') {
        require __DIR__ . '/index.php';
        return;
    }

    $mimeTypes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'ico'  => 'image/x-icon',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'eot'  => 'application/vnd.ms-fontobject',
        'pdf'  => 'application/pdf',
        'txt'  => 'text/plain',
        'xml'  => 'application/xml',
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
    ];

    $contentType = $mimeTypes[$ext] ?? (function_exists('mime_content_type') ? mime_content_type($filePath) : 'application/octet-stream');

    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: public, max-age=604800');
    header('X-Content-Type-Options: nosniff');
    readfile($filePath);
    exit;
}

require __DIR__ . '/index.php';
