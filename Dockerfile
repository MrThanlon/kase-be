FROM registry.stuhome.com/devops/dockerepo/php-fpm:7.2-1.0.1

COPY ./ /build/

ARG ENV=dev

RUN set -xe;\
    cd /build;\
    cp nginx.conf /etc/nginx/conf.d/;\
    sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories;\
    apk add git;\
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
    apk del git;\
    chmod 0777 /storage
    starconf_set_entry remote http://config.stuhome.com/$ENV/kase-be/config.json;\
    starconf_configure_root add /app