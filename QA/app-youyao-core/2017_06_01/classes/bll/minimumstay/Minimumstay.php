<?php
class Bll_Minimumstay_Minimumstay {
    private $MinimumstayDao;

    public function __construct() {
        $this->MinimumstayDao = new Dao_Minimumstay_Minimumstay();
    }

	public function get_is_minimumstay_by_rid($rid){
		$result = $this->MinimumstayDao->get_is_minimumstay_by_rid($rid);
		return $result['minimum_stay'] > 1 ? 1:0;
	}

	public function validate_minimumstay_date_by_rid($rid, $checkin, $checkout){
		$ms_date = $this->MinimumstayDao->get_minimumstay_date_by_rid($rid);
		$validate = 0;
		foreach($ms_date as $k=>$v){
			if(strtotime($v['start_date']) <= strtotime($checkin) && strtotime($checkout) <= strtotime($v['end_date'])) {
				$validate = 1;
				break;
			}else{
				$validate = -1;
			}
		}

		return $validate;
	}

	/*
	 * 返回0代表满足条件； 2: 代表不满足2晚连住条件, 3 etc...
	 */
	public function validateMinStayRequirement($rid, $checkin, $checkout) {
		if (empty($checkin) || empty($checkout)) return 0;

		$result = $this->MinimumstayDao->get_is_minimumstay_by_rid($rid);
		if ($result['minimum_stay'] < 2) {
			return 0;
		}

		$inTime = strtotime($checkin);
		$outTime = strtotime($checkout);
		//首先判断用户连住天数是否大于设定值
		if (($outTime - $inTime) / (24*60*60) >= $result['minimum_stay']) {
			return 0;
		}

		//如果入住天数小于设定要求，判断是否有设定时间段
		$ms_date = $this->MinimumstayDao->get_minimumstay_date_by_rid($rid);
		if (empty($ms_date)) { // 全部时间段都N晚连住
			return $result['minimum_stay'];
		}

		foreach ($ms_date as $k=>$v) {
			$sTime = strtotime($v['start_date']);
			$eTime = strtotime($v['end_date']);
			// 入住或离店时间在设定范围内，就不满足条件
			if ($inTime >= $sTime && $inTime <= $eTime || $outTime > $sTime && $outTime <= $eTime) {
				return $result['minimum_stay'];
			}
		}

		return 0;
	}

	public function get_room_destid_by_nid($nid)
	{
		return $this->MinimumstayDao->get_room_destid_by_nid($nid);
	}
}
