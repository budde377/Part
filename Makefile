update:
	@echo "### Upgrading dart pub ###"
	export HOME=/home/www-data/; pub upgrade > /dev/null
	@echo "### Upgrading PHP dependencies ###"
	composer update > /dev/null

