#!/usr/bin/env bash

COMPOSER=`which composer`
NPM=`which npm`

if [ -z $COMPOSER ]; then
    echo "Please install Composer."
    exit 1
fi

if [ -z $NPM ]; then
    echo "Please install NPM."
fi

composer install
npm install
./node_modules/gulp/bin/gulp.js