#!/bin/bash

if [ -z "$1" ] ; then
	echo Cannot execute an empty command
	exit 1
fi

# Make sure we start off in the proper directory
base=$(dirname $0)
cd $base
executable=$EXECUTABLE

# Create the locks directory
locks=$base/cache/locks
mkdir -p $locks 2>/dev/null

# Determine the lock file
OIFS="$IFS"
IFS="."
lockFile="$locks/$*.lock"
IFS="$OIFS"
flock=$(which flock)

# Execute!
#echo $base/cli.php
$flock -w 63 $lockFile $executable $base/cli.php "$@"
