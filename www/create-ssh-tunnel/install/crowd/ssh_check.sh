#!/bin/bash

# Check to ensure SSH tunnel is running.
# Works by performing a write test from the remote back to local.
# On failure, attempts to clean up autossh (vm-side) and sshd (master side)

REMOTE_ADDRESS=MY_SERVER_ADDRESS
REMOTE_PORT=MY_SSH_PORT
REVERSE_TUNNEL_PORT=MY_REVERSE_TUNNEL_PORT
LOCAL_HOSTNAME=$(/bin/hostname)
LOCAL_IDENTITYFILE=/root/.ssh/provision

/usr/bin/ssh -o ConnectTimeout=10 -i "$LOCAL_IDENTITYFILE" user@${REMOTE_ADDRESS} -p ${REMOTE_PORT} hostname
if [ "$?" != "0" ]; then
	echo "FATAL: Cannot connect to server."
	exit
fi

/usr/bin/ssh -o ConnectTimeout=10 -i "$LOCAL_IDENTITYFILE" -p ${REMOTE_PORT} user@${REMOTE_ADDRESS} ssh -o ConnectTimeout=10 ${LOCAL_HOSTNAME} hostname
if [ "$?" != "0" ]; then
	echo "Tunnel is down. Attempting to bring tunnel back up..."

	# Try to close any stale sshd connection on the server. Hide errors since stale sshd isn't a guarantee.
	# Note that NOPASSWD:/bin/netstat must be set in /etc/sudoers on the server.
	#ssh -i /root/.ssh/provision user@104.131.71.66 ps aux | grep -v grep | grep 14014 | awk '{print $2}' | xargs kill -9 2> /dev/null
	pid=$(/usr/bin/ssh -t -t -i "$LOCAL_IDENTITYFILE" user@${REMOTE_ADDRESS} sudo netstat -antlp | grep -v grep | grep ${REVERSE_TUNNEL_PORT} | grep 127.0.0.1 | awk '{print $7}' | sed 's@/.*@@g' | head -n1)
	/usr/bin/ssh -i "$LOCAL_IDENTITYFILE" -p ${REMOTE_PORT} user@${REMOTE_ADDRESS} /bin/kill -9 "$pid"

	# Kill any local autossh instances associated with the port as well.
	/bin/ps aux | /bin/grep -v grep | /bin/grep autossh | /bin/grep ${REVERSE_TUNNEL_PORT} | /bin/awk '{print $2}' | /bin/xargs /bin/kill -9 2> /dev/null

	# Start a clean instance of autossh.
	/usr/bin/autossh -M 0 -f -N -o "ServerAliveInterval 60" -o "ServerAliveCountMax 3" -o "ExitOnForwardFailure=yes" -R ${REVERSE_TUNNEL_PORT}:localhost:${REMOTE_PORT} user@${REMOTE_ADDRESS} -p ${REMOTE_PORT}
fi

