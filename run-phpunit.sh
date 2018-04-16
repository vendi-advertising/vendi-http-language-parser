#!/bin/bash

##see: http://stackoverflow.com/questions/192249/how-do-i-parse-command-line-arguments-in-bash
# Use -gt 1 to consume two arguments per pass in the loop
# Use -gt 0 to consume one or more arguments per pass in the loop
RUN_LINT=true
RUN_SEC=true
RUN_PHP_CS=true
RUN_PHP_UNIT=true
while [[ $# -gt 0 ]]
do
    key="$1"

        case $key in

            -g|--group)
                GROUP="$2"
                shift # past argument
            ;;

            --test-only)
                RUN_PHP_CS=false
                RUN_LINT=false
                RUN_SEC=false
                RUN_PHP_UNIT=true
            ;;

            --simple)
                RUN_PHP_CS=false
                RUN_LINT=true
                RUN_SEC=false
                RUN_PHP_UNIT=false
            ;;

            --no-run-php-cs)
                RUN_PHP_CS=false
            ;;

            --no-lint)
                RUN_LINT=false
            ;;

            *)
                    # unknown option
            ;;
        esac

    shift # past argument or value
done

maybe_run_phpunit()
{
    echo "Maybe running PHP Unit...";
    if [ "$RUN_PHP_UNIT" = true ]; then
    {
        echo "running...";

        ./vendor/bin/phpunit -c phpunit.xml --coverage-html ./tests/logs/coverage/
        if [ $? -ne 0 ]; then
        {
            echo "Error with composer... exiting";
            exit 1;
        }
        fi
    }
    else
        echo "skipping";
    fi

    printf "\n";
}

maybe_run_php_cs()
{
    echo "Maybe running PHP CS Fixer...";
    if [ "$RUN_PHP_CS" = true ]; then
    {
        echo "running...";

        vendor/bin/php-cs-fixer fix
        if [ $? -ne 0 ]; then
        {
            echo "Error with composer... exiting";
            exit 1;
        }
        fi
    }
    else
        echo "skipping";
    fi

    printf "\n";
}

maybe_run_linter()
{
    echo "Maybe running linter...";
    if [ "$RUN_LINT" = true ]; then
    {
        echo "running...";
        ./vendor/bin/parallel-lint --exclude vendor/ .
        if [ $? -ne 0 ]; then
        {
            echo "Error with PHP linter... exiting";
            exit 1;
        }
        fi
    }
    else
        echo "skipping";
    fi

    printf "\n";
}

maybe_run_security_check()
{
    echo "Maybe running security check...";
    if [ "$RUN_SEC" = true ]; then
    {
        echo "running...";
        vendor/bin/security-checker security:check --end-point=http://security.sensiolabs.org/check_lock --timeout=30 ./composer.lock
        if [ $? -ne 0 ]; then
        {
            echo "Error with security checker... exiting";
            exit 1;
        }
        fi
    }
    else
        echo "skipping";
    fi

    printf "\n";
}


if [ ! -d './vendor' ]; then
    composer update
fi

maybe_run_linter;
maybe_run_php_cs;
maybe_run_security_check;
maybe_run_phpunit;
