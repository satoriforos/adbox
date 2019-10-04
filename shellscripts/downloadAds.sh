#!/bin/bash

# make sure to 'sudo chmod ugo+xs' this file!

localfolder=/home/pi/ads
remotefolder=http://example.com/ads

cd $localfolder
wget -r -l1  -nd -nH -A jpg -e robots=off --no-parent $remotefolder
