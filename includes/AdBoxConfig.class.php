<?php

/**
 * AdBox handles the changes to network infrastructure
 * and configuration on the advertising box
 *
 */
class AdBoxConfig {

	/**
	 * Configuration
	 */
	private $__configFile			= '/etc/network/interfaces'; 		// path to network intefaces config
	private $__config;

	public $networkInterface		= "wlan0"; 							// wireless lan device

	private $__search_wlan0_dhcp 	= "iface wlan0 inet dhcp";			// line in network config for client mode
	private $__search_wpa_ssid 		= "wpa-ssid";						// line in network config for client mode
	private $__search_wpa_psk 		= "wpa-psk";						// line in network config for client mode

	private $__search_wlan0_static = "iface wlan0 inet static";			// line in network config for host mode
	private $__search_wlan0_address = "address 192.168.0.1";			// line in network config for host mode
	private $__search_wlan0_netmask = "netmask255.255.255.0";			// line in network config for host mode

	/**
	 * Instantiate the AdBoxConfig manager
	 * @param String 	the config file location, eg /etc/network/interfaces
	 */
	public function __construct() {
		$this->__config = $this->readConfigFile($this->__configFile);
	}

	/**
	 * Configure network settings for Wifi Access Point (Host) mode
	 */
	public function setConfigForHostMode() {
		if (!$this->inHostMode()) {
			$this->__config = $this->commentOutDhcp($this->__config);
			$this->__config = $this->uncommentStatic($this->__config);
			$this->saveConfigFile($this->__config, $this->__configFile);
		}
	}
	public function setConfigForClientMode() {
		$this->__config = $this->commentOutStatic($this->__config);
		$this->__config = $this->uncommentDhcp($this->__config);
		$this->saveConfigFile($this->__config, $this->__configFile);
	}

	/**
	 * Open a config file
	 * @param String 	the config file location, eg /etc/network/interfaces
	 * @return String 	the config file text contents
	 */
	public function readConfigFile($configFile) {
		$config = file_get_contents($configFile);
		return $config;
	}

	/**
	 * Save the config file
	 * @param String 	the text config
	 * @param String 	the config file location, eg /etc/network/interfaces
	 */
	public function saveConfigFile($config, $configFile) {
		file_put_contents($configFile, $config);
	}

	/**
	 * Chang the wifi wpa settings 
	 * @param String 	the text config
	 * @param String 	the wpa ssid
	 * @param String 	the wpa password
	 * @return String 	the text config with new wpa settings
	 */
	public function updateWpaAuth($wpa_ssid, $wpa_psk) {
		$ssid_search = 'wpa-ssid "[^"]*"/';
		$ssid_replace = 'wpa-ssid "'.$wpa_ssid.'"';

		$psk_search = 'wpa-psk "[^"]*"/';
		$psk_replace = 'wpa-psk "'.$wpa_ssid.'"';

		$this->__config = preg_replace(
			$ssid_search,
			$ssid_replace,
			$this->__config
		);

		$this->__config = preg_replace(
			$psk_search,
			$psk_replace,
			$this->__config
		);
		$this->saveConfigFile($this->__config, $this->__configFile);
		//return $this->__config;
	}



	/**
	 * True if the network interface is configured with DHCP
	 */
	public function inHostMode() {
		return $this->isNetworkConfiguredStatic();
	}
	public function isNetworkConfiguredStatic() {
		// look for a line in /etc/network/interfaces to be uncommented
		$interfacesFile = file("/etc/network/interfaces");
		// loop through each line of the file
		foreach ($interfacesFile as $interfacesLine) {
			if ($interfacesLine == $this->__search_wlan0_dhcp) {
				return false;
			}
		}
		return true;
	}


	/**
	 * Comment out DHCP settings in /etc/network/interfaces config
	 * @param String 	the text config
	 * @return String 	the config with comments applied
	 */
	public function commentOutDhcp($config) {
		$searchArray = array(
			$this->__search_wlan0_dhcp,
			$this->__search_wpa_ssid,
			$this->__search_wpa_psk
		);
		$config = $this->__comment($config, $searchArray);
		return $config;
	}

	/**
	 * Uncomment DHCP settings in /etc/network/interfaces config
	 * @param String 	the text config
	 * @return String 	the config without comments
	 */
	public function uncommentDhcp($config) {
		$searchArray = array(
			$this->__search_wlan0_dhcp,
			$this->__search_wpa_ssid,
			$this->__search_wpa_psk
		);
		$config = $this->__uncomment($config, $searchArray);
		return $config;
	}


	/**
	 * Comment out static network settings in /etc/network/interfaces config
	 * @param String 	the text config
	 * @return String 	the config without comments
	 */
	public function commentOutStatic($config) {
		$searchArray = array(
			$this->__search_wlan0_static,
			$this->__search_wlan0_address,
			$this->__search_wlan0_netmask
		);
		$config = $this->__comment($config, $searchArray);
		return $config;
	}


	/**
	 * Uncomment static network settings in /etc/network/interfaces config
	 * @param String 	the text config
	 * @return String 	the config without comments
	 */
	public function uncommentStatic($config) {
		$searchArray = array(
			$this->__search_wlan0_static,
			$this->__search_wlan0_address,
			$this->__search_wlan0_netmask
		);
		$config = $this->__uncomment($config, $searchArray);
		return $config;
	}


	/**
	 * Comment lines in a text configuration
	 * 
	 * @param String 	text configuration
	 * @param String[]	the array of lines to comment (given without the comment prefix)
	 * @return String 	text configuration with comments applied
	 * @example $this->__comment("line1\nline2", array("line2"));
	 */
	private function __comment($config, $searchArray) {
		foreach ($searchArray as $search) {
			$config = str_replace("#".$search, $search, $config);
		}
		return $config;
	}

	/**
	 * Uncomment lines in a text configuration
	 * 
	 * @param String 	text configuration
	 * @param String[]	the array of lines to uncomment (given without the comment prefix)
	 * @param String 	text configuration with uncomments applied
	 * @example $this->__uncomment("line1\nline2", array("line2"));
	 */
	private function __uncomment($config, $searchArray) {
		foreach ($searchArray as $search) {
			$config = str_replace($search, "#".$search, $config);
		}
		return $config;
	}

}


?>
