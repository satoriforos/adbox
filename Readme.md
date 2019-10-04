AdBox Prototype
########

This project is a prototype designed to run on a Rasbperry Pi.

It aims to be an autonomous hand sanitizer that displays ads, downloaded from an ad network.

It has been programmed in emulation on a Raspberry Pi raspbian platform, but not tested or debugged!

Known Issues
============
Right now, after web configuration, there is no check to make sure Client mode works.  This means that if Wifi is unable to connect, it won't be until the next time the cron script runs that the box will be put back in client mode.

There is no advertisement server yet.  One needs to be created.  We need sample ads.

Additionally, configuration is spread between several files and classes.  Configuration should be centralized into a config file.



Mode of Operation
==================


This software creates 2 modes:
1) Host Mode
2) Client Mode

The project has 2 parts:

1) Cron Scripts that periodically download new ad content.
2) Local website for configuration


Installation
=============
To install, you must install certaint programs and copy the scripts into the right places

Software
-----------
This project requires
* apache2
* php5
* hostapd
* fbi
* wget

To install, run the following code on the Raspberry Pi:

$ mkdir /home/pi/adphotos

$ sudo mkdir -p /usr/local/bin

$ sudo mkdir -p /usr/local/includes

$ sudo apt-get install hostapd apache2 php5 libapache2-mod-php5 -y

Software configuration
---------------------
You will need to edit /etc/hostapd/hostapd.conf to create the WiFi access point settings


Script installation and configuration
-----------------------------------
- copy the cron scripts into /usr/local/bin
- copy the shellscripts/*.sh into /usr/local bin
- copy the includes/*.class.php files into /usr/local/includes
- change permissions on the scripts by adding a suid bit so that that non-root users can make system changes:

$ sudo chmod ugo+sx /usr/local/bin/*.sh

$ sudo chmod ugo+sx /usr/local/bin/*.php

- copy the contents of the website folder into /var/www and make sure www-data owns it

$ sudo cp -R website/* /var/www

$ sudo chown -R www-data:www-data /var/www

- edit the scripts to know how to locate all files and other scripts.  That includes:
* includes/AdBox.class.php
* includes/AdBoxConfig.class.php
* cronscripts/downloadads.php
* website/api/updatewifisettings.php
* shellscripts/downloadAds.sh

- set up the /usr/local/bin/downloadads.php script to run in cron every 1 minute or so for debugging.
For help with cron, follow this [cron tutorial](https://www.raspberrypi.org/documentation/linux/usage/cron.md)

- make displayAds.sh script run on bootup

$ sudo cp boosscripts/displayAds.sh /etc/init.d

$ sudo chmod 755 /etc/init.d/displayAds.sh

$ sudo update-rc.d displayAds.sh defaults



Modes
=====

HostMode
--------
Host Mode creates a local web server and WiFi access point, so that a user can connect to the box and make chages

Client Mode
-----------
Client Mode connects to local WiFi and downloads advertisements


Parts
======

Cron Scripts
---------------
The cron scripts do two things:

1) auto-download ad content periodically
2) switch system into host mode if things aren't working

Website
-----------
The website allows a user to do two things:

1) Configure the Client mode wifi
2) Switch to Wifi Client mode

