update:
	@echo "### Upgrading dart pub ###"
	pub upgrade > /dev/null
	@echo "### Upgrading PHP dependencies ###"
	composer update > /dev/null
