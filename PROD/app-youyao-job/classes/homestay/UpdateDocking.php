<?php
class Homestay_UpdateDocking {

    public function run() {
        $dock_bll = new Bll_homestay_Docking();
        $list = $dock_bll->get_all_active_list();
        $rids = array();
        foreach($list as $row) {
            $uids[] = $row['uid'];
            $rids = array_merge(
                    array_values($rids),
                    explode(',', $row['rids'])
                );
        }

        try{
            $dock_bll->add_homestay_byuids($uids);
            $dock_bll->add_room_type_by_rids($rids);
            $dock_bll->add_rates_by_rids($rids);
        } catch(Exception $e) {
            print_r($e->getMessage());
        }

    }
}
