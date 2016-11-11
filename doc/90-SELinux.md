# <a id="selinux"></a> SELinux

## <a id="selinux-introduction"></a> Introduction

SELinux is a mandatory access control (MAC) system on Linux which adds a fine granular permission system for access to all resources on the system such as files, devices, networks and inter-process communication.

The most important questions are answered briefly in the [FAQ of the SELinux Project](http://selinuxproject.org/page/FAQ). For more details on SELinux and how to actually use and administrate it on your systems have a look at [Red Hat Enterprise Linux 7 - SELinux User's and Administrator's Guide](https://access.redhat.com/documentation/en-US/Red_Hat_Enterprise_Linux/7/html/SELinux_Users_and_Administrators_Guide/index.html). For an simplified (and funny) introduction download the [SELinux Coloring Book](https://github.com/mairin/selinux-coloring-book).

This documentation will use a similar format like the SELinux User's and Administrator's Guide.

### <a id="selinux-policy"></a> Policy

Icinga Web 2 is providing its own SELinux Policy. At the moment it is not upstreamed to the reference policy because it is under development. Target of the development is a policy package for Red Hat Enterprise Linux 7 and its derivates running the targeted policy which confines Icinga Web 2 with support for all its modules. All other distributions will require some tweaks.

The policy for Icinga Web 2 will also require the policy for Icinga 2 which provides access to its interfaces. It covers only the scenario running Icinga Web 2 in Apache HTTP Server with mod_php.

### <a id="selinux-policy-installation"></a> Installation

There are two ways to install the SELinux Policy for Icinga Web 2 on Enterprise Linux 7. Installing it from the provided package which is the preferred option and manual installation if you need some fixes not released yet or for development.

If the system runs in enforcing mode, you can still set icinga2 to run its domain permissve if problems occure, so please make sure to run the system in this mode.

    # sestatus
    SELinux status:                 enabled
    SELinuxfs mount:                /sys/fs/selinux
    SELinux root directory:         /etc/selinux
    Loaded policy name:             targeted
    Current mode:                   enforcing
    Mode from config file:          enforcing
    Policy MLS status:              enabled
    Policy deny_unknown status:     allowed
    Max kernel policy version:      28

You can change the configured mode by editing `/etc/selinux/config` and the current mode by executing `setenforce 0`.

d="selinux-policy-installation-package"></a> Package installation

The packages are not provided yet.

Simply add the selinux subpackage to your installation.

    # yum install icingaweb2-selinux

Verify that http run in its own domain `httpd_t` and icingaweb2 configuration has its own context `icingaweb2_config_t`.

    # ps -eZ | grep http
    system_u:system_r:httpd_t:s0     9785 ?        00:00:00 httpd
    # ls -ldZ /etc/icingaweb2/
    drwxrws---. root icingaweb2 system_u:object_r:icingaweb2_config_t:s0 /etc/icingaweb2/

#### <a id="selinux-policy-installation-manual"></a> Manual installation

This section describes the installation to support development and testing. It assumes that Icinga Web 2 is already installed from packages and running on the system.

As a prerequisite install the `git`, `selinux-policy-devel` and `audit` package. Enable and start the audit daemon afterwards.

    # yum install git selinux-policy-devel audit
    # systemctl enable auditd.service
    # systemctl start auditd.service

After that clone the icingaweb2 git repository.

    # git clone git://git.icinga.org/icingaweb2.git

To create and install the policy package run the installation script which also labels the resources. 

    # cd selinux/
    # ./icingaweb2.sh

Verify that http run in its own domain `httpd_t` and icingaweb2 configuration has its own context `icingaweb2_config_t`.

    # ps -eZ | grep http
    system_u:system_r:httpd_t:s0     9785 ?        00:00:00 httpd
    # ls -ldZ /etc/icingaweb2/
    drwxrws---. root icingaweb2 system_u:object_r:icingaweb2_config_t:s0 /etc/icingaweb2/

### <a id="selinux-policy-general"></a> General

When the SELinux policy package for Icinga Web 2 is installed, it creates its own type of apache content and labels its configuration `icingaweb2_config_t` to allow confining access to it.

### <a id="selinux-policy-types"></a> Types

The configuration is labeled `icingaweb2_config_t` and other services can request access to it by using the interfaces `icingaweb2_read_config` and `icingaweb2_manage_config`.

File requiring read access are labeled `icingaweb2_content_t`, requiring write access `icingaweb2_rw_content_t`.

### <a id="selinux-policy-booleans"></a> Booleans

SELinux is based on the least level of access required for a service to run. Using booleans you can grant more access in a defined way. The Icinga Web 2 policy package provides the following booleans.

**httpd_can_manage_icingaweb2_config**

Having this boolean enabled allows httpd to write to the configuration labeled `icingaweb2_config_t`. This is enabled by default, if not needed you can disable it for more security. This will disable the webbased configuration of Icinga Web 2 including users creating their own navigation items and dashboards.

## <a id="selinux-bugreports"></a> Bugreports

If you experience any problems while running in enforcing mode try to reproduce it in permissive mode. If the problem persists it is not related to SELinux because in permissive mode SELinux will not deny anything.

When filing a bug report please add the following information additionally to the [normal ones](https://www.icinga.org/icinga/faq/):
* Output of `semodule -l | grep -e icinga2 -e icingaweb2 -e nagios -e apache`
* Output of `semanage boolean -l | grep icinga`
* Output of `ps -eZ | grep httpd`
* Output of `audit2allow -li /var/log/audit/audit.log`

If access to a file is blocked and you can tell which one please provided the output of `ls -lZ /path/to/file` (and perhaps the directory above).

If asked for full audit.log add `-w /etc/shadow -p w` to `/etc/audit/rules.d/audit.rules`, restart the audit daemon, reproduce the problem and add `/var/log/audit/audit.log` to the bug report. With the added audit rule it will include the path of files access was denied to.

If asked to provide full audit log with dontaudit rules disabled executed `semodule -DB` before reproducing the problem. After that enable the rules again to prevent auditd spamming your logfile by executing `semodule -B`.
