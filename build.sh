#!/bin/sh
set -xe
ENV=$(git branch --show-current)
if [ $ENV = "master" ]
then
    $ENV="production"
fi
echo $ENV
docker build -t $DOCKER_BUILD_TAG --build-arg "ENV=$ENV" .
