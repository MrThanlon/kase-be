#!/bin/sh
set -e
#sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories
#apk add git
ENV=$CI_COMMIT_REF_NAME
TOKEN=$PRODUCTION_TOKEN
if [ "$ENV" = "master" ]
then
    ENV="production"
    CONFIG=$PRODUCTION_CONFIG
    TOKEN=$PRODUCTION_TOKEN
else
    if [ "$ENV" = "staging" ]
    then
        ENV="staging"
        CONFIG=$STAGING_CONFIG
        TOKEN=$STAGING_TOKEN
    else
        exit 1
    fi
fi
echo $ENV
docker build -t $DOCKER_BUILD_TAG --build-arg "ENV=$ENV" --build-arg "TOKEN=$TOKEN" .