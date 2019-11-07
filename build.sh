#!/bin/sh

envsubst < config.php.temp > config.php
docker build -t $DOCKER_BUILD_TAG --build-arg "ENV=$ENV" .
