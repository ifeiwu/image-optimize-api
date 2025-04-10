<br />
<div align="center">
  <img src="image/logo.png" alt="Logo" width="80" height="80">
  <h3 align="center">Image Optimize Api</h3>
  <p align="center">
    基于PHP实现的自托管简单图片压缩服务API
  </p>
</div>

## 特性
1. **多格式**：支持JPEG、PNG、WebP、AVIF、GIF和SVG等多种常见的图片格式，满足不同场景下的图片压缩需求。
2. **高压缩**： 直接调用Linux系统安装的图像优化工具（如cwebp、JpegOptim、Pngquant等），能够实现高压缩率，显著减小图片文件大小，同时尽量保持图片质量。
3. **自托管**： 完全不借助第三方服务并且基于Docker容器化部署，便于在不同环境中快速部署和扩展。

## 开始

### Docker 安装

1. 克隆本仓库
```sh
git clone https://github.com/ifeiwu/image-optimize-api.git
cd image-optimize-api
```
3. Composer安装依赖包
```sh
composer install --no-dev --optimize-autoloader
```
4. 构建 Docker 镜像
```sh
docker build -t image-optimize-api .
```
5. 运行容器
```sh
docker run -d --restart=unless-stopped --name image-optimize-api -p 8182:8182 -p 8181:8181 -v "$(pwd)":/app -e API_TOKEN="your_api_key" image-optimize-api
 ```

## 使用例子

**查看[完整示例](https://github.com/ifeiwu/image-optimize-api/tree/main/example)**


## 技术栈
* [ImageOptimizer](https://github.com/spatie/image-optimizer)
* [RippleHttp](https://github.com/cloudtay/ripple-http)
* [Ripple](https://github.com/cloudtay/ripple)