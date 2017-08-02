<?php
apf_require_class("APF_DB_Factory");

class Dao_Transaction_Info {

	private $pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("master");
	}

        public function set_transaction_by_yyid($u_yyid, $t_yyid, $data) {
                if(isset($data['id'])) unset($data['id']);
                if(isset($data['yyid'])) unset($data['yyid']);
                if(isset($data['t_yyid'])) unset($data['t_yyid']);
                if(isset($data['created'])) unset($data['created']);
                if(isset($data['update_date'])) unset($data['update_date']);
                $data['u_yyid'] = $u_yyid;
                $data['yyid'] = $t_yyid;

                $sql = "update `t_transaction` set `u_yyid` = :u_yyid, `h_yyid` = :h_yyid, `d_yyid` = :d_yyid, `datei` = :datei, `tid` = :tid, `name` = :name, `specs` = :specs, `t_num` = :t_num, `buyer_hospital` = :buyer_hospital, `trans_date` = :trans_date, `hospital_id` = :hospital_id, `product_id` = :product_id, `batch_number` = :batch_number, `email` = :email, `version` = :version, `status` = :status, `client_ip` = :client_ip where `yyid` = :yyid ;";

                Logger::info(__FILE__, __CLASS__, __LINE__, "sql: $sql");
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));

                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute($data);
        }

        private function get_transaction_yyid_by_id($id) {
                $sql = "select `yyid` from `t_transaction` where `id` = ? ;";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array("$id"));
                $yyid = $stmt->fetchColumn();
                if(!empty($yyid) && strlen($yyid) == 32){
                   //$yyid = '';
                }
                else{
                   $yyid = '';
                }
                return $yyid;
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute($data);
        }

        public function add_transaction($u_yyid, $data) {
                if(isset($data['id'])) unset($data['id']);
                if(isset($data['yyid'])) unset($data['yyid']);
                if(isset($data['t_yyid'])) unset($data['t_yyid']);
                if(isset($data['update_date'])) unset($data['update_date']);
                $data['u_yyid'] = $u_yyid;
               $sql = "insert into `t_transaction` (`id`, `yyid`, `u_yyid`, `h_yyid`, `d_yyid`, `datei`, `tid`, `name`, `specs`, `t_num`, `buyer_hospital`, `trans_date`, `hospital_id`, `product_id`, `batch_number`, `email`, `version`, `status`, `client_ip`, `created`, `update_date`) values(0, replace(upper(uuid()),'-',''), :u_yyid, :h_yyid, :d_yyid, :datei, :tid, :name, :specs, :t_num, :buyer_hospital, :trans_date, :hospital_id, :product_id, :batch_number, :email, :version, :status, :client_ip, :created, now());";
                $stmt = $this->pdo->prepare($sql);
                Logger::info(__FILE__, __CLASS__, __LINE__, "sql: $sql");
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));
                $res = $stmt->execute($data);
                /* */
                $last_id = $this->pdo->lastInsertId();
                //Logger::info(__FILE__, __CLASS__, __LINE__, "last_id: $last_id");
                $t_yyid = self::get_transaction_yyid_by_id($last_id);
                return $t_yyid;
        }
/*
        private function get_transaction_yyid_by_id($id) {
                $sql = "select `yyid` from `t_transaction` where `id` = ? ;";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array("$id"));
                $yyid = $stmt->fetchColumn();
                if(!empty($yyid) && strlen($yyid) == 32){
                   //$yyid = '';
                }
                else{
                   $yyid = '';
                }
                return $yyid;
        }
*/


        public function get_transaction($yyid) {
                $sql = "select `yyid`, `u_yyid`, `h_yyid`, `d_yyid`, `datei`, `tid`, `name`, `specs`, `t_num`, `buyer_hospital`, `trans_date`, `hospital_id`, `product_id`, `batch_number`, `version`, `status`, `client_ip`, `created`, `update_date` from `t_transaction` where `yyid` = ? and status = 1 limit 1;";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array("$yyid"));
                $row = $stmt->fetch();
                if(isset($row['created'])) $row['created'] = date('y-m-d H:i:s', $row['created']);
                //Logger::info(__FILE__, __CLASS__, __LINE__, var_export($row, true));
                return $row;
        }




}
