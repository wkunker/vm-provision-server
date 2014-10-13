#!/usr/bin/php

<?php

/*
MapLevelToTableTBLHelper.php 
*/

//function getAllTablesFromLevel($levelName, $siteType) {
function getAllTablesFromLevel($pdo,$levelName, $siteType) {
/*
	update -apr28/2014 - bdouglas
	 changed interface to func, now pass in the pdo from 
	 the calling parent
*/


	if($pdo=="")
	{
		//denny --need to throw some error here
	}
	else
	{
		$sql = "select tableName from mapLevelToTableTBL where Level=:levelName and SiteType=:siteType";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(":levelName", $levelName);
		$stmt->bindParam(":siteType", $siteType);
		$stmt->execute();
		if($stmt->rowCount()==0)
		{
			throw new Exception("levelName/siteType does not exist in mapLevelToTableTBL.");
		}
		return $stmt->fetchAll();
	}
}

function getAllLevelsFromTable($pdo,$tableName) {
//function getAllLevelsFromTable($tableName) {
/*
	update -apr28/2014 - bdouglas
	 changed interface to func, now pass in the pdo from 
	 the calling parent
*/


	if($pdo=="")
	{
		//denny --need to throw some error here
	}
	else
	{
		$sql = "select Level, SiteType from mapLevelToTableTBL where tableName=:tableName";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(":tableName", $tableName);
		$stmt->execute();
		if($stmt->rowCount()==0)
		{
			throw new Exception("tableName does not exist in mapLevelToTableTBL.");
		}
		return $stmt->fetchAll();
	}
}

function isLevelInTable($pdo, $level, $tableName) {
	$levels = getAllLevelsFromTable($pdo, $tableName);
    foreach($levels as $row) {
        if($level == $row['Level'])
        	return true;
    }
    return false;
}

?>

