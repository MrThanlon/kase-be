FROM registry.stuhome.com/devops/dockerepo/php-fpm:7-1.0.1

COPY ./ /build/

RUN set -xe;\
    cd /build;\
    cp nginx.conf /etc/nginx/conf.d/;\
    sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories;\
    apk add git;\
    curl -o /usr/local/bin/composer https://getcomposer.org/download/1.9.0/composer.phar;\
    chmod +x /usr/local/bin/composer;\
    composer install -v;\
    mkdir -p modules/pdf.js;\
    cd modules/pdf.js;\
    curl -L -o pdf.js.zip https://github.com/mozilla/pdf.js/releases/download/v2.2.228/pdfjs-2.2.228-dist.zip;\
    unzip pdf.js.zip;\
    rm pdf.js.zip;\
    sed -i "1773,1775d" web/viewer.js;\
    cd /;\
    mv /build /app;\
    mkdir /storage;\
    apk del git;\
    chmod 0777 /storage