<?php

class Bll_Price_ExchangeRate {
	public function get_dest_exchange_rate_by_time($dest_id, $time) {
        if($dest_id == 12) return 1;
		$dao_exchange_rate = new Dao_Price_ExchangeRateMemcache();
        $time = $time - 60 * 60 * 15.5; 
        //  我们汇率的逻辑是11点去取汇率，
        //  但是到晚上才会更新汇率，
        //  所以订单实际汇率最好减去12小时

		$rate = $dao_exchange_rate->get_dest_exchange_rate_by_time($dest_id, $time);
        if(empty($rate)) {
            $area_bll = new Bll_Area_Area();
            $config = $area_bll->get_dest_config_by_destid($dest_id);
            $rate = $config['exchange_rate'];
        }
		return $rate;
	}
}
