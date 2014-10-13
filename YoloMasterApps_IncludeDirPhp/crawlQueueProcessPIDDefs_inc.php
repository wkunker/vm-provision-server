<?php
/*
#-------------------------------------------------------------
#
#	FileName:
#		crawlQueueProcessPIDDefs_inc.php
#		_DIR_/YoloMasterApps_IncludeDirPhp/crawlQueueProcessPIDDefs_inc.php
#
#	Goal:	
#		contains the list of the pid dat files for the associated 
#		 queue processes
#
#		contains the list of the cron pid files as well
#
#	this is used for the yolo-master-vm2 processes
#
*/


$tdir=getenv('top_test_master_dir');

/*
5800	-fetchQueuePID.dat 				allApps->Queue Queue->index.php 
5849	tmpParseQueuePID.dat			index.php->Queue Queue->tmp
5850	parseQueuePID.dat				parseReturntmp->Queue  Queue->
5899	tmpParseProcessQueuePID.dat		--ParseReturnTmpParseProcessQueue.php
5900	parseProcessQueuePID.dat		--parseProcessy.php

5904	initReferrerProcessQueuePID.dat			--initReferrerProcess.php
5905	termProcessQueuePID.dat			--termProcess.php
5910	universityProcessQueuePID.dat	--universityProcess.php
5920	campusProcessQueuePID.dat			--campusProcess.php
5930	collegeProcessQueuePID.dat			--collegeProcess.php
5940	deptProcessQueuePID.dat			--deptProcess.php
5944	courseListProcessQueuePID.dat			--courseListProcess.php
5945	courseProcessQueuePID.dat			--courseProcess.php
5950	sectionProcessQueuePID.dat			--sectionProcess.php
5955	facultyProcessQueuePID.dat			--facultyProcess.php
5960	bookProcessQueuePID.dat			--bookProcess.php
5965	courseSectionProcessQueuePID.dat			--courseSectionProcess.php
5970	courseSectionDayProcessQueuePID.dat			--courseSectionDayProcess.php
5975	courseSectionDayFacultyProcessQueuePID.dat			--courseSectionDayFacultyProcess.php


5980	rateStateQueuePID.dat			--rateStateProcessy.php
5985	rateStateCollegeQueuePID.dat	--rateStateCollegeProcessy.php
5990	collegeFaculty1QueuePID.dat		--rateCollegeFaculty1Processy.php
5995	collegeFaculty2QueuePID.dat		--rateCollegeFaculty2Processy.php
*/


//queueProcess pid filenames

$tmpParseQueuePID					= "tmpParseQueuePID.dat";
$tmpParseProcessQueuePID			= "tmpParseProcessQueuePID.dat";
$parseProcessQueuePID				= "parseProcessQueuePID.dat";

$initReferrerQueuePID			= "initReferrerProcessQueuePID.dat";
$termQueuePID					= "termProcessQueuePID.dat";
$universityQueuePID				= "universityProcessQueuePID.dat";
$campusQueuePID					= "campusProcessQueuePID.dat";
$collegeQueuePID				= "collegeProcessQueuePID.dat";
$deptQueuePID					= "deptProcessQueuePID.dat";
$courseListQueuePID				= "courseListProcessQueuePID.dat";
$courseQueuePID					= "courseProcessQueuePID.dat";
$sectionQueuePID				= "sectionProcessQueuePID.dat";
$facultyQueuePID				= "facultyProcessQueuePID.dat";
$bookProcessQueuePID				= "bookProcessQueuePID.dat";
$courseSectionQueuePID				= "courseSectionProcessQueuePID.dat";
$courseSectionDayQueuePID			= "courseSectionDayProcessQueuePID.dat";	
$courseSectionDayFacultyQueuePID		= "courseSectionDayFacultyProcessQueuePID.dat";	

$rateStateQueuePID				= "rateStateQueuePID.dat";
$rateStateCollegeQueuePID			= "rateStateCollegeQueuePID.dat";
$collegeFaculty1QueuePID			= "collegeFaculty1QueuePID.dat";
$collegeFaculty2QueuePID			= "collegeFaculty2QueuePID.dat";

//cron pid filenames

$cronJobActionSchedulePID 	="cronJobActionSchedule.pid";
$cronTakesOver_fetchPID		="cronTakesOver_fetch.pid";
$cronTakesOverPID		="cronTakesOver.pid";
$cronTakesOver_queuePID		="cronTakesOver_queue.pid";


?>


