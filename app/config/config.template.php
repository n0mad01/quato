<?php
/**
 * Configuration
 *
 * Defining default options and server configuration
 * @package 
 */
 
/**
* Configuration of MongoDB database
*/
class dbconf {

	private static $default = array(

		'host' => '',	// Replace your MongoDB host ip or domain name herea e.g. localhost
		'port' => '',   // MongoDB connection port e.g 27017
		'database' => '',
		'username' => '',
		'password' => '',
		'persistent' => 'x'     // MongoDB persistent connection type

	);

	public static function getDefault() {
		
		return self::$default;
	}
}

// 2 salt keys are mandatory
global $salt;
$salt = '';     // eg 'k3H4ldR569eD'

global $salt2;
$salt2 = ''; // e.g. '51a170032h7a64o';
