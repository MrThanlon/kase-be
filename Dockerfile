FROM registry.stuhome.com/devops/dockerepo/php-fpm:7-1.0.1

COPY ./ /build/

RUN set -xe;\
    cd /build;\
    cp nginx.conf /etc/nginx/conf.d/;\
    php -r "copy('https://getcomposer.org/download/1.9.0/composer.phar', 'composer-setup.php');";\
    php -r "if (hash_file('sha256', 'composer-setup.php') === 'c9dff69d092bdec14dee64df6677e7430163509798895fbd54891c166c5c0875') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;";\
    php composer-setup.php;\
    php -r "unlink('composer-setup.php');";\
    composer install -vvv;\
    cd /;\
    mv /build /app;\
    mkdir /storage;\
    chmod 0777 /storage;\