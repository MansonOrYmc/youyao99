<?php
class Room_CalendarSync{

    public function run(){
        $calendar_bll = new Bll_Room_CalendarSync();
        $list = $calendar_bll->get_all_calendar_info();
        $opts = getopt('', array( 
                    'thread:'
                ));
        $thread = $opts['thread'];
        foreach($list as $row) {
            if(isset($thread) && $row['id']%10 != $thread ) continue;
            if($row['rid'] && $row['url']) {
                try{
                    $calendar_bll->sync_calendar_to_zzk($row['rid'], $row['url']);
                }catch(Exception $e) {
                    Logger::info(__FILE__, __CLASS__, __LINE__, var_export($e->getMessage(), TRUE));
                }
            }
        }
    }
}
