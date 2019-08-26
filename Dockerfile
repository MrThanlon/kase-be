FROM    php:7.1-apache
RUN     sed -i 's/deb.debian.org/mirrors.ustc.edu.cn/g' /etc/apt/sources.list \
        && apt-get update \
        && apt-get install -y zlib1g-dev \
        && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
        && docker-php-ext-install -j$(nproc) json \
        && docker-php-ext-install -j$(nproc) zip \
        && docker-php-ext-install -j$(nproc) mysqli

COPY    .   /var/www/html/