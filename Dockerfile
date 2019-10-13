FROM registry.stuhome.com/devops/dockerepo/php-fpm:7-1.0.1

COPY ./ /build/

RUN set -xe;\
    cd /build;\
    cp nginx.conf /etc/nginx/conf.d/;\
    curl -o /usr/local/bin/composer https://getcomposer.org/download/1.9.0/composer.phar;\
    chmod +x /usr/local/bin/composer;\
    composer install -vvv;\
    mkdir modules;\
    cd modules;\
    curl -o pdf.js.zip https://github.com/mozilla/pdf.js/releases/download/v2.2.228/pdfjs-2.2.228-dist.zip;\
    unzip pdf.js.zip;\
    cd /;\
    mv /build /app;\
    mkdir /storage;\
    chmod 0777 /storage