FROM php:8.3

# 安装必要的依赖
RUN apt-get update && apt-get install -y \
    # 安装图像优化工具
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    webp \
    libavif-bin \
    # 安装 Node.js 和 npm（用于安装svgo）
    curl \
    && curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# 安装 svgo
RUN npm install -g svgo

# 安装 PHP 扩展
RUN docker-php-ext-install sockets opcache

# 设置时区
ENV TZ=Asia/Shanghai
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# 设置工作目录
WORKDIR /app

# 复制启动脚本
COPY start.sh /start.sh

# 设置脚本可执行权限
RUN chmod +x /start.sh

# 设置启动命令
CMD ["sh", "/start.sh"]

#CMD ["php", "/app/server.php"]