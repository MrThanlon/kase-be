#!/bin/sh
docker build -t $DOCKER_BUILD_TAG --build-arg "ENV=$ENV" .
