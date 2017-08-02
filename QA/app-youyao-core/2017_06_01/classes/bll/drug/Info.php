<?php

class  Bll_Drug_Info {
	private $drugInfoDao;

	public function __construct() {
		$this->drugInfoDao = new Dao_drug_Info();
	}

        public function set_drug_by_yyid($u_yyid, $d_yyid, $data) {
                if(empty($u_yyid) || empty($d_yyid)) return array();
                return $this->drugInfoDao->set_drug_by_yyid($u_yyid, $d_yyid, $data);
        }

        public function add_drug($u_yyid, $data) {
                if(empty($u_yyid)) return array();
                return $this->drugInfoDao->add_drug($u_yyid, $data);
        }

        public function get_drug($d_yyid) {
                if(empty($d_yyid)) return array();
                return $this->drugInfoDao->get_drug($d_yyid);
        }

        public function get_drug_list($limit, $offset)
        {
                return $this->drugInfoDao->get_drug_list($limit, $offset);
        }
 
        public function get_drug_count()
        {
                return $this->drugInfoDao->get_drug_count();
        }


}
