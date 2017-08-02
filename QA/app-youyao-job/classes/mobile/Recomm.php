<?php
class Mobile_Recomm{

    public function run(){
        echo "start\n";
        $i=1;
        $day = date('Y-m-d',time()-86400);
;        while(true){
            $guid_list = $this->get_guid_list($i,$day);
            if(empty($guid_list)){
                break;
            }
            $bll_user =  new Bll_User_UserInfo();
            $bll_push  = new Bll_Push_PushInfo();
            $bll_stay  = new Bll_Homestay_StayInfo();

            foreach($guid_list as $key=>$value){
                $homestay_list  = $this->get_hlist_byguid($day,$value['guid']);
                $uid_list = array();
                foreach($homestay_list as $k=>$v){
                    $uid_list[] = $v['h_uid'];
                }
                $rec_homstay = $this->get_recommlist_byhid($uid_list);
                $stay_info = $bll_stay->get_stayinfo_by_id($rec_homstay[0]['item2']);
                $user  = $bll_user->get_user_by_uid($value['uid']);
                $token_info = $bll_push->get_token_bymail($user['mail']);

                if(empty($token_info) || empty($stay_info)){
                    continue;
                }

                $title = '为您精选优质民宿'.Util_Common::zzk_translate($stay_info['title'],'zh-cn').',速速来抢！';

                Util_Notify::push_message_client($user['mail'],'',$value['guid'],$title,Util_Notify::get_push_mtype('homestay'),$rec_homstay[0]['item2']);
                //Util_Notify::push_message_client('jfchen@zizaike.com','','7B171DA7-A244-49BF-953D-3306ABA51F20',$title,Util_Notify::get_push_mtype('homestay'),$rec_homstay[0]['item2']);

                //echo $user['mail'].'-----'.$value['guid'].'-----'.$title.'-----'.Util_Notify::get_push_mtype('homestay').'-----'.$rec_homstay[0]['item2']."\n";
                echo $user['mail']." ".date('Y-m-d H:i:s')." pushed\n";
            }

            $i++;
        }
        echo "end\n";

    }

    public function get_hlist_byguid($day,$guid){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("dwmaster");
        $sql = "select * from  zzk_app_home_browse_detail where date_desc=? and guid=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($day,$guid));
        return $stmt->fetchAll();
    }

    public function get_recommlist_byhid($hlist){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("dwmaster");
        $sql = "select * from  tmp_recommend_data_fp  where item1 in (".implode(',',$hlist).") order by score limit 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function send_guest_msg($info){
        $bll_push = new Bll_Push_PushInfo();
        $bll_push->send_push_message($info);
    }

    public function get_guid_list($page,$day){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("dwmaster");
        $page_num = 10;
        $start    = $page_num*($page-1);
        $sql = "select * from  zzk_app_home_browse_detail where date_desc=? group by guid limit $start,$page_num";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($day));
        return $stmt->fetchAll();
    }

}