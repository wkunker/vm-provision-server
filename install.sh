#!/bin/bash

# install.sh
# yolo-unattended-vm-master-server installer
#
# Install the yolo-unattended-vm-master-server to a centos 6.5 system.
# This will install/configure an apache web server if not already 
#  installed/configured. 
# The default /var/www/html directory will be used unless otherwise 
#  specified in the variable below.
# Script must be run with elevated privileges or as root.

#########################################################
# DO NOT MODIFY PAST THIS POINT.
#########################################################

ORIG_DIR=$(pwd)

# Get script directory so installation is not dependent on working directory
#  of parent shell. Follow links for increased environment compatibility.
# Taken from http://stackoverflow.com/questions/59895/can-a-bash-script-tell-what-directory-its-stored-in
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

cd "$DIR"

source "./config.env"

if [ "$?" != "0" ]
then
	echo "Failure: config.env could not be sourced. Please make sure you've modified config.env.CHANGEME to match your desired settings, and renamed config.env.CHANGEME to config.env, then try running this script again."
	cd "$ORIG_DIR"
	exit 1
fi

yum -y install httpd mysql-server php php-pdo php-mysql
mkdir -p "${WWW_DIRECTORY}/"

adduser "$SSH_USER"

service httpd stop
service iptables stop
chkconfig iptables off # TODO: Properly configure the firewall without disabling.


# Install files to server.
cp -rfa "www/." "${WWW_DIRECTORY}/"
cp -rfa "profile.d/." "/etc/profile.d/"
mkdir -p /ycrawl/dcrawl/run/yolo-master/YoloMasterApps_IncludeDirPhp
cp -rfa "YoloMasterApps_IncludeDirPhp/." "/ycrawl/dcrawl/run/yolo-master/YoloMasterApps_IncludeDirPhp/"

sed -i.bkp "s@MY_SERVER_ADDRESS@${SERVER_ADDRESS}@g" "${WWW_DIRECTORY}/ks.cfg"
sed -i.bkp "s@MY_SERVER_ADDRESS@${SERVER_ADDRESS}@g" "${WWW_DIRECTORY}/create-ssh-tunnel/install.sh"
sed -i.bkp "s@MY_SERVER_ADDRESS@${SERVER_ADDRESS}@g" "${WWW_DIRECTORY}/create-ssh-tunnel/install/crowd/ssh_check.sh"

# Modify /etc/sudoers so netstat can be performed by the clients without requiring a password (otherwise client cron task ssh_check.sh fails).
sed -i.bkp "s@^root.*@root    ALL=(ALL)       ALL\n${SSH_USER}    ALL=(ALL)       ALL,NOPASSWD:/bin/netstat@g" "/etc/sudoers"

# Make sure the server can be accessed by the vms.
mkdir -p "/home/$SSH_USER/.ssh/"
touch "/home/$SSH_USER/.ssh/authorized_keys"
cat "${WWW_DIRECTORY}/create-ssh-tunnel/install/ssh/provision.pub" >> "/home/$SSH_USER/.ssh/authorized_keys"
chmod 600 "/home/$SSH_USER/.ssh/authorized_keys"
chown -R "$SSH_USER" "/home/$SSH_USER/.ssh/"

# Start the database server so it may be populated with the schema.
service mysqld start
chkconfig mysqld on

# Import the schema (and create the db if it does not exist).
mysql -uroot < schema.sql

# Start the web service.
service httpd start
chkconfig httpd on

# TODO: configure port assignment server from YoloWebServicesDir files. This depends upon clientSystemMgmtTBL and similar from crawlManagerDB MySQL database.

cd "$ORIG_DIR"

