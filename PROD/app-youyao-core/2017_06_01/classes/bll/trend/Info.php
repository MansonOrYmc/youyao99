<?php

class  Bll_Trend_Info {
	private $trendInfoDao;

	public function __construct() {
		$this->trendInfoDao = new Dao_Trend_Info();
	}

        public function get_trend_list($date_range, $limit, $offset, $d_name = '', $h_name = '')
        {
                return $this->trendInfoDao->get_trend_list($date_range, $limit, $offset, $d_name, $h_name);
        }

        public function get_trend_count($date_range, $d_name = '', $h_name = '')
        {
                return $this->trendInfoDao->get_trend_count($date_range, $d_name, $h_name);
        }
        public function get_trend_drug_sum($date_range, $drug_name, $h_name = '')
        {
                return $this->trendInfoDao->get_trend_count($date_range, $drug_name, $h_name);
        }

}
