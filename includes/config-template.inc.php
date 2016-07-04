<?php

define('DATABASEPATH', '/Users/joshpirihi/NetBeansProjects/ParkerDB/database.sqlite');
//define('DATABASEPATH', '/home/pi/parker/database.sqlite');

/**
 * Function to autoload the requested class name.
 *
 * @param string $class_name Name of the class to be loaded.
 * @return boolean Whether the class was loaded or not.
 */
function __autoload($class_name)
{
    // Start from the base path and determine the location from the class name,
    //$base_path = 'AirMaestroAPI';

	$include_file = $class_name.'.php';
	if (stream_resolve_include_path($include_file)) {
		@include_once($include_file);
		return;
	}
	
}