FROM php:8.3

# 设置阿里云镜像源
RUN echo "deb http://mirrors.aliyun.com/debian/ bookworm main contrib non-free" > /etc/apt/sources.list && \
    echo "deb http://mirrors.aliyun.com/debian/ bookworm-updates main contrib non-free" >> /etc/apt/sources.list && \
    echo "deb http://mirrors.aliyun.com/debian/ bookworm-backports main contrib non-free" >> /etc/apt/sources.list && \
    echo "deb http://mirrors.aliyun.com/debian-security bookworm-security main contrib non-free" >> /etc/apt/sources.list

# 安装必要的依赖
RUN apt-get update && apt-get install -y \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    webp \
    libavif-bin \
    nodejs \
    npm

# 清理 apt 缓存，减小镜像体积
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 安装 svgo
RUN npm install -g svgo

# 安装 PHP 扩展
RUN docker-php-ext-install sockets

# 设置时区
ENV TZ=Asia/Shanghai
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# 设置工作目录
WORKDIR /app

# 复制启动脚本
COPY ./start.sh /app

# 设置脚本可执行权限
RUN chmod +x /app/start.sh

# 设置启动命令
CMD ["sh", "/app/start.sh"]

#CMD ["php", "/app/server.php"]