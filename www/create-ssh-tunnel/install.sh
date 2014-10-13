#!/bin/bash

############
# install.sh
#
# Install script for the virtual machine instance
#   that runs inside of the host operating system.
#
# This is meant to be run during a period
#   designated for provisioning. In other words,
#   on an in-house network or similar secure environment.
# 
#   It may be wise to store this custom VM image on
#   DVD (with read-only DVD drives) or some other
#   kind of immutable storage medium, where it would
#   then be copied to the hard drive during install.
#
# The overall goal of this is to build a robust edge
#   server system which allows secure remote maintenance
#   and self-recovery in case of the most fatal
#   situation imaginable (such as mass deployment of
#   of fatal code/settings or similar).
#
# The VM kickstart file is responsible for executing this script.
############

############
# SETTINGS #
############

SSHDIR="/root/.ssh"

REMOTE_SSHDIR="/home/user/.ssh"

LOGDIR="/root"

# Master-side connects to localhost via SSH tunnel.
HOST="localhost"

# SSH tunnel port (when master-side connects via localhost).
# WARNING: This variable is now deprecated in favor of ASSIGNED_PORT
#   via the port assignment server (used during the provisioning process).
#PORT="19999"

# The location of the identity file used during
#   the provisioning process.
IDENTITYFILE="/root/.ssh/provision"

# Periodically make sure the SSH tunnel is running.
#   TODO: This may eventually be replaced with autossh
#   or similar depending on what state they are in.
SSHCHECKFILE="/crowd/ssh_check.sh"

# Hostname of the master-side provisioning server.
PROVISIONING_HOST="MY_SERVER_ADDRESS"

# Name or address of the master-side provisioning server.
PROVISIONING_ADDR="MY_SERVER_ADDRESS"

# Port of the master-side provisioning server.
PROVISIONING_PORT="22"

PORT_ASSIGNMENT_SERVER="http://MY_SERVER_ADDRESS/TunnelProvision/index.php"

PROVISIONING_SSHDIR="/home/user/.ssh"

REMOTE_USER="user"

LOCAL_USER="root"

############################
# INTERNAL. DO NOT MODIFY. #
############################

yum -y install nmap uuid
#yum -y update #TODO: Uncomment this--it's just commented to install faster for development purposes.

#wget http://pkgs.repoforge.org/autossh/autossh-1.4c-1.el6.rf.x86_64.rpm
#rpm -Uvh autossh-1.4c-1.el6.rf.x86_64.rpm

mkdir -p "$SSHDIR"
mkdir -p "/crowd"

# 'provision' is the key pair which is shared only for provisioning purposes.
#   The master server side must "open the gate" for a moment by enabling
#   the provision.pub key in the authorized_keys file. Note that this
#   won't ever be necessary if the VM image with its own key baked in is
#   stored on some form of permanent/immutable storage; However, it's
#   still used during provisioning and then destroyed afterwards,
#   since the provisioning process is presumably done in a secure environment.
cp -f "install/ssh/provision" "$SSHDIR/provision"
cp -f "install/ssh/provision.pub" "$SSHDIR/provision.pub"
cp -f "install$SSHCHECKFILE" "$SSHCHECKFILE"

# Provisioning identity file permissions need to be 600.
chmod 600 "$IDENTITYFILE"

# Make sure no SSH config already exists... This could
#   be the case if the installer somehow were ran twice.
mv "$SSHDIR/config" "$SSHDIR/config.OLD" 2> /dev/null
rm -f "$SSHDIR/config"

# Create the local config to connect to the provisioning server.
echo "" >> "$SSHDIR/config"
echo "Host $PROVISIONING_HOST" >> "$SSHDIR/config"
echo "HostName $PROVISIONING_ADDR" >> "$SSHDIR/config"
echo "Port $PROVISIONING_PORT" >> "$SSHDIR/config"
echo "User $REMOTE_USER" >> "$SSHDIR/config"
echo "IdentityFile \"$IDENTITYFILE\"" >> "$SSHDIR/config"
echo "UserKnownHostsFile /dev/null" >> "$SSHDIR/config"
echo "StrictHostKeyChecking no" >> "$SSHDIR/config"
echo "PasswordAuthentication no" >>"$SSHDIR/config"

RETURN_UUID=$(uuid)

# Request a port from the port assignment server.
#ASSIGNED_PORT=$(curl "$PORT_ASSIGNMENT_SERVER")
MAC_ADDR=$(cat /sys/class/net/eth?/address | head -n1 | sed 's/://g')
ASSIGNED_PORT=$(curl "$PORT_ASSIGNMENT_SERVER" -s -X POST -d "p_mac=$MAC_ADDR" | xargs php getport.php)
STATUS=$(curl "$PORT_ASSIGNMENT_SERVER" -s -X POST -d "p_mac=$MAC_ADDR" | xargs php getstatus.php)

curl "$PORT_ASSIGNMENT_SERVER" -s -X POST -d "p_mac=$MAC_ADDR"
if [ "$?" -ne "0" ]
then
	echo "FATAL ERROR: Failed to connect to port assignment server. Aborting installation." >> "$LOGDIR/create-ssh-tunnel.log"
	exit 1
fi

if [ "$STATUS" -ne "1" ]
then
	echo "FATAL ERROR: MAC address was not found in the provision/port assignment server's clientSystemMgmtTBL. Aborting installation." >> "$LOGDIR/create-ssh-tunnel.log"
	exit 1
fi

# Create the master-side config which lets the master-side
#   know which port the tunnel exists on, identity file, and so-on.
rm -f "$SSHDIR/masterside_config"
echo "" >> "$SSHDIR/masterside_config"
echo "Host \"$RETURN_UUID\"" >> "$SSHDIR/masterside_config"
echo "HostName \"$HOST\"" >> "$SSHDIR/masterside_config"
echo "User $LOCAL_USER" >> "$SSHDIR/masterside_config"
echo "Port \"$ASSIGNED_PORT\"" >> "$SSHDIR/masterside_config"
echo "IdentityFile \"$PROVISIONING_SSHDIR/$RETURN_UUID\"" >> "$SSHDIR/masterside_config"
echo "UserKnownHostsFile /dev/null" >> "$SSHDIR/masterside_config"
echo "StrictHostKeyChecking no" >> "$SSHDIR/masterside_config"
echo "PasswordAuthentication no" >> "$SSHDIR/masterside_config"

# SSH check takes place in a cron job.
sed -i.bkp "s/MY_SSH_PORT/$ASSIGNED_PORT/g" "$SSHCHECKFILE"
echo "*/2 * * * * /bin/bash $SSHCHECKFILE" >> "/var/spool/cron/root"

echo ""
echo "Be sure to add the pubkey to the server's authorized_keys file if it isn't already there."

# Prepare the provisioning server's SSH directory.
ssh "$PROVISIONING_HOST" mkdir -p .ssh
ssh "$PROVISIONING_HOST" rm -f .ssh/known_hosts

# Overwrite .ssh/config on the master server.
scp "$SSHDIR/masterside_config" "$PROVISIONING_HOST":.ssh/config

# Create a return key (allow masterside to access this host)
ssh "$PROVISIONING_HOST" 'ssh-keygen -q -t rsa -f '".ssh/$RETURN_UUID"' -N ""'
scp "$PROVISIONING_HOST":.ssh/${RETURN_UUID}.pub "$SSHDIR"
AUTHKEY=$(cat "$SSHDIR/$RETURN_UUID.pub")
rm -f "$SSHDIR/authorized_keys"
echo "$AUTHKEY" >> "$SSHDIR/authorized_keys"

hostname "$RETURN_UUID"
sed -i.bkp "s/HOSTNAME=.*/HOSTNAME=$RETURN_UUID/g" /etc/sysconfig/network

chmod -R go-rwx "$SSHDIR"

