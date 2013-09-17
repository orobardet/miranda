#!/usr/bin/env bash
LESS_FILES="bootstrap.less style.less"
LESS_DIR=$(dirname $0)/public/css
CSS_DIR=$LESS_DIR

if [ "$1" = "wait" ] ; then
	# Wait a few moment for file to be written (if network mounted) 
	sleep 1
fi

for less in $LESS_FILES ; do
	css=$(echo $less | sed 's/\.less$/.css'/)
	echo "$less => $css..."
	lessc "$LESS_DIR/$less" "$CSS_DIR/$css"
done
