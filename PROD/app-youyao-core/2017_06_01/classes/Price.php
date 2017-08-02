<?php
class Price {

    /* 价格转换
     * @from_dest 原始币种，默认人民币
     * @to_dest 目标币种，默认通过货币方法取
     * @time 由于每天汇率都会变动，加上时间方便取当时的汇率
     * */
	public static function c($price, $from_dest=null, $time=0, $to_dest=null) {

        // 默认价格源是rmb
        if(!$from_dest) {
            $from_dest = 12;
        }
        //默认转换为
        if(!$to_dest) {
            $to_dest = Util_Currency::get_cy_id();
        }

        // 有时间需要取出当时的汇率
        if($time > 0) {
            $bll_exchange_rate = new Bll_Price_ExchangeRate();
            $row_to['exchange_rate']   = $bll_exchange_rate->get_dest_exchange_rate_by_time($to_dest, $time);
            $row_from['exchange_rate'] = $bll_exchange_rate->get_dest_exchange_rate_by_time($from_dest, $time);
        }
        // 没时间取当前汇率
        else{
            $bll_area_info = new Bll_Area_Area();
            $row_to   = $bll_area_info->get_dest_config_by_destid($to_dest);
            $row_from = $bll_area_info->get_dest_config_by_destid($from_dest);
        }

        $money_rate = $row_to['exchange_rate']/$row_from['exchange_rate'];
        if(is_numeric($price)) {
            $price = round($price*$money_rate,0);
        }
        $price = (int)$price; // 最低1元

        return $price;
	}

    /*  将价格包装用户所见的文字， 
     *  @price_from xx起
     *  @use_symbol $1234
     * */
    public static function tostr($price, $price_from=false, $use_symbol=true) {
        $price_text = $price;
        if($use_symbol) {
            $price_text = Trans::t('%p_price_symbol', Util_Currency::get_cy_id(), array('%p' => $price));
        }
        if($price_from) {
            $price_text = Trans::t('price_from_%p', Util_Language::get_locale_id(), array('%p' => $price_text));
        }
        return $price_text;
    }

    /* 通过房间、入住、退房日期获得房价明细
     * 兼容多货币
     * 加人为超过就加，加床需要用户选择（现在还没有加床逻辑）
     * */
    public static function get_order_price($nid, $in, $out, $pax, $room_num = 1, $add_bed_num = 0){
    }

}
