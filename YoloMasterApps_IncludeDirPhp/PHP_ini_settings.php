<?php
/*
 *	FileName: PHP_ini_settings.php
 *	getenv('yolo_masterYoloMasterApps_IncludeDirPhp')/PHP_ini_settings.php
 *
 *	Creation date:
 *		aug/6/14
 *
 *	Modification/update:
 *
 *
 *	Purpose:
 *		handle/set php init settings
 *		-used in all php files for the crawl
 *
 *	
 *
 *	
 *	Usage:
 *		resides on the backend
 *
 *
 *
 *	Returns:
 *
 *	
 *	App Logic:
 *
 *
 *
 *
*/
	//ini_set("memory_limit","500M");
	ini_set('display_errors', 'On');
	ini_set('error_reporting', E_ALL);
	ini_set('error_log', '/var/log/php-errors.log'); 

?>

