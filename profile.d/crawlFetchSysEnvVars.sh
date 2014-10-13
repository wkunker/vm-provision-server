# crawl init env script
#
#	resides in the /etc/profile.d
#
#	crawlFetchSysEnvVars.sh
#
#	runs:
#		. /etc/profile.d/crawlFetchSysEnvVars.sh
#
#	make/set all dir/paths to be fully qualified pathnames
#
#
#
#

#create top level dir client dir/location sysenv for 
top_fetch_dir="/ycrawl/dcrawl/run/yolo-client-fetch"
export top_fetch_dir


#create client dir/location sysenv for 
# fetch YoloFetchPidDir
yolo_fetchPidDir=${top_fetch_dir}"/YoloFetchPidDir"


#create client dir/location sysenv for 
# local fetch output files
yolo_clientFetchOutputDir=${top_fetch_dir}"/YoloFetchOutputDir"



#create client dir/location sysenv for 
# local fetch target apps fooFetch.py
yolo_clientFetchTargetAppsDir=${top_fetch_dir}"/YoloFetchAppsDir"


#create client dir/location sysenv for 
# local fetch shell scripts
yolo_clientFetchShellScriptsDir=${top_fetch_dir}"/YoloFetchShellScriptDir"




#create client dir/location sysenv for 
# local fetch include dir (for py)
yolo_clientFetchPyIncludeDir=${top_fetch_dir}"/Yolo_FetchClientApps_includeDirPy"

#create client dir/location sysenv for 
# local fetch include dir (for php)
yolo_clientFetchPhpIncludeDir=${top_fetch_dir}"/Yolo_FetchClientApps_includeDirPhp"



#create client dir/location sysenv for 
# local gearman apps
yolo_clientFetchGearmanAppsDir=${top_fetch_dir}"/YoloGearmanFetchProcessAppDir"


#create client dir/location sysenv for 
# local fetch daemons/apps
yolo_clientFetchAppsDir=${top_fetch_dir}"/YoloClientFetchApps"



#create client dir/location sysenv for 
# local gearman_ipAddress (for gearmanQueue)
#yolo_clientFetchGearmanQueueAddress="192.168.1.45"
yolo_clientFetchGearmanQueueAddress="127.0.0.1"


#create client dir/location sysenv for 
# local gearman_port (for gearmanQueue)
yolo_clientFetchGearmanQueuePort="5777"



#create masterside ipaddress for the 
# crawl webservice
#yolo_mastersideWebServiceAddress="http://192.168.5.17/"
yolo_mastersideWebServiceAddress="http://dell2/"


#create client dir/location sysenv for 
# local fetch nic dat/files
yolo_clientFetchNicDir=${top_fetch_dir}"/YoloFetchNicDir"


#create masterside ipaddress for the 
# crawl webservice
yolo_clientFetchTmpDir=${top_fetch_dir}"/YoloFetchTmpDir"

#create fetchside count for the child/gearmand 
#processes -- spawnApp countDir for the count dat file
FetchChildCountDir=${top_fetch_dir}"/FetchChildCountDir"

#set default value for the number (#) of the 
#fetch spawned gearmand processes to be running if the 
#value isn't set in the countFile, or from the webService
FetchChildCountDefault=25


#create masterside ipaddress for the 
# crawl webservice
#yolo_clientGearmanFetchQueueDir="YoloGearmanFetchQueueDir"


#create displayDebug sys var for the 
#crawl fetch process
displayCrawlFetchDebug="1"



export yolo_FetchPidDir
export yolo_clientFetchOutputDir
export yolo_clientFetchTargetAppsDir
export yolo_clientFetchGearmanAppsDir
export yolo_clientFetchAppsDir

export yolo_clientFetchGearmanQueueAddress
export yolo_clientFetchGearmanQueuePort
export yolo_mastersideWebServiceAddress

export yolo_clientFetchShellScriptsDir

export yolo_clientFetchPyIncludeDir
export yolo_clientFetchPhpIncludeDir
export yolo_clientFetchTmpDir

export FetchChildCountDir
export FetchChildCountDefault


#export yolo_clientGearmanFetchQueueDir
export displayCrawlFetchDebug
export yolo_clientFetchNicDir


