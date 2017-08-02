<?php
apf_require_class("APF_DB_Factory");

class Dao_Drug_Info {

	private $pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("master");
	}

        public function set_drug_by_yyid($u_yyid, $d_yyid, $data) {
                if(isset($data['id'])) unset($data['id']);
                if(isset($data['yyid'])) unset($data['yyid']);
                if(isset($data['d_yyid'])) unset($data['d_yyid']);
                if(isset($data['created'])) unset($data['created']);
                if(isset($data['update_date'])) unset($data['update_date']);
                $data['u_yyid'] = $u_yyid;
                $data['yyid'] = $d_yyid;

                $sql = "update `t_drug` set `u_yyid` = :u_yyid, `name` = :name, `c_name` = :c_name, `e_name` = :e_name, `py_name` = :py_name, `type` = :type, `approval_id` = :approval_id, `specs` = :specs, `dosage_form` = :dosage_form, `indication` = :indication, `ingredients` = :ingredients, `shape` = :shape, `usage` = :usage, `adverse_reaction` = :adverse_reaction, `taboo` = :taboo, `attentions` = :attentions, `attentions_pw` = :attentions_pw, `attentions_ch` = :attentions_ch, `attentions_oa` = :attentions_oa, `overdose` = :overdose, `chinical_trial` = :chinical_trial, `toxicology` = :toxicology, `pharmacokinetics` = :pharmacokinetics, `storage_conditions` = :storage_conditions, `package` = :package, `period_validity` = :period_validity, `performance_standards` = :performance_standards, `price` = :price, `preparation` = :preparation, `adaptation_department` = :adaptation_department, `therapeutic_field` = :therapeutic_field, `product_advantage` = :product_advantage, `channel` = :channel, `business_type` = :business_type, `medical_insurance` = :medical_insurance, `competitive_products` = :competitive_products, `manufacturer` = :manufacturer, `l_info` = :l_info, `v_info` = :v_info, `imgs_num` = :imgs_num, `status` = :status where `yyid` = :yyid ;";
                //Logger::info(__FILE__, __CLASS__, __LINE__, var_export($data, true));
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute($data);
        }

        public function add_drug($u_yyid, $data) {
                if(isset($data['id'])) unset($data['id']);
                if(isset($data['yyid'])) unset($data['yyid']);
                if(isset($data['d_yyid'])) unset($data['d_yyid']);
                if(isset($data['update_date'])) unset($data['update_date']);
                $data['u_yyid'] = $u_yyid;
                $sql = "insert into `t_drug` (`id`, `yyid`, `u_yyid`, `name`, `c_name`, `e_name`, `py_name`, `type`, `approval_id`, `specs`, `dosage_form`, `indication`, `ingredients`, `shape`, `usage`, `adverse_reaction`, `taboo`, `attentions`, `attentions_pw`, `attentions_ch`, `attentions_oa`, `overdose`, `chinical_trial`, `toxicology`, `pharmacokinetics`, `storage_conditions`, `package`, `period_validity`, `performance_standards`, `price`, `preparation`, `adaptation_department`, `therapeutic_field`, `product_advantage`, `channel`, `business_type`, `medical_insurance`, `competitive_products`, `manufacturer`, `l_info`, `v_info`, `imgs_num`, `status`, `created`, `update_date`) values(0, replace(upper(uuid()),'-',''), :u_yyid, :name, :c_name, :e_name, :py_name, :type, :approval_id, :specs, :dosage_form, :indication, :ingredients, :shape, :usage, :adverse_reaction, :taboo, :attentions, :attentions_pw, :attentions_ch, :attentions_oa, :overdose, :chinical_trial, :toxicology, :pharmacokinetics, :storage_conditions, :package, :period_validity, :performance_standards, :price, :preparation, :adaptation_department, :therapeutic_field, :product_advantage, :channel, :business_type, :medical_insurance, :competitive_products, :manufacturer, :l_info, :v_info, :imgs_num, :status, :created, now());";
                $stmt = $this->pdo->prepare($sql);
                $res = $stmt->execute($data);
                /* */
                $last_id = $this->pdo->lastInsertId();
                //Logger::info(__FILE__, __CLASS__, __LINE__, "last_id: $last_id");
                $d_yyid = self::get_drug_yyid_by_id($last_id);
                return $d_yyid;
        }

        private function get_drug_yyid_by_id($id) {
                $sql = "select `yyid` from `t_drug` where `id` = ? ;";
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


        public function get_drug($yyid) {
                $sql = "select `yyid`, `u_yyid`, `name`, `c_name`, `e_name`, `py_name`, `type`, `approval_id`, `specs`, `dosage_form`, `indication`, `ingredients`, `shape`, `usage`, `adverse_reaction`, `taboo`, `attentions`, `attentions_pw`, `attentions_ch`, `attentions_oa`, `overdose`, `chinical_trial`, `toxicology`, `pharmacokinetics`, `storage_conditions`, `package`, `period_validity`, `performance_standards`, `price`, `preparation`, `adaptation_department`, `therapeutic_field`, `product_advantage`, `channel`, `business_type`, `medical_insurance`, `competitive_products`, `manufacturer`, `l_info`, `v_info`, `imgs_num`, `status`, `created`, `update_date` from `t_drug` where `yyid` = ?  and status = 1 limit 1;";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array("$yyid"));
                $row = $stmt->fetch();
                if(isset($row['created'])) $row['created'] = date('y-m-d H:i:s', $row['created']);
                //Logger::info(__FILE__, __CLASS__, __LINE__, var_export($row, true));
                return $row;
        }

        public function get_drug_list($limit, $offset)
        {
            $sql = "select `yyid`, `name`, `c_name`, `e_name`, `py_name`, `type`, `approval_id`, `specs`, `dosage_form`, `indication`, `ingredients`, `shape`, `usage`, `adverse_reaction`, `taboo`, `manufacturer`, `imgs_num`, `status`, `created`, `update_date` from t_drug where status = 1  order by id desc LIMIT :limit OFFSET :offset ;";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $jobs = $stmt->fetchAll();
            $job = array();
            foreach($jobs as $k=>$j){
              $j['created'] = isset($j['created']) ? date('Y-m-d H:i:s', $j['created']) : '';
              $job[$k] = $j;
            }
    
            return $job;
        }
    
        public function get_drug_count()
        {
            $c = 0;
            $get_count_sql = "select count(*) c from t_drug where status=1;";
            $stmt = $this->pdo->prepare($get_count_sql);
            $stmt->execute();
            $c = $stmt->fetchColumn();
            return $c;
        }

}
