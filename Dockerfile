FROM registry.stuhome.com/devops/dockerepo/php-fpm:7-1.0.1

COPY ./ /build/

RUN set -xe;\
    cd /build;\
    cp nginx.conf /etc/nginx/conf.d/;\
    cd /;\
    mv /build /app;\
    mkdir /storage;\
    chmod 0777 /storage;\