<br />
<div align="center">
  <img src="logo.png" alt="Logo" width="80" height="80">
  <h3 align="center">Image Optimize Api</h3>
  <p align="center">
    基于PHP实现的自托管简单图片压缩服务API
  </p>
</div>

## 简介
这是一个基于PHP实现的图片压缩服务API，以自托管的方式运行在Docker容器中。它的主要功能是接收用户上传的图片，对其进行压缩处理，并返回压缩后的图片。适合用于优化图片体积、减少带宽占用等场景。通过Docker容器化部署，具备快速部署、可移植性强的特点，适用于开发者搭建轻量级的图片压缩处理服务。

## 特性
1. **多格式**：支持JPEG、PNG、WebP、AVIF、GIF和SVG等多种常见的图片格式，满足不同场景下的图片压缩需求。
2. **高压缩**：直接调用Linux系统安装的图像优化工具（如gifsicle、JpegOptim、Pngquant等），能够实现高压缩率，显著减小图片文件大小，同时尽量保持图片质量。
3. **自托管**：完全不借助第三方服务并且基于Docker容器化部署，便于在不同环境中快速部署和扩展。

## 开始

### Docker 构建安装

1. 克隆仓库
```sh
git clone https://github.com/ifeiwu/image-optimize-api.git
```
2. 进入目录
```sh
cd image-optimize-api
```
3. 构建镜像
```sh
docker build -t image-optimize-api .
```
4. 运行容器
```sh
docker run -d --restart=unless-stopped --name image-optimize-api -p 8182:8182 -p 8181:8181 -v "$(pwd)":/app -e API_TOKEN="your_api_key" image-optimize-api
 ```

## 使用例子

**查看[完整示例](https://github.com/ifeiwu/image-optimize-api/tree/main/example)**


## 技术栈
* [ImageOptimizer](https://github.com/spatie/image-optimizer)
* [RippleHttp](https://github.com/cloudtay/ripple-http)
* [Ripple](https://github.com/cloudtay/ripple)