#!/bin/sh 
#
# crawl init env script
#
#	resides in the /etc/profile.d
#
#	crawlMasterSysEnvVars.sh
#
#	runs:
#		. /etc/profile.d/crawlMasterSysEnvVars.sh
#
#

#create top level dir master dir/location sysenv for 
top_master_dir="/ycrawl/dcrawl/run/yolo-master"
export top_master_dir

#create top level test dir master dir/location sysenv for 
##top_test_master_dir="/ycrawl/dcrawl/run/yolo-master/test"
top_test_master_dir="/home/ihubuser/testDir"

export top_test_master_dir


#create client dir/location sysenv for 
# master YoloMasterPidDir
yolo_masterPidDir=${top_master_dir}"/YoloMasterPidDir"


#create client dir/location sysenv for 
# master YoloWebServicesDir
yolo_masterYoloWebServicesDir=${top_master_dir}"/YoloWebServicesDir"


#create client dir/location sysenv for 
# master FetchRequest
yolo_masterFetchRequestDir=${top_master_dir}"/YoloWebServicesDir/FetchRequest"


#create client dir/location sysenv for 
# master FetchReturn
yolo_masterFetchReturnDir=${top_master_dir}"/YoloWebServicesDir/FetchReturn"


#create client dir/location sysenv for 
# master ParseRequest
yolo_masterParseRequestDir=${top_master_dir}"/YoloWebServicesDir/ParseRequest"


#create client dir/location sysenv for 
# master ParseReturn
yolo_masterParseReturnDir=${top_master_dir}"/YoloWebServicesDir/ParseReturn"


#create client dir/location sysenv for 
# master YoloMasterCrawlAppsDir
yolo_masterYoloMasterCrawlAppsDir=${top_master_dir}"/YoloMasterCrawlAppsDir"



#create client dir/location sysenv for 
# master YoloMasterApps_IncludeDirPhp
yolo_masterYoloMasterApps_IncludeDirPhp=${top_master_dir}"/YoloMasterApps_IncludeDirPhp"


#create client dir/location sysenv for 
# master YoloMasterApps_IncludeDirPy
yolo_masterYoloMasterApps_IncludeDirPy=${top_master_dir}"/YoloMasterApps_IncludeDirPy"



#create client dir/location sysenv for 
# master YoloMasterGearmanQueueProcessAppDir
yolo_masterYoloMasterGearmanQueueProcessAppDir=${top_master_dir}"/YoloMasterGearmanQueueProcessAppDir"



#create client dir/location sysenv for 
# master YoloMasterDBSQLDir
yolo_masterYoloMasterDBSQLDir=${top_master_dir}"/YoloMasterDBSQLDir"



#create client dir/location sysenv for 
# master YoloMasterShellScriptDir
yolo_masterYoloMasterShellScriptDir=${top_master_dir}"/YoloMasterShellScriptDir"



#create client dir/location sysenv for 
# master YoloMasterClientContentDir
yolo_masterYoloMasterClientContentDir=${top_master_dir}"/YoloMasterClientContentDir"



#create client dir/location sysenv for 
# master YoloSSHDir
yolo_masterYoloSSHDir=${top_master_dir}"/YoloSSHDir"


#create client dir/location sysenv for 
# master YoloWebServiceConfigFilesDir
#yolo_masterYoloWebServiceConfigFilesDir="/foo"

# create client dir/location for
# master YoloUnitTestsDir
yolo_masterYoloUnitTestsDir=${top_master_dir}"/YoloUnitTestsDir"

# create client dir/location for
# master YoloMasterInstallDir
yolo_masterYoloMasterInstallDir=${top_master_dir}"/YoloMasterInstallDir"


#create client dir/location sysenv for 
# local master nic dat/files
yolo_masterNicDir=${top_master_dir}"/YoloMasterNicDir"

#create master sysenv for 
# master tmp dir
yolo_masterTmpDir=${top_master_dir}"/MasterTmpDir"



#create masterside count for the child/gearmand 
#processes -- spawnApp countDir for the count dat file
#all childCount files are in the same dir
MasterChildCountDir=${top_master_dir}"/MasterChildCountDir"

#set default value for the number (#) of the 
#fetch spawned gearmand processes to be running if the 
#value isn't set in the countFile, or from the webService
Master_CourseSectionDayFaculty_ChildCountDefault=20

#set default value for the number (#) of the 
#fetch spawned gearmand processes to be running if the 
#value isn't set in the countFile, or from the webService
Master_Dept_ChildCountDefault=20

#set default value for the number (#) of the 
#fetch spawned gearmand processes to be running if the 
#value isn't set in the countFile, or from the webService
Master_Parse_ChildCountDefault=20

#set default value for the number (#) of the 
#fetch spawned gearmand processes to be running if the 
#value isn't set in the countFile, or from the webService
Master_RateCollegeFaculty1_ChildCountDefault=20

#set default value for the number (#) of the 
#fetch spawned gearmand processes to be running if the 
#value isn't set in the countFile, or from the webService
Master_RateCollegeFaculty2_ChildCountDefault=20

#set default value for the number (#) of the 
#fetch spawned gearmand processes to be running if the 
#value isn't set in the countFile, or from the webService
Master_RateStateCollege_ChildCountDefault=20

#set default value for the number (#) of the 
#fetch spawned gearmand processes to be running if the 
#value isn't set in the countFile, or from the webService
Master_RateState_ChildCountDefault=20

#set default value for the number (#) of the 
#fetch spawned gearmand processes to be running if the 
#value isn't set in the countFile, or from the webService
Master_University_ChildCountDefault=20

Master_Term_ChildCountDefault=20
Master_Campus_ChildCountDefault=20
Master_College_ChildCountDefault=20
Master_Course_ChildCountDefault=20
Master_CourseSectionDay_ChildCountDefault=20
Master_CourseSection_ChildCountDefault=20
Master_Section_ChildCountDefault=20
Master_Faculty_ChildCountDefault=20



#create displayDebug sys var for the 
#crawl master process
displayCrawlMasterDebug="1"

#create displayDebug sys var for the 
#crawl webService processes
displayCrawlWebServiceDebug="1"



export yolo_masterPidDir
export yolo_masterYoloWebServicesDir
export yolo_masterFetchRequestDir
export yolo_masterFetchReturnDir
export yolo_masterParseRequestDir
export yolo_masterParseReturnDir
export yolo_masterYoloMasterCrawlAppsDir
export yolo_masterYoloMasterApps_IncludeDirPhp
export yolo_masterYoloMasterApps_IncludeDirPy
export yolo_masterYoloMasterGearmanQueueProcessAppDir
export yolo_masterYoloMasterDBSQLDir
export yolo_masterYoloMasterShellScriptDir
export yolo_masterYoloMasterClientContentDir
export yolo_masterYoloSSHDir
#export yolo_masterYoloWebServiceConfigFilesDir
export yolo_masterYoloUnitTestsDir
export yolo_masterYoloMasterInstallDir
export displayCrawlMasterDebug
export displayCrawlWebServiceDebug

export MasterChildCountDir
export Master_CourseSectionDayFaculty_ChildCountDefault
export Master_Dept_ChildCountDefault
export Master_Parse_ChildCountDefault
export Master_RateCollegeFaculty1_ChildCountDefault
export Master_RateCollegeFaculty2_ChildCountDefault
export Master_RateStateCollege_ChildCountDefault
export Master_RateState_ChildCountDefault
export Master_University_ChildCountDefault
export Master_Term_ChildCountDefault
export Master_Campus_ChildCountDefault
export Master_College_ChildCountDefault
export Master_Course_ChildCountDefault
export Master_CourseSectionDay_ChildCountDefault
export Master_CourseSection_ChildCountDefault
export Master_Section_ChildCountDefault
export Master_Faculty_ChildCountDefault


export yolo_masterNicDir
export yolo_masterTmpDir


