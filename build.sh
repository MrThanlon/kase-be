#!/bin/sh
set -e
sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories
apk add git
ENV=$(git branch --show-current)
if [ $ENV = "master" ]
then
    $ENV="production"
else
    if [ $ENV = "staging" ]
    then
        $ENV="staging"
    else
        logerror "Failed, unknow branch: $ENV"
    fi
fi
echo $ENV
docker build -t $DOCKER_BUILD_TAG --build-arg "ENV=$ENV" .
