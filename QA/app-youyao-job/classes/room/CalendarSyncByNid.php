<?php
class Room_CalendarSyncByNid{

    public function run(){
        $calendar_bll = new Bll_Room_CalendarSync();
        $opts = getopt('', array(
                    'nid:'
                ));
        $nid = $opts['nid'];
        if(!$nid) return;
        $ical_base = reset($calendar_bll->get_calendar_sync_info_byrid($nid, 1));
        if(empty($ical_base)) return;
        try{
            $calendar_bll->sync_calendar_to_zzk($nid, $ical_base['url']);
        }catch(Exception $e) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($e->getMessage(), TRUE));
        }
    }
}
