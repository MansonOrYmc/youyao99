<?php
apf_require_class("APF_DB_Factory");

class Dao_Sale_DispatchCustomer {

	public function get_littlest_sale($destid, $on_leave) {
        if(empty($destid)){$destid=10;}
        $pdo_value = array($destid);
        $mid_where = '';
        foreach($on_leave as $row) {
            $mid_where .= " and mid != ?";
            $pdo_value[] = $row;
        } 
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$rand = rand(1,1000);
		$sql = "select * from t_sales_dispatch_customer where $rand =$rand and status = 1 and destid = ? $mid_where order by real_weight_rate asc, dispatch_weight desc , rand() desc limit 1";
		$stmt = $pdo->prepare($sql);
		$stmt->execute($pdo_value);
		return $stmt->fetch();
	}
	
	public function get_sale_bymid($mid,$destid=10) {
        if(empty($destid)){$destid=10;}
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "select * from t_sales_dispatch_customer where mid = ? and destid = '$destid' ";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($mid));
		return $stmt->fetch();
	}

	public function get_sales_only_bymid($mid) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "select * from t_sales_dispatch_customer where mid = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($mid));
		return $stmt->fetch();
	}
	
	public function set_sale_customer($mid,$cus_number,$rate) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
// rate 通过数据库自己计算 
		$sql = "update t_sales_dispatch_customer SET dispatch_real = ?, real_weight_rate = dispatch_real/dispatch_weight , last_update = ? WHERE mid = ?";
		$stmt = $pdo->prepare($sql);
		return $stmt->execute(array($cus_number, time(), $mid));
	}
	
	public function get_sale_customercount($mid,$py) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "select count(*) from t_customer where create_time between ".strtotime(date('Y-m-d'))." and ".strtotime(date('Y-m-d',strtotime('+1 day'))).
		" and first_admin_uid=? and sales_flag=? and campaign_code not like 'bee_%'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($mid,$py));
		return $stmt->fetchColumn();	
	}

	public function get_all_sales() {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "select * from t_sales_dispatch_customer where destid > 0 order by destid asc,status desc";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();

	}

	public function insert_into_sales($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "";
		foreach($params as $k=>$v) {
			if($v===null) continue;
			$key[] = $k;
			$insertValue[] = $v;
		}

		$placeHolder = Util_Common::placeholders("?", sizeof($insertValue), ",");

		$sql = "insert into t_sales_dispatch_customer (".implode(",", $key).") values ($placeHolder)";
//print_r($sql);
//print_r($insertValue);
		$stmt = $pdo->prepare($sql);
		return $stmt->execute($insertValue);
	}

	public function update_sales_dispatch($fields, $condition) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sqlValue = array();
		foreach($fields as $key=>$val) {
			$questMark[] = " {$key} = ?";
			$sqlValue[] = $val;
		}

		foreach($condition as $key=>$val) {
			$conditionMark[] = " {$key} = ?";
			$sqlValue[] = $val;
		}

		$sql = "update t_sales_dispatch_customer set " . implode(",", $questMark) . " where " . implode(",", $conditionMark);
//print_r($sql);
//print_r($sqlValue);
		$stmt = $pdo->prepare($sql);
		$stmt->execute($sqlValue);
	}

}
