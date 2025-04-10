<?php
$file_name = $_GET['filename'];
$file_path = __DIR__ . "/$file_name";

if (file_exists($file_path)) {
    header('Content-Type: image/jpeg');
    readfile($file_path);
} else {
    echo 'File not found!';
}