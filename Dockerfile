FROM registry.stuhome.com/devops/dockerepo/php-fpm:7-1.0.1

COPY ./ /build/

RUN set -xe;\
    cd /build;\
    cp nginx.conf /etc/nginx/conf.d/;\
    curl -o /usr/local/bin/composer https://getcomposer.org/download/1.9.0/composer.phar;\
    chmod +x /usr/local/bin/composer;\
    composer install -vvv;\
    cd /;\
    mv /build /app;\
    mkdir /storage;\
    chmod 0777 /storage