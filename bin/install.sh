#!/bin/bash

set -e

if [ ! -f composer.json ]; then
    printf "Please make sure to run this script from the root directory of this repository.\n"
    exit 1
fi

#############################################################
############# COMMANDS START ################################
#############################################################
rm -rf vendor
cp .env.development .env
mkdir -p ./public/uploads
sed -i "s|UPLOAD_PATH=.*|UPLOAD_PATH=\"$(pwd -W | sed 's|/|\\\\\\\\|g')\\\\\\\\uploads\"|g" .env
composer install
php artisan key:generate
php artisan cms:install
#############################################################
############# COMMANDS END ##################################
#############################################################
