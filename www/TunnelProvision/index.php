<?php
	/*
	 *	FileName: index.php
 	 *
 	 *
	 *		Creation date:
 	 *		apr/20/14
	 *
	 *	Modification/update:
	 *
	 *
	 *	Purpose:
         *
         *
	 *	
	 *	Usage:
	 *
	 *
	 *	Returns:
	 *		returns a struct of data to the FetchRequestQueue
	 *
	 *	
	 *	App Logic:
	 *

		index.php
	 * SSH tunnel provisioning web service.
	 * Accepts a MAC address from a client,
	 *   checks it against the list of valid
	 *   MACs, and returns the appropriate result.
	 *
	 * Actions:
	 * -MAC not present:
	 *   Returns 'failure' status ('0').
	 * -MAC present, no port assigned:
	 *   Finds valid port, updates the DB, and
	 *   returns the port.
	 * -MAC present, port already assigned:
	 *   Returns the assigned port from the DB.
	 */
//-*

	ini_set('log_errors', 'On');

	#$includeLib=getenv('yolo_masterYoloMasterApps_IncludeDirPhp');

	#require_once('/yolo-master/YoloMasterApps_IncludeDirPhp/DB_conf_inc.php');

	$dbhost = '127.0.0.1';
	$dbport = '3306';
	$dbname = 'crawlManagerDB';
	$dbuser = 'root';
	$dbpass = '';

	// Setup DB.
	//$pdo_def="mysql:host=".$dbhost.";dbname=".$dbname;
	$pdo_def="mysql:host=".$dbhost.";port=".$dbport.";dbname=".$dbname;
	$pdo = new PDO($pdo_def,$dbuser,$dbpass);

	$current_port = 14000; // Default.

#!/usr/bin/php
	// Create the output.
	$output = array('status' => '0', 'port' => '0');

	// Get MAC address from client.
	//$_POST['p_mac']="00:0F:55:A6:9C:C0";
	//$_POST['p_mac']="000f55a68c2b";
	//$_POST['p_mac']="001ec944bec6";

	$p_mac = $_REQUEST['p_mac'];

	if(!is_string($p_mac))
	{
		//handle not being a string
		exit();
	}
	$p_mac=htmlspecialchars($p_mac);
	$mac = trim($p_mac);

	// Make sure MAC is correctly formatted before continuing.
	//if(strlen($mac) < 12) {
	if(strlen($mac) != 12) {	# 12 yes ?? bdouglas
		// 12 is the length of a MAC address without the colon delimeter.
		// Return failure if it's not at least that length.
		echo rawurlencode(json_encode($output));
		exit;
	}

	// Check MAC address against DB.
	$sql = 'select masterTunnelPort from clientSystemMgmtTBL where macAddressID=:macAddr';
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':macAddr', $mac);
	$stmt->execute();

	if($stmt->rowCount() == 0) {
		// Client with given MAC address does not exist in the DB.
		// Return new port.
		$sql = 'select max(masterTunnelPort) from clientSystemMgmtTBL';
		$stmt1 = $pdo->prepare($sql);
		$stmt1->execute();
		$row1 = current($stmt1->fetchAll(PDO::FETCH_ASSOC));
		if(((int)$row1["max(masterTunnelPort)"]) >= 14000) {
			$output['port'] = (string)((int)$row1["max(masterTunnelPort)"] + 1);
			$output['status'] = 1;
			$sql = 'insert into clientSystemMgmtTBL(clientUUID, MacAddressID, active, masterTunnelPort) values(:clientUUID, :MacAddressID, :active, :masterTunnelPort)';
			$stmt2 = $pdo->prepare($sql);
			$stmt2->bindParam(':clientUUID', $mac);
			$stmt2->bindParam(':MacAddressID', $mac);
			$active = '1';
			$stmt2->bindParam(':active', $active);
			$stmt2->bindParam(':masterTunnelPort', $output['port']);
			$stmt2->execute();
		} else {
			$port = 14000;
			$output['port'] = $port;
			$output['status'] = 1;
			$sql = 'insert into clientSystemMgmtTBL(clientUUID, MacAddressID, active, masterTunnelPort) values(:clientUUID, :MacAddressID, :active, :masterTunnelPort)';
			$stmt2 = $pdo->prepare($sql);
			$stmt2->bindParam(':clientUUID', $mac);
			$stmt2->bindParam(':MacAddressID', $mac);
			$active = '1';
			$stmt2->bindParam(':active', $active);
			$stmt2->bindParam(':masterTunnelPort', $port);
			$stmt2->execute();
		}
		echo rawurlencode(json_encode($output));
		exit;
	}

	$row = current($stmt->fetchAll(PDO::FETCH_ASSOC));

	if (((string)$row['masterTunnelPort'] == '0') ||
	    ((string)$row['masterTunnelPort'] == '' ))  {
		// Client with given MAC exists, but port is not assigned.
		// Check for availability and assign port.

		$port = getFirstAvailablePort($current_port);

		// Update the table accordingly.
		$sql = 'update clientSystemMgmtTBL set masterTunnelPort=:mtPort where macAddressID=:macAddr';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':mtPort', $port);
		$stmt->bindParam(':macAddr', $mac);
		$stmt->execute();

		if($stmt->rowCount() == 0) {
			// Failed to update client with given MAC address in the DB.
			// Return failure.
			echo rawurlencode(json_encode($output));
			exit;
		}

		$output['status'] = '1';
		$output['port'] = $port;
		echo rawurlencode(json_encode($output));
                exit;
	} else {
		// Client with given MAC exists, port already assigned. Return assigned port.
		$output['status'] = '1';
		$output['port'] = (string)$row['masterTunnelPort'];
		echo rawurlencode(json_encode($output));
                exit;
	}


/*
 *
 *	func declarations
*/

	/*
	 * Get the lowest available port given all the currently
	 *   assigned ports in the database.
	 */
	function getFirstAvailablePort($current_port) {
		// Get the highest port in use, and set the current port
		//   to the next. START_PORT is used if no ports are currently in use.
		global $pdo;
		global $displayDebug;

		$sql = 'select MAX(masterTunnelPort) as masterTunnelPort from clientSystemMgmtTBL';
		$stmt = $pdo->prepare($sql);
		$stmt->execute();

		/*
			bdouglas -may23/2014 -- rewrote
		*/
		if($stmt->rowCount() > 0) {
			// Iterate through every available port, find the highest,
			//   and set the next as the current_port.
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$highest = 0;

			/*
			foreach($rows as $row) {
				$cur = (int)$row['masterTunnelPort'];
				if($cur > $highest)
					$highest = $cur;
			}
			*/

			$highest=$rows[0]['masterTunnelPort'];
			if($highest > 0) {
				$max_tries = 20000; 
				$i = 0;
				$available = 2;
				while(((int)$available) > 0 && ($i < $max_tries)) {
					$i += 1;
					$current_port = $highest + 1;

					// Make sure port is available on master system.
					$available = shell_exec('netstat -lnt | awk \'$6 == "LISTEN" && $4 ~ ".' . $current_port . '"\' | wc -l');
				}
			}
		}

		return $current_port;
	}


?>


