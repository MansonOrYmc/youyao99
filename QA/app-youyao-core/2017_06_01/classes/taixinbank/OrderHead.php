<?php

class Taixinbank_OrderHead {

	private $companycode;
	private $order;
	private $taixindao;
	public function __construct() {
		$this->companycode = 8577;
		$this->taixindao = new Dao_Taixinbank_Info();
	}

	public function get_virtual_accounts_byoid($params) {

		$params['account_type'] = empty($params['account_type']) ? 1 : $params['account_type'];
		$recordaccount = self::get_code_byrecord($params);
		if(!empty($recordaccount)) {
			return $recordaccount;
		}
		$companycode = $this->companycode;
		$orderbll = new Bll_Order_OrderInfo();
		$order = $orderbll->get_order_info_byid($params['oid']);

		if($params['account_type']==2){
			$refundbll = new Bll_Refund_RefundInfo();
			$refundinfo = $refundbll->get_refund_info_by_oid($order['id']);
			$timecode = self::get_time_code_bycreatetime(strtotime($refundinfo['update_date']), $params['account_type']);
			$time_out = date('Y-m-d', strtotime($refundinfo['update_date'])+60*60*24*13);
		}else{
			$timecode = self::get_time_code_bycreatetime(strtotime($order['update_date']), $params['account_type']);
			$time_out = date('Y-m-d', strtotime($order['update_date'])+60*60*24*1);
		}
//		$timecode = self::get_time_code_bycreatetime(strtotime($order['update_date']), $params['account_type']);
		$code = $companycode.str_pad(substr($order['id'],-5), 0, 5, STR_PAD_LEFT).$timecode;

		$code = $code.self::get_verify_code($code, $params['price']);
		if(strlen($code)==14){ 
			self::insert_account_record($code, $params, $time_out);
			return array('code'=>$code,'time_out'=>$time_out);
		}

	}

    public function get_virtual_accounts($oid, $price, $type, $time) {
        $recordaccount = self::get_code_byrecord(array('oid' => $oid, 'account_type' => $type));
		if(!empty($recordaccount)) {
			return $recordaccount;
		}
		$companycode = $this->companycode;
        $timecode = self::get_time_code_bycreatetime($time, $type);
        $time_out = date('Y-m-d', $time + 60*60*6);

        $code = $companycode.str_pad(substr($oid,-5), 0, 5, STR_PAD_LEFT).$timecode;

        $code = $code.self::get_verify_code($code, $price);
        if(strlen($code)==14){
            self::insert_account_record($code, array('oid'=>$oid, 'account_type'=> $type, 'price'=> $price), $time_out);
            return array('code'=>$code,'time_out'=>$time_out);
        }
    }

	private function get_code_byrecord($params) {
		$record = $this->taixindao->get_code_byrecord($params['oid'], $params['account_type']);
		if($params['account_type'] == 2) {
			if($record['price'] == $params['price'] && (time()+60*60*24*7) < strtotime($record['time_out'])) {
				return array(
					'code' => $record['account'],
					'time_out' => $record['time_out']
				);
			}else{
				return;
			}
		}else{
			if($record['price'] == $params['price'] && time() < strtotime($record['time_out'])) {
				return array(
					'code' => $record['account'],
					'time_out' => $record['time_out']
				);
			}else{
				return;
			}
		}
	}

	private function insert_account_record($code, $params, $update) {
		return $this->taixindao->insert_account_record($code, $params, $update);
	}

	private function get_time_code_bycreatetime($createtime, $account_type=1) {

		if($account_type==2){
			$deadline = 14; //民宿退款允许14天
		}else{
			$deadline = 2; //付款时限加了2天
		}
        if($deadline) $createtime += $deadline*24*60*60;
		$year = (((int)date("Y", $createtime))-1911)%100; //民国纪年最后一位
		$days = str_pad(date("z", $createtime), 3 ,'0', STR_PAD_LEFT); 
		return $year.$days;

	}

	private function get_verify_code($number, $price) {
		$a=0; $b=0; $c=0;
        $even=true;
        for($idx=strlen($number)-1;$idx>=0;$idx--) {
            $c=substr($number, $idx, 1);
            if($even) { $a+=($c*3); $even=false;} else { $b+=(int)$c; $even=true;}
        }
        $c=0;
        $cc=array(5, 4, 3, 2, 3, 4, 5);
        for($idx=strlen($price)-1;$idx>=0;$idx--) {$c+=substr($price, $idx, 1)*$cc[((strlen($price)-$idx)-1)];}
        $d=$a+$b+$c;
        $d1=substr($d, strlen($d)-1);
        $d2=abs((((int)$d1)-10));
        if($d2==10) {$d2=0;}
        return $d2;
	}
}
