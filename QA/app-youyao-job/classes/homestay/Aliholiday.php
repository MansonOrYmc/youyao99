<?php
class Homestay_Aliholiday {

    public function run() {
        $dock_bll = new Bll_homestay_Docking();

        $dock_bll->add_aliholiday(array(395,398,22736,35953), 66);
//        $dock_bll->update_aliholiday_price(array(395,398,22736,35953));
    }

}
