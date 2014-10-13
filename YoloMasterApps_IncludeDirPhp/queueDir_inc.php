<?php
/*
#-------------------------------------------------------------
#
#	FileName:
#		queueDir_inc.php
#		_DIR_/YoloMasterApps_IncludeDirPhp/queueDir_inc.php
#
#	Goal:	
#		contains the list of dirName/dir for the associated queues
#		-the dirs contain the data files for the associated
#		 mapped data in the file mappped to the fuuid in the queueData
#
#
#	these could be set as sysEnvVars
#
#	this is used for the yolo-master-vm2 processes
#
*/



//$tdir=getenv('yolo_masterTmpDir');
$tdir=getenv('top_test_master_dir');


	//--dirname
/*
yolo_master_gParseBookWorkerPort=5960
yolo_master_gServer_hostAddress=127.0.0.1

	fetchRequestQueueDir
	parseRequestQueueDir			
	fetchReturnQueueDir				yolo_master_gFetchWorkerPort=5800
	tmpParseReturnQueueDir			yolo_master_gTmpParseWorkerPort=5849
	parseReturnQueueDir				yolo_master_gParseWorkerPort=5850
	tmpParseProcessQueueDir			yolo_master_gTmpParseProcessWorkerPort=5899
	parseProcessQueueDir			yolo_master_gParseProcessWorkerPort=5900
	initReferrerProcessQueueDir		yolo_master_gParseInitReferrerProcessWorkerPort=5904
	termProcessQueueDir				yolo_master_gParseTermProcessWorkerPort=5905
	campusQueueDir					yolo_master_gParseCampusWorkerPort=5920
	collegeQueueDir					yolo_master_gParseCollegeWorkerPort=5930
	courseListQueueDir				yolo_master_gParseCourseListWorkerPort=5944
	courseQueueDir					yolo_master_gParseCourseWorkerPort=5945
	courseSectionQueueDir			yolo_master_gParseCourseSectionWorkerPort=5965
	courseSectionDayQueueDir		yolo_master_gParseCourseSectionDayWorkerPort=5970
	courseSectionDayFacultyQueueDir	yolo_master_gParseCourseSectionDayFacultyWorkerPort=5975
	deptQueueDir					yolo_master_gParseDeptWorkerPort=5940
	facultyQueueDir					yolo_master_gParseFacultyWorkerPort=5955
	rateCollegeFaculty1QueueDir		yolo_master_gParseRateCollegeFaculty1WorkerPort=5990
	rateCollegeFaculty2QueueDir		yolo_master_gParseRateCollegeFaculty2WorkerPort=5990
	rateStateCollegeQueueDir		yolo_master_gParseRateStateCollegeWorkerPort=5985
	rateStateQueueDir				yolo_master_gParseRateStateWorkerPort=5980
	universityQueueDir				yolo_master_gParseUniversityWorkerPort=5910
	sectionQueueDir					yolo_master_gParseSectionWorkerPort=5950
*/

	$fetchQueueDir					= $tdir."/"."fetchQueueDir";
	$fetchReturnQueueDir			= $tdir."/"."fetchReturnQueueDir";
	$tmpParseQueueDir				= $tdir."/"."tmpParseQueueDir";	//-from the fetchReturn-before the parse
	$parseQueueDir					= $tdir."/"."parseQueueDir";
	$parseReturnQueueDir			= $tdir."/"."parseReturnQueueDir";
	$tmpParseProcessQueueDir		= $tdir."/"."tmpParseProcessQueueDir";	//-from the parseReturn-before the parseProcess
	$parseProcessQueueDir			= $tdir."/"."parseProcessQueueDir";
	$campusQueueDir					= $tdir."/"."campusQueueDir";
	$initReferrerQueueDir			= $tdir."/"."initReferrerQueueDir";
	$termQueueDir					= $tdir."/"."termQueueDir";
	$collegeQueueDir				= $tdir."/"."collegeQueueDir";
	$courseListQueueDir				= $tdir."/"."courseListQueueDir";
	$courseQueueDir					= $tdir."/"."courseQueueDir";
	$courseSectionQueueDir			= $tdir."/"."courseSectionQueueDir";
	$courseSectionDayQueueDir		= $tdir."/"."courseSectionDayQueueDir";
	$courseSectionDayFacultyQueueDir = $tdir."/"."courseSectionDayFacultyQueueDir";
	$deptQueueDir					= $tdir."/"."deptQueueDir";
	$facultyQueueDir				= $tdir."/"."facultyQueueDir";
	$rateCollegeFaculty1QueueDir	= $tdir."/"."rateCollegeFaculty1QueueDir";
	$rateCollegeFaculty2QueueDir	= $tdir."/"."rateCollegeFaculty2QueueDir";
	$rateStateCollegeQueueDir		= $tdir."/"."rateStateCollegeQueueDir";
	$rateStateQueueDir				= $tdir."/"."rateStateQueueDir";
	$universityQueueDir				= $tdir."/"."universityQueueDir";
	$sectionQueueDir				= $tdir."/"."sectionQueueDir";


	$fetchQueuefname				= "fetch__";
	$fetchReturnQueuefname			= "fetchReturn__";
	$tmpParseQueuefname				= "tmpParse__";
	$parseQueuefname				= "parse__";
	$parseReturnQueuefname			= "parseReturn__";
	$tmpParseProcessQueuefname		= "tmpParseProcess__";
	$parseProcessQueuefname			= "parseProcess__";
	$initReferrerQueuefname			= "initReferrer__";
	$termQueuefname					= "term__";
	$campusQueuefname				= "campus__";
	$collegeQueuefname				= "college__";
	$courseListQueuefname			= "courseList__";
	$courseQueuefname				= "course__";
	$courseSectionQueuefname		= "courseSection__";
	$courseSectionDayQueuefname		= "courseSectionDay__";
	$courseSectionDayFacultyQueuefname = "courseSectionDayFaculty__";
	$deptQueuefname					= "dept__";
	$facultyQueuefname				= "faculty__";
	$rateCollegeFaculty1Queuefname	= "rateCollegeFaculty1__";
	$rateCollegeFaculty2Queuefname	= "rateCollegeFaculty2__";
	$rateStateCollegeQueuefname		= "rateStateCollege__";
	$rateStateQueuefname			= "rateState__";
	$universityQueuefname			= "university__";
	$sectionQueuefname				= "section__";


	$fetchQueueName					= "fetchQueue";
	$tmpParseQueueName				= "tmpParseQueue";
	$parseQueueName					= "parseQueue";
	$tmpParseProcessQueueName		= "tmpParseProcessQueue";
	$parseProcessQueueName			= "parseProcessQueue";
	$initReferrerQueueName			= "initReferrerQueue";
	$termQueueName					= "termQueue";
	$campusQueueName				= "campusQueue";
	$collegeQueueName				= "collegeQueue";
	$courseListQueueName			= "courseListQueue";
	$courseQueueName				= "courseQueue";
	$courseSectionQueueName		= "courseSectionQueue";
	$courseSectionDayQueueName		= "courseSectionDayQueue";
	$courseSectionDayFacultyQueueName = "courseSectionDayFacultyQueue";
	$deptQueueName					= "deptQueue";
	$facultyQueueName				= "facultyQueue";
	$rateCollegeFaculty1QueueName	= "rateCollegeFaculty1Queue";
	$rateCollegeFaculty2QueueName	= "rateCollegeFaculty2Queue";
	$rateStateCollegeQueueName		= "rateStateCollegeQueue";
	$rateStateQueueName			= "rateStateQueue";
	$universityQueueName			= "universityQueue";
	$sectionQueueName				= "sectionQueue";



?>


