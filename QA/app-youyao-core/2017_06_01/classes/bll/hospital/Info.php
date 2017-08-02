<?php

class  Bll_Hospital_Info {
	private $hospitalInfoDao;

	public function __construct() {
		$this->hospitalInfoDao = new Dao_Hospital_Info();
	}

        public function set_hospital_by_yyid($u_yyid, $h_yyid, $data) {
                if(empty($u_yyid) || empty($h_yyid)) return array();
                $sk_yyid =  $this->hospitalInfoDao->get_hospital_sk_by_hyyid($h_yyid);
                Logger::info(__FILE__, __CLASS__, __LINE__, "sk_yyid: $sk_yyid");
                Logger::info(__FILE__, __CLASS__, __LINE__, "yyid: $h_yyid");
                if(empty($sk_yyid)){
                    //backup v1 data
                    $backup = $this->hospitalInfoDao->get_hospital_by_yyid($h_yyid); 
                    if(!empty($backup)){
                       Logger::info(__FILE__, __CLASS__, __LINE__, var_export($backup, true));
                       $this->hospitalInfoDao->add_hospital_sk('sysops', $backup);
                    }
                }
                return $this->hospitalInfoDao->set_hospital_by_yyid($u_yyid, $h_yyid, $data);
        }

        public function add_hospital($u_yyid, $data) {
                if(empty($u_yyid)) return array();
                return $this->hospitalInfoDao->add_hospital($u_yyid, $data);
        }

        public function add_hospital_sk($u_yyid, $data) {
                if(empty($u_yyid)) return array();
                return $this->hospitalInfoDao->add_hospital_sk($u_yyid, $data);
        }
        
        public function get_hospital($h_yyid) {
                if(empty($h_yyid)) return array();
                return $this->hospitalInfoDao->get_hospital($h_yyid);
        }

        public function get_hospital_list($loc_code, $limit, $offset)
        {
                if(empty($loc_code) || !is_numeric($limit) || !is_numeric($offset)){
                  return array();
                }
                return $this->hospitalInfoDao->get_hospital_list($loc_code, $limit, $offset);
        }

        public function get_hospital_count($loc_code)
        {
                if(empty($loc_code)) {
                    return array();
                }
                return $this->hospitalInfoDao->get_hospital_count($loc_code);
        }

        public function get_location($yyid)
        {
                if(empty($yyid)) {
                    return array();
                }
                return $this->hospitalInfoDao->get_location($yyid);
        }

}
