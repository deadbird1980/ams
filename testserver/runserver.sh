#!/bin/sh

HTTPD=/usr/sbin/httpd
PWD=`pwd`
ROOT=`dirname $0 | sed -e "s#^[^/]#$PWD/&#"`
ENV=development
export ENV

case "$1" in
    start)
        "$HTTPD" -f "$ROOT/httpd.conf" -C "DocumentRoot $ROOT/../app" -C "ErrorLog \"$ROOT/error_log\"" -C "CustomLog \"$ROOT/access_log\" combined" -C "PidFile $ROOT/pid" -C "LockFile $ROOT/lock" -C "PHP_Admin_Value include_path \"$ROOT/../\""
        rm -f lock.* pid
        ;;
    stop)
        [ -e "$ROOT/pid" ] && kill -TERM `cat "$ROOT/pid"`
        ;;
esac
