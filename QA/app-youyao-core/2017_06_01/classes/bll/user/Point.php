<?php

class Bll_User_Point {
	public function __construct() {
		$this->dao = new Dao_User_Point();
	}

	public function get_total_available_point($uid) {
		$income = $this->dao->get_total_income_point($uid);
		$outgo = $this->dao->get_total_outgo_point($uid);
		return number_format($income - $outgo, 2, '.', '');
	}

    public function get_total_point_by_type($uid, $type) {
        if(empty($uid) || empty($type)) return false;
        return $this->dao->get_total_point_by_type($uid, $type);
    }

	public function get_available_point_detail($uid, $limit, $offset) {
		return $this->dao->get_available_point_detail($uid, $limit, $offset);
	}

	public function add_point_use_log($uid, $point_value, $order_id,$remark) {
		return $this->dao->add_point_use_log($uid, $point_value, $order_id,$remark);
	}

	public function check_share_activity_point_source($uid) {
		$count = $this->dao->count_point_source($uid, '1111_share');
		if ($count <= 4) {
			return TRUE;
		}
		return FALSE;
	}

	public function check_share_activity_today_point_source($uid) {

		$count = $this->dao->count_point_source_by_date($uid, '1111_share', date('Y-m-d'));
		if ($count == 0) {
			return TRUE;
		}
		return FALSE;
	}

	public function add_share_activity_user_point($uid) {
		return $this->dao->add_user_point($uid, 5, '双11分享送积分', '1111_share', strtotime('+1 years'));
	}

	public function add_lottery_activity_user_point($uid, $point) {
		return $this->dao->add_user_point($uid, $point, '双11转盘积分', '1111_lottery', strtotime('+1 years'));
	}

    public function get_use_point_by_order($uid, $order) {
        return $this->dao->get_use_point_by_order($uid, $order);
    }
}
