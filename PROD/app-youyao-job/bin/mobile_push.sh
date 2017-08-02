#!/bin/bash
# author Leon
# date 15-09-01
# program ios 极光手机推送 

for (( ; ; ))
do
	count=`ps -ef |grep -v grep | grep "class=mobile_Push" |wc -l`
	if [ 0 == $count ];then
#echo "start"
		cd /home/tonycai/one.zizaike.com.v2/app-zizaike-job/bin; /usr/bin/nohup /usr/local/php/bin/php ./launcher.php --class=mobile_Push > /dev/null &
#		cd /Users/LeonChen/webcode/new.zizaike.com/app-zizaike-job/bin; /usr/local/Cellar/php53/5.3.29/bin/php ./launcher.php --class=mobile_Push > /dev/null
	else

	txphp=`ps -ef |grep -v grep | grep "class=mobile_Push" | /usr/bin/head -n1`
	time_out=`echo $txphp | awk '{print $5}'`
	now=`date +%s`;
	unix_time=`date -d "$time_out" +%s`
	let differ=$now-$unix_time
#echo "differ:"$differ
	fi

	if [ "$differ"1 -gt "701" ];then
		pid=`echo $txphp | awk '{print $2}'`
#		/usr/bin/kill $pid
#echo "pid:"$pid
	fi

#	echo "infinite loops [ hit CTRL+C to stop]"
	sleep 5
done
