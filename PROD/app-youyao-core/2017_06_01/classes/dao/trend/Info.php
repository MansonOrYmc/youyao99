<?php
apf_require_class("APF_DB_Factory");

class Dao_Trend_Info {

	private $pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("master");
	}
        
        public function get_trend_list($date_range, $limit, $offset, $d_name, $h_name)
        {
            $search = "";
            $search1 = "";
            if(!empty($d_name)){
              $r = explode(' ', $d_name);
              $d_name = isset($r[0]) ? $r[0] : $d_name;
              $search = "and name like '%$d_name%'";
            }

            if(!empty($h_name)){
              $r = explode(' ', $h_name);
              $h_name = isset($r[0]) ? $r[0] : $h_name;
              $search1 = "and buyer_hospital like '%$h_name%'";
            }
            $condition = "";
            switch ($date_range) {
                case 0:
                    $condition = "";
                    break;
                case 1:
                    $condition = "and substr(trans_date, 1,7) = substr(CURRENT_DATE(), 1,7)";
                    break;
                case 2:
                    $condition = "and substr(trans_date, 1,7) = substr(CURRENT_DATE() - INTERVAL 1 MONTH, 1,7)";
                    break;
                case 3:
                    $condition = "and substr(trans_date, 1,4) = substr(CURRENT_DATE(), 1,4)";
                    break;
            }

            $sql = "select name, t_num, trans_date, buyer_hospital from t_transaction where email like '%@leapfrogchina.com' and name!='' and t_num > 0 and status=1 $search $search1 $condition order by trans_date desc LIMIT :limit OFFSET :offset  ;";
            //Logger::info(__FILE__, __CLASS__, __LINE__, var_export($backup, true));
            Logger::info(__FILE__, __CLASS__, __LINE__, $sql);
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $jobs = $stmt->fetchAll();
            $job = array();
            foreach($jobs as $k=>$j){
              $r = explode('  ', $j['name']);
              $j['name'] = isset($r[0]) ? $r[0] : $j['name'];
              $j['specs'] = isset($r[1]) ? $r[1] : '';
              $job[$k] = $j;
            }

            return $job;
        }

        public function get_trend_count($date_range, $d_name, $h_name)
        {
            $c = 0;
            $search = "";
            $search1 = "";
            if(!empty($d_name)){
              $r = explode(' ', $d_name);
              $d_name = isset($r[0]) ? $r[0] : $d_name;
              $search = "and name like '%$d_name%'";
            }

            if(!empty($h_name)){
              $r = explode(' ', $h_name);
              $h_name = isset($r[0]) ? $r[0] : $h_name;
              $search1 = "and buyer_hospital like '%$h_name%'";
            }

            $condition = "";
            switch ($date_range) {
                case 0:
                    $condition = "";
                    break;
                case 1:
                    $condition = "and substr(trans_date, 1,7) = substr(CURRENT_DATE(), 1,7)";
                    break;
                case 2:
                    $condition = "and substr(trans_date, 1,7) = substr(CURRENT_DATE() - INTERVAL 1 MONTH, 1,7)";
                    break;
                case 3:
                    $condition = "and substr(trans_date, 1,4) = substr(CURRENT_DATE(), 1,4)";
                    break;
            }
            $get_count_sql = "select count(*) c from t_transaction where email like '%@leapfrogchina.com' and name!='' and t_num > 0 and status=1 $search $search1 $condition ;";
            Logger::info(__FILE__, __CLASS__, __LINE__, $get_count_sql);
            $stmt = $this->pdo->prepare($get_count_sql);
            $stmt->execute();
            $c = $stmt->fetchColumn();
            return $c;
        }

        public function get_trend_drug_sum($date_range, $drug_name, $h_name)
        {
            $c = 0;
            $search = "";
            $search1 = "";
            $r = explode(' ', $drug_name);
            $drug_name = isset($r[0]) ? $r[0] : $drug_name;
            if(!empty($drug_name)){
              $search = "and name like '%$drug_name%'";
            }
            else{
              return $c;
            }

            if(!empty($h_name)){
              $r = explode(' ', $h_name);
              $h_name = isset($r[0]) ? $r[0] : $h_name;
              $search1 = "and buyer_hospital like '%$h_name%'";
            }

            $condition = "";
            switch ($date_range) {
                case 0:
                    $condition = "";
                    break;
                case 1:
                    $condition = "and substr(trans_date, 1,7) = substr(CURRENT_DATE(), 1,7)";
                    break;
                case 2:
                    $condition = "and substr(trans_date, 1,7) = substr(CURRENT_DATE() - INTERVAL 1 MONTH, 1,7)";
                    break;
                case 3:
                    $condition = "and substr(trans_date, 1,4) = substr(CURRENT_DATE(), 1,4)";
                    break;
            }
            $get_count_sql = "select sum(t_num) c from t_transaction where email like '%@leapfrogchina.com' and name!='' and t_num > 0 and status=1 $search $search1 $condition ;";
            Logger::info(__FILE__, __CLASS__, __LINE__, $get_count_sql);
            $stmt = $this->pdo->prepare($get_count_sql);
            $stmt->execute();
            $c = $stmt->fetchColumn();
            return $c;
        }



}
