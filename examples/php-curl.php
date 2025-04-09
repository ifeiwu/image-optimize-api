<?php
define('ROOT_PATH', dirname(__DIR__) . '/');

$api_uri = 'http://192.168.31.5';
$api_key = 'your_api_key';
$source_image = ROOT_PATH . 'images/test.jpg';
$target_image = ROOT_PATH . 'output/test.jpg';

try {
    $file_mime = mime_content_type($source_image);
    $file_size = filesize($source_image);
    $file_name = pathinfo($source_image, PATHINFO_BASENAME);
    $file = new \CURLFile($source_image, $file_mime, $file_name);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "{$api_uri}:8181/");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['files' => $file]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key
    ]);

    $content = curl_exec($ch);
    $info = curl_getinfo($ch);
    if ($info['http_code'] != 200) {
        throw new \Exception("HTTP Error: {$info['http_code']}");
    } elseif (curl_errno($ch) != 0) {
        throw new \Exception(curl_error($ch));
    }
    curl_close($ch);
    // 返回压缩文件信息
    $compress = json_decode($content, true);
    if (empty($compress)) {
        throw new \Exception("处理请求时出错：{$api_uri}");
    }
    // 数组必需要 file 属性
    if (array_key_exists('file', $compress)) {
        // 压缩后的图片小于源图片才会保存图片
        if ($compress['size'] < $file_size) {
            $file_url = "{$api_uri}:8182/image.php?filename={$compress['file']}";
            $fp = fopen($target_image, 'wb');
            $ch = curl_init($file_url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);

            $compressionRate = (($file_size - $compress['size']) / $file_size) * 100;
            echo '压缩率是: ' . number_format($compressionRate, 2) . '%'; // 输出压缩率，保留两位小数
        } elseif ($overwrite == false) {
            copy($source_image, $target_image);

            echo '压缩后大于原始图片，已跳过压缩。';
        }
    } else {
        throw new \Exception($compress['message']);
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}