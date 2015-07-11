#!/bin/sh

if (php --version | grep -i HipHop > /dev/null); then
    echo "Skipping Imagick on HHVM"
    exit 0
fi

###
# Install Imagick
###
IMAGICK_VERSION=3.1.2

wget http://pecl.php.net/get/imagick-${IMAGICK_VERSION}.tgz
tar zxvf imagick-${IMAGICK_VERSION}.tgz
cd "imagick-${IMAGICK_VERSION}"
phpize && ./configure && make install && echo "Installed ext/imagick-${IMAGICK_VERSION}"

echo "extension = imagick.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini