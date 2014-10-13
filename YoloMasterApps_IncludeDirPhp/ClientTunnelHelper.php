<?php

	/*
	 * Attempt to retrieve the designated reverse tunnel port.
	 * On success: returns the tunnel port as a string.
	 * On failure: returns false.
	 */
	function getTunnelPortFromMAC($pdo, $mac) {

		$sql = 'select masterTunnelPort from clientSystemMgmtTBL where macAddressID=:macAddr';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':macAddr', $mac);
		$stmt->execute();

		if($stmt->rowCount() == 0) {
			// Client with given MAC address does not exist in the DB.
			// Return failure.
			return false;
		}

		$row = current($stmt->fetchAll(PDO::FETCH_ASSOC));

		$port = (string) $row['masterTunnelPort'];

		if($port == '0') {
			return false;
		} else {
			return $port;
		}
	}

?>

