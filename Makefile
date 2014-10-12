update: install-composer
	@echo "### Upgrading PUB###"
	export HOME=/home/www-data/; cd dart/*/; pub upgrade
	@echo "### Building JavaScript files ###"
	find dart/ -type f -name 'main*.dart' -exec dart2js {} --minify -o  {}.js \;


update-dev: update-composer-dev
	@echo "### Upgrading PUB###"
	cd dart/*/; pub upgrade
	@echo "### Building JavaScript files ###"
	find dart/ -type f -name 'main*.dart' -exec dart2js {} -o {}.js \;

update-composer: get-composer
	@echo "### Updating composer ###"
	export HOME=/home/www-data/; php composer.phar update

install-composer: get-composer
	@echo "### Installing composer ###"
	export HOME=/home/www-data/; php composer.phar install

update-composer-dev: get-composer
	@echo "### Updating composer ###"
	php composer.phar update

install-composer-dev: get-composer
	@echo "### Installing composer ###"
	php composer.phar install

get-composer:
	rm -f composer.phar
	php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"


analyze-dart:
	cd dart/*/; dartanalyzer */*.dart


update-composer-dev-and-commit: update-composer-dev
	git commit composer.lock -m "Updated to new version" 
	git push

