<?php
class Bll_Sale_DispatchCustomer {
     private $dao;
	 public function __construct() {
        $this->dao    = new Dao_Sale_DispatchCustomer();
    }
    
    public function get_next_sale($destid, $phone, $uid, $email){
		$oldsales = APF::get_instance()->get_config('oldsales_dispatch','oldsales');
		$source = 0;
		$invaild = 0;

        if($destid == 12) $destid = 10;

//        $third_channel_uid = array(
//                APF::get_instance()->get_config("uid", "alitrip"),
//            );
//        if(in_array($uid, $third_channel_uid)) {
//		    $customer = $this->find_customer($phone); // 查询之前是否下过单
//        }else{
		    $customer = $this->find_customer($phone, $email); // 查询之前是否下过单
//        }

		if(strpos($email, '@youyao')){ // 邮箱有youyao算作测试情况
			$mid = 12903;
			$source = 1;
		} elseif ($customer['first_admin_uid'] == 12903) { // 之前是韩海燕的客人
			if($customer['first_admin_uid'] == $customer['last_admin_uid']){
				$mid = $oldsales['12903'];
				$invaild = 2;
			}else{
				$mid = $customer['last_admin_uid'];
				$invaild = 1;
			}
			$source = 1;
		}

		/*
		if($uid && empty($mid)){ // 根据 f code 分配 
			$paladin = new Util_TravelFund();
			$friend = $paladin->get_friend_byuid($uid);
			$friend_sale = $friend?$this->find_customer_byuid($friend):array();
			$mid = $friend_sale['first_admin_uid'];
			$source = 2;
		}
		*/

		if(empty($mid)){  // 通过电话/E-mail分配
			$mid = $customer['first_admin_uid'];
			$source = 3;
		}

		if($mid>0){  //获得销售信息
			$sale = $this->dao->get_sale_bymid($mid,$destid);
			if(empty($sale) && $customer['first_admin_uid']!=$customer['last_admin_uid']) { // 谁跟进分配给谁
				$sale = $this->dao->get_sale_bymid($customer['last_admin_uid'],$destid);
				$invaild = 1;
			}

			// 之前考虑到休假的情况，所以制定一个新老销售分配， 现在直接按照新分配
			/*
			if(empty($sale) || $sale['status'] == 0) { // 按老销售-> 新销售配置来分配
				$source = 0;
				//$mid = $oldsales[$customer['first_admin_uid']];
				//$sale = $this->dao->get_sale_bymid($mid,$destid);
				//$invaild = 2;
			}
			*/

		} else {
			$source = 0;
		}

		// 权重分配
        if(empty($sale['status']) && $sale['mid'] != 12903) {
            $schedule_bll = new Bll_Sale_Schedule();
            $on_leave = $schedule_bll->get_on_leave_by_date(); // 去除休假销售
	        $sale =  $this->dao->get_littlest_sale($destid, $on_leave);
            if(empty($sale)) $sale = $this->dao->get_littlest_sale($destid, array()); // 防止一个人都没有匹配到
        }

		$mid = $sale['mid'];
		if($source == 0) {
    			$total_num = $sale['dispatch_real']+1;
    			$rate = $total_num/$sale['dispatch_weight'];
    			$this->dao->set_sale_customer($mid = $sale['mid'],$total_num,$rate);
		}
		if(in_array($destid,array('12'))){
			return array('mid'=>24763,'group'=>'LZ','cid'=>($customer['id']?$customer['id']:0));
		}elseif(in_array($destid,array('15'))){
			return array('mid'=>200444,'group'=>'GMM','cid'=>($customer['id']?$customer['id']:0));
		}



		return array('mid'=>$mid,'group'=>$sale['group_code'],'cid'=>($customer['id']?$customer['id']:0),'invaild'=>$invaild);

    }

	public function find_customer_byphone($phone) {

		$polaris = new Dao_Customer_Info();
		$result = $polaris->get_customer_info_byphone($phone);
		
		return $result;
	}

	private function find_customer ($phone, $email) {
		
		$preg = array(
			'/^\+86/',
			'/^86/',
			'/^0*86/',
		);
		if(strlen($phone)<8){
        	return;
        }
		$phone = trim($phone);
		$email = trim($email);
		$phone = preg_replace($preg, '', $phone);
		$phone = trim($phone);
		$paladin = new Dao_Customer_Info();

		$result = $paladin->get_customer_by_phone_email($phone, $email);
		return $result;
	}

	private function find_customer_byuid ($uid) {
		
		$paradise = new Dao_User_UserInfo();
		$user = $paradise->get_userinfo_by_ids(array($uid));

		if($user[0]['mail']){

			$polaris = new Dao_Customer_Info();
			$result = $polaris->get_customer_info_bymail($user[0]['mail']);

		}
		
		return $result;
	}

    public function set_sale_cus_count($mid,$py,$destid=10){
    	$today_cus_count = $this->dao->get_sale_customercount($mid,$py);
    	$sale_dispatch   = $this->dao->get_sale_bymid($mid,$destid);
    	$real_customer   = $today_cus_count + $sale_dispatch['dispatch_weight']*10;
    	$sale_rate       = $real_customer/$sale_dispatch['dispatch_weight'];
    	$this->dao->set_sale_customer($mid, $real_customer, $sale_rate);
    }

	public function get_all_sales() {
		return $this->dao->get_all_sales();
	}

	public function get_sale_bymid($mid, $dest) {
		return $this->dao->get_sale_bymid($mid, $dest);
	}

	public function get_sales_only_bymid($mid) {
		return $this->dao->get_sales_only_bymid($mid);
	}

	public function insert_into_sales($uid, $name, $group, $dest_id, $weight, $status, $work_num=null) {
		$params = array(
			'mid'              => $uid,
			'sales_name'       => $name,
			'group_code'       => $group,
			'destid'           => $dest_id,
			'dispatch_weight'  => $weight,
			'real_weight_rate' => 10,
			'status'           => $status,
			'last_update'      => time(),
			'work_num'         => $work_num,
		);
		return $this->dao->insert_into_sales($params);
	}

	public function update_sales_dispatch($mid, $dest_id, $work_num, $weight, $status) {
		$update = array(
			'destid' => $dest_id,
			'dispatch_weight' => $weight,
			'status' => $status,
			'last_update' => time(),
			'work_num' => $work_num,
		);

		$condition = array('mid' => $mid);

		return $this->dao->update_sales_dispatch($update, $condition);
	}
	
}
