<?php
apf_require_class("APF_DB_Factory");

class Dao_Hospital_Info {

	private $pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("master");
	}

        public function set_hospital_by_yyid($u_yyid, $h_yyid, $data) {
                $data['u_yyid'] = $u_yyid;
                if(isset($data['h_yyid'])) unset($data['h_yyid']);
                if(isset($data['created'])) unset($data['created']);
                if(isset($data['ver'])) unset($data['ver']);
                $data['yyid'] = $h_yyid;

                $sql = "update `t_hospital` set `u_yyid` = :u_yyid, `name` = :name, `grade` = :grade, `tel_num` = :tel_num, `address` = :address, `traffic_guide` = :traffic_guide, `medical_guide` = :medical_guide, `introduction` = :introduction, `hos_dean` = :hos_dean, `annual_income` = :annual_income, `use_rate` = :use_rate, `p_department` = :p_department, `kn_department` = :kn_department, `bed_num` = :bed_num, `operation_num` = :operation_num, `outpatient_num` = :outpatient_num, `loc_code` = :loc_code, `status` = :status, `views` = :views where `yyid` = :yyid ;";
                //Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));
                //Logger::info(__FILE__, __CLASS__, __LINE__, $sql);
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute($data);
        }

        public function add_hospital($u_yyid, $data) {
                $data['u_yyid'] = $u_yyid;
                if(isset($data['h_yyid'])) unset($data['h_yyid']);
                if(isset($data['ver'])) unset($data['ver']);
                $sql = "insert into `t_hospital` (`hid`, `yyid`, `u_yyid`, `name`, `grade`, `tel_num`, `address`, `traffic_guide`, `medical_guide`, `introduction`, `hos_dean`, `annual_income`, `use_rate`, `p_department`, `kn_department`, `active_doctor`, `bed_num`, `operation_num`, `outpatient_num`, `loc_code`, `status`, `views`, `map_long`, `map_lat`, `map_zoom`, `imgs_num`, `created`, `update_date`) values(0, replace(upper(uuid()),'-',''), :u_yyid, :name, :grade, :tel_num, :address, :traffic_guide, :medical_guide, :introduction, :hos_dean, :annual_income, :use_rate, :p_department, :kn_department, 0, :bed_num, :operation_num, :outpatient_num, :loc_code, :status, :views, '0', '0', '15', 0, :created, now());";
                //Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));
                //Logger::info(__FILE__, __CLASS__, __LINE__, $sql);
                $stmt = $this->pdo->prepare($sql);
                $res = $stmt->execute($data);
                /* */
                $last_id = $this->pdo->lastInsertId();
                //Logger::info(__FILE__, __CLASS__, __LINE__, "last_id: $last_id");
                $h_yyid = self::get_hospital_yyid_by_hid($last_id);
                return $h_yyid;
        }

        private function get_hospital_yyid_by_hid($hid) {
                $yyid = '';
                $sql = "select `yyid` from `t_hospital` where `hid` = ? ;";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array("$hid"));
                $yyid = $stmt->fetchColumn();
                if(!empty($yyid) && strlen($yyid) == 32){
                   //$yyid = '';
                }
                else{
                   $yyid = '';
                }
                return $yyid;
        }

        public function get_hospital_sk_by_hyyid($h_yyid) {
                $yyid = '';
                $sql = "select `yyid` from `t_hospital_sk` where `h_yyid` = ? limit 1;";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array("$h_yyid"));
                $yyid = $stmt->fetchColumn();
                return $yyid;
        }

        public function get_hospital_by_yyid($yyid) {
                $sql = "select  `yyid` h_yyid, `name`, `grade`, `tel_num`, `address`, `traffic_guide`, `medical_guide`, `introduction`, `hos_dean`, `annual_income`, `use_rate`, `p_department`, `kn_department`, `bed_num`, `operation_num`, `outpatient_num`, `loc_code`, `views`, status, `created` from `t_hospital` where `yyid` = ? limit 1;";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array("$yyid"));
                //Logger::info(__FILE__, __CLASS__, __LINE__, "h_yyid: $yyid");
                $row = $stmt->fetch();
                if(isset($row['created'])) $row['ver'] = date('ymdHi', $row['created']);
                //Logger::info(__FILE__, __CLASS__, __LINE__, var_export($row, true));
                return $row;
        }

        public function get_hospital($yyid) {
                $sql = "select `yyid`, `u_yyid`, `name`, `grade`, `tel_num`, `address`, `traffic_guide`, `medical_guide`, `introduction`,`hos_dean`, `annual_income`, `use_rate`, `p_department`, `kn_department`, `active_doctor`, `bed_num`, `operation_num`, `outpatient_num`, `spider_url`, `loc_code`, `status`, `views`, `map_long`, `map_lat`, `map_zoom`, `imgs_num`, `created`, `update_date` from `t_hospital` where `yyid` = ? and status = 1 limit 1 ;";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array("$yyid"));
                $row = $stmt->fetch();
                if(isset($row['created'])) $row['created'] = date('y-m-d H:i:s', $row['created']);
                //Logger::info(__FILE__, __CLASS__, __LINE__, var_export($row, true));
                return $row;
        }

        public function add_hospital_sk($u_yyid, $data) {
                $data['u_yyid'] = $u_yyid;
               
                $sql = "insert into `t_hospital_sk` (`hid`, `yyid`, `h_yyid`, `u_yyid`, `name`, `grade`, `tel_num`, `address`, `traffic_guide`, `medical_guide`, `introduction`, `hos_dean`, `annual_income`, `use_rate`, `p_department`, `kn_department`, `active_doctor`, `bed_num`, `operation_num`, `outpatient_num`, `loc_code`, `status`, `views`, `map_long`, `map_lat`, `map_zoom`, `imgs_num`, `ver`, `created`, `update_date`) values(0, replace(upper(uuid()),'-',''), :h_yyid, :u_yyid, :name, :grade, :tel_num, :address, :traffic_guide, :medical_guide, :introduction, :hos_dean, :annual_income, :use_rate, :p_department, :kn_department, 0, :bed_num, :operation_num, :outpatient_num, :loc_code, :status, :views, '0', '0', '15', 0, :ver, :created, now());";
                $s = $stmt = $this->pdo->prepare($sql);
                //Logger::info(__FILE__, __CLASS__, __LINE__, $sql);
                //Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));
                $res = $stmt->execute($data);
                self::add_index_MQ($data['h_yyid'], $data);
                return $res;
        }

        private function add_index_MQ($yyid, $data) {
                $created = $data['created'];
                $res = array(
                    'yyid' => $yyid,
                    'info_type' => 1, // 0为只作记录 1为医院，2为科室，3为医生，4为药口，5为代理人，6为用户，7为交易记录
                    'action' => 1, // 0为只作记录 1为添加或更新，2为删除
                    'status' => 1,
                    'datei' => date('y-m-d', $created),
                    'created' => $created
                );
                $sql = "insert into `t_index_mq` (`id`, `yyid`, `info_type`, `action`, `status`, `datei`, `created`) values(0, :yyid, :info_type, :action, :status, :datei, :created);";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute($res);
        }

        public function get_hospital_list($loc_code, $limit, $offset)
        {
            if(empty($loc_code) || !is_numeric($limit) || !is_numeric($offset)){
                return array();
            }
    
            $sql = "select yyid, name, loc_code, grade, tel_num, address, map_long, map_lat, map_zoom from t_hospital where loc_code LIKE :keyword and status=1 order by hid asc LIMIT :limit OFFSET :offset ;";
            $keyword = "".$loc_code."%";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':keyword', $keyword, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $jobs = $stmt->fetchAll();
            $job = array();
            foreach($jobs as $k=>$j){
              $job[$k] = $j;
            }
    
            return $job;
        }
    
        public function get_hospital_count($loc_code)
        {
            if(empty($loc_code)) {
                return array();
            }
    
            $c = 0;
            $get_count_sql = "select count(*) c from t_hospital where loc_code like ? and status=1;";
            $stmt = $this->pdo->prepare($get_count_sql);
            $stmt->execute(array("$loc_code%"));
            $c = $stmt->fetchColumn();
            return $c;
        }
    
        public function get_location($yyid)
        {
            if(empty($yyid)) {
                return array();
            }
    
            $sql = "select yyid, name, parent_id, type_code, name_code, area_level from t_location where status=1 and yyid = ? limit 1;";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array($yyid));
            $rows = $stmt->fetchAll();
            $row = array();
            foreach($rows as $k=>$v){
              $row = $v;
            }
    
            return $row;
        }


}
