#!/bin/bash

CYAN="\033[36m"
RED="\033[31m"
RESC="\033[0m"

BASE_PATH=$(cd $(dirname $0) ; pwd)
cd $BASE_PATH

if ! php_loc="$(type -p "php")" || [ -z "$php_loc" ]; then
	echo -e "${RED}*** PHP CLI not detected. Please install PHP CLI and add it to the path before using this installer${RESC}"
	exit 1
fi

echo -e "${CYAN}Installing composer...${RESC}"

if [ -e composer ] ; then
	rm -fr composer > /dev/null 2>&1
fi
php -r "readfile('https://getcomposer.org/installer');" | php -- --filename=composer --force

if [ ! -e composer ] ; then
	echo -e "${RED}*** Failed installing composer${RESC}"
	exit 1
fi
if [ ! -x composer ] ; then
	chmod +x composer
fi

echo ""
echo -e "${CYAN}Installing dependencies...${RESC}"
./composer update --no-dev -o

echo ""
echo -e "${CYAN}Compiling CSS...${RESC}"
./make_css.sh

echo ""
echo -e "${CYAN}Creating autloader classmap...${RESC}"
./make_zf_map.php

echo ""
echo -e "${CYAN}Running installer...${RESC}"
php scripts/installer.php

