update:
	@echo "### Upgrading PHP dependencies ###"
	rm -f composer.phar
	php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"
	php composer.phar update

