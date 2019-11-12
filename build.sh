#!/bin/sh
set -xe
sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories
apk add git
ENV=$(git branch --show-current)
if [ $ENV = "master" ]
then
    $ENV="production"
fi
echo $ENV
docker build -t $DOCKER_BUILD_TAG --build-arg "ENV=$ENV" .
