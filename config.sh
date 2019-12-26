case $env in
    production)
        echo $MASTER_CONFIG > config.php
        ;;

    staging)
        echo $STAGING_CONFIG > config.php
        ;;

    *)
        logerror Invalid environment: $env
        ;;
esac