#!/bin/bash
#获取当前脚本所在路径
currdir=$(cd "$(dirname "$0")"; pwd)

#进入脚本所在目录
echo $0
echo $1
cd $currdir

function check(){
    count=`ps -ef |grep $1 |grep -v "grep" |wc -l`
    #echo $count
    if [ 0 == $count ];then
        nohup php /home/tonycai/one.zizaike.com.v2/app-zizaike-job/bin/launcher.php --class=Mail_Queue 1 &
        nohup php /home/tonycai/one.zizaike.com.v2/app-zizaike-job/bin/launcher.php --class=Mail_Queue 2 &
        nohup php /home/tonycai/one.zizaike.com.v2/app-zizaike-job/bin/launcher.php --class=Mail_Queue 3 &
        nohup php /home/tonycai/one.zizaike.com.v2/app-zizaike-job/bin/launcher.php --class=Mail_Queue 4 &
        nohup php /home/tonycai/one.zizaike.com.v2/app-zizaike-job/bin/launcher.php --class=Mail_Queue 5 &
        nohup php /home/tonycai/one.zizaike.com.v2/app-zizaike-job/bin/launcher.php --class=Mail_Queue 6 &
        nohup php /home/tonycai/one.zizaike.com.v2/app-zizaike-job/bin/launcher.php --class=Mail_Queue 7 &
        nohup php /home/tonycai/one.zizaike.com.v2/app-zizaike-job/bin/launcher.php --class=Mail_Queue 8 &
        nohup php /home/tonycai/one.zizaike.com.v2/app-zizaike-job/bin/launcher.php --class=Mail_Queue 9 &
        nohup php /home/tonycai/one.zizaike.com.v2/app-zizaike-job/bin/launcher.php --class=Mail_Queue 10 &
    fi
}

while true
do
  check Mail_Queue
  sleep 1
done
