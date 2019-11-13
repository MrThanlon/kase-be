FROM registry.stuhome.com/devops/dockerepo/php-fpm:7.2-1.0.1

COPY ./ /build/

ARG ENV=dev
ARG TOKEN=1

RUN set -xe;\
    cd /build;\
    cp nginx.conf /etc/nginx/conf.d/;\
    sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories;\
    apk update;\
    apk add git curl;\
    curl -H "AUTHORIZATION:Bearer $TOKEN" -o config.php http://config.stuhome.com/$ENV/kase-be/config.php;\
    cat config.php;\
    composer config repo.packagist composer https://mirrors.aliyun.com/composer/;\
    composer install -v;\
    mkdir -p modules/pdf.js;\
    cd modules/pdf.js;\
    curl -L -o pdf.js.zip https://github.com/mozilla/pdf.js/releases/download/v2.2.228/pdfjs-2.2.228-dist.zip;\
    unzip pdf.js.zip;\
    rm pdf.js.zip;\
    sed -i -e "1773,1775d" -e "13346,13350d" -e "12895,12911d" web/viewer.js;\
    sed -i -e "118,120d" -e "221,223d" web/viewer.html;\
    cd /;\
    mv /build /app;\
    mkdir /storage;\
    apk del git curl;\
    chmod 0777 /storage