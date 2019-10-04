<?php
/**
 * Make sure to edit the config on this file, also make sure your PHP script timeout in your php.ini is several minutes long
 *
 * This file should take in a JSON with an acesspoint and password, apply it to the wifi config, and try to enter client mode
 *
 * Example input: {"accesspoint":"xfinitywifi", "password":"totallysecurepassword"}
 *
 */

/**
 * config
 */
$adBoxClassPath 		= "/usr/local/includes/AdBox.class.php";			// path to AdBox class file
$adBoxConfigClassPath 	= "/usr/local/includes/AdBoxConfig.class.php";		// path to AdBoxConfig class file
$debugMode 				= true;									// debug mode - off during production

/**
 * includes
 */
require_once($adBoxClassPath);
require_once($adBoxConfigClassPath);
$AdBoxConfig = new AdBoxConfig();
$AdBox = new AdBox($AdBoxConfig);
if ($debugMode) {
	ini_set('display_errors', "on");
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
}

/**
 * read input
 */
$postData = file_get_contents("php://input");
$jsonData = json_decode($postData);

/**
 * error checking
 */
if (!$jsonData) {
	reportError("invalid_json", "invalid JSON input");
}
if (!$jsonData->accessPoint) {
	reportError("invalid_access_point", "No access point provided");
}
if (!$jsonData->password) {
	reportError("invalid_password", "No password provided");
}

/**
 * process data and try to switch to client mode
 */
$AdBoxConfig->updateWpaAuth($jsonData->accessPoint, $jsonData->password);
reportSuccess(null);
$AdBox->switchToClientMode();



/**
 * support functions
 */
function reportError($type, $description) {
	$output = new stdClass();
	$output->status = "error";
	$output->type = $type;
	$output->description = $description;

	header("HTTP/1.1 400 Bad Request");
	header("Content-Type: application/json");
	echo(json_encode($output));
	exit;
}

function reportSuccess($data=null) {
	$output = new stdClass();
	$output->status = "success";
	if ($data) $output->data = $data;

	header("HTTP/1.1 200 OK");
	header("Content-Type: application/json");
	echo(json_encode($output));
}
?>
