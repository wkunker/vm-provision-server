<?php
/*
 * Helper used to insert a new record into the fetch queue and
 *   corresponding database tables. This is typically used by
 *   the 'last' pieces of the crawler to 'reinsert' the next levels
 *   back into the crawl process.
 *
 *	Update:
 *	apr 24/2014 - bdouglas 
 *	changed the reinsert to not mod the uuid... left the mpiid alone,
 *	 set it to the masterparseuuid of the $data[]
 *
 *
 *	May 21/2014
 *	bdouglas - setup the dbg process to only display dbg statements during 
 *	 the setting of the sysEnvVar - "displayCrawlDebug"
 *
 *	May 27/2014
 *	bdouglas - created passthru func - to be used to passthru a rec, without resetting the 
 *	parseReturn Status
 *
 *
 *	Jul9/14
 *	bdouglas - updated to copy -> reinsert -> reinsert_fqueue
 *	reinsert_fqueue uses the updated queue/file stuff...
 *	-still writes to the fetchQueue..
 *
 *
 *
 * ReinsertHelper.php
 *
 */

//require_once('/yolo-libs/UUIDHelper.php');

class InsertionHelper {
	private $pdo = null;
	private $clientP = null;

	private $displayDebug = null;


	public function __construct($pdo, $gServer_host, $gFetchworker_port) {
		$this->_setup($pdo,$gServer_host, $gFetchworker_port);
	}

	private function _setup($pdo,$gServer_host, $gFetchworker_port) {
		ini_set('display_errors', 'On');
		ini_set('error_reporting', E_ALL);

		/*
			update - apr 28/2014 -bdouglas
			modified to pass the pdo from the 
			calling parent
		//--setup for db access

		/*
			use parameterized/queries
		*/

		$this->pdo = $pdo;
		$this->displayDebug=getenv('displayCrawlMasterDebug');

		/*
			Setup the initial connection to the master Gearman-Parse Daemon
			This matches the the Gearman-Parse Daemon host/port as defined 
			 in the "gtest.sh" script

			The client class/method is then used to connect with the queue 
			 to place the data on the queue for the distributed worker app/
			 function to process the data/queue by the distributed 
			 crawler parse/fetch edge server
		*/
		$this->clientP = new GearmanClient();
		$this->clientP->addServer($gServer_host, $gFetchworker_port);
	}

	private function _checkJobScheduleUUID($jsuid) {
		$sql="select * from JobTBL where JobUUID=:jsuid";

		if($this->displayDebug==1)
		{
			print "sql11 aa = ".$sql."\n";
		}
		//		$result = mysql_query($sql);
		//		$count=mysql_num_rows($result);

		$stmt = $this->pdo->prepare($sql);
		$stmt->bindParam(':jsuid', $jsuid);
		//$stmt->bindParam(':jid', $jsuid, PDO::PARAM_STR);
		$stmt->execute();

		if($stmt->rowCount()==0)
		{
			throw new Exception("jobScheduleUUID does not exist.");
		}
	}

	private function _checkBatchTBL($bid) {
		/*
			simulate inserting/setting the data/row for the BatchTBL
		*/
		$sql="select * from BatchTBL ";
		$sql=$sql." where ";
		$sql=$sql." BatchUUID=:bid";

		$stmt = $this->pdo->prepare($sql);
		//$stmt->bindParam(':jsuid', $jsuid);
		$stmt->bindParam(':bid', $bid);
		$stmt->execute();

		if($stmt->rowCount()==0)
		{
			throw new Exception("batchUUID does not exist.");
		}
	}

	private function _checkJobBatchTBL($jsuid, $bid) {
		/*
		simulate inserting/setting the data/row for the JobTBL
		*/
		$sql="select * from JobBatchTBL ";
		$sql=$sql." where ";
		$sql=$sql." JobUUID=:jsuid and BatchUUID=:bid";

		$stmt = $this->pdo->prepare($sql);
		$stmt->bindParam(':jsuid', $jsuid);
		$stmt->bindParam(':bid', $bid);
		$stmt->execute();

		//$result = mysql_query($sql);
		//$count=mysql_num_rows($result);
		if($stmt->rowCount()==0)
		{
			throw new Exception("JobBatchTBLUUID does not exist.");
		}
	}

	private function _getJobType($jsuid) {
		//--get the urltype/collegetype
		$sql1="select * from JobTBL ";
		$sql1=$sql1." where ";
		$sql1=$sql1." JobUUID=:jsuid";

		$stmt = $this->pdo->prepare($sql1);
		$stmt->bindParam(':jsuid', $jsuid);
		$stmt->execute();

		$row = $stmt->fetch();
		//$result = mysql_query($sql1);
		//$row = mysql_fetch_array($result, MYSQL_BOTH);
		$type=trim($row['Type']);

		return $type;
	}

	private function _getTermVal($jsuid) {
		$sql="select Semester from JobTBL where JobUUID=:jsuid";

		$stmt = $this->pdo->prepare($sql);
		$stmt->bindParam(':jsuid', $jsuid);
		$stmt->execute();

		//$result = mysql_query($sql);
		if($stmt->rowCount() == 0)
		{
			throw new Exception("TermVal/Semester does not exist.");
		}

		$row = $stmt->fetch();

		$TermVal=$row['Semester'];
		$TermVal=trim($TermVal);

		return $TermVal;
	}







	/*
	 * Rebuild and reinsert a packet for a currently running job/batch.
	 */
	public function reinsert(
		/*$urlType, $jobScheduleUUID, $collegeID, $termVal, $batchUUID, $branch, $cdate, $cbType, 
			$ParentLevel, $ChildLevel, $burl, $rtmp, $fetchParseOperationFlg, $statDynFlg, $mapi, $mpiid,
			$retFetchFileName, $Status, $MacAddress, $time, $bucket, $parseContentFileName, $parseFileName,
			$level, $fetchFileType, $fetchFileName, $deptVal = '', $classVal = '', $sectionVal = ''*/
		$data
		) {


		// Convert the associative array to the
		//   variables that were already written...
		// This is just a hack to save some time,
		//   but it works.
		#foreach($arr as $key=>$val) {
		#	${$key} = $val;
		#}


		$urlType = array_key_exists('urlType', $data) ? $data['urlType'] : '';
		$jobScheduleUUID = array_key_exists('jobScheduleUUID', $data) ? $data['jobScheduleUUID'] : '';
		$collegeID = array_key_exists('CollegeID', $data) ? $data['CollegeID'] : '';
		$termVal = array_key_exists('TermVal', $data) ? $data['TermVal'] : '';

		$type = ''; #!!!!!

		$batchUUID = array_key_exists('batchID', $data) ? $data['batchID'] : '';
		$branch = array_key_exists('branch', $data) ? $data['branch'] : '';
		#$branch = $data['branch'] . '_' . $data['masterParseInputUUID'];

		$fpdat = ''; #!!!!!

		$cdate = array_key_exists('jobCrawlDate', $data) ? $data['jobCrawlDate'] : '';
		$cbType = array_key_exists('collegebookType', $data) ? $data['collegebookType'] : '';

		$pid = ''; #!!!!!

		$ParentLevel = array_key_exists('ParentLevel', $data) ? $data['ParentLevel'] : '';

		$ChildLevel = array_key_exists('ChildLevel', $data) ? $data['ChildLevel'] : '';

		$burl = array_key_exists('baseURL', $data) ? $data['baseURL'] : '';

		$rtmp = 1; #!!!!!!

		$fetchParseOperationFlg = array_key_exists('fetchParseOperationFlg', $data) ? $data['fetchParseOperationFlg'] : '';

		$statDynFlg = array_key_exists('statDynFlg', $data) ? $data['statDynFlg'] : '';
		$mapi = array_key_exists('mapid', $data) ? $data['mapid'] : '';
		$mapi = '111'; // NOTICE: field is deprecated.

		$retFetchFileName = array_key_exists('fetchFileName', $data['FetchParseUrlPacket']['fetchinput']) ? $data['FetchParseUrlPacket']['fetchinput']['fetchFileName'] : '';
		$Status = array_key_exists('Status', $data['FetchParseUrlPacket']['returnParseData']) ? $data['FetchParseUrlPacket']['returnParseData']['Status'] : '';
		$MacAddress = array_key_exists('macAddress', $data['FetchParseUrlPacket']['returnParseData']) ? $data['FetchParseUrlPacket']['returnParseData']['macAddress'] : '';
		$time = array_key_exists('time', $data) ? $data['time'] : '';
		$bucket = array_key_exists('fetchFileType', $data['FetchParseUrlPacket']['parseinput']) ? $data['FetchParseUrlPacket']['parseinput']['fetchFileType'] : '';
		$parseContentFileName = array_key_exists('parseContentFileName', $data['FetchParseUrlPacket']['parseinput']) ? $data['FetchParseUrlPacket']['parseinput']['parseContentFileName'] : '';


		$parseFileName = array_key_exists('parseFileName', $data['FetchParseUrlPacket']['parseinput']) ? $data['FetchParseUrlPacket']['parseinput']['parseFileName'] : '';

		$writeData = intval((bool)$data['WriteData']);
		$level = $ChildLevel;
		$fetchFileType = $bucket;
		$fetchFileName = $retFetchFileName;

		// masterParseInputUUID is not changed now.. it's changed in the 
		// parseProcess routine
		$mpiid=array_key_exists('masterParseInputUUID', $data) ? $data['masterParseInputUUID'] : '';
		$mpiid=uuidgen(); // denny???? why wtf! masterParseInputUUID is a new ID--since this is inserting the fetch for the next level.

		$parentParseTBL_name = $data['parentParseTBL_name'];
		$parentParseTBL_uuid = $data['parentParseTBL_uuid'];
		$childtbl = $data['childtbl'];


		$jsuid = $jobScheduleUUID;
		$TermVal = $termVal;
		$bid = $batchUUID;

		$this->_checkJobScheduleUUID($jsuid);
		$type = $this->_getJobType($jsuid);
		$this->_checkBatchTBL($bid);
		$this->_checkJobBatchTBL($jsuid, $bid);
		$this->_getTermVal($jsuid);

		#$fetchParseUrlPacket = array();
		
		#$fin = array();
		#$rdat = array();
		$fetchParseUrlPacket = $data['FetchParseUrlPacket'];
		$fin = $fetchParseUrlPacket['fetchinput'];
		$rdat = $fetchParseUrlPacket['returnFetchData'];

		$rdat['FetchFileName']=$retFetchFileName;
		$rdat['Status']=$Status;
		$rdat['MacAddress']=$MacAddress;
		$rdat['timestamp']=$time;

		#$pin=array();
		$pin = $fetchParseUrlPacket['parseinput'];
		$pin['fetchFileType']=$bucket;	//bucket
		$pin['parseContentFileName']=$parseContentFileName;		//fetched content
		$pin['parseFileName']=$parseFileName;		//parse app
		$pin['level'] = $pin['level'] ? $pin['level'] : $level;

		#$pret=array();
		$pret = $fetchParseUrlPacket['returnParseData'];
		$pret['Status']='fail'; // Status defaults to fail
		$pret['recCount'] = $pret['recCount'] ? $pret['recCount'] : 0;
		$pret['data'] = $pret['data'] ? $pret['data'] : '';
		$pret['nextLevel'] = $pret['nextLevel'] ? $pret['nextLevel'] : '';
		$pret['timestamp'] = $pret['timestamp'] ? $pret['timestamp'] : '';


		$macAddr = system("ifconfig eth0 | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}' | sed 's/://g'");
		$pret['macAddress']=$macAddr; # TODO: Change me! This should be set by the client.

		$fin['currentURL']=$burl;
		$fin['fetchFileType']=$fetchFileType;
		$fin['fetchFileName']=$fetchFileName;

		$fin['requestType']=$type; 
		$fin['cookie'] = $fin['cookie'] ? $fin['cookie'] : '';
		$fin['referer']= $fin['referer'] ? $fin['referer'] : ''; 
		$fin['queryStr']= $fin['queryStr'] ? $fin['queryStr'] : '';

		/*
			update-apr 28/2014 -bdouglas
			changed to allow this to always happen here,
			right before the data is placed in the Queue 
			as opposed to on the clientside..
		*/
		$fin['level']=$data['ChildLevel'];
		$fin['termVal']=$data['TermVal'];
	

		$user_agent = "Mozilla\/4.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident\/4.0; .NET CLR 2.0.50727; .NET CLR 1.1.4322; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; OfficeLiveConnector.1.4; OfficeLivePatch.1.3; .NET4.0C; .NE";
		$fin['user_agent'] = $fin['user_agent'] ? $fin['user_agent'] : $user_agent;

		$fetchParseUrlPacket['masterlevel']=$ChildLevel;
		$fetchParseUrlPacket['returnStatus']="fail"; # Defaults to fail.
		$fetchParseUrlPacket['fetchinput']=$fin;
		$fetchParseUrlPacket['returnFetchData']=$rdat;
		$fetchParseUrlPacket['parseinput']=$pin;
		$fetchParseUrlPacket['returnParseData']=$pret;

		if($this->displayDebug==1)
		{
			print "inside Reinsert!!! - let's see the fin packet \n";
			var_dump($fetchParseUrlPacket);
		}

		$fetchParseUrlPacket_json=rawurlencode(json_encode($fetchParseUrlPacket));


		$sql1="insert into MasterFetchRequestTBL ";
		$sql1=$sql1." (jobCrawlDate,collegebookType,TermVal,jobScheduleUUID,batchID,CollegeID,";
		$sql1=$sql1." FetchParseUrlPacket,";
		$sql1=$sql1." urlType,ParentLevel,ChildLevel,";
		$sql1=$sql1." time,baseURL,parentParseTBL_name, ";
		$sql1=$sql1." parentParseTBL_uuid, daytbl, booktbl, ";
		$sql1=$sql1." runStatFlg, fetchParseOperationFlg, statDynFlg, ";
		$sql1=$sql1." WriteData, mapid, branch, masterParseInputUUID) ";
		$sql1=$sql1." values ";
		$sql1=$sql1." (:crawlDate,:cbType,:TermVal, :jsuid, :bid, :collegeID, ";
		$sql1=$sql1." :fetchParseUrlPacket, :type, :ParentLevel, ";
		$sql1=$sql1." :ChildLevel, :time, :burl, ";
		$sql1=$sql1." :pTblName, :pTblUUID, :daytbl, :booktbl,";
		$sql1=$sql1." :rtmp, :fpFlg, :statFlg, :writeData, :mapi, :branch, :mpiid)";

		$stmt2 = $this->pdo->prepare($sql1);
		$stmt2->bindParam(':crawlDate', $cdate);
		$stmt2->bindParam(':cbType', $cbType);
		$stmt2->bindParam(':TermVal', $TermVal);
		$stmt2->bindParam(':jsuid', $jsuid);
		$stmt2->bindParam(':bid', $bid);
		$stmt2->bindParam(':collegeID', $collegeID);
		$stmt2->bindParam(':fetchParseUrlPacket', $fetchParseUrlPacket_json);
		$stmt2->bindParam(':type', $type);
		$stmt2->bindParam(':ParentLevel', $ParentLevel);
		$stmt2->bindParam(':ChildLevel', $ChildLevel);
		$stmt2->bindParam(':time', $time);
		$stmt2->bindParam(':burl', $burl);

		if($this->displayDebug==1)
		{
			print "\ncrawlDate:" . $cdate;
			print "\ncbType:" . $cbType;
			print "\nTermVal:" . $TermVal;
			print "\njsuid:" . $jsuid;
			print "\nbid:" . $bid;
			print "\ncollegeID:" . $collegeID;
			print "\nfetchParseUrlPacket:" . $fetchParseUrlPacket_json;
			print "\ntype:" . $type;
			print "\nParentLevel:" . $ParentLevel;
			print "\nChildLevel:" . $ChildLevel;
			print "\ntime:" . $time;
			print "\nburl:" . $burl . "\n";
		}

		$t=" ";	//simply to setup...
		$stmt2->bindParam(':pTblName',$t);
		$stmt2->bindParam(':pTblUUID', $t);
		$stmt2->bindParam(':daytbl', $t);
		$stmt2->bindParam(':booktbl', $t);

		if($this->displayDebug==1)
		{
			print "\npTblName,pTblUUID,daytbl,booktbl:".$t;
		}

		$stmt2->bindParam(':rtmp', $rtmp);
		$stmt2->bindParam(':fpFlg', $fetchParseOperationFlg);
		$stmt2->bindParam(':statFlg', $statDynFlg);
		$stmt2->bindParam(':writeData', $writeData);
		$stmt2->bindParam(':mapi', $mapi);
		$stmt2->bindParam(':branch', $branch);
		$stmt2->bindParam(':mpiid', $mpiid);

		if($this->displayDebug==1)
		{
			print "\nrtmp:".$rtmp;
			print "\nfpFlg:".$fetchParseOperationFlg;
			print "\nstatFlg:".$statDynFlg;
			print "\nwriteData:".$writeData;
			print "\nmapi:".$mapi;
			print "\nbranch:".$branch;
			print "\nmpiid:".$mpiid."\n";
		}
		$stmt2->execute();
		var_dump($stmt2->errorInfo());
                var_dump($stmt2->errorCode());
                print "reinsert vvvv111 after any errs...\n";


#		$stmt2->debugDumpParams(); exit();

		if($stmt2->rowCount() == 0) {
			throw new Exception("Failed to write to MasterFetchRequestTBL");
		}

		$fetch_packet = $data; // Comment this line out to 'whitelist' data that gets reinserted.

		// Whitelist -- only applies if $fetch_packet = $data` line is commented out.
		$fetch_packet['jobCrawlDate']=$cdate;
		$fetch_packet['collegebookType']=$cbType;
		$fetch_packet['TermVal']=$TermVal;
		$fetch_packet['jobScheduleUUID']=$jsuid;
		$fetch_packet['batchID']=$bid;
		$fetch_packet['CollegeID']=$collegeID;
		$fetch_packet['FetchParseUrlPacket']=$fetchParseUrlPacket_json;
		$fetch_packet['urlType']=$type;
		$fetch_packet['ParentLevel']=$ParentLevel;
		$fetch_packet['ChildLevel']=$ChildLevel;
		$fetch_packet['time']=$time;
		$fetch_packet['parentParseTBL_name']=$parentParseTBL_name;
		$fetch_packet['parentParseTBL_uuid']=$parentParseTBL_uuid;
		$fetch_packet['childtbl']=$childtbl;
		$fetch_packet['daytbl']=" ";
		$fetch_packet['booktbl']=" ";
		$fetch_packet['baseURL']=$burl;
		//$fetch_packet['fetchInput']=$fi;
		$fetch_packet['runStatFlg']=$rtmp;
		$fetch_packet['fetchParseOperationFlg']=$fetchParseOperationFlg;
		$fetch_packet['statDynFlg']=$statDynFlg;
		$fetch_packet['WriteData']=$writeData;
		$fetch_packet['mapid']=$mapi;
		$fetch_packet['branch']=$branch;
		$fetch_packet['masterParseInputUUID']=$mpiid;

		//$fetch_packet['catalogNum']='';
		//$fetch_packet['bterm']='';
		//$fetch_packet['courseID']='';

		if($this->displayDebug==1)
		{
			print "still in Reinsert.. \n";
			var_dump(json_decode(rawurldecode($fetchParseUrlPacket_json),true));
			print "abov tested fin \n";
		}

		$dat1=rawurlencode(json_encode($fetch_packet));


		# TODO: Need to roll back the table inserts here if the Gearman insertion failed.

		$z= $this->clientP->doBackground("CrawlFetchFunction", $dat1);
		print "rcode = ".$this->clientP->returnCode()."\n\n";

		return $this->clientP->returnCode();
	}


	/*
	 * Rebuild and reinsert/passthru a packet for a currently running job/batch.
		-just like the reinsert - except doesn't reset the parseReturnData['Status']

	 */
	public function passthru(
		/*$urlType, $jobScheduleUUID, $collegeID, $termVal, $batchUUID, $branch, $cdate, $cbType, 
			$ParentLevel, $ChildLevel, $burl, $rtmp, $fetchParseOperationFlg, $statDynFlg, $mapi, $mpiid,
			$retFetchFileName, $Status, $MacAddress, $time, $bucket, $parseContentFileName, $parseFileName,
			$level, $fetchFileType, $fetchFileName, $deptVal = '', $classVal = '', $sectionVal = ''*/
		$data
		) {


		// Convert the associative array to the
		//   variables that were already written...
		// This is just a hack to save some time,
		//   but it works.
		#foreach($arr as $key=>$val) {
		#	${$key} = $val;
		#}


		$urlType = array_key_exists('urlType', $data) ? $data['urlType'] : '';
		$jobScheduleUUID = array_key_exists('jobScheduleUUID', $data) ? $data['jobScheduleUUID'] : '';
		$collegeID = array_key_exists('CollegeID', $data) ? $data['CollegeID'] : '';
		$termVal = array_key_exists('TermVal', $data) ? $data['TermVal'] : '';

		$type = ''; #!!!!!

		$batchUUID = array_key_exists('batchID', $data) ? $data['batchID'] : '';
		$branch = array_key_exists('branch', $data) ? $data['branch'] : '';
		#$branch = $data['branch'] . '_' . $data['masterParseInputUUID'];

		$fpdat = ''; #!!!!!

		$cdate = array_key_exists('jobCrawlDate', $data) ? $data['jobCrawlDate'] : '';
		$cbType = array_key_exists('collegebookType', $data) ? $data['collegebookType'] : '';

		$pid = ''; #!!!!!

		$ParentLevel = array_key_exists('ParentLevel', $data) ? $data['ParentLevel'] : '';

		$ChildLevel = array_key_exists('ChildLevel', $data) ? $data['ChildLevel'] : '';

		$burl = array_key_exists('baseURL', $data) ? $data['baseURL'] : '';

		$rtmp = 1; #!!!!!!

		$fetchParseOperationFlg = array_key_exists('fetchParseOperationFlg', $data) ? $data['fetchParseOperationFlg'] : '';

		$statDynFlg = array_key_exists('statDynFlg', $data) ? $data['statDynFlg'] : '';
		$mapi = array_key_exists('mapid', $data) ? $data['mapid'] : '';
		$mapi = '111'; // NOTICE: field is deprecated.

		$retFetchFileName = array_key_exists('fetchFileName', $data['FetchParseUrlPacket']['fetchinput']) ? $data['FetchParseUrlPacket']['fetchinput']['fetchFileName'] : '';
		$Status = array_key_exists('Status', $data['FetchParseUrlPacket']['returnParseData']) ? $data['FetchParseUrlPacket']['returnParseData']['Status'] : '';
		$MacAddress = array_key_exists('macAddress', $data['FetchParseUrlPacket']['returnParseData']) ? $data['FetchParseUrlPacket']['returnParseData']['macAddress'] : '';
		$time = array_key_exists('time', $data) ? $data['time'] : '';
		$bucket = array_key_exists('fetchFileType', $data['FetchParseUrlPacket']['parseinput']) ? $data['FetchParseUrlPacket']['parseinput']['fetchFileType'] : '';
		$parseContentFileName = array_key_exists('parseContentFileName', $data['FetchParseUrlPacket']['parseinput']) ? $data['FetchParseUrlPacket']['parseinput']['parseContentFileName'] : '';


		$parseFileName = array_key_exists('parseFileName', $data['FetchParseUrlPacket']['parseinput']) ? $data['FetchParseUrlPacket']['parseinput']['parseFileName'] : '';

		$writeData = intval((bool)$data['WriteData']);
		$level = $ChildLevel;
		$fetchFileType = $bucket;
		$fetchFileName = $retFetchFileName;

		// masterParseInputUUID is not changed now.. it's changed in the 
		// parseProcess routine
		$mpiid=array_key_exists('masterParseInputUUID', $data) ? $data['masterParseInputUUID'] : '';
		//$mpiid=uuidgen(); // masterParseInputUUID is a new ID--since this is inserting the fetch for the next level.

		$parentParseTBL_name = $data['parentParseTBL_name'];
		$parentParseTBL_uuid = $data['parentParseTBL_uuid'];
		$childtbl = $data['childtbl'];


		$jsuid = $jobScheduleUUID;
		$TermVal = $termVal;
		$bid = $batchUUID;

		$this->_checkJobScheduleUUID($jsuid);
		$type = $this->_getJobType($jsuid);
		$this->_checkBatchTBL($bid);
		$this->_checkJobBatchTBL($jsuid, $bid);
		$this->_getTermVal($jsuid);

		#$fetchParseUrlPacket = array();
		
		#$fin = array();
		#$rdat = array();
		$fetchParseUrlPacket = $data['FetchParseUrlPacket'];
		$fin = $fetchParseUrlPacket['fetchinput'];
		$rdat = $fetchParseUrlPacket['returnFetchData'];

		$rdat['FetchFileName']=$retFetchFileName;
		$rdat['Status']=$Status;
		$rdat['MacAddress']=$MacAddress;
		$rdat['timestamp']=$time;

		#$pin=array();
		$pin = $fetchParseUrlPacket['parseinput'];
		$pin['fetchFileType']=$bucket;	//bucket
		$pin['parseContentFileName']=$parseContentFileName;		//fetched content
		$pin['parseFileName']=$parseFileName;		//parse app
		$pin['level'] = $pin['level'] ? $pin['level'] : $level;

		#$pret=array();
		$pret = $fetchParseUrlPacket['returnParseData'];
		//$pret['Status']='fail'; // Status defaults to fail
		$pret['recCount'] = $pret['recCount'] ? $pret['recCount'] : 0;
		$pret['data'] = $pret['data'] ? $pret['data'] : '';
		$pret['nextLevel'] = $pret['nextLevel'] ? $pret['nextLevel'] : '';
		$pret['timestamp'] = $pret['timestamp'] ? $pret['timestamp'] : '';


		$macAddr = system("ifconfig eth0 | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}' | sed 's/://g'");
		$pret['macAddress']=$macAddr; # TODO: Change me! This should be set by the client.

		$fin['currentURL']=$burl;
		$fin['fetchFileType']=$fetchFileType;
		$fin['fetchFileName']=$fetchFileName;

		$fin['requestType']=$type; 
		$fin['cookie'] = $fin['cookie'] ? $fin['cookie'] : '';
		$fin['referer']= $fin['referer'] ? $fin['referer'] : ''; 
		$fin['queryStr']= $fin['queryStr'] ? $fin['queryStr'] : '';

		/*
			update-apr 28/2014 -bdouglas
			changed to allow this to always happen here,
			right before the data is placed in the Queue 
			as opposed to on the clientside..
		*/
		$fin['level']=$data['ChildLevel'];
		$fin['termVal']=$data['TermVal'];
	

		$user_agent = "Mozilla\/4.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident\/4.0; .NET CLR 2.0.50727; .NET CLR 1.1.4322; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; OfficeLiveConnector.1.4; OfficeLivePatch.1.3; .NET4.0C; .NE";
		$fin['user_agent'] = $fin['user_agent'] ? $fin['user_agent'] : $user_agent;

		$fetchParseUrlPacket['masterlevel']=$ChildLevel;
		$fetchParseUrlPacket['returnStatus']="fail"; # Defaults to fail.
		$fetchParseUrlPacket['fetchinput']=$fin;
		$fetchParseUrlPacket['returnFetchData']=$rdat;
		$fetchParseUrlPacket['parseinput']=$pin;
		$fetchParseUrlPacket['returnParseData']=$pret;

		if($this->displayDebug==1)
		{
			print "inside Reinsert!!! - let's see the fin packet \n";
			var_dump($fetchParseUrlPacket);
		}

		$fetchParseUrlPacket_json=rawurlencode(json_encode($fetchParseUrlPacket));


		$sql1="insert into MasterFetchRequestTBL ";
		$sql1=$sql1." (jobCrawlDate,collegebookType,TermVal,jobScheduleUUID,batchID,CollegeID,";
		$sql1=$sql1." FetchParseUrlPacket,";
		$sql1=$sql1." urlType,ParentLevel,ChildLevel,";
		$sql1=$sql1." time,baseURL,parentParseTBL_name, ";
		$sql1=$sql1." parentParseTBL_uuid, daytbl, booktbl, ";
		$sql1=$sql1." runStatFlg, fetchParseOperationFlg, statDynFlg, ";
		$sql1=$sql1." WriteData, mapid, branch, masterParseInputUUID) ";
		$sql1=$sql1." values ";
		$sql1=$sql1." (:crawlDate,:cbType,:TermVal, :jsuid, :bid, :collegeID, ";
		$sql1=$sql1." :fetchParseUrlPacket, :type, :ParentLevel, ";
		$sql1=$sql1." :ChildLevel, :time, :burl, ";
		$sql1=$sql1." :pTblName, :pTblUUID, :daytbl, :booktbl,";
		$sql1=$sql1." :rtmp, :fpFlg, :statFlg, :writeData, :mapi, :branch, :mpiid)";

		$stmt2 = $this->pdo->prepare($sql1);
		$stmt2->bindParam(':crawlDate', $cdate);
		$stmt2->bindParam(':cbType', $cbType);
		$stmt2->bindParam(':TermVal', $TermVal);
		$stmt2->bindParam(':jsuid', $jsuid);
		$stmt2->bindParam(':bid', $bid);
		$stmt2->bindParam(':collegeID', $collegeID);
		$stmt2->bindParam(':fetchParseUrlPacket', $fetchParseUrlPacket_json);
		$stmt2->bindParam(':type', $type);
		$stmt2->bindParam(':ParentLevel', $ParentLevel);
		$stmt2->bindParam(':ChildLevel', $ChildLevel);
		$stmt2->bindParam(':time', $time);
		$stmt2->bindParam(':burl', $burl);

		if($this->displayDebug==1)
		{
			print "\ncrawlDate:" . $cdate;
			print "\ncbType:" . $cbType;
			print "\nTermVal:" . $TermVal;
			print "\njsuid:" . $jsuid;
			print "\nbid:" . $bid;
			print "\ncollegeID:" . $collegeID;
			print "\nfetchParseUrlPacket:" . $fetchParseUrlPacket_json;
			print "\ntype:" . $type;
			print "\nParentLevel:" . $ParentLevel;
			print "\nChildLevel:" . $ChildLevel;
			print "\ntime:" . $time;
			print "\nburl:" . $burl . "\n";
		}

		$t=" ";	//simply to setup...
		$stmt2->bindParam(':pTblName',$t);
		$stmt2->bindParam(':pTblUUID', $t);
		$stmt2->bindParam(':daytbl', $t);
		$stmt2->bindParam(':booktbl', $t);

		if($this->displayDebug==1)
		{
			print "\npTblName,pTblUUID,daytbl,booktbl:".$t;
		}

		$stmt2->bindParam(':rtmp', $rtmp);
		$stmt2->bindParam(':fpFlg', $fetchParseOperationFlg);
		$stmt2->bindParam(':statFlg', $statDynFlg);
		$stmt2->bindParam(':writeData', $writeData);
		$stmt2->bindParam(':mapi', $mapi);
		$stmt2->bindParam(':branch', $branch);
		$stmt2->bindParam(':mpiid', $mpiid);

		if($this->displayDebug==1)
		{
			print "\nrtmp:".$rtmp;
			print "\nfpFlg:".$fetchParseOperationFlg;
			print "\nstatFlg:".$statDynFlg;
			print "\nwriteData:".$writeData;
			print "\nmapi:".$mapi;
			print "\nbranch:".$branch;
			print "\nmpiid:".$mpiid."\n";
		}
		$stmt2->execute();
		var_dump($stmt2->errorInfo());
		var_dump($stmt2->errorCode());
		print "vvvv111 after any errs...\n";


#		$stmt2->debugDumpParams(); exit();

		if($stmt2->rowCount() == 0) {
			throw new Exception("Failed to write to MasterFetchRequestTBL");
		}

		$fetch_packet = $data; // Comment this line out to 'whitelist' data that gets reinserted.

		// Whitelist -- only applies if $fetch_packet = $data` line is commented out.
		$fetch_packet['jobCrawlDate']=$cdate;
		$fetch_packet['collegebookType']=$cbType;
		$fetch_packet['TermVal']=$TermVal;
		$fetch_packet['jobScheduleUUID']=$jsuid;
		$fetch_packet['batchID']=$bid;
		$fetch_packet['CollegeID']=$collegeID;
		$fetch_packet['FetchParseUrlPacket']=$fetchParseUrlPacket_json;
		$fetch_packet['urlType']=$type;
		$fetch_packet['ParentLevel']=$ParentLevel;
		$fetch_packet['ChildLevel']=$ChildLevel;
		$fetch_packet['time']=$time;
		$fetch_packet['parentParseTBL_name']=$parentParseTBL_name;
		$fetch_packet['parentParseTBL_uuid']=$parentParseTBL_uuid;
		$fetch_packet['childtbl']=$childtbl;
		$fetch_packet['daytbl']=" ";
		$fetch_packet['booktbl']=" ";
		$fetch_packet['baseURL']=$burl;
		//$fetch_packet['fetchInput']=$fi;
		$fetch_packet['runStatFlg']=$rtmp;
		$fetch_packet['fetchParseOperationFlg']=$fetchParseOperationFlg;
		$fetch_packet['statDynFlg']=$statDynFlg;
		$fetch_packet['WriteData']=$writeData;
		$fetch_packet['mapid']=$mapi;
		$fetch_packet['branch']=$branch;
		$fetch_packet['masterParseInputUUID']=$mpiid;

		//$fetch_packet['catalogNum']='';
		//$fetch_packet['bterm']='';
		//$fetch_packet['courseID']='';

		if($this->displayDebug==1)
		{
			print "still in Reinsert.. \n";
			var_dump(json_decode(rawurldecode($fetchParseUrlPacket_json),true));
			print "abov tested fin \n";
		}

		$dat1=rawurlencode(json_encode($fetch_packet));


		# TODO: Need to roll back the table inserts here if the Gearman insertion failed.

		$z= $this->clientP->doBackground("CrawlFetchFunction", $dat1);
		print "rcode = ".$this->clientP->returnCode()."\n\n";

		return $this->clientP->returnCode();
	}



	/*
	 * Rebuild and reinsert a packet for a currently running job/batch.
		reinsrt_fqueue, uses the queue/file stuff for the updated queue/file
		 jobcheduler mods...
	 */
	public function reinsert_fqueue(
		/*$urlType, $jobScheduleUUID, $collegeID, $termVal, $batchUUID, $branch, $cdate, $cbType, 
			$ParentLevel, $ChildLevel, $burl, $rtmp, $fetchParseOperationFlg, $statDynFlg, $mapi, $mpiid,
			$retFetchFileName, $Status, $MacAddress, $time, $bucket, $parseContentFileName, $parseFileName,
			$level, $fetchFileType, $fetchFileName, $deptVal = '', $classVal = '', $sectionVal = ''*/
		$data,$pdo,$fetchQueueDir,$fetchQueuefname,$fetchQueueName
		) {

		$includeLib=getenv('yolo_masterYoloMasterApps_IncludeDirPhp');

		//--include base queueDir_inc.php/dir vars/vals
		#require_once($includeLib.'/queueDir_inc.php');
		#global $fetchQueueDir;
		#global $fetchQueuefname;


		//--include base gcSetup_inc.php/function
		require_once($includeLib.'/gcSetup_inc.php');

		//--include base crawl_functions.inc.php/function
		require_once($includeLib.'/crawl_functions.inc.php');

		//pid defs
		include($includeLib.'/crawlQueueProcessPIDDefs_inc.php');

print "--fqueue1 ".$fetchQueueDir."\n";
print "--fqueue1fn ".$fetchQueuefname."\n";
print "--fqueue1nam ".$fetchQueueName."\n";

		// Convert the associative array to the
		//   variables that were already written...
		// This is just a hack to save some time,
		//   but it works.
		#foreach($arr as $key=>$val) {
		#	${$key} = $val;
		#}


		$urlType = array_key_exists('urlType', $data) ? $data['urlType'] : '';
		$jobScheduleUUID = array_key_exists('jobScheduleUUID', $data) ? $data['jobScheduleUUID'] : '';
		$collegeID = array_key_exists('CollegeID', $data) ? $data['CollegeID'] : '';
		$termVal = array_key_exists('TermVal', $data) ? $data['TermVal'] : '';

		$type = ''; #!!!!!

		$batchUUID = array_key_exists('batchID', $data) ? $data['batchID'] : '';
		$branch = array_key_exists('branch', $data) ? $data['branch'] : '';
		#$branch = $data['branch'] . '_' . $data['masterParseInputUUID'];

		$fpdat = ''; #!!!!!

		$cdate = array_key_exists('jobCrawlDate', $data) ? $data['jobCrawlDate'] : '';
		$cbType = array_key_exists('collegebookType', $data) ? $data['collegebookType'] : '';

		$pid = ''; #!!!!!

		$ParentLevel = array_key_exists('ParentLevel', $data) ? $data['ParentLevel'] : '';

		$ChildLevel = array_key_exists('ChildLevel', $data) ? $data['ChildLevel'] : '';

		$burl = array_key_exists('baseURL', $data) ? $data['baseURL'] : '';

		$rtmp = 1; #!!!!!!

		$fetchParseOperationFlg = array_key_exists('fetchParseOperationFlg', $data) ? $data['fetchParseOperationFlg'] : '';

		$statDynFlg = array_key_exists('statDynFlg', $data) ? $data['statDynFlg'] : '';
		$mapi = array_key_exists('mapid', $data) ? $data['mapid'] : '';
		$mapi = '111'; // NOTICE: field is deprecated.

		$retFetchFileName = array_key_exists('fetchFileName', $data['FetchParseUrlPacket']['fetchinput']) ? $data['FetchParseUrlPacket']['fetchinput']['fetchFileName'] : '';
		$Status = array_key_exists('Status', $data['FetchParseUrlPacket']['returnParseData']) ? $data['FetchParseUrlPacket']['returnParseData']['Status'] : '';
		$MacAddress = array_key_exists('macAddress', $data['FetchParseUrlPacket']['returnParseData']) ? $data['FetchParseUrlPacket']['returnParseData']['macAddress'] : '';
		$time = array_key_exists('time', $data) ? $data['time'] : '';
		$bucket = array_key_exists('fetchFileType', $data['FetchParseUrlPacket']['parseinput']) ? $data['FetchParseUrlPacket']['parseinput']['fetchFileType'] : '';
		$parseContentFileName = array_key_exists('parseContentFileName', $data['FetchParseUrlPacket']['parseinput']) ? $data['FetchParseUrlPacket']['parseinput']['parseContentFileName'] : '';


		$parseFileName = array_key_exists('parseFileName', $data['FetchParseUrlPacket']['parseinput']) ? $data['FetchParseUrlPacket']['parseinput']['parseFileName'] : '';

		$writeData = intval((bool)$data['WriteData']);
		$level = $ChildLevel;
		$fetchFileType = $bucket;
		$fetchFileName = $retFetchFileName;

		// masterParseInputUUID is not changed now.. it's changed in the 
		// parseProcess routine
		$mpiid=array_key_exists('masterParseInputUUID', $data) ? $data['masterParseInputUUID'] : '';
		$mpiid=uuidgen(); // denny???? why wtf! masterParseInputUUID is a new ID--since this is inserting the fetch for the next level.

		$parentParseTBL_name = $data['parentParseTBL_name'];
		$parentParseTBL_uuid = $data['parentParseTBL_uuid'];
		$childtbl = $data['childtbl'];


		$jsuid = $jobScheduleUUID;
		$TermVal = $termVal;
		$bid = $batchUUID;

		$this->_checkJobScheduleUUID($jsuid);
		$type = $this->_getJobType($jsuid);
		$this->_checkBatchTBL($bid);
		$this->_checkJobBatchTBL($jsuid, $bid);
		$this->_getTermVal($jsuid);

		#$fetchParseUrlPacket = array();
		
		#$fin = array();
		#$rdat = array();
		$fetchParseUrlPacket = $data['FetchParseUrlPacket'];
		$fin = $fetchParseUrlPacket['fetchinput'];
		$rdat = $fetchParseUrlPacket['returnFetchData'];

		$rdat['FetchFileName']=$retFetchFileName;
		$rdat['Status']=$Status;
		$rdat['MacAddress']=$MacAddress;
		$rdat['timestamp']=$time;

		#$pin=array();
		$pin = $fetchParseUrlPacket['parseinput'];
		$pin['fetchFileType']=$bucket;	//bucket
		$pin['parseContentFileName']=$parseContentFileName;		//fetched content
		$pin['parseFileName']=$parseFileName;		//parse app
		$pin['level'] = $pin['level'] ? $pin['level'] : $level;

		#$pret=array();
		$pret = $fetchParseUrlPacket['returnParseData'];
		$pret['Status']='fail'; // Status defaults to fail
		$pret['recCount'] = $pret['recCount'] ? $pret['recCount'] : 0;
		$pret['data'] = $pret['data'] ? $pret['data'] : '';
		$pret['nextLevel'] = $pret['nextLevel'] ? $pret['nextLevel'] : '';
		$pret['timestamp'] = $pret['timestamp'] ? $pret['timestamp'] : '';


		$macAddr = system("ifconfig eth0 | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}' | sed 's/://g'");
		$pret['macAddress']=$macAddr; # TODO: Change me! This should be set by the client.

		$fin['currentURL']=$burl;
		$fin['fetchFileType']=$fetchFileType;
		$fin['fetchFileName']=$fetchFileName;

		$fin['requestType']=$type; 
		$fin['cookie'] = $fin['cookie'] ? $fin['cookie'] : '';
		$fin['referer']= $fin['referer'] ? $fin['referer'] : ''; 
		$fin['queryStr']= $fin['queryStr'] ? $fin['queryStr'] : '';

		/*
			update-apr 28/2014 -bdouglas
			changed to allow this to always happen here,
			right before the data is placed in the Queue 
			as opposed to on the clientside..
		*/
		$fin['level']=$data['ChildLevel'];
		$fin['termVal']=$data['TermVal'];
	

		$user_agent = "Mozilla\/4.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident\/4.0; .NET CLR 2.0.50727; .NET CLR 1.1.4322; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; OfficeLiveConnector.1.4; OfficeLivePatch.1.3; .NET4.0C; .NE";
		$fin['user_agent'] = $fin['user_agent'] ? $fin['user_agent'] : $user_agent;

		$fetchParseUrlPacket['masterlevel']=$ChildLevel;
		$fetchParseUrlPacket['returnStatus']="fail"; # Defaults to fail.
		$fetchParseUrlPacket['fetchinput']=$fin;
		$fetchParseUrlPacket['returnFetchData']=$rdat;
		$fetchParseUrlPacket['parseinput']=$pin;
		$fetchParseUrlPacket['returnParseData']=$pret;

		if($this->displayDebug==1)
		{
			print "inside Reinsert!!! - let's see the fin packet \n";
			var_dump($fetchParseUrlPacket);
		}

		$fetchParseUrlPacket_json=rawurlencode(json_encode($fetchParseUrlPacket));


		$sql1="insert into MasterFetchRequestTBL ";
		$sql1=$sql1." (jobCrawlDate,collegebookType,TermVal,jobScheduleUUID,batchID,CollegeID,";
		$sql1=$sql1." FetchParseUrlPacket,";
		$sql1=$sql1." urlType,ParentLevel,ChildLevel,";
		$sql1=$sql1." time,baseURL,parentParseTBL_name, ";
		$sql1=$sql1." parentParseTBL_uuid, daytbl, booktbl, ";
		$sql1=$sql1." runStatFlg, fetchParseOperationFlg, statDynFlg, ";
		$sql1=$sql1." WriteData, mapid, branch, masterParseInputUUID) ";
		$sql1=$sql1." values ";
		$sql1=$sql1." (:crawlDate,:cbType,:TermVal, :jsuid, :bid, :collegeID, ";
		$sql1=$sql1." :fetchParseUrlPacket, :type, :ParentLevel, ";
		$sql1=$sql1." :ChildLevel, :time, :burl, ";
		$sql1=$sql1." :pTblName, :pTblUUID, :daytbl, :booktbl,";
		$sql1=$sql1." :rtmp, :fpFlg, :statFlg, :writeData, :mapi, :branch, :mpiid)";

		$stmt2 = $this->pdo->prepare($sql1);
		$stmt2->bindParam(':crawlDate', $cdate);
		$stmt2->bindParam(':cbType', $cbType);
		$stmt2->bindParam(':TermVal', $TermVal);
		$stmt2->bindParam(':jsuid', $jsuid);
		$stmt2->bindParam(':bid', $bid);
		$stmt2->bindParam(':collegeID', $collegeID);
		$stmt2->bindParam(':fetchParseUrlPacket', $fetchParseUrlPacket_json);
		$stmt2->bindParam(':type', $type);
		$stmt2->bindParam(':ParentLevel', $ParentLevel);
		$stmt2->bindParam(':ChildLevel', $ChildLevel);
		$stmt2->bindParam(':time', $time);
		$stmt2->bindParam(':burl', $burl);

		if($this->displayDebug==1)
		{
			print "\ncrawlDate:" . $cdate;
			print "\ncbType:" . $cbType;
			print "\nTermVal:" . $TermVal;
			print "\njsuid:" . $jsuid;
			print "\nbid:" . $bid;
			print "\ncollegeID:" . $collegeID;
			print "\nfetchParseUrlPacket:" . $fetchParseUrlPacket_json;
			print "\ntype:" . $type;
			print "\nParentLevel:" . $ParentLevel;
			print "\nChildLevel:" . $ChildLevel;
			print "\ntime:" . $time;
			print "\nburl:" . $burl . "\n";
		}

		$t=" ";	//simply to setup...
		$stmt2->bindParam(':pTblName',$t);
		$stmt2->bindParam(':pTblUUID', $t);
		$stmt2->bindParam(':daytbl', $t);
		$stmt2->bindParam(':booktbl', $t);

		if($this->displayDebug==1)
		{
			print "\npTblName,pTblUUID,daytbl,booktbl:".$t;
		}

		$stmt2->bindParam(':rtmp', $rtmp);
		$stmt2->bindParam(':fpFlg', $fetchParseOperationFlg);
		$stmt2->bindParam(':statFlg', $statDynFlg);
		$stmt2->bindParam(':writeData', $writeData);
		$stmt2->bindParam(':mapi', $mapi);
		$stmt2->bindParam(':branch', $branch);
		$stmt2->bindParam(':mpiid', $mpiid);

		if($this->displayDebug==1)
		{
			print "\nrtmp:".$rtmp;
			print "\nfpFlg:".$fetchParseOperationFlg;
			print "\nstatFlg:".$statDynFlg;
			print "\nwriteData:".$writeData;
			print "\nmapi:".$mapi;
			print "\nbranch:".$branch;
			print "\nmpiid:".$mpiid."\n";
		}
		$stmt2->execute();
		var_dump($stmt2->errorInfo());
                var_dump($stmt2->errorCode());
                print "reinsert vvvv111 after any errs...\n";


#		$stmt2->debugDumpParams(); exit();

		if($stmt2->rowCount() == 0) {
			throw new Exception("Failed to write to MasterFetchRequestTBL");
		}

		$fetch_packet = $data; // Comment this line out to 'whitelist' data that gets reinserted.

		// Whitelist -- only applies if $fetch_packet = $data` line is commented out.
		$fetch_packet['jobCrawlDate']=$cdate;
		$fetch_packet['collegebookType']=$cbType;
		$fetch_packet['TermVal']=$TermVal;
		$fetch_packet['jobScheduleUUID']=$jsuid;
		$fetch_packet['batchID']=$bid;
		$fetch_packet['CollegeID']=$collegeID;
		$fetch_packet['FetchParseUrlPacket']=$fetchParseUrlPacket_json;
		$fetch_packet['urlType']=$type;
		$fetch_packet['ParentLevel']=$ParentLevel;
		$fetch_packet['ChildLevel']=$ChildLevel;
		$fetch_packet['time']=$time;
		$fetch_packet['parentParseTBL_name']=$parentParseTBL_name;
		$fetch_packet['parentParseTBL_uuid']=$parentParseTBL_uuid;
		$fetch_packet['childtbl']=$childtbl;
		$fetch_packet['daytbl']=" ";
		$fetch_packet['booktbl']=" ";
		$fetch_packet['baseURL']=$burl;
		//$fetch_packet['fetchInput']=$fi;
		$fetch_packet['runStatFlg']=$rtmp;
		$fetch_packet['fetchParseOperationFlg']=$fetchParseOperationFlg;
		$fetch_packet['statDynFlg']=$statDynFlg;
		$fetch_packet['WriteData']=$writeData;
		$fetch_packet['mapid']=$mapi;
		$fetch_packet['branch']=$branch;
		$fetch_packet['masterParseInputUUID']=$mpiid;

		//$fetch_packet['catalogNum']='';
		//$fetch_packet['bterm']='';
		//$fetch_packet['courseID']='';

		if($this->displayDebug==1)
		{
			print "still in Reinsert.. \n";
			var_dump(json_decode(rawurldecode($fetchParseUrlPacket_json),true));
			print "abov tested fin \n";
		}

		$dat1=rawurlencode(json_encode($fetch_packet));

		$fuuid=uuidgen();

		$d=array();
		$d['jobUUID'] = $jsuid;
		$d['fnameUUID']=$fuuid;
		$d['mpuuid']=$mpiid;

		/*
			ok - update - jul 5/15 -- bdouglas
			major differrence here, takes into account the jobScheduling process 
			can be stopped/modified by a user via the job scheduler webapp
			so, the process needs to determine when/where to stop, or to 
			place the packet for the jobuuid
		*/
		$dir=getenv('yolo_masterPidDir');
		$cronFile=$dir."/".$cronTakesOver_queuePID;
		$jcmd="grep -i '".$jsuid."' ".$cronFile;
		$jcmd=system($jcmd);
		$jcmd=trim($jcmd);

		$dir=$fetchQueueDir;	//where the file for the queue is located
									//keep it in a dir mapped to the queue

		$fname=$dir."/".$fetchQueuefname.trim($fuuid).".dat";

		$f1=fopen($fname,"wr+");
		if(!$f1)
			die("file open err");
		fwrite($f1, $dat1);
		fclose($f1);


		if($jcmd)
		{				
			//the file should have "jobuuid - jobAction
			//--one jobAction/jobuuid per file
			if(strpos($jcmd,"wait")>0)
			{
				//insert into jobQueueWaitTBL
				$sql="select * from jobQueueWaitTBL ";
				$sql=$sql."where "; 
				$sql=$sql."jobUUID=:juuid and "; 
				$sql=$sql."fnameUUID=:fuuid and "; 
				$sql=$sql."mpUUID=:mpuuid and ";
				$sql=$sql."QueueName=:qname";

				$stmt1 = $pdo->prepare($sql);
				$stmt1->bindParam(':juuid', $jsuid);
				$stmt1->bindParam(':fuuid', $fuuid);
				$stmt1->bindParam(':mpuuid', $mpiid);
				$stmt1->bindParam(':qname', $fetchQueueName);
				$stmt1->execute();

				if($stmt1->rowCount() == 0) 
				{
					$jqwait_uuid=uuidgen();

					//--create/insert into tbl
					$sql="insert into jobQueueWaitTBL ";
					$sql=$sql."(jobUUID, fnameUUID, mpUUID, QueueName,jqwait_uuid) "; 
					$sql=$sql."values "; 
					$sql=$sql."(:juuid, :fuuid, :mpuuid, :qname, :jquuid)"; 

					$stmt1 = $pdo->prepare($sql);
					$stmt1->bindParam(':juuid', $jsuid);
					$stmt1->bindParam(':fuuid', $fuuid);
					$stmt1->bindParam(':mpuuid', $mpiid);
					$stmt1->bindParam(':qname', $fetchQueueName);
					$stmt1->bindParam(':jquuid', $jqwait_uuid);
					$stmt1->execute();
				}
				else
				{
					//--some type of error..
					//--should never!! get here...
					//--attributes 'uuidgen' used to generate the vals should always be unique!
				}
			}
			elseif(strpos($jcmd,"stop")>0)
			{
				//insert into jobQueueStopTBL
				$sql="select * from jobQueueStopTBL ";
				$sql=$sql."where "; 
				$sql=$sql."jobUUID=:juuid and "; 
				$sql=$sql."fnameUUID=:fuuid and "; 
				$sql=$sql."mpUUID=:mpuuid and ";
				$sql=$sql."QueueName=:qname";

				$stmt1 = $pdo->prepare($sql);
				$stmt1->bindParam(':juuid', $jsuid);
				$stmt1->bindParam(':fuuid', $fuuid);
				$stmt1->bindParam(':mpuuid', $mpiid);
				$stmt1->bindParam(':qname', $fetchQueueName);
				$stmt1->execute();

				if($stmt1->rowCount() == 0) 
				{
					$jqwait_uuid=uuidgen();

					//--create/insert into tbl
					$sql="insert into jobQueueStopTBL ";
					$sql=$sql."(jobUUID, fnameUUID, mpUUID, QueueName,jqwait_uuid) "; 
					$sql=$sql."values "; 
					$sql=$sql."(:juuid, :fuuid, :mpuuid, :qname, :jquuid)"; 

					$stmt1 = $pdo->prepare($sql);
					$stmt1->bindParam(':juuid', $jsuid);
					$stmt1->bindParam(':fuuid', $fuuid);
					$stmt1->bindParam(':mpuuid', $mpiid);
					$stmt1->bindParam(':qname', $fetchQueueName);
					$stmt1->bindParam(':jquuid', $jqwait_uuid);
					$stmt1->execute();
				}
				else
				{
					//--some type of error..
					//--should never!! get here...
					//--attributes 'uuidgen' used to generate the vals should always be unique!
				}
			}
			elseif(strpos($jcmd,"delete")>0)
			{
				//insert into jobQueueDeleteTBL
				$sql="select * from jobQueueDeleteTBL ";
				$sql=$sql."where "; 
				$sql=$sql."jobUUID=:juuid and "; 
				$sql=$sql."fnameUUID=:fuuid and "; 
				$sql=$sql."mpUUID=:mpuuid and ";
				$sql=$sql."QueueName=:qname";

				$stmt1 = $pdo->prepare($sql);
				$stmt1->bindParam(':juuid', $jsuid);
				$stmt1->bindParam(':fuuid', $fuuid);
				$stmt1->bindParam(':mpuuid', $mpiid);
				$stmt1->bindParam(':qname', $fetchQueueName);
				$stmt1->execute();

				if($stmt1->rowCount() == 0) 
				{
					$jqwait_uuid=uuidgen();

					//--create/insert into tbl
					$sql="insert into jobQueueDeleteTBL ";
					$sql=$sql."(jobUUID, fnameUUID, mpUUID, QueueName,jqwait_uuid) "; 
					$sql=$sql."values "; 
					$sql=$sql."(:juuid, :fuuid, :mpuuid, :qname, :jquuid)"; 

					$stmt1 = $pdo->prepare($sql);
					$stmt1->bindParam(':juuid', $jsuid);
					$stmt1->bindParam(':fuuid', $fuuid);
					$stmt1->bindParam(':mpuuid', $mpiid);
					$stmt1->bindParam(':qname', $fetchQueueName);
					$stmt1->bindParam(':jquuid', $jqwait_uuid);
					$stmt1->execute();
				}
				else
				{
					//--some type of error..
					//--should never!! get here...
					//--attributes 'uuidgen' used to generate the vals should always be unique!
				}
			}
			elseif(strpos($jcmd,"pause")>0)
			{
				//insert into jobQueuePauseTBL
				$sql="select * from jobQueuePauseTBL ";
				$sql=$sql."where "; 
				$sql=$sql."jobUUID=:juuid and "; 
				$sql=$sql."fnameUUID=:fuuid and "; 
				$sql=$sql."mpUUID=:mpuuid and ";
				$sql=$sql."QueueName=:qname";

				$stmt1 = $pdo->prepare($sql);
				$stmt1->bindParam(':juuid', $jsuid);
				$stmt1->bindParam(':fuuid', $fuuid);
				$stmt1->bindParam(':mpuuid', $mpiid);
				$stmt1->bindParam(':qname', $fetchQueueName);
				$stmt1->execute();

				if($stmt1->rowCount() == 0) 
				{
					$jqwait_uuid=uuidgen();

					//--create/insert into tbl
					$sql="insert into jobQueuePauseTBL ";
					$sql=$sql."(jobUUID, fnameUUID, mpUUID, QueueName,jqwait_uuid) "; 
					$sql=$sql."values "; 
					$sql=$sql."(:juuid, :fuuid, :mpuuid, :qname, :jquuid)"; 

					$stmt1 = $pdo->prepare($sql);
					$stmt1->bindParam(':juuid', $jsuid);
					$stmt1->bindParam(':fuuid', $fuuid);
					$stmt1->bindParam(':mpuuid', $mpiid);
					$stmt1->bindParam(':qname', $fetchQueueName);
					$stmt1->bindParam(':jquuid', $jqwait_uuid);
					$stmt1->execute();
				}
				else
				{
					//--some type of error..
					//--should never!! get here...
					//--attributes 'uuidgen' used to generate the vals should always be unique!
				}
			}
		}
		else
		{
			$sql="select * from jobFileMpQueueMappingTBL ";
			$sql=$sql."where "; 
			$sql=$sql."jobUUID=:juuid and "; 
			$sql=$sql."fUUID=:fuuid and "; 
			$sql=$sql."mpUUID=:mpuuid and ";
			$sql=$sql."QueueName=:qname";

			$stmt1 = $pdo->prepare($sql);
			$stmt1->bindParam(':juuid', $jsuid);
			$stmt1->bindParam(':fuuid', $fuuid);
			$stmt1->bindParam(':mpuuid', $mpiid);
			$stmt1->bindParam(':qname', $fetchQueueName);
			$stmt1->execute();

			if($stmt1->rowCount() == 0) 
			{
				//--create/insert into tbl
				$sql="insert into jobFileMpQueueMappingTBL ";
				$sql=$sql."(jobUUID, fUUID, mpUUID, QueueName) "; 
				$sql=$sql."values "; 
				$sql=$sql."(:juuid, :fuuid, :mpuuid, :qname)"; 

				$stmt1 = $pdo->prepare($sql);
				$stmt1->bindParam(':juuid', $jsuid);
				$stmt1->bindParam(':fuuid', $fuuid);
				$stmt1->bindParam(':mpuuid', $mpiid);
				$stmt1->bindParam(':qname', $fetchQueueName);
				$stmt1->execute();
			}
			else
			{
				//--some type of error..
				//--should never!! get here...
				//--attributes 'uuidgen' used to generate the vals should always be unique!
			}

			//$z= $clientP->doBackground("CrawlParseProcessFunction", rawurlencode(json_encode($d)));
			$z= $this->clientP->doBackground("CrawlFetchFunction", rawurlencode(json_encode($d)));

			//$z= $clientP->doBackground("CrawlParseProcessFunction", $data);
			//echo $z;

			print "\n denny!! -YoloClientSpawnParseApp \n";



#$a=$clientP->addServer($gServer_host, $gParseProcessworker_port);
			//print "aa ".$gServer_host."\n";
			//print "bb ".$gParseProcessWorker_port."\n";
			print "rcode = ".$this->clientP->returnCode()."\n\n";

			return $this->clientP->returnCode();

	//exit();

		}


		# TODO: Need to roll back the table inserts here if the Gearman insertion failed.

		//$z= $this->clientP->doBackground("CrawlFetchFunction", $dat1);
		//$z= $this->clientP->doBackground("CrawlFetchFunction", $d);
		//print "rcode = ".$this->clientP->returnCode()."\n\n";

		//return $this->clientP->returnCode();
	}



	/*
	 * Rebuild and reinsert/passthru a packet for a currently running job/batch.
		-just like the reinsert - except doesn't reset the parseReturnData['Status']

	 */
	public function passthru_fqueue(
		/*$urlType, $jobScheduleUUID, $collegeID, $termVal, $batchUUID, $branch, $cdate, $cbType, 
			$ParentLevel, $ChildLevel, $burl, $rtmp, $fetchParseOperationFlg, $statDynFlg, $mapi, $mpiid,
			$retFetchFileName, $Status, $MacAddress, $time, $bucket, $parseContentFileName, $parseFileName,
			$level, $fetchFileType, $fetchFileName, $deptVal = '', $classVal = '', $sectionVal = ''*/
		$data,$pdo,$fetchQueueDir,$fetchQueuefname,$fetchQueueName
		) {

		//include def from the sysEnv
		$includeLib=getenv('yolo_masterYoloMasterApps_IncludeDirPhp');

		//pid defs
		include($includeLib.'/crawlQueueProcessPIDDefs_inc.php');


		// Convert the associative array to the
		//   variables that were already written...
		// This is just a hack to save some time,
		//   but it works.
		#foreach($arr as $key=>$val) {
		#	${$key} = $val;
		#}


		$urlType = array_key_exists('urlType', $data) ? $data['urlType'] : '';
		$jobScheduleUUID = array_key_exists('jobScheduleUUID', $data) ? $data['jobScheduleUUID'] : '';
		$collegeID = array_key_exists('CollegeID', $data) ? $data['CollegeID'] : '';
		$termVal = array_key_exists('TermVal', $data) ? $data['TermVal'] : '';

		$type = ''; #!!!!!

		$batchUUID = array_key_exists('batchID', $data) ? $data['batchID'] : '';
		$branch = array_key_exists('branch', $data) ? $data['branch'] : '';
		#$branch = $data['branch'] . '_' . $data['masterParseInputUUID'];

		$fpdat = ''; #!!!!!

		$cdate = array_key_exists('jobCrawlDate', $data) ? $data['jobCrawlDate'] : '';
		$cbType = array_key_exists('collegebookType', $data) ? $data['collegebookType'] : '';

		$pid = ''; #!!!!!

		$ParentLevel = array_key_exists('ParentLevel', $data) ? $data['ParentLevel'] : '';

		$ChildLevel = array_key_exists('ChildLevel', $data) ? $data['ChildLevel'] : '';

		$burl = array_key_exists('baseURL', $data) ? $data['baseURL'] : '';

		$rtmp = 1; #!!!!!!

		$fetchParseOperationFlg = array_key_exists('fetchParseOperationFlg', $data) ? $data['fetchParseOperationFlg'] : '';

		$statDynFlg = array_key_exists('statDynFlg', $data) ? $data['statDynFlg'] : '';
		$mapi = array_key_exists('mapid', $data) ? $data['mapid'] : '';
		$mapi = '111'; // NOTICE: field is deprecated.

		$retFetchFileName = array_key_exists('fetchFileName', $data['FetchParseUrlPacket']['fetchinput']) ? $data['FetchParseUrlPacket']['fetchinput']['fetchFileName'] : '';
		$Status = array_key_exists('Status', $data['FetchParseUrlPacket']['returnParseData']) ? $data['FetchParseUrlPacket']['returnParseData']['Status'] : '';
		$MacAddress = array_key_exists('macAddress', $data['FetchParseUrlPacket']['returnParseData']) ? $data['FetchParseUrlPacket']['returnParseData']['macAddress'] : '';
		$time = array_key_exists('time', $data) ? $data['time'] : '';
		$bucket = array_key_exists('fetchFileType', $data['FetchParseUrlPacket']['parseinput']) ? $data['FetchParseUrlPacket']['parseinput']['fetchFileType'] : '';
		$parseContentFileName = array_key_exists('parseContentFileName', $data['FetchParseUrlPacket']['parseinput']) ? $data['FetchParseUrlPacket']['parseinput']['parseContentFileName'] : '';


		$parseFileName = array_key_exists('parseFileName', $data['FetchParseUrlPacket']['parseinput']) ? $data['FetchParseUrlPacket']['parseinput']['parseFileName'] : '';

		$writeData = intval((bool)$data['WriteData']);
		$level = $ChildLevel;
		$fetchFileType = $bucket;
		$fetchFileName = $retFetchFileName;

		// masterParseInputUUID is not changed now.. it's changed in the 
		// parseProcess routine
		$mpiid=array_key_exists('masterParseInputUUID', $data) ? $data['masterParseInputUUID'] : '';
		//$mpiid=uuidgen(); // masterParseInputUUID is a new ID--since this is inserting the fetch for the next level.

		$parentParseTBL_name = $data['parentParseTBL_name'];
		$parentParseTBL_uuid = $data['parentParseTBL_uuid'];
		$childtbl = $data['childtbl'];


		$jsuid = $jobScheduleUUID;
		$TermVal = $termVal;
		$bid = $batchUUID;

		$this->_checkJobScheduleUUID($jsuid);
		$type = $this->_getJobType($jsuid);
		$this->_checkBatchTBL($bid);
		$this->_checkJobBatchTBL($jsuid, $bid);
		$this->_getTermVal($jsuid);

		#$fetchParseUrlPacket = array();
		
		#$fin = array();
		#$rdat = array();
		$fetchParseUrlPacket = $data['FetchParseUrlPacket'];
		$fin = $fetchParseUrlPacket['fetchinput'];
		$rdat = $fetchParseUrlPacket['returnFetchData'];

		$rdat['FetchFileName']=$retFetchFileName;
		$rdat['Status']=$Status;
		$rdat['MacAddress']=$MacAddress;
		$rdat['timestamp']=$time;

		#$pin=array();
		$pin = $fetchParseUrlPacket['parseinput'];
		$pin['fetchFileType']=$bucket;	//bucket
		$pin['parseContentFileName']=$parseContentFileName;		//fetched content
		$pin['parseFileName']=$parseFileName;		//parse app
		$pin['level'] = $pin['level'] ? $pin['level'] : $level;

		#$pret=array();
		$pret = $fetchParseUrlPacket['returnParseData'];
		//$pret['Status']='fail'; // Status defaults to fail
		$pret['recCount'] = $pret['recCount'] ? $pret['recCount'] : 0;
		$pret['data'] = $pret['data'] ? $pret['data'] : '';
		$pret['nextLevel'] = $pret['nextLevel'] ? $pret['nextLevel'] : '';
		$pret['timestamp'] = $pret['timestamp'] ? $pret['timestamp'] : '';


		$macAddr = system("ifconfig eth0 | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}' | sed 's/://g'");
		$pret['macAddress']=$macAddr; # TODO: Change me! This should be set by the client.

		$fin['currentURL']=$burl;
		$fin['fetchFileType']=$fetchFileType;
		$fin['fetchFileName']=$fetchFileName;

		$fin['requestType']=$type; 
		$fin['cookie'] = $fin['cookie'] ? $fin['cookie'] : '';
		$fin['referer']= $fin['referer'] ? $fin['referer'] : ''; 
		$fin['queryStr']= $fin['queryStr'] ? $fin['queryStr'] : '';

		/*
			update-apr 28/2014 -bdouglas
			changed to allow this to always happen here,
			right before the data is placed in the Queue 
			as opposed to on the clientside..
		*/
		$fin['level']=$data['ChildLevel'];
		$fin['termVal']=$data['TermVal'];
	

		$user_agent = "Mozilla\/4.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident\/4.0; .NET CLR 2.0.50727; .NET CLR 1.1.4322; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; OfficeLiveConnector.1.4; OfficeLivePatch.1.3; .NET4.0C; .NE";
		$fin['user_agent'] = $fin['user_agent'] ? $fin['user_agent'] : $user_agent;

		$fetchParseUrlPacket['masterlevel']=$ChildLevel;
		$fetchParseUrlPacket['returnStatus']="fail"; # Defaults to fail.
		$fetchParseUrlPacket['fetchinput']=$fin;
		$fetchParseUrlPacket['returnFetchData']=$rdat;
		$fetchParseUrlPacket['parseinput']=$pin;
		$fetchParseUrlPacket['returnParseData']=$pret;

		if($this->displayDebug==1)
		{
			print "inside Reinsert!!! - let's see the fin packet \n";
			var_dump($fetchParseUrlPacket);
		}

		$fetchParseUrlPacket_json=rawurlencode(json_encode($fetchParseUrlPacket));


		$sql1="insert into MasterFetchRequestTBL ";
		$sql1=$sql1." (jobCrawlDate,collegebookType,TermVal,jobScheduleUUID,batchID,CollegeID,";
		$sql1=$sql1." FetchParseUrlPacket,";
		$sql1=$sql1." urlType,ParentLevel,ChildLevel,";
		$sql1=$sql1." time,baseURL,parentParseTBL_name, ";
		$sql1=$sql1." parentParseTBL_uuid, daytbl, booktbl, ";
		$sql1=$sql1." runStatFlg, fetchParseOperationFlg, statDynFlg, ";
		$sql1=$sql1." WriteData, mapid, branch, masterParseInputUUID) ";
		$sql1=$sql1." values ";
		$sql1=$sql1." (:crawlDate,:cbType,:TermVal, :jsuid, :bid, :collegeID, ";
		$sql1=$sql1." :fetchParseUrlPacket, :type, :ParentLevel, ";
		$sql1=$sql1." :ChildLevel, :time, :burl, ";
		$sql1=$sql1." :pTblName, :pTblUUID, :daytbl, :booktbl,";
		$sql1=$sql1." :rtmp, :fpFlg, :statFlg, :writeData, :mapi, :branch, :mpiid)";

		$stmt2 = $this->pdo->prepare($sql1);
		$stmt2->bindParam(':crawlDate', $cdate);
		$stmt2->bindParam(':cbType', $cbType);
		$stmt2->bindParam(':TermVal', $TermVal);
		$stmt2->bindParam(':jsuid', $jsuid);
		$stmt2->bindParam(':bid', $bid);
		$stmt2->bindParam(':collegeID', $collegeID);
		$stmt2->bindParam(':fetchParseUrlPacket', $fetchParseUrlPacket_json);
		$stmt2->bindParam(':type', $type);
		$stmt2->bindParam(':ParentLevel', $ParentLevel);
		$stmt2->bindParam(':ChildLevel', $ChildLevel);
		$stmt2->bindParam(':time', $time);
		$stmt2->bindParam(':burl', $burl);

		if($this->displayDebug==1)
		{
			print "\ncrawlDate:" . $cdate;
			print "\ncbType:" . $cbType;
			print "\nTermVal:" . $TermVal;
			print "\njsuid:" . $jsuid;
			print "\nbid:" . $bid;
			print "\ncollegeID:" . $collegeID;
			print "\nfetchParseUrlPacket:" . $fetchParseUrlPacket_json;
			print "\ntype:" . $type;
			print "\nParentLevel:" . $ParentLevel;
			print "\nChildLevel:" . $ChildLevel;
			print "\ntime:" . $time;
			print "\nburl:" . $burl . "\n";
		}

		$t=" ";	//simply to setup...
		$stmt2->bindParam(':pTblName',$t);
		$stmt2->bindParam(':pTblUUID', $t);
		$stmt2->bindParam(':daytbl', $t);
		$stmt2->bindParam(':booktbl', $t);

		if($this->displayDebug==1)
		{
			print "\npTblName,pTblUUID,daytbl,booktbl:".$t;
		}

		$stmt2->bindParam(':rtmp', $rtmp);
		$stmt2->bindParam(':fpFlg', $fetchParseOperationFlg);
		$stmt2->bindParam(':statFlg', $statDynFlg);
		$stmt2->bindParam(':writeData', $writeData);
		$stmt2->bindParam(':mapi', $mapi);
		$stmt2->bindParam(':branch', $branch);
		$stmt2->bindParam(':mpiid', $mpiid);

		if($this->displayDebug==1)
		{
			print "\nrtmp:".$rtmp;
			print "\nfpFlg:".$fetchParseOperationFlg;
			print "\nstatFlg:".$statDynFlg;
			print "\nwriteData:".$writeData;
			print "\nmapi:".$mapi;
			print "\nbranch:".$branch;
			print "\nmpiid:".$mpiid."\n";
		}
		$stmt2->execute();
		var_dump($stmt2->errorInfo());
		var_dump($stmt2->errorCode());
		print "vvvv111 after any errs...\n";


#		$stmt2->debugDumpParams(); exit();

		if($stmt2->rowCount() == 0) {
			throw new Exception("Failed to write to MasterFetchRequestTBL");
		}

		$fetch_packet = $data; // Comment this line out to 'whitelist' data that gets reinserted.

		// Whitelist -- only applies if $fetch_packet = $data` line is commented out.
		$fetch_packet['jobCrawlDate']=$cdate;
		$fetch_packet['collegebookType']=$cbType;
		$fetch_packet['TermVal']=$TermVal;
		$fetch_packet['jobScheduleUUID']=$jsuid;
		$fetch_packet['batchID']=$bid;
		$fetch_packet['CollegeID']=$collegeID;
		$fetch_packet['FetchParseUrlPacket']=$fetchParseUrlPacket_json;
		$fetch_packet['urlType']=$type;
		$fetch_packet['ParentLevel']=$ParentLevel;
		$fetch_packet['ChildLevel']=$ChildLevel;
		$fetch_packet['time']=$time;
		$fetch_packet['parentParseTBL_name']=$parentParseTBL_name;
		$fetch_packet['parentParseTBL_uuid']=$parentParseTBL_uuid;
		$fetch_packet['childtbl']=$childtbl;
		$fetch_packet['daytbl']=" ";
		$fetch_packet['booktbl']=" ";
		$fetch_packet['baseURL']=$burl;
		//$fetch_packet['fetchInput']=$fi;
		$fetch_packet['runStatFlg']=$rtmp;
		$fetch_packet['fetchParseOperationFlg']=$fetchParseOperationFlg;
		$fetch_packet['statDynFlg']=$statDynFlg;
		$fetch_packet['WriteData']=$writeData;
		$fetch_packet['mapid']=$mapi;
		$fetch_packet['branch']=$branch;
		$fetch_packet['masterParseInputUUID']=$mpiid;

		//$fetch_packet['catalogNum']='';
		//$fetch_packet['bterm']='';
		//$fetch_packet['courseID']='';

		if($this->displayDebug==1)
		{
			print "still in Reinsert.. \n";
			var_dump(json_decode(rawurldecode($fetchParseUrlPacket_json),true));
			print "abov tested fin \n";
		}

		$dat1=rawurlencode(json_encode($fetch_packet));

		$fuuid=uuidgen();

		$d=array();
		$d['jobUUID'] = $jsuid;
		$d['fnameUUID']=$fuuid;
		$d['mpuuid']=$mpiid;

		/*
			ok - update - jul 5/15 -- bdouglas
			major differrence here, takes into account the jobScheduling process 
			can be stopped/modified by a user via the job scheduler webapp
			so, the process needs to determine when/where to stop, or to 
			place the packet for the jobuuid
		*/
		$dir=getenv('yolo_masterPidDir');
		$cronFile=$dir."/".$cronTakesOver_queuePID;
		$jcmd="grep -i '".$jsuid."' ".$cronFile;
		$jcmd=system($jcmd);
		$jcmd=trim($jcmd);

		$dir=$fetchQueueDir;	//where the file for the queue is located
									//keep it in a dir mapped to the queue

		$fname=$dir."/".$fetchQueuefname.trim($fuuid).".dat";

		$f1=fopen($fname,"wr+");
		if(!$f1)
			die("file open err");
		fwrite($f1, $dat1);
		fclose($f1);


		if($jcmd)
		{				
			//the file should have "jobuuid - jobAction
			//--one jobAction/jobuuid per file
			if(strpos($jcmd,"wait")>0)
			{
				//insert into jobQueueWaitTBL
				$sql="select * from jobQueueWaitTBL ";
				$sql=$sql."where "; 
				$sql=$sql."jobUUID=:juuid and "; 
				$sql=$sql."fnameUUID=:fuuid and "; 
				$sql=$sql."mpUUID=:mpuuid and ";
				$sql=$sql."QueueName=:qname";

				$stmt1 = $pdo->prepare($sql);
				$stmt1->bindParam(':juuid', $jsuid);
				$stmt1->bindParam(':fuuid', $fuuid);
				$stmt1->bindParam(':mpuuid', $mpiid);
				$stmt1->bindParam(':qname', $fetchQueueName);
				$stmt1->execute();

				if($stmt1->rowCount() == 0) 
				{
					$jqwait_uuid=uuidgen();

					//--create/insert into tbl
					$sql="insert into jobQueueWaitTBL ";
					$sql=$sql."(jobUUID, fnameUUID, mpUUID, QueueName,jqwait_uuid) "; 
					$sql=$sql."values "; 
					$sql=$sql."(:juuid, :fuuid, :mpuuid, :qname, :jquuid)"; 

					$stmt1 = $pdo->prepare($sql);
					$stmt1->bindParam(':juuid', $jsuid);
					$stmt1->bindParam(':fuuid', $fuuid);
					$stmt1->bindParam(':mpuuid', $mpiid);
					$stmt1->bindParam(':qname', $fetchQueueName);
					$stmt1->bindParam(':jquuid', $jqwait_uuid);
					$stmt1->execute();
				}
				else
				{
					//--some type of error..
					//--should never!! get here...
					//--attributes 'uuidgen' used to generate the vals should always be unique!
				}
			}
			elseif(strpos($jcmd,"stop")>0)
			{
				//insert into jobQueueStopTBL
				$sql="select * from jobQueueStopTBL ";
				$sql=$sql."where "; 
				$sql=$sql."jobUUID=:juuid and "; 
				$sql=$sql."fnameUUID=:fuuid and "; 
				$sql=$sql."mpUUID=:mpuuid and ";
				$sql=$sql."QueueName=:qname";

				$stmt1 = $pdo->prepare($sql);
				$stmt1->bindParam(':juuid', $jsuid);
				$stmt1->bindParam(':fuuid', $fuuid);
				$stmt1->bindParam(':mpuuid', $mpiid);
				$stmt1->bindParam(':qname', $fetchQueueName);
				$stmt1->execute();

				if($stmt1->rowCount() == 0) 
				{
					$jqwait_uuid=uuidgen();

					//--create/insert into tbl
					$sql="insert into jobQueueStopTBL ";
					$sql=$sql."(jobUUID, fnameUUID, mpUUID, QueueName,jqwait_uuid) "; 
					$sql=$sql."values "; 
					$sql=$sql."(:juuid, :fuuid, :mpuuid, :qname, :jquuid)"; 

					$stmt1 = $pdo->prepare($sql);
					$stmt1->bindParam(':juuid', $jsuid);
					$stmt1->bindParam(':fuuid', $fuuid);
					$stmt1->bindParam(':mpuuid', $mpiid);
					$stmt1->bindParam(':qname', $fetchQueueName);
					$stmt1->bindParam(':jquuid', $jqwait_uuid);
					$stmt1->execute();
				}
				else
				{
					//--some type of error..
					//--should never!! get here...
					//--attributes 'uuidgen' used to generate the vals should always be unique!
				}
			}
			elseif(strpos($jcmd,"delete")>0)
			{
				//insert into jobQueueDeleteTBL
				$sql="select * from jobQueueDeleteTBL ";
				$sql=$sql."where "; 
				$sql=$sql."jobUUID=:juuid and "; 
				$sql=$sql."fnameUUID=:fuuid and "; 
				$sql=$sql."mpUUID=:mpuuid and ";
				$sql=$sql."QueueName=:qname";

				$stmt1 = $pdo->prepare($sql);
				$stmt1->bindParam(':juuid', $jsuid);
				$stmt1->bindParam(':fuuid', $fuuid);
				$stmt1->bindParam(':mpuuid', $mpiid);
				$stmt1->bindParam(':qname', $fetchQueueName);
				$stmt1->execute();

				if($stmt1->rowCount() == 0) 
				{
					$jqwait_uuid=uuidgen();

					//--create/insert into tbl
					$sql="insert into jobQueueDeleteTBL ";
					$sql=$sql."(jobUUID, fnameUUID, mpUUID, QueueName,jqwait_uuid) "; 
					$sql=$sql."values "; 
					$sql=$sql."(:juuid, :fuuid, :mpuuid, :qname, :jquuid)"; 

					$stmt1 = $pdo->prepare($sql);
					$stmt1->bindParam(':juuid', $jsuid);
					$stmt1->bindParam(':fuuid', $fuuid);
					$stmt1->bindParam(':mpuuid', $mpiid);
					$stmt1->bindParam(':qname', $fetchQueueName);
					$stmt1->bindParam(':jquuid', $jqwait_uuid);
					$stmt1->execute();
				}
				else
				{
					//--some type of error..
					//--should never!! get here...
					//--attributes 'uuidgen' used to generate the vals should always be unique!
				}
			}
			elseif(strpos($jcmd,"pause")>0)
			{
				//insert into jobQueuePauseTBL
				$sql="select * from jobQueuePauseTBL ";
				$sql=$sql."where "; 
				$sql=$sql."jobUUID=:juuid and "; 
				$sql=$sql."fnameUUID=:fuuid and "; 
				$sql=$sql."mpUUID=:mpuuid and ";
				$sql=$sql."QueueName=:qname";

				$stmt1 = $pdo->prepare($sql);
				$stmt1->bindParam(':juuid', $jsuid);
				$stmt1->bindParam(':fuuid', $fuuid);
				$stmt1->bindParam(':mpuuid', $mpiid);
				$stmt1->bindParam(':qname', $fetchQueueName);
				$stmt1->execute();

				if($stmt1->rowCount() == 0) 
				{
					$jqwait_uuid=uuidgen();

					//--create/insert into tbl
					$sql="insert into jobQueuePauseTBL ";
					$sql=$sql."(jobUUID, fnameUUID, mpUUID, QueueName,jqwait_uuid) "; 
					$sql=$sql."values "; 
					$sql=$sql."(:juuid, :fuuid, :mpuuid, :qname, :jquuid)"; 

					$stmt1 = $pdo->prepare($sql);
					$stmt1->bindParam(':juuid', $jsuid);
					$stmt1->bindParam(':fuuid', $fuuid);
					$stmt1->bindParam(':mpuuid', $mpiid);
					$stmt1->bindParam(':qname', $fetchQueueName);
					$stmt1->bindParam(':jquuid', $jqwait_uuid);
					$stmt1->execute();
				}
				else
				{
					//--some type of error..
					//--should never!! get here...
					//--attributes 'uuidgen' used to generate the vals should always be unique!
				}
			}
		}
		else
		{
			$sql="select * from jobFileMpQueueMappingTBL ";
			$sql=$sql."where "; 
			$sql=$sql."jobUUID=:juuid and "; 
			$sql=$sql."fUUID=:fuuid and "; 
			$sql=$sql."mpUUID=:mpuuid and ";
			$sql=$sql."QueueName=:qname";

			$stmt1 = $pdo->prepare($sql);
			$stmt1->bindParam(':juuid', $jsuid);
			$stmt1->bindParam(':fuuid', $fuuid);
			$stmt1->bindParam(':mpuuid', $mpiid);
			$stmt1->bindParam(':qname', $fetchQueueName);
			$stmt1->execute();

			if($stmt1->rowCount() == 0) 
			{
				//--create/insert into tbl
				$sql="insert into jobFileMpQueueMappingTBL ";
				$sql=$sql."(jobUUID, fUUID, mpUUID, QueueName) "; 
				$sql=$sql."values "; 
				$sql=$sql."(:juuid, :fuuid, :mpuuid, :qname)"; 

				$stmt1 = $pdo->prepare($sql);
				$stmt1->bindParam(':juuid', $jsuid);
				$stmt1->bindParam(':fuuid', $fuuid);
				$stmt1->bindParam(':mpuuid', $mpiid);
				$stmt1->bindParam(':qname', $fetchQueueName);
				$stmt1->execute();
			}
			else
			{
				//--some type of error..
				//--should never!! get here...
				//--attributes 'uuidgen' used to generate the vals should always be unique!
			}

			$z= $this->clientP->doBackground("CrawlFetchFunction", rawurlencode(json_encode($d)));
			//$z= $clientP->doBackground("CrawlParseProcessFunction", rawurlencode(json_encode($d)));

			//$z= $clientP->doBackground("CrawlParseProcessFunction", $data);
			//echo $z;

			print "\n denny!! -YoloClientSpawnParseApp \n";



#$a=$clientP->addServer($gServer_host, $gParseProcessworker_port);
			//print "aa ".$gServer_host."\n";
			//print "bb ".$gParseProcessWorker_port."\n";


			print "rcode = ".$this->clientP->returnCode()."\n\n";

			return $this->clientP->returnCode();
	//exit();



		}


		# TODO: Need to roll back the table inserts here if the Gearman insertion failed.

		//$z= $this->clientP->doBackground("CrawlFetchFunction", $dat1);
		//print "rcode = ".$this->clientP->returnCode()."\n\n";

		//return $this->clientP->returnCode();
	}

}



?>


