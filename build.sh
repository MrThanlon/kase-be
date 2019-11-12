#!/bin/sh
case $ENV in
    production)
        echo $MASTER_CONFIG > config.php
        ;;

    staging)
        echo $STAGING_CONFIG > config.php
        ;;

    *)
        logerror Invalid environment: $ENV
        ;;
esac
envsubst < config.php.temp > config.php
docker build -t $DOCKER_BUILD_TAG --build-arg "ENV=$ENV" .
