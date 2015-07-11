#!/bin/sh

if (php --version | grep -i HipHop > /dev/null); then
    echo "Skipping gmagick on HHVM"
    exit 0
fi


###
#Install gmagick
###
GMAGICK=1.1.7RC3

#sudo apt-get install libgraphicsmagick1-dev
wget http://pecl.php.net/get/gmagick-${GMAGICK}.tgz
tar zxvf gmagick-${GMAGICK}.tgz
cd "gmagick-${GMAGICK}"
phpize && ./configure && make install && echo "Installed ext/gmagick-${GMAGICK}"

echo "extension = gmagick.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini