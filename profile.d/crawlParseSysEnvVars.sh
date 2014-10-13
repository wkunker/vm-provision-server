# crawl init env script
#
#	resides in the /etc/profile.d
#
#	crawlParseSysEnvVars.sh
#
#	runs:
#		. /etc/profile.d/crawlParseSysEnvVars.sh
#
#


#create top level dir client dir/location sysenv for 
top_parse_dir="/ycrawl/dcrawl/run/yolo-client-parse"
export top_parse_dir


#create client dir/location sysenv for 
# parse YoloParsePidDir
yolo_parsePidDir=${top_parse_dir}"/YoloParsePidDir"


#create client dir/location sysenv for 
# local parse input files
yolo_clientParseInputDir=${top_parse_dir}"/YoloParseInputDir"


#create client dir/location sysenv for 
# local fetch target apps fooFetch.py
yolo_clientParseTargetAppsDir=${top_parse_dir}"/YoloParseAppsDir"


#create client dir/location sysenv for 
# local parse shell scripts
yolo_clientParseShellScriptsDir=${top_parse_dir}"/YoloParseShellScriptDir"



#create client dir/location sysenv for 
# local parse include dir (for py)
yolo_clientParsePyIncludeDir=${top_parse_dir}"/Yolo_ParseClientApps_includeDirPy"

#create client dir/location sysenv for 
# local parse include dir (for php)
yolo_clientParsePhpIncludeDir=${top_parse_dir}"/Yolo_ParseClientApps_includeDirPhp"




#create client dir/location sysenv for 
# local gearman apps
yolo_clientParseGearmanAppsDir=${top_parse_dir}"/YoloGearmanParseProcessAppDir"


#create client dir/location sysenv for 
# local parse daemons/apps
yolo_clientParseAppsDir=${top_parse_dir}"/YoloClientParseApps"


#create client dir/location sysenv for 
# local gearman_ipAddress (for gearmanQueue)
yolo_clientParseGearmanQueueAddress="127.0.0.1"


#create client dir/location sysenv for 
# local gearman_port (for gearmanQueue)
yolo_clientParseGearmanQueuePort="5778"


#create masterside ipaddress for the 
# crawl webservice
#yolo_mastersideWebServiceAddress="http://192.168.5.17/"
yolo_mastersideWebServiceAddress="http://dell2/"


#create client dir/location sysenv for 
# local parse nic dat/files
yolo_clientParseNicDir=${top_parse_dir}"/YoloParseNicDir"


#create client tmp dir for parse request file/content
yolo_clientParseTmpDir=${top_parse_dir}"/YoloParseTmpDir"


#create parseside count for the child/gearmand 
#processes -- spawnApp countDir for the count dat file
ParseChildCountDir=${top_parse_dir}"/ParseChildCountDir"

#set default value for the number (#) of the 
#parse spawned gearmand processes to be running if the 
#value isn't set in the countFile, or from the webService
ParseChildCountDefault=25



#create masterside ipaddress for the 
# crawl webservice
#yolo_clientGearmanParseQueueDir="YoloGearmanParseQueueDir"

#create displayDebug sys var for the 
#crawl parse process
displayCrawlParseDebug="1"




export yolo_parsePidDir
export yolo_clientParseInputDir
export yolo_clientParseTargetAppsDir
export yolo_clientParseGearmanAppsDir
export yolo_clientParseAppsDir

export yolo_clientParseGearmanQueueAddress
export yolo_clientParseGearmanQueuePort
export yolo_mastersideWebServiceAddress

export yolo_clientParseShellScriptsDir

export yolo_clientParsePyIncludeDir
export yolo_clientParsePhpIncludeDir
export yolo_clientParseTmpDir

export ParseChildCountDir
export ParseChildCountDefault

#export yolo_clientGearmanParseQueueDir

export displayCrawlParseDebug
export yolo_clientParseNicDir


