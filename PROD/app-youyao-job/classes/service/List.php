<?php

class Service_List {

    private $slave_pdo;
    private $one_slave_pdo;
    private $matches;

	public function run() {

        $apf = APF::get_instance();
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");

		$uid = 1320 ; //1320

		$services = $this->acquire_additional_service($uid);
		#$homestay_info = $this->get_homestay_info($uid);
        //echo $userinfo['userName']. "\n";
        foreach($services as $service){
          //echo var_export($service, true);
          $userinfo = $this->acquire_user_info($service['uid']);
          if(isset($service['service']['image'])){
             #foreach($service['service']['image'] as $img){
             foreach($service['service']['imgids'] as $img){
                   //echo "$img". "\n";
                   $title =  $service['title'];
                   $content = $service['content'];
                   $price_cn = $service['price_cn'];
                   $service_name = $service['service_name'];
                   #echo var_export($service, true);
                   $content = mb_substr($content, 0, 30, 'utf-8');

                  #$s = "". $content. " 预订价格：".$price_cn." 元（RMB） " . " #title#" .$title." ".$service_name . " ".$userinfo['title']."#title# ".$userinfo['address']."#title# 预订: http://m.zizaike.com/service/view/".$service['id']."?campaign_code=douban #特色服务# IMG:/home/tonycai/service/img/".$img.".jpg";
                  #$s = "". $content. " 预订价格：".$price_cn." 元（RMB） " . "" .$title." ".$service_name . " ".$userinfo['title']." ".$userinfo['address']." 预订: http://m.zizaike.com/service/view/".$service['id']."?campaign_code=weibo #特色服务# IMG:/home/tonycai/service/img/".$img.".jpg";

                  #images
                  $s = "wget \"http://img1.zzkcdn.com/$img/2000x1500.jpg-homepic1024x768.jpg\" -O /home/tonycai/service/img/$img.jpg";                    
                  #$s = Util_Common::zzk_translate($s, 'zh-cn');
                  $s = str_replace(PHP_EOL, '', $s); 
                  print  "$s" ."\n";
                  #print  "#\n";
             }
          }
        }
        //echo $userinfo['title']. "\n";
        //echo $userinfo['address']. "\n";
        //substr_replace($row['userName'],'***',3,-3)
        //Util_Common::zzk_translate($content, 'zh-tw')

	}

    private function acquire_service_view($serviceid) { // ID =  108560

        $bll_home = new Bll_Homestay_StayInfo();
        if (empty($title)) {
            $info = $bll_home->get_other_service_by_id($serviceid);
            if (empty($info)) return false;
            $item = $info[0];
            if ($item['category'] == 'unset')
                $title = $item['service_name'];
            else $title = $item['title'];
        }

        $imgs = $bll_home->get_other_service_images_byids($serviceid);
        #Logger::info(__FILE__, __CLASS__, __LINE__, var_export($imgs, true));
        $temp = array_values($imgs[$serviceid]);
        foreach ($temp as $k => $v) {
            $img[$k] = Util_Image::imglink($v, 'homepic800x600.jpg');
            $ids[$k] = $v;
        }

        #Logger::info(__FILE__, __CLASS__, __LINE__, var_export($img, true));

        return array_merge(Push_Pusher::service_recommend_push($serviceid)
            , array(
                'title' => $title,
                'image' => $img,
                'imgids' => $ids,
                'type' => 'service'
            ));
    }

    private function acquire_additional_service($uid) { // ID =  108560
        $ss = array();
        $key = 0;
        $item = array();
        $sql = "select id, category, uid, service_id, service_name, title, free, price, content, create_time, update_date, dest_id, alone_buy, seller_type  from t_additional_service where status=1 and ";
        $sql = $sql . '1320 = ? ';
        $sql = $sql . 'order by create_time desc';
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($uid));
        $results = $stmt->fetchAll();
        foreach ($results as $value) {
            $ss = $value;
		    $service = $this->acquire_service_view($value['id']);
            $ss['service'] = $service;
            $areabll = new Bll_Area_Area();
            $destconfig = $areabll->get_dest_config_by_destid($value['dest_id']);
            $ss['price_cn'] = round( $value['price'] /  $destconfig['exchange_rate'] );
            $ss['content'] = htmlentities($ss['content'],ENT_QUOTES,'UTF-8');
            $item[$key] = $ss;
            $key++;
        }
        return $item;
    }
    private function acquire_user_info($uid) {
        $sql = <<<SQL
select users.uid, users.name iname, users.address iaddress,nickname.field_nickname_value as nickname,orders.guest_mail,name as userName, picture,
ifnull(concat('http://img1.zzkcdn.com/',substr(file.uri,10),'-userphotomedium.jpg'),
concat('http://img1.zzkcdn.com/',img.uri,'/2000x1500.jpg-userphotomedium.jpg')) url
from one_db.drupal_users users
left join LKYou.t_img_managed img on users.picture=img.fid
LEFT JOIN one_db.drupal_file_managed file ON users.picture=file.fid
LEFT JOIN one_db.drupal_field_data_field_nickname nickname ON users.uid=nickname.entity_id
LEFT JOIN LKYou.t_homestay_booking orders on users.uid=orders.guest_uid
where users.status = 1 and users.uid = :uid
SQL;
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        $results = $stmt->fetchAll();
        $result = $results[0];
        $guest_mail_name = explode("@", $result['guest_mail']);
        $guest_mail_name = substr($guest_mail_name[0], 0, -2);
        $userName = $result['nickname'];
        $userName = $userName ? $userName : $result['userName'];
        $userName = ($userName && substr($userName, 0, 3) !== "zzk") ? $userName : $guest_mail_name;
        $img_url = $result['url'];

        if(empty($img_url)) {
            return array('userName'=>$userName, 'userProfile'=>Util_Avatar::dispatch_avatar($uid), 'title'=>$result['iname'],'address'=>$result['iaddress']);
        }else {
            return array('userName'=>$userName, 'userProfile'=>$img_url, 'title'=>$result['iname'],'address'=>$result['iaddress']);
        }
    }

}
