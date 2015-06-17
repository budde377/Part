update: install-composer update-pub update-dart update-db delete-tmp-folder

update-dev: update-composer-dev update-pub-dev update-dart-dev update-db delete-tmp-folder

update-db:
	php update_db.php

delete-tmp-folder:
	php remove_tmp.php

update-pub:
	@echo "### Upgrading PUB###"
	export HOME=/home/www-data/; cd dart/*/; pub upgrade

update-dart:
	@echo "### Building JavaScript files ###"
	find dart/ -type f -name 'main*.dart' -exec dart2js {} --enable-experimental-mirrors --minify -o  {}.js \;


update-dev: update-composer-dev update-pub-dev update-dart-dev

update-pub-dev:
	@echo "### Upgrading PUB###"
	cd dart/*/; pub upgrade


update-dart-dev:
	@echo "### Building JavaScript files ###"
	find dart/ -type f -name 'main*.dart' -exec dart2js {} --enable-experimental-mirrors -o {}.js \;

update-composer: get-composer
	@echo "### Updating composer ###"
	export HOME=/home/www-data/; php composer.phar update

install-composer: get-composer
	@echo "### Installing composer ###"
	export HOME=/home/www-data/; php composer.phar install

update-composer-dev: get-composer
	@echo "### Updating composer ###"
	php composer.phar update --prefer-source

install-composer-dev: get-composer
	@echo "### Installing composer ###"
	php composer.phar install --prefer-source

get-composer:
	rm -f composer.phar
	php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"


analyze-dart:
	cd dart/*/; dartanalyzer */*.dart


update-composer-dev-and-commit: update-composer-dev
	git commit composer.lock -m "Updated to new version" 
	git push

