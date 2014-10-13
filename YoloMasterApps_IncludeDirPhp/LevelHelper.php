#!/usr/bin/php

<?php

/*
	LevelHelper.php
*/


/*
	updated function - mod the initial function
	uses single/multiple select sql/query
	-returns the next child level for the college, based on the input 
	 childlevel
	-returns "" if no next child level exists..

	--needs to be modified/corrected to remove the throw exceptions..
*/
function getNextLevelName($pdo,$currentLevelName, $collegeID) {
//function getNextLevelName($currentLevelName, $collegeID) {

/*
	update -apr28/2014 - bdouglas
	 changed interface to func, now pass in the pdo from 
	 the calling parent
*/

	$displayDebug=getenv('displayCrawlMasterDebug');

	if($pdo=="")
	{
		throw new Exception("Provided PDO instance is not valid.");

	}
	else
	{

		$sql="select levelName from crawlClassBookLevelTBL ";
		$sql=$sql." where ";
		$sql=$sql." LevelID= ";
		$sql=$sql."		(select childLevelId from crawlChildParentLevelByTypeTBL ";
		$sql=$sql."		 where ";
		$sql=$sql."		 parentLevelId=";
		$sql=$sql."			(select LevelID from  crawlClassBookLevelTBL ";
		$sql=$sql."				where ";
		$sql=$sql."			levelName=:curLevelName) and "; 
		$sql=$sql."		 CollegeID=:colID)";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(":curLevelName", $currentLevelName);
		$stmt->bindParam(":colID", $collegeID);
		$stmt->execute();
		if($stmt->rowCount()==0)
		{
			$nextlevelName="";
		}
		elseif($stmt->rowCount()==1)
		{
			$row = $stmt->fetch();
			$nextlevelName=$row['levelName'];
			$nextlevelName=trim($nextlevelName);
		}
		elseif($stmt->rowCount()>1)
		{
			#throw new Exception("multiple recs - error.");
			$nextlevelName=-1;
		}


		if($displayDebug==1)
		{
			print "lhelp  currentLevelName = ".$currentLevelName."\n";
			print "lhelp  colID = ".$collegeID."\n";
		}
		return($nextlevelName);

	}
}



/*
	original function
	-worked per the initial crawlClassBookLevelTBL/crawlChildParentLevelByTypeTBL
	 tbls
*/
function getNextLevelName_denny_aug24($pdo,$currentLevelName, $collegeID) {
//function getNextLevelName($currentLevelName, $collegeID) {

/*
	update -apr28/2014 - bdouglas
	 changed interface to func, now pass in the pdo from 
	 the calling parent
*/

	$displayDebug=getenv('displayCrawlMasterDebug');

	if($pdo=="")
	{
		throw new Exception("Provided PDO instance is not valid.");

	}
	else
	{
		$sql = "select LevelID from crawlClassBookLevelTBL where levelName=:curLevelName";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(":curLevelName", $currentLevelName);
		$stmt->execute();
		if($stmt->rowCount()==0)
		{
			throw new Exception("curLevelName does not exist in crawlClassBookLevelTBL.");
		}
		$row = $stmt->fetch();
		$curLevelID=$row['LevelID'];
		$curLevelID=trim($curLevelID);

		if($displayDebug==1)
		{
			print "lhelp  colID = ".$collegeID."\n";
			print "lhelp  curlvl = ".$curLevelID."\n";
		}

		// Determine the next level.
		$sql = "select childLevelId from crawlChildParentLevelByTypeTBL where CollegeID=:collegeID and parentLevelId=:curLevelID";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(":collegeID", $collegeID);
		$stmt->bindParam(":curLevelID", $curLevelID);
		$stmt->execute();

        if($stmt->rowCount()==0)
		{
			#throw new Exception("Level does not exist in crawlChildParentLevelByTypeTBL.");
			return(False);
		}
		$row = $stmt->fetch();
		$nextLevelID=$row['childLevelId'];
		$nextLevelID=trim($nextLevelID);

		$sql = "select levelName from crawlClassBookLevelTBL where LevelID=:levelID";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(":levelID", $nextLevelID);
		$stmt->execute();
		if($stmt->rowCount()==0)
		{
			// I think at this point
			//   an exception could be thrown here, since it's not expected behavior
			//   for a levelName to not exist given a valid LevelID.
			throw new Exception("nextLevelName does not exist in crawlClassBookLevelTBL.");

			#return null;	# returning null as expected behavior should probably be avoided since it could lead
							#   to bugs (think of an unexpected null being mistakenly handled as an expected condition).

			#return false;
		}
		$row = $stmt->fetch();
		$nextLevelName=$row['levelName'];
		$nextLevelName=trim($nextLevelName);
		return $nextLevelName;
	}
}

?>

