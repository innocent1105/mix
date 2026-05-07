<?php
    if (!isset($_GET['file'])) {
        exit('No file specified');
    }

    $filename = basename($_GET['file']);
    $filepath = __DIR__ . '/avatars/' . $filename; 

    if (!file_exists($filepath)) {
        http_response_code(404);
        exit('File not found');
    }

    $mime = mime_content_type($filepath);
    header("Content-Type: $mime");
    header('Content-Length: ' . filesize($filepath));

    readfile($filepath);
    exit;
