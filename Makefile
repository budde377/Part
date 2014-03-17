update:
	@echo "### Upgrading PHP dependencies ###"
	rm -f composer.phar
	php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"
	export HOME=/home/www-data/; php composer.phar update --no-dev

update-dev:
	@echo "### Upgrading PHP dependencies ###"
	rm -f composer.phar
	php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"
	php composer.phar update
	@echo "### Upgrading Dart dependencies ###"
	cd dart/cb_common; pub upgrade