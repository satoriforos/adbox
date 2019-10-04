#!/usr/bin/php
<?php

/**
 * This script runs every X minutes
 * if there is no lock file at /tmp/adbox.lock
 * if we are currently on dhcp
 * 		comment out dhcp
 *		uncoment static
 *		restart networking
 * 		start hostapd (wifi server)
 * else 
 *		comment out static
 * 		comment out dhcp
 * 		restart networking
 * 		attempt to download images
 * end
 *
 */

$lockfile = "/tmp/adbox.lock";
$configFile = "networkinterfaces"; // "/etc/network/interfaces";
$config = file_get_contents($configFile);

$search_wlan0_dhcp = "iface wlan0 inet dhcp";
$search_wpa_ssid = "wpa-ssid";
$search_wpa_psk = "wpa-psk";

$interface = "wlan0";

function routine() {
	if (!lockFileExists($lockFile)) {
		if (isRunningDhcp()) {
			commentOutDhcp($config);
			uncommentStatic($config);
			saveConfigFile($config, $configFile);
			restartNetworking();
			startAccessPoint();
		} else {
			commentOutStatic($config);
			uncommentDhcp($config);
			stopAccessPoint();
			saveConfigFile($config . $configFile);
			restartNetworking();
			downloadAds();
		}
	}
}

function lockFileExists($lockFile) {
	return file_exists($lockFile);
}

function isRunningDhcp() {
	// look for a line in /etc/network/interfaces to be uncommented
	$wlan0_dhcp = "iface wlan0 inet dhcp";
	$interfacesFile = file("/etc/network/interfaces");
	// loop through each line of the file
	foreach ($interfacesFile as $interfacesLine) {
		if ($interfacesLine == $wlan0_dhcp) {
			return true;
		}
	}
	return false;
}

function commentOut($config, $searchArray) {
	foreach ($searchArray as $search) {
		$config = str_replace("#".$search, $search, $config);
	}
	return $config;
}
function uncomment($config, $searchArray) {
	foreach ($searchArray as $search) {
		$config = str_replace($search, "#".$search, $config);
	}
	return $config;
}
function commentOutDhcp($config) {
	$searchArray = array(
		$search_wlan0_dhcp,
		$search_wpa_ssid,
		$search_wpa_psk
	);
	$config = commentOut($config, $searchArray);
	return $config;
}
function uncommentDhcp($config) {
	$searchArray = array(
		$search_wlan0_dhcp,
		$search_wpa_ssid,
		$search_wpa_psk
	);
	$config = uncomment($config, $searchArray);
	return $config;
}
function commentOutStatic($config) {
	$searchArray = array(
		$search_wlan0_static,
		$search_wlan0_address,
		$search_wlan0_netmask
	);
	$config = commentOut($config, $searchArray);
	return $config;
}
function uncommentStatic($config) {
	$searchArray = array(
		$search_wlan0_static,
		$search_wlan0_address,
		$search_wlan0_netmask
	);
	$config = uncomment($config, $searchArray);
	return $config;
}
function restartNetworking($interface) {
	$command = "restartNetworking.sh ".$interface;
	shell_exec($command);
}
function killAccessPoint() {
	$command = "killAccessPoint.sh";
	shell_exec($command);
}
function startAccessPoint() {
	$command = "startAccessPoint.sh";
	shell_exec($command);
}
function downloadAds() {
	$command = "downloadAds.sh";
	shell_exec($command);
}


?>
