<?php
apf_require_class("APF_Controller");

class My_LoginController extends APF_Controller{
	
    public function handle_request(){    


    exit("hello");
         
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select nid,uid,client_ip,guest_date,guest_checkout_date from t_homestay_booking where create_time 
        between UNIX_TIMESTAMP('2014-10-16') and UNIX_TIMESTAMP('2014-10-17')  order by client_ip desc ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($phone));
        $recods =  $stmt->fetchAll();
       
        
       foreach ($recods as $key=> $value) {
            $uids[] = $value['uid'];   //房间id
       }
       
   
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select uid,loc_typecode from t_weibo_poi_tw where uid in (".implode(',',$uids).")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $recods_tws =  $stmt->fetchAll();   //民宿区域板块
       
        
        foreach ($recods_tws as $key=> $value) {
            $loclids[$value['uid']] = $value;
        }
       
       
       
       foreach ($recods as $key=>$value){
       	     $nodes[$value['nid']] = $loclids[$value['uid']];    //  nid  -local code
       }
       
       $prefix_ip="";
       $prefix_date = "";
       $prefix_code = "";
       foreach ($recods as $key=>$value){  	   
       	   
       	   if($prefix_ip==$value['client_ip'] && !empty($prefix_ip)){
       	   	    if($prefix_code == $nodes[$value['nid']]['loc_typecode'] && !empty($prefix_code)){
		       	   	if($prefix_date == $value['guest_date']){
		       	   		   $again[] = $value;
		       	   	}
       	   	    }
       	   	
       	   }
       	   
       	   $prefix_date = $value['guest_date'];
       	   $prefix_ip   = $value['client_ip'];
       	   $prefix_code = $nodes[$value['nid']]['loc_typecode'];
       }
       
       echo  "订单共计；".count($recods);
       echo "<br /><br />";
       
       echo  "重复订单共计；".count($again);
       
       echo "<br /><br />";
       
       
       var_dump($again);
       
        return "My_Login";
    }
}