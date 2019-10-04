#!/usr/bin/php
<?php
/**
 * Make sure to chmod go+x this file to give proper executable permissions
 *
 * Set this up with cron on your system to run every X minutes 
 * X minutes is probably 1-5 for debugging and demonstrations, 30-60 or more for production
 *
 * 
 */


$adBoxClassPath 		= "/usr/local/includes/AdBox.class.php";		// path to AdBox.class.php
$adBoxConfigClassPath 	= "/usr/local/includes/AdBoxConfig.class.php";	// path to AdBoxConfig.class.php

require_once($adBoxClassPath);
require_once($adBoxConfigClassPath);

$AdBoxConfig = new AdBoxConfig();
$AdBox = new AdBox($AdBoxConfig);

function routine() {
	if ($AdBox->lockFileExists()) {
		$AdBox->switchToAccessPointMode();
	} else {
		$AdBox->switchToClientMode();
	}
}


?>
