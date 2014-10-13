<?php

class clientSystemSetupTBLHelper {
	private $pdo = null;

	public function __construct($_pdo) {
		$this->pdo = $_pdo;
	}

	public function insertRow() {
		$sql = "insert into clientSystemSetupTBL (clientSystemName, Owner, Email, Phone, clientSystemBaseOS, networkType, clientUUID) values (:clientSystemName, :Owner, :Email, :Phone, :clientSystemBaseOS, :networkType, :clientUUID)";
		$stmt = $this->pdo->prepare($sql);
		$stmt->bindParam(':clientSystemName', $clientSystemName);
		$stmt->bindParam(':Owner', $Owner);
		$stmt->bindParam(':Email', $Email);
		$stmt->bindParam(':Phone', $Phone);
		$stmt->bindParam(':clientSystemBaseOS', $clientSystemBaseOS);
		$stmt->bindParam(':networkType', $networkType);
		$stmt->bindParam(':clientUUID', $clientUUID);
		$stmt->execute();

		if($stmt->rowCount() == 0) {
			# TODO: Create conventions/rules for exceptions, etc.
			throw new Exception("Failed to aaupdate ".get_class($this).".");
		}
	}

	// Returns the fetch result on success/not-empty, FALSE on error/empty.
	public function getNetworkTypeByClientUUID($clientUUID) {
		$sql = "select networkType from clientSystemSetupTBL where clientSystemSetupTBL.clientUUID='clientUUID";
		$stmt = $this->pdo->prepare($sql);
		$stmt->bindParam(':clientUUID', $clientUUID);
		$stmt->execute();
		$rslt = $stmt->fetch(PDO::FETCH_ASSOC);
		return $rslt ? $rslt : FALSE;
	}

	// Returns the fetch result on success/not-empty, FALSE on error/empty.
	public function getEnabledValidActiveClientUUIDByMAC($mac) {
		$sql = "select * from clientSystemMgmtTBL where macAddressID=':mac' and enable='enable' and valid='true' and active='true'";
		$stmt = $this->pdo->prepare($sql);
		$stmt->bindParam(':mac', $mac);
		$stmt->execute();
		$rslt = $stmt->fetch(PDO::FETCH_ASSOC);
		return $rslt ? $rslt : FALSE;
	}
}
?>

