<?php
class Test {

    public function run() {

        $dock_bll = new Bll_homestay_Docking();
        $calendar = new Bll_Room_CalendarSync();
        $date = reset($calendar->get_calendar_sync_info_byrid("198209"));
        $calendar->sync_calendar_to_zzk($date['rid'], $date['url']);
//        $room_list = $dock_bll->get_room_list_by_channel("alitravel");
//        $dock_bll->add_rates_by_rids(array(27680,27681));
//        $dock_bll->add_booking_rates_by_rids(array(
//198796
//));
    }

}
