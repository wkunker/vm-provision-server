<?php

/*
#
 *	FileName: DB_conf_inc.php
 *
 *
 *	Creation date:
 *		apr/17/14
 *
 *	Modification/update:
 *
 *
 *	Purpose:
 * 		Include file, containing the base mysql config attributes.
 *		 Used to connect to the db/tbls from the different masterside 
 *		 applications for the crawl process.
 *	
 *	Usage:
 *		resides on the backend
 *
 *
*/

/*
	update - apr 28/2014
	handle the crawl db
*/
$dbhost="localhost";
$dbname="crawlManagerDB_testing";
//$dbname="atlDB_s2_g";
$dbuser="crawl_user";
$dbpass="crawlpasswd";

/*
	update - apr 28/2014
	handle the gearmanQueue db
*/
$gearman_dbhost="localhost";
$gearman_dbname="g_queue_testing";
//$dbname="atlDB_s2_g";
$gearman_dbuser="g_user1";
$gearman_dbpass="gearman";



?>

