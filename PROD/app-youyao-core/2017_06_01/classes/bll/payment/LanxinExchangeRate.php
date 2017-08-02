<?php
//apf_require_class("Util_MemCacheKey");
//apf_require_class("Util_MemCacheTime");
apf_require_class("APF_Cache_Factory");

class Bll_Payment_LanxinExchangeRate {

	public function get_lanxin_exchangerate() {
		
		$ali_rate = self::cny2usd_rate();
		$taixin_rate = self::usd2twd_rate();
		
		return array(
			'usdcny' => $ali_rate[3],
			'usdtwd' => $taixin_rate,
		);
		
	}

	public function cny2usd_rate() {

		$ali = new Alipay_Global();
		$key = 'cny2usdrate';
		$memcache = APF_Cache_Factory::get_instance()->get_memcache();

		$value = $memcache->get($key);
		$resettime = $value[0].$value[1];
		if(
			!$value || 
			time() >= strtotime($resettime)+60*60*24 
		)
		{
			$time = 60*60*24;
			$value = $ali->cny2usd_rate();
			$memcache->set($key, $value, 0, $time);
		}

		return $value;
	}

	public function usd2twd_rate() {
		
        $taixin = new Taixinbank_ExchangeRate();
		$key = 'usd2twdrate';
		$memcache = APF_Cache_Factory::get_instance()->get_memcache();

        $value = $memcache->get($key);
		if(!$value) {
			$time = 60*60;
        	$value = $taixin->usd2twd_rate();
			$memcache->set($key, $value, 0, $time);
		}

		return $value;

	}

}
