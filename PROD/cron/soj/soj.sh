#!/bin/bash

CURR_DAY=`date +%F`
LOG_DIR="/data2/log/soj"

echo $CURR_DAY

mv $LOG_DIR/soj.log $LOG_DIR/soj.log.$CURR_DAY
mv $LOG_DIR/mobile.log $LOG_DIR/mobile.log.$CURR_DAY
mv $LOG_DIR/postfeed.log $LOG_DIR/postfeed.log.$CURR_DAY

cd /var/www/php-v2/PROD/app-youyao-job
/usr/bin/php bin/launcher.php --class=Soj_Collect
/usr/bin/php bin/launcher.php --class=Soj_MobileCollect 
