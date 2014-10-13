Create SSH Tunnel
====
>Create an SSH tunnel back to the central server, which can be used to access this host 
>without NAT interference.

>This is not currently capable of handling automatic port assignment, so a web service 
>will be made if we still want that feature.

Provisioning Process
----
>Internal-facing web server provides the initial bootstrapping files (install scripts with
passwords/credentials pre-loaded) to bring the machines online in a local-only network 
environment specifically designated for the provisioning process.

>This works because the edges which are being provisioned can automatically generate an SSH 
key and place it on the master-side, via that initial SSH provisioning key, which is removed 
from the masterside and the edges once the provisioning process is complete.

