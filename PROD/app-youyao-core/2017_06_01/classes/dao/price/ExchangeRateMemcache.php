<?php
apf_require_class("Dao_Price_ExchangeRate");
apf_require_class("APF_Cache_Factory");

class Dao_Price_ExchangeRateMemcache extends Dao_Price_ExchangeRate {

	public function get_dest_exchange_rate_by_time($dest_id, $time) {
        /* 请看Bll_Price_ExchangeRate的注释
         * 因为缓存按天做比较方便， 所以这里有加了12个小时
         * */
        $key = md5("exchange_by_date_{$dest_id}_".date('Y-m-d', $time + 60*60*12.5));
        $memcache = APF_Cache_Factory::get_instance()->get_memcache();
        $value = $memcache->get($key);

        if (empty($value)) {
            $mem_time = 60*60*24;
            $value = parent::get_dest_exchange_rate_by_time($dest_id, $time);
            $memcache->set($key, $value, 0, $mem_time);
        }

        return $value;

	}
}
