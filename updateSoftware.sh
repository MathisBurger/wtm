sleep 10
rm -r assets bin config migrations public src templates translations composer.json composer.lock importmap.php LICENSE README.md SOFTWARE_VERSION symfony.lock .gitignore
wget "https://github.com/MathisBurger/wtm/archive/refs/tags/v$1.zip"
unzip "v$1.zip"
rm -r "wtm-$1/desktop"
rm -r "wtm-$1/.github"
rm -r "wtm-$1/docs"
rm -r "wtm-$1/Dockerfile"
rm -r "wtm-$1/tests"
rm -r "wtm-$1/.env"
rm -r "wtm-$1/.env.test"
rm -r "wtm-$1/initEnv.sh"
rm -r "wtm-$1/phpunit.xml.dist"
rm -r "wtm-$1/updateSoftware.sh"
rm -r "v$1.zip"
mv ./wtm-$1/* .
rm -r "wtm-$1"
composer install
php bin/console cache:clear
php bin/console assets:install public
php bin/console importmap:install
php bin/console doctrine:schema:update --force