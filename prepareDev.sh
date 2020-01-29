php bin/console fos:js-routing:dump --format=json --target=assets/static/fos_js_routes.json
php bin/console cache:clear --env=dev
php bin/console cache:warmup
chmod -Rf 0777 ./var
chmod -Rf 0777 ./vendor/symfony/cache
php bin/console crosier:uppercaseFieldsJsonBuilder
yarn encore dev --watch --watch-poll 

