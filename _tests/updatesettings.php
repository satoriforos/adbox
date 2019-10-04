<?php
/**
 * drop a lock file
 * edit /etc/network/interfaces
 * change wpa-ssid "<ssid>"
 * change wpa-psk "<password>"
 * comment out static
 * uncomment dhcp
 * tell user that settings have changed
 * kill hostapd
 * restart networking
 * if networking is not working
 * 		switch back to static
 * 		start hostapd
 *
 */

$wpa_ssid = "testSSID";
$wpa_psk = "testPassword";

$lockFile = "/tmp/adbox.lock";
$configFile = "networkinterfaces";
$config = file_get_contents($configFile);


$search_wlan0_dhcp = "iface wlan0 inet dhcp";
$search_wpa_ssid = "wpa-ssid";
$search_wpa_psk = "wpa-psk";

$interface = "wlan0";


function routine() {
	createFile($lockFile);
	$config = readConfigFile($configFile);
	$config = updateWpaAuth($config, $wpa_ssid, $wpa_psk);

	$config = commentOutStatic($config);
	$config = uncommentDhcp($config);
	saveConfigFile($config, $configFile);

	killAccessPoint();

	restartNetworking();

	$error = false;
	$success = false;
	if (!isNetworkingWorking($interface)) {
		$error = true;
		$success = false;
		$config = commentOutDhcp($config);
		$config = uncommentStatic($config);
		saveConfigFile($config, $configFile);
		restartNetworking();
		startAccessPoint();
	} else {
		$error = false;
		$success = true;
		downloadAds();
	}
	removeFile($lockFile);
}



function createFile($file) {
	$fileRef = fopen($file);
	fclose($fileRef);
}
function removeFile($file) {
	unlink($file);
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
function updateUpaAuth($config, $wpa_ssid, $wpa_psk) {
	$ssid_search = 'wpa-ssid "[^"]*"/';
	$ssid_replace = 'wpa-ssid "'.$wpa_ssid.'"';

	$psk_search = 'wpa-psk "[^"]*"/';
	$psk_replace = 'wpa-psk "'.$wpa_ssid.'"';

	$config = preg_replace(
		$ssid_search,
		$ssid_replace,
		$config
	);

	$config = preg_replace(
		$psk_search,
		$psk_replace,
		$config
	);
}

function readConfigFile($configFile) {
	$config = file_get_contents($configFile);
	return $config;
}
function saveConfigFile($config, $configFile) {
	file_put_contents($configFile, $config);
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


function isNetworkingWorking($interface) {
	$command = "ifconfig ".$interface;

	$output = shell_exec($command);

	$inet_addr_search = "/inet addr:([^\s]+)\s/";
	$matches = array();
	preg_match($inet_addr_search, $output, $matches);

	$address = "";
	if (count($matches)) {
		$address = $matches[1];
	}

	$retval = false;
	if ($address) {
		$retval = true;
	}
	return $retval;
}


function downloadAds() {
	$command = "downloadAds.sh";
	shell_exec($command);
}


?>
