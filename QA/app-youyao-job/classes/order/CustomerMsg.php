<?php
class Order_CustomerMsg
{
	
	private $bll_msg;

    public function run(){
        // 脚本先停了  03/07 2016 leon
    	echo "start\n";
    	$this->bll_msg = new Bll_User_Msg();
    	$bll_stats = new Bll_Stats_JobIndex();
    	$point     = $bll_stats->get_sdata(Util_Job::customer_msg_notify);
    	$start     = json_decode($point['j_data'],true);   
    	$now       = time();
    	$interval_time  = 900;   //每隔执行间隔多少s  需要与contab 保持一致
    	$msg_lists = $this->get_msg_list($start['mid'],$now-2*$interval_time,$now-$interval_time);    
    	$bll_user  = new Bll_User_UserInfo();
    	$bll_order  = new Bll_Order_OrderInfo();
    	$lastid     = 0;
    	foreach ($msg_lists as $key=>$value){
    		$lastid = $value['mid'];
    		$user = $bll_user->get_whole_user_info($value['author']);  
    		if($user['poi_id']>0){ 
    			$msg_info =  $this->get_msgindex_byid($value['mid']);
    			foreach ($msg_info as $k=>$v){
    				if($v['recipient']!=$value['author']){
    					$order  = $bll_order->get_order_list_byguestuid($v['recipient']);  
    					if($order){
    						$this->send_guest_msg($order[0]['guest_telnum'], $user['name']);
    						echo $order['id']."\n";
    					}
    					break;
    				}
    			}
    			
    		}
    	}
    	
    	if($lastid>0){
    		$params = array(
	    		'j_name'=>Util_Job::customer_msg_notify,
	    		'j_data'=>json_encode(array('mid'=>$lastid))
    		);
    		$bll_stats->update_sdata($params);
    	}
    	
    	echo "end\n";
    		
    }
    
    public function get_msg_list($id,$start_time,$end_time){
    	$pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
        $sql = "select * from  drupal_pm_message  where mid>? and timestamp between $start_time and $end_time";    //时间没有索引
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($id));
        return $stmt->fetchAll();
    }
    
    public function get_msgindex_byid($id){
    	$pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
        $sql = "select * from  drupal_pm_index  where mid=? and is_new=1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($id));
        return $stmt->fetchAll();
    }
    
    public function send_guest_msg($phone,$title){
    	$phone = ltrim($phone,'+86');
    	$phone = ltrim($phone,'86');
    	$phone = trim($phone);

        $arr = array('oid'=>0,
    	             'sid'=>0,
    	             'uid'=>0,
    	             'mobile'=>$phone,
    	             'content'=>'【自在客】'.$title.'民宿主人有回复您的私信，赶紧去看看吧,http://m.zizaike.com/user/pmlist',
                     'area'=>1,
                     'retry'=>0);
    	$this->bll_msg->send_msg($arr);	
    }
    
}
