#!/usr/bin/env bash

set -e

rm -rf var/cache/test
rm -rf var/log/test.log

# vendor/bin/phpunit

bin/console -q --env=test # Compile container
echo "Container ready"

bin/console -q --env=test swagger:check # Check swagger annotations
echo "Swagger   OK"

bin/console doctrine:database:drop --env=test --force -q || true
bin/console doctrine:database:create --env=test -q
bin/console doctrine:migrations:migrate --env=test -q
echo "Database  ready"

vendor/bin/behat -f progress "$@"
