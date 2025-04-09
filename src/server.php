<?php
require_once __DIR__ . '/vendor/autoload.php';

define('ROOT_PATH', __DIR__ . '/');

// 所有错误和异常记录
ini_set('error_reporting', E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', false);
ini_set('ignore_repeated_errors', true);
ini_set('log_errors', true);
ini_set('error_log', ROOT_PATH . 'logs/error.log');

use Ripple\Http\Server;
use Ripple\Http\Server\Request;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Spatie\ImageOptimizer\Optimizers\Avifenc;
use Spatie\ImageOptimizer\Optimizers\Cwebp;
use Spatie\ImageOptimizer\Optimizers\Gifsicle;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Svgo;

$server = new Server('http://0.0.0.0:8181');
$server->onRequest(static function (Request $request) {
    $uri = $request->SERVER['REQUEST_URI'];
    $method = $request->SERVER['REQUEST_METHOD'];
    if ($method == 'GET') {
        // 注意：目前暂时不使用这里响应输出图片，因为部署到服务器获取图片有问题。
        // 目录前解决方式：开启 PHP 自带服务来获取图片：/image.php?filename=/uploads/67ed44843b51c.jpg
        // 访问压缩后的图片
        /*if (stripos($uri, '/uploads') !== false) {
            $file_path = ROOT_PATH . $uri;
            if (file_exists($file_path)) {
                $info = getimagesize($file_path);
                $content = file_get_contents($file_path);
                // 响应输出图片
                $request->respond($content, [
                    'Content-Type' => $info['mime']
                ]);
            } else {
                $request->respond('File not found', [], 404);
            }
        }*/
    } elseif ($method == 'POST') {
        // 请求令牌验证
        $authorization = $request->SERVER['HTTP_AUTHORIZATION'];
        if (strpos($authorization, 'Bearer') === 0) {
            $token = substr($authorization, 7);
        }
        if ($token != getenv('API_TOKEN')) {
            $request->respond("{$request->SERVER["SERVER_PROTOCOL"]} 401 Unauthorized", [], 401);
        }
        // 压缩上传图片
        $resp_data = [];
        // 获取上传文件信息
        $file = $request->FILES['files'][0];
        if ($file) {
            // 创建上传目录
            $upload_dir = 'uploads';
            $upload_path = ROOT_PATH . "{$upload_dir}";
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }
            // 生成唯一文件名
            $file_name = uniqid() . '.' . $file->getClientOriginalExtension();
            $file_path = "{$upload_path}/{$file_name}";
            // 从上传临时文件重名称，移动到指定上传目录。
            if (rename($file->getPathname(), $file_path)) {
                $quality = intval($request->GET['quality']);
                // 执行压缩图片
                $optimizerChain = OptimizerChainFactory::create([
                    Jpegoptim::class => [
                        '-m' . ($quality ?: 90),
                        '--strip-all',
                        '--all-progressive',
                    ],
                    Pngquant::class => [
                        '--quality=' . ($quality ?: 90)
                    ],
                    Optipng::class => [
                        '-i0',
                        '-o2',
                        '-quiet',
                    ],
                    Svgo::class => [
                        '--config=/app/svgo-config.js'
                    ],
                    Gifsicle::class => [
                        '-b',
                        '-O3',
                    ],
                    Cwebp::class => [
                        '-m 6',
                        '-pass 10',
                        '-mt',
                        '-q ' . ($quality ?: 90),
                    ],
                    Avifenc::class => [
                        '-a cq-level=' . ($quality ? round(63 - $quality * 0.63) : 23),
                        '-j all',
                        '--min 0',
                        '--max 63',
                        '--minalpha 0',
                        '--maxalpha 63',
                        '-a end-usage=q',
                        '-a tune=ssim',
                    ],
                ]);
                $optimizerChain->optimize($file_path);
                // 响应压缩后图片信息
                $resp_data['file'] = "{$upload_dir}/{$file_name}";
                $resp_data['size'] = filesize($file_path);
                $resp_data['message'] = '图片压缩成功';
                // 不保留图片在本地：设置定时任务，60秒后自动删除图片
                \Co\async(static function () use ($file_path) {
                    \Co\sleep(60);
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                });
            } else {
                $resp_data['message'] = '图片移动失败';
            }
        } else {
            $resp_data['message'] = "没有图片上传";
        }

        $request->respondJson($resp_data);
    }

    $request->respond('Hello Image Optimize Api');
});

$server->listen();

\Co\wait();