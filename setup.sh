#/bin/bash

echo "### Building JavaScript files ###"
find _dart -type f -name 'main*.dart' -exec dart2js {} -o  {}.js \;
echo "### Upgrading dart pub ###"
cd _common
pub upgrade > /dev/null
echo "### Upgrading PHP dependencies ###"
composer update > /dev/null
