FROM ubuntu:24.04

LABEL maintainer="Tongle Xu"

ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN echo "Acquire::http::Pipeline-Depth 0;" > /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::BrokenProxy    true;" >> /etc/apt/apt.conf.d/99custom

RUN sed -i 's@//.*archive.ubuntu.com@//mirrors.aliyun.com@g' /etc/apt/sources.list.d/ubuntu.sources \
    && sed -i 's@//security.ubuntu.com@//mirrors.ustc.edu.cn@g' /etc/apt/sources.list.d/ubuntu.sources

# 安装开发工具和依赖
RUN apt-get update && \
    apt-get install -y \
            build-essential \
            cmake clang llvm-dev libclang-dev \
            git curl \
            wget zip unzip lsof nload htop net-tools dnsutils openssh-server vim \
            # PHP 相关依赖 \
            mysql-client \
            libzip-dev \
            libpng-dev \
            libjpeg-dev \
            libfreetype6-dev \
            libonig-dev \
            libxml2-dev \
            libcurl4-openssl-dev \
            libssl-dev \
            libsqlite3-dev \
            libpq-dev \
            libmemcached-dev \
            librdkafka-dev \
            zlib1g-dev \
            libicu-dev \
            g++ && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# 安装 PHP 和扩展
RUN apt-get update && apt-get install -y software-properties-common \
        && add-apt-repository ppa:ondrej/php --yes \
        && apt-get update \
        && apt-get install -y php8.4-cli php8.4-dev \
           php8.4-pgsql php8.4-sqlite3 php8.4-gd php8.4-imagick \
           php8.4-curl php8.4-mongodb \
           php8.4-imap php8.4-mysql php8.4-mbstring \
           php8.4-xml php8.4-zip php8.4-bcmath php8.4-soap \
           php8.4-intl php8.4-readline \
           php8.4-msgpack php8.4-igbinary php8.4-redis php8.4-swoole \
           php8.4-memcached \
        && apt-get update \
        && apt-get -y autoremove \
        && apt-get clean \
        && rm -rf /var/lib/apt/lists/*

# 安装 Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY .ide/php.ini /etc/php/8.4/cli/conf.d/99-php.ini

# 下载并安装 nvm：
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash \
    && \. "/root/.nvm/nvm.sh" \
    && nvm install 24 \
    && node -v

# 安装 code-server 和 PHP 开发扩展
RUN curl -fsSL https://code-server.dev/install.sh | sh \
    && code-server --install-extension bmewburn.vscode-intelephense-client \
    && code-server --install-extension formulahendry.code-runner \
    && code-server --install-extension ms-vscode.vscode-typescript-next \
    && code-server --install-extension esbenp.prettier-vscode \
    && code-server --install-extension tencent-cloud.coding-copilot \
    && code-server --install-extension zobo.php-intellisense \
    && code-server --install-extension laravel.vscode-laravel

# 指定字符集支持命令行输入中文
ENV LANG C.UTF-8
ENV LANGUAGE C.UTF-8
