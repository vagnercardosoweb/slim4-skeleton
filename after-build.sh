#!/usr/bin/env bash

find . -type d -exec chmod ug=rwx,o=rx {} \;
find . -type f -exec chmod ug=rw,o=r {} \;

find ./application/storage/cache -iname "*.php" -exec rm {} \;

find ./application/storage/cache/twig/* -type d -exec rm -rf {} \;
mkdir -p ./application/storage/cache/twig
