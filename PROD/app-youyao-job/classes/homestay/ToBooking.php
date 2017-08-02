<?php
class Homestay_Aliholiday {

    public function run() {

        $dock_bll = new Bll_homestay_Docking();
        $room_list = $dock_bll->get_room_list_by_channel("booking");
        $dock_bll->add_booking_rates_by_rids($room_list);
    }

}
