<?php
class Bll_Speedroom_Verify {
    private $speedDateDao;

    public function __construct() {
        $this->speedDateDao = new Dao_SpeedRoom_Date();
    }

	public function verify_speed_date($homestay_info, $checkin, $checkout, $isarray=1) {
		foreach($homestay_info as $row) {
			if($isarray){
				if($row['speed_room'] == 1){
					$nids[] = $row['id'];
				}
			}else{
				if($row->speed_room == 1){
					$nids[] = $row->id;
				}
			}
		}

		$speed_date = $this->speedDateDao->get_speedroom_date_bynids($nids);
        $diffarray =array();
        $is_speed = array();
		if(!empty($speed_date)){
			foreach($speed_date as $r) {
                $diffarray[] = $r['rid'];
				if((strtotime($r['start_date']) <= strtotime($checkin)
				  && strtotime($r['end_date']) >= strtotime($checkout))){
					$is_speed[] = $r['rid'];
				}
			}
		}
        $diff_nids = array_diff($nids,$diffarray);
        $is_speed = array_merge($is_speed,$diff_nids);

        if(!empty($is_speed)) {
			foreach($homestay_info as $k=>$v) {
				if($isarray) {
					if($v['speed_room'] == 1 && in_array($v['id'], $is_speed)) {
						$homestay_info[$k]['speed_room'] = 1;
					}else{
                        $homestay_info[$k]['speed_room'] = 0;
                    }
				} else {
					if($v->speed_room == 1 && in_array($v->id, $is_speed)) {
						$homestay_info[$k]->speed_room = 1;
					}else{
                        $homestay_info[$k]->speed_room = 0;
                    }
				}
			}
		}

		return $homestay_info;
	}
}
