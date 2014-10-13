#!/bin/sh
#
# test to generate/email eth/client data
#
# eth0/1 ipaddress
# mac address
# external ip address
#
# run as a cron job, mail the data 
#
#
# /crowd/ssh_check.sh
#
#	The goal is to have a clientside cron app/script that will check to see if the master
#	side ssh server/process is up/running, and to reset the local ssh/reverse tunnel 
#	as required
#
#	checks the top level/master ssh port - nmap
#	checks the local ssh reverse/tunnel process via the proc tbl
#	checks to see if the masterside tunnel is running
#
#	emails results
#
#	will ultimately check via a master/webservice kind of process to get the 
#	results/data from/for the initial config file for the local port, as well 
#	as the user/masterIP/etc..
#
#	this file would be created on the masterside, during the vm process
#	it would be the exact same for all client servers of a given type
#	-the diff would occur in the config files, where the different vals
#		are maintained for the port/fport/etc...
#
#	in this manner, the overall process could then update the process on a 
#		periodic basis via the secure scp process from the master side, or
#		via the web based/services
#
#
#	simple app/function to check the url/port and see if it's up/running...
#	returns 1/0 (success/failure)
#
#	script handles managing the ssh connection for the backend process
#	 for the thor ssh user - there is no separate "thor" system user
#	 although there could be
#
#	thor - is tied to the /root/.ssh/id_rsa_thor
#	the thor/ssh priv/pub keys are tied to port 29999 on the
#	 vm - digital ocean (yolodo.no-ip.biz p22)
#
#	ssh -i .ssh/id_rsa_thor ihubuser@yolodo.no-ip.biz -p 22
#
#
#
#
#


# MY_SSH_PORT value should not be changed, as it is used by the
#  create-ssh-tunnel install.sh script to set the port as assigned
#  by the master server.
port=MY_SSH_PORT
user=user
##ip=yolodo.no-ip.biz
ip=MY_SERVER_ADDRESS
fport=22
#si="-i .ssh/ip_rsa_thor"

ex_ip=$(/usr/bin/curl -s checkip.dyndns.org | sed -e 's/.*Current IP Address: //' -e 's/<.*$//')

#in_ip=$( { /sbin/ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'; } 2>&1)
#mac=$({ /sbin/ifconfig eth0 | awk '/HWaddr/ {print $5}'; } 2>&1)

wIP=$( /sbin/ip addr | awk '/inet/ && /wlan/{sub(/\/.*$/,"",$2); print $2}'  2>&1)
#eIP=$( {/sbin/ip addr | awk '/inet/ && /eth/{sub(/\/.*$/,"",$2); print $2}'; } 2>&1)
# /sbin/ip -o link | grep ': eth' | awk '/ether/ { print $13}'
# /sbin/ip -o link | grep ': wlan' | awk '/ether/ { print $13}'

mac=$( /sbin/ip -o link | grep ': eth' | awk '/ether/ { print $13}' 2>&1)

I_ip=$( /sbin/ip addr | awk '/inet/ && /eth/{sub(/\/.*$/,"",$2); print $2}' 2>&1)
if [[ ${wIP} ]]; then
	I_ip=$( /sbin/ip addr | awk '/inet/ && /wlan/{sub(/\/.*$/,"",$2); print $2}' 2>&1)
	mac=$({ /sbin/ip -o link | grep ': wlan' | awk '/ether/ { print $13}'; } 2>&1)
fi

#in_ip=$( { /sbin/ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'; } 2>&1)
#mac=$({ /sbin/ifconfig eth0 | awk '/HWaddr/ {print $5}'; } 2>&1)



echo $ex_ip
#echo $in_ip
echo $I_ip
echo $mac

#a="Internal IP::"$in_ip"\n"
a="Internal IP::"$I_ip"\n"
b="External IP::"$ex_ip"\n"
c="Mac::"$mac"\n"
d1=${a}${b}${c}
d="$a $b $c"

dat=$(date)
dat=$dat" - client data" 

g1=$(nmap  "$ip" -p"$fport" -Pn | grep "Host is up")
g2=$(nmap  "$ip" -p"$fport" -Pn | grep "tcp open")
#g2=""

echo "g1 = "$g1
echo "g2 = "$g2
nmap=$(nmap  "$ip" -p"$fport" -Pn)
#$nmap
z='$nmap'
echo $nmap

if [[ ${g1} && ${g2} ]]; then
	echo "working"

	t=${port}":localhost:22 "${user}@${ip}
	echo "tt = "$t

	#pout=$(ps aux | grep  "$t" | grep "$fport")
	pout=$(ps aux | grep -v grep | grep "$t" | grep "$fport")
	

echo "pout = " "$pout"

	if [[ ! -z "$pout" ]]; then
		echo "running"
		echo $pout

		#check to see if the reverse/remote is running
		c=$(ssh  -p "$fport" "$user"@"$ip" netstat -an | grep "$port")
		echo "tested ssh/remote"
      echo "ccc = "$c
      #exit

		if [[ ! -z "$c" ]]; then
			echo "remote running"
			echo $c

			r="complete ssh running +++d1 ok2 \n"

			d="$r $d\n"
			d="$d do  $dat\n"
 
			r="master/client ssh up2 do - "
			dat="$r $dat"


		else
			echo "remote not running"

			t=${port}":localhost:22 "${user}@${ip}
			echo "tt = "$t

			pid=$(ps aux | grep -v grep | grep "$t" | grep "$fport")

			if [[ ! -z "$pid" ]]; then
				echo "kill it"
				echo $pid

				pkill=$(kill -9 "$pid")


				r="killing ssh process2 do \n"
				d="$r do \n $d"


			fi

		fi

	else
		echo "not running"
		# go ahead and run the reverse ssh
		ssh   -f -N -R   "$port":localhost:22  "$user"@"$ip" -p "$fport"
		echo "tried to restart ssh/reverse on client"




	fi
else	
	echo "not working"

	#
	# in the event the eth process on the machine somehow fails..
	# go ahead, kill the ssh/procTBL connection so this will auto 
	# restart
	
	t=${port}":localhost:22 "${user}@${ip}
	echo "tt = "$t

	pid=$(ps aux | grep "$t"  | grep "$fport")

	if [[ ! -z "$pid" ]]; then
		echo "kill it"
		echo $pid

		pkill=$(kill -9 "$pid")

		r="killing the ssh proc do\n"
		d="$r $d\n"
		d="$d   do \n  $dat\n"
 
		dat="master ssh down2 - killing local ssh $ex_ip $dat"
 

	fi

fi

exit
