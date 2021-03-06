<?php

/*
#-------------------------------------------------------------
#
#	FileName:
#		gcSetup_inc.php
#		_DIR_/YoloMasterApps_IncludeDirPhp/gcSetup_inc.php
#
#	Goal:	
#		used in the various process code/logic to handle the 
#		 setup/processing of queue insertion with/regards 
#		 to handling the jobAction actions as well for the
#		 given jobUUID...
#
#
#	this is used for the yolo-master-vm2 processes
#
#
#	modification/update::
#	MAJOR UPDATE::
#	--created/changed gcSetup_group to accomodate the 
#		group insert process into the tbls.. 
#
#
#
*/

$includeLib=getenv('yolo_masterYoloMasterApps_IncludeDirPhp');

//--include base crawl_functions.inc.php/function
require_once($includeLib.'/crawl_functions.inc.php');






/*
functon gcSetup($host,$port,$funcName,$odata)
			$clientP = new GearmanClient();
			global $gParseCourseSectionWorker_port;
			$a = $clientP->addServer($gServer_host, $gParseCourseSectionWorker_port);
			// TODO: handle gearman failures, etc
			$clientFunc = "CrawlCourseSectionFunction";
			$output_data = rawurlencode(json_encode($dataCpy));
			$z = $clientP->doBackground($clientFunc, $output_data);
*/

/*
	functionName:	gcSetup
	goal:			used to setup the "abstracted" queue insertion process, as well as 
					 handle the potential jobSchedule action process

	usage:			called by the parentApp, uses data from the queueFunction of the 
					parent

	inputDefs:
		$host:		gQueue Server IP/Address
		$port:		gQueue Port Address
		$funcName:	queue function name of the "intended" queue process
		$odata:		data for the queueFile, mapped to the internal fpuuid
					 in the qdArray
		$jobUUID:	jobuuid used to schedule/map/track the job action, used to 
					 determine if the data is written/inserted in the queue, or 
					 if the "data" is placed in a separate tbl, while the 
					 jobActionCron process handles managing the data based 
					 on the crawl/job action requirements
					 from the "dataPacket"
		$fuuid:		the uuid to generate the file, for the assocated data to 
					 process the data
		$mpuuid:	masterProcUUID from the "dataPacket"
		$qdir:		the associated queueDir for the queueProcess, different for 
					 each queue, all the associated queue files are stored in 
					 the dir
		$fprename:	the associated "pre name" for the queueProcess file
		$queueName:	the queueName, for the jobQueueActionTBL (wait/stop/delete/..)
		$prevRename:	rename/prefix of the current/prev queue data file
		$prevQueueDir:	dir of the current/prev queue data file
		$prevUUID:	uuid of the previous file/data
					used to generate the file of data used for the currentQueue, 
					 this file is deleted once the new/next queue is inserted/created
*/
function gcSetup($pdo,$host,$port,$funcName,$odata,$jobUUID,$mpuuid,$qdir,
		$fprename,$queueName,$prevRename,$prevQueueDir,$prevUUID)
{


	$fUUID=uuidgen();

	//--for the queue insertion/data
	$d=array();
	$qd=array();
	$qd['jobUUID'] = $jobUUID;
	$qd['fnameUUID']=$fUUID;
	$qd['mpuuid']=$mpuuid;

	/*
		ok - update - jul 5/15 -- bdouglas
		major differrence here, takes into account the jobScheduling process 
		can be stopped/modified by a user via the job scheduler webapp
		so, the process needs to determine when/where to stop, or to 
		place the packet for the jobuuid
	*/
	$dir=getenv('yolo_masterPidDir');
	$cronFile=$dir."/"."cronTakesOver.pid";
	$jcmd="grep -i '".$jobUUID."' ".$cronFile;
	$jcmd=system($jcmd);
	$jcmd=trim($jcmd);


	//$qdir=parseProcessTmpDir;	//where the file for the queue is located
								//keep it in a dir mapped to the queue

	//$fname=$qdir."/"."parsereturn_".trim($fuuid).".dat";
	$fname=$qdir."/".$fprename.trim($fUUID).".dat";

	$f1=fopen($fname,"wr+");
	if(!$f1)
		die("file open err");
	fwrite($f1, $odata);
	fclose($f1);


	$sql="select * from jobFileMpQueueMappingTBL ";
	$sql=$sql."where "; 
	$sql=$sql."jobUUID=:juuid and "; 
	$sql=$sql."fUUID=:fuuid and "; 
	$sql=$sql."mpUUID=:mpuuid and ";
	$sql=$sql."QueueName=:qname";

	$stmt1 = $pdo->prepare($sql);
	$stmt1->bindParam(':juuid', $jobUUID);
	$stmt1->bindParam(':fuuid', $fuuid);
	$stmt1->bindParam(':mpuuid', $mpuuid);
	$stmt1->bindParam(':qname', $queueName);
	$stmt1->execute();

	if($stmt1->rowCount() == 0) 
	{
		//--create/insert into tbl
		$sql="insert into jobFileMpQueueMappingTBL ";
		$sql=$sql."(jobUUID, fUUID, mpUUID, QueueName) "; 
		$sql=$sql."values "; 
		$sql=$sql."(:juuid, :fuuid, :mpuuid, :qname)"; 

		$stmt1 = $pdo->prepare($sql);
		$stmt1->bindParam(':juuid', $jobUUID);
		$stmt1->bindParam(':fuuid', $fuuid);
		$stmt1->bindParam(':mpuuid', $mpuuid);
		$stmt1->bindParam(':qname', $queueName);
		$stmt1->execute();
	}
	else
	{
		//--some type of error..
		//--should never!! get here...
		//--attributes 'uuidgen' used to generate the vals should always be unique!
	}


	//$z= $clientP->doBackground("CrawlParseProcessFunction", rawurlencode(json_encode($qd)));
	$clientP = new GearmanClient();
	$a = $clientP->addServer($host, $port);
	$z = $clientP->doBackground($funcName, rawurlencode(json_encode($qd)));
	$clientP="";


	/*
		successful queue insertion, delete the existing file
			$prevRename,$prevQueueDir,$prevUUID
	*/

	$fname=$prevQueueDir."/".$prevRename.trim($prevUUID).".dat";
	if((strlen($prevQueueDir)>0) && (strlen($fname)>0))
	{
		$dcmd="/bin/rm -f ".$fname;
		$dcmd=system($dcmd);
	}

	//$z= $clientP->doBackground("CrawlParseProcessFunction", $data);
	//echo $z;

	print "\n denny!! -YoloClientSpawnParseApp \n";

#$a=$clientP->addServer($gServer_host, $gParseProcessworker_port);
	print "aa ".$host."\n";
	print "bb ".$port."\n";
//exit();

}





/*
functon gcSetup($host,$port,$funcName,$odata)
			$clientP = new GearmanClient();
			global $gParseCourseSectionWorker_port;
			$a = $clientP->addServer($gServer_host, $gParseCourseSectionWorker_port);
			// TODO: handle gearman failures, etc
			$clientFunc = "CrawlCourseSectionFunction";
			$output_data = rawurlencode(json_encode($dataCpy));
			$z = $clientP->doBackground($clientFunc, $output_data);
*/

/*
	functionName:	gcSetup_groupinsert
	goal:			used to setup the "abstracted" queue insertion process, as well as 
					 handle the potential jobSchedule action process

	usage:			called by the parentApp, uses data from the queueFunction of the 
					parent

	inputDefs:
		$host:		gQueue Server IP/Address
		$port:		gQueue Port Address
		$funcName:	queue function name of the "intended" queue process
		$odata:		data for the queueFile, mapped to the internal fpuuid
					 in the qdArray
		$jobUUID:	jobuuid used to schedule/map/track the job action, used to 
					 determine if the data is written/inserted in the queue, or 
					 if the "data" is placed in a separate tbl, while the 
					 jobActionCron process handles managing the data based 
					 on the crawl/job action requirements
					 from the "dataPacket"
		$fuuid:		the uuid to generate the file, for the assocated data to 
					 process the data
		$mpuuid:	masterProcUUID from the "dataPacket"
		$qdir:		the associated queueDir for the queueProcess, different for 
					 each queue, all the associated queue files are stored in 
					 the dir
		$fprename:	the associated "pre name" for the queueProcess file
		$queueName:	the queueName, for the jobQueueActionTBL (wait/stop/delete/..)
		$prevRename:	rename/prefix of the current/prev queue data file
		$prevQueueDir:	dir of the current/prev queue data file
		$prevUUID:	uuid of the previous file/data
					used to generate the file of data used for the currentQueue, 
					 this file is deleted once the new/next queue is inserted/created
*/
function gcSetup_groupinsert($pdo,$host,$port,$funcName,$odata,$jobUUID,$mpuuid,$qdir,
		$fprename,$queueName,$prevRename,$prevQueueDir,$prevUUID,$sqlArray)
{


	$fUUID=uuidgen();

	//--for the queue insertion/data
	$d=array();
	$qd=array();
	$qd['jobUUID'] = $jobUUID;
	$qd['fnameUUID']=$fUUID;
	$qd['mpuuid']=$mpuuid;



	//$qdir=parseProcessTmpDir;	//where the file for the queue is located
								//keep it in a dir mapped to the queue

	//$fname=$qdir."/"."parsereturn_".trim($fuuid).".dat";
	$fname=$qdir."/".$fprename.trim($fUUID).".dat";

	$f1=fopen($fname,"wr+");
	if(!$f1)
		die("file open err");
	fwrite($f1, $odata);
	fclose($f1);


	$sql="select * from jobFileMpQueueMappingTBL ";
	$sql=$sql."where "; 
	$sql=$sql."jobUUID=:juuid and "; 
	$sql=$sql."fUUID=:fuuid and "; 
	$sql=$sql."mpUUID=:mpuuid and ";
	$sql=$sql."QueueName=:qname";

	$stmt1 = $pdo->prepare($sql);
	$stmt1->bindParam(':juuid', $jobUUID);
	$stmt1->bindParam(':fuuid', $fuuid);
	$stmt1->bindParam(':mpuuid', $mpuuid);
	$stmt1->bindParam(':qname', $queueName);
	$stmt1->execute();

	if($stmt1->rowCount() == 0) 
	{
		//--create/insert into tbl
		$sql="insert into jobFileMpQueueMappingTBL ";
		$sql=$sql."(jobUUID, fUUID, mpUUID, QueueName) "; 
		$sql=$sql."values "; 
		$sql=$sql."(:juuid, :fuuid, :mpuuid, :qname)"; 

		$stmt1 = $pdo->prepare($sql);
		$stmt1->bindParam(':juuid', $jobUUID);
		$stmt1->bindParam(':fuuid', $fuuid);
		$stmt1->bindParam(':mpuuid', $mpuuid);
		$stmt1->bindParam(':qname', $queueName);
		$stmt1->execute();
	}
	else
	{
		//--some type of error..
		//--should never!! get here...
		//--attributes 'uuidgen' used to generate the vals should always be unique!
	}


	//$z= $clientP->doBackground("CrawlParseProcessFunction", rawurlencode(json_encode($qd)));
	$clientP = new GearmanClient();
	$a = $clientP->addServer($host, $port);
	$z = $clientP->doBackground($funcName, rawurlencode(json_encode($qd)));
	$clientP="";


	/*
		successful queue insertion, delete the existing file
			$prevRename,$prevQueueDir,$prevUUID
	*/

	$fname=$prevQueueDir."/".$prevRename.trim($prevUUID).".dat";
	if((strlen($prevQueueDir)>0) && (strlen($fname)>0))
	{
		$dcmd="/bin/rm -f ".$fname;
		$dcmd=system($dcmd);
	}

	//$z= $clientP->doBackground("CrawlParseProcessFunction", $data);
	//echo $z;

	print "\n denny!! -YoloClientSpawnParseApp \n";

#$a=$clientP->addServer($gServer_host, $gParseProcessworker_port);
	print "aa ".$host."\n";
	print "bb ".$port."\n";
//exit();


}




/*
functon gcSetup($host,$port,$funcName,$odata)
			$clientP = new GearmanClient();
			global $gParseCourseSectionWorker_port;
			$a = $clientP->addServer($gServer_host, $gParseCourseSectionWorker_port);
			// TODO: handle gearman failures, etc
			$clientFunc = "CrawlCourseSectionFunction";
			$output_data = rawurlencode(json_encode($dataCpy));
			$z = $clientP->doBackground($clientFunc, $output_data);
*/

/*
	functionName:	gcSetup_groupinsert
	goal:			used to setup the "abstracted" queue insertion process, as well as 
					 handle the potential jobSchedule action process

	usage:			called by the parentApp, uses data from the queueFunction of the 
					parent

	inputDefs:
		$host:		gQueue Server IP/Address
		$port:		gQueue Port Address
		$funcName:	queue function name of the "intended" queue process
		$odata:		data for the queueFile, mapped to the internal fpuuid
					 in the qdArray
		$jobUUID:	jobuuid used to schedule/map/track the job action, used to 
					 determine if the data is written/inserted in the queue, or 
					 if the "data" is placed in a separate tbl, while the 
					 jobActionCron process handles managing the data based 
					 on the crawl/job action requirements
					 from the "dataPacket"
		$fuuid:		the uuid to generate the file, for the assocated data to 
					 process the data
		$mpuuid:	masterProcUUID from the "dataPacket"
		$qdir:		the associated queueDir for the queueProcess, different for 
					 each queue, all the associated queue files are stored in 
					 the dir
		$fprename:	the associated "pre name" for the queueProcess file
		$queueName:	the queueName, for the jobQueueActionTBL (wait/stop/delete/..)
		$prevRename:	rename/prefix of the current/prev queue data file
		$prevQueueDir:	dir of the current/prev queue data file
		$prevUUID:	uuid of the previous file/data
					used to generate the file of data used for the currentQueue, 
					 this file is deleted once the new/next queue is inserted/created
*/
function gcSetupA($pdo,$host,$port,$funcName,$odata,$jobUUID,$mpuuid,$qdir,
		$fprename,$queueName,$prevRename,$prevQueueDir,$prevUUID)
{


	$fUUID=uuidgen();

	//--for the queue insertion/data
	$d=array();
	$qd=array();
	$qd['jobUUID'] = $jobUUID;
	$qd['fnameUUID']=$fUUID;
	$qd['mpuuid']=$mpuuid;



	//$qdir=parseProcessTmpDir;	//where the file for the queue is located
								//keep it in a dir mapped to the queue

	//$fname=$qdir."/"."parsereturn_".trim($fuuid).".dat";
	$fname=$qdir."/".$fprename.trim($fUUID).".dat";

	$f1=fopen($fname,"wr+");
	if(!$f1)
		die("file open err");
	fwrite($f1, $odata);
	fclose($f1);


	$sql="select * from jobFileMpQueueMappingTBL ";
	$sql=$sql."where "; 
	$sql=$sql."jobUUID=:juuid and "; 
	$sql=$sql."fUUID=:fuuid and "; 
	$sql=$sql."mpUUID=:mpuuid and ";
	$sql=$sql."QueueName=:qname";

	$stmt1 = $pdo->prepare($sql);
	$stmt1->bindParam(':juuid', $jobUUID);
	$stmt1->bindParam(':fuuid', $fUUID);
	$stmt1->bindParam(':mpuuid', $mpuuid);
	$stmt1->bindParam(':qname', $queueName);
	$stmt1->execute();

	if($stmt1->rowCount() == 0) 
	{
		//--create/insert into tbl
		$sql="insert into jobFileMpQueueMappingTBL ";
		$sql=$sql."(jobUUID, fUUID, mpUUID, QueueName) "; 
		$sql=$sql."values "; 
		$sql=$sql."(:juuid, :fuuid, :mpuuid, :qname)"; 

		$stmt1 = $pdo->prepare($sql);
		$stmt1->bindParam(':juuid', $jobUUID);
		$stmt1->bindParam(':fuuid', $fUUID);
		$stmt1->bindParam(':mpuuid', $mpuuid);
		$stmt1->bindParam(':qname', $queueName);
		$stmt1->execute();

		print $sql."\n";
		print "juuid ".$jobUUID."\n";
                print "fuuid ".$fUUID."\n";
                print "mpuuid ".$mpuuid."\n";
                print "qname ".$queueName."\n";

	}
	else
	{
		//--some type of error..
		//--should never!! get here...
		//--attributes 'uuidgen' used to generate the vals should always be unique!
	}


	//$z= $clientP->doBackground("CrawlParseProcessFunction", rawurlencode(json_encode($qd)));
	$clientP = new GearmanClient();
	$a = $clientP->addServer($host, $port);
	$z = $clientP->doBackground($funcName, rawurlencode(json_encode($qd)));
	$clientP="";


	/*
		successful queue insertion, delete the existing file
			$prevRename,$prevQueueDir,$prevUUID
	*/

	$fname=$prevQueueDir."/".$prevRename.trim($prevUUID).".dat";
	if((strlen($prevQueueDir)>0) && (strlen($fname)>0))
	{
		$dcmd="/bin/rm -f ".$fname;
		$dcmd=system($dcmd);
	}

	//$z= $clientP->doBackground("CrawlParseProcessFunction", $data);
	//echo $z;

	print "\n denny!! -YoloClientSpawnParseApp \n";

#$a=$clientP->addServer($gServer_host, $gParseProcessworker_port);
	print "aa ".$host."\n";
	print "bb ".$port."\n";
//exit();


}



?>


