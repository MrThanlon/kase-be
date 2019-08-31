#!/bin/sh

sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories
apk update
apk add php php-fpm
php -r "copy('https://getcomposer.org/download/1.9.0/composer.phar', 'composer-setup.php');"
php -r "if (hash_file('sha256', 'composer-setup.php') === 'c9dff69d092bdec14dee64df6677e7430163509798895fbd54891c166c5c0875') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
composer install -vvv
docker build -t $DOCKER_BUILD_TAG --build-arg "ENV=$ENV" .