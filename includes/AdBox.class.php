<?php

/**
 * AdBox handles the changes to network infrastructure and ad downloading
 *
 */
class AdBox {

	/**
	 * Configuration
	 */
	public $command_restartNetworking 	= "/usr/local/bin/restartNetworking.sh";
	public $command_killAccessPoint 	= "/usr/local/bin/killAccessPoint.sh";
	public $command_startAccessPoint 	= "/usr/local/bin/startAccessPoint.sh";
	public $command_downloadAds			= "/usr/local/bin/downloadAds.sh";
	public $command_ifconfig			= "/usr/locall/sbin/ifconfig";  				// path to ifconfig

	public $networkInterface 			= "wlan0"; 										// wireless lan device

	private  $__lockFile 				= "/tmp/adbox.lock"; 							// path to lockfile

	private $__AdBoxConfig;

	/**
	 * Instantiate the AdBox Manager
	 */
	public function __construct($AdBoxConfig) {
		$this->__AdBoxConfig = $AdBoxCofig;
	}


	/**
	 * Switch AdBox into AccessPoint (Host) mode, ready for configuration
	 */
	public function switchToAccessPointMode() {
		if (!$this->__AdBoxConfig->inHostMode()) {
			$this->__AdBoxConfig->setConfigForHostMode();
			$this->restartNetworking();
			$this->startAccessPoint();
		}

	}

	/**
	 * Switch AdBox into Client mode, for downloading ads
	 */
	public function switchToClientMode() {
		$this->__AdBoxConfig->setConfigForClientMode();
		$this->stopAccessPoint();
		$this->restartNetworking();
		$this->downloadAds();
	}

	/**
	 * Create a Lock file - run when starting configuration
	 */
	public function createLockFile() {
		$fileRef = fopen($this->__lockFile);
		fclose($fileRef);
	}

	/**
	 * Removes the lock file - run when done with configuration
	 */
	public function removeLockFile() {
		unlink($this->__lockFile);
	}

	/**
	 * True if lock file exists
	 */
	public function lockFileExists($file) {
		return file_exists($this->__lockFile);
	}


	/**
	 * Restart networking
	 */
	function restartNetworking() {
		$command = $this->command_restartNetworking.' '.$this->networkInterface;
		shell_exec($command);
	}

	/**
	 * Kill the Wifi Access Point
	 */
	function killAccessPoint() {
		$command = $this->command_killAccessPoint;
		shell_exec($command);
	}

	/**
	 * Start the wifi access point
	 */
	function startAccessPoint() {
		$command = $this->command_startAccessPoint;
		shell_exec($command);
	}


	/**
	 * True if the network interface has an IP address
	 */
	function isNetworkingActive($interface) {
		$command = $this->command_ifconfig." ".$this->networkInterface;

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


	/**
	 * Download advertisements
	 */
	function downloadAds() {
		$command = $this->command_downloadAds;
		shell_exec($command);
	}



}



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


?>
