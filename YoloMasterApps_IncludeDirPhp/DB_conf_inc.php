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


	update jun24/14 - bdouglas
	change localhost to 127.0.0.1
	added port=3306

	this forces (i think) to use tcp instead of sockets..
	apparently localhost is interpreted, and triggers using sockets
	 but pdo/apache then has issue of perms with sockets when 
	 and if mysql is changed from the normal mysql location..

*/
//$dbhost="localhost";
$dbhost="127.0.0.1";
//$dbsock="/home/ihubuser/m2/mysql.sock";
$dbport="3306";
$dbname="crawlManagerDB";
//$dbname="atlDB_s2_g";
$dbuser="root";
$dbpass="";

/*
	update - apr 28/2014
	handle the gearmanQueue db
*/
//$gearman_dbhost="localhost";
$gearman_dbhost="127.0.0.1";
//$gearman_dbsock="/home/ihubuser/m2/mysql.sock";
$gearman_dbport="3306";
$gearman_dbname="g_queue_testing";
//$dbname="atlDB_s2_g";
$gearman_dbuser="g_user1";
$gearman_dbpass="gearman";



?>

