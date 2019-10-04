#!/bin/bash
# make sure to 'sudo chmod ugo+xs' this file!

# grab the first parameter and call it $interface
$interface = $1

ifconfig down $interface
ifconfig up $interface

