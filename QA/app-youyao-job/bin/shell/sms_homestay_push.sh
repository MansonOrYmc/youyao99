#!/bin/bash
function check(){
    count=`ps -ef |grep "Sms_HenxinSend homestay" |grep -v "grep" |wc -l`
    #echo $count
    if [ 0 == $count ];then
        cd /var/www/php-v2/PROD/app-zizaike-job
        nohup  php bin/launcher.php --class=Sms_HenxinSend homestay >> /tmp/sms.log &
    fi
}

while true
do
  check
  sleep 2
done
