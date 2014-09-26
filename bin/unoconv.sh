#!/bin/bash
# openoffice.org headless server script
#
# chkconfig: 2345 80 30
# description: headless openoffice server script
# processname: openoffice
#
# Author: Christophe Robert
#
UNOCONV='/usr/lib64/libreoffice/program/soffice.bin'
OPTIONS='--headless --invisible --nocrashreport --nodefault --nologo --nofirststartwizard --norestore --accept=socket,host=127.0.0.1,port=2002;urp;StarOffice.ComponentContext'
ERROR=1
PIDFILE=/var/run/uniconv-server.pid
set -e

start() {
        if [ -f $PIDFILE ] && [ -e /proc/`cat $PIDFILE` ]; then
                echo "OpenOffice headless server has already started."
        else
                if [ -f $PIDFILE ] && [ ! -e /proc/`cat $PIDFILE` ]; then
                        rm -f $PIDFILE
                fi

                $UNOCONV $OPTIONS & > /dev/null 2>&1
                if [ $? = 0 ]; then
                echo $! > $PIDFILE
                fi
                ERROR=$?
        fi
}

stop() {
	if [ -f $PIDFILE ]; then
		echo "Stopping OpenOffice headless server."
		kill -TERM `cat $PIDFILE`
		rm -f $PIDFILE
	fi
}

case "$1" in
	start)
		start
		;;
	stop)
		stop
		;;
	restart|reload)
        	stop
        	start
        ;;
  	*)
        	echo $"Usage: $0 {start|stop|restart|reload}"
        	ERROR=1
	esac
exit $ERROR
