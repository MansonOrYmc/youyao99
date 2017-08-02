#!/bin/bash

while true
do
this_time=`ps -ef |grep grep| sed -n '1p;1q'|awk '{print $5}' `
echo $this_time
last_id1=`ps -ef |grep Mail_Queue |grep -v "grep"| sed -n '1p;1q'|awk '{print $2}' `
last_id2=`ps -ef |grep Mail_Queue |grep -v "grep"| sed -n '2p;2q'|awk '{print $2}' `
last_id3=`ps -ef |grep Mail_Queue |grep -v "grep"| sed -n '3p;3q'|awk '{print $2}' `
last_id4=`ps -ef |grep Mail_Queue |grep -v "grep"| sed -n '4p;4q'|awk '{print $2}' `
last_id5=`ps -ef |grep Mail_Queue |grep -v "grep"| sed -n '5p;5q'|awk '{print $2}' `
last_id6=`ps -ef |grep Mail_Queue |grep -v "grep"| sed -n '6p;6q'|awk '{print $2}' `
last_id7=`ps -ef |grep Mail_Queue |grep -v "grep"| sed -n '7p;7q'|awk '{print $2}' `
last_id8=`ps -ef |grep Mail_Queue |grep -v "grep"| sed -n '8p;8q'|awk '{print $2}' `
last_id9=`ps -ef |grep Mail_Queue |grep -v "grep"| sed -n '9p;9q'|awk '{print $2}' `
last_time=`ps -ef |grep Mail_Queue |grep -v "grep"| sed -n '1p;1q'|awk '{print $5}' `
if [ "$this_time" != "$last_time" ];then
echo "[ != ]"
sudo kill -9 $last_id1 $last_id2 $last_id3 $last_id4 $last_id5 $last_id6 $last_id7 $last_id8 $last_id9
fi
echo $last_time
echo $last_id
  sleep 60
done
