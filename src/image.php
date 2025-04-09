<?php
$file_name = $_GET['filename'];
$root_path = __DIR__;
$file_path = "{$root_path}/{$file_name}";
if (file_exists($file_path)) {
    $info = getimagesize($file_path);
    header('Content-Type: image/jpeg');
    readfile($file_path);
} else {
    echo 'File not found!';
}