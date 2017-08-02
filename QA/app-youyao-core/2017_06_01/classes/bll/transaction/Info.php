<?php

class  Bll_Transaction_Info {
	private $transactionInfoDao;

	public function __construct() {
		$this->transactionInfoDao = new Dao_Transaction_Info();
	}

        public function set_transaction_by_yyid($u_yyid, $t_yyid, $data) {
                if(empty($u_yyid) || empty($t_yyid)) return array();
                return $this->transactionInfoDao->set_transaction_by_yyid($u_yyid, $t_yyid, $data);
        }

        public function add_transaction($u_yyid, $data) {
                if(empty($u_yyid)) return array();
                return $this->transactionInfoDao->add_transaction($u_yyid, $data);
        }

        public function get_transaction($t_yyid) {
                if(empty($t_yyid)) return array();
                return $this->transactionInfoDao->get_transaction($t_yyid);
        }

}
