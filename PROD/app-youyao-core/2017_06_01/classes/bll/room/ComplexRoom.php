<?php
class Bll_Room_ComplexRoom {
    public $money = array(
        10=>'NT$ per person',
        11=>'Yen per person',
        12=>'RMB per person',
        13=>'dollar per person',
        14=>'HK$ per person',
        15=>'KRW per person',
    );
    public $bed = array(
        10=>'NT$ per bed',
        11=>'Yen per bed',
        12=>'RMB per bed',
        13=>'dollar per bed',
        14=>'HK$ per bed',
        15=>'KRW per bed',
    );
    private $mini_dates;//最小入住数组
    private $speed_dates;//最小入住数组
    private $promotion_dates;//促销入住时间数组
    private $disc;//折扣率
    private $least_days;//最小连住天数
    public $disc_info;//页面使用折扣信息
    //币种数组！！！vruan专用！！！
    private $currency =array(
        10=>'NT$',
        11=>'円',
        12=>'¥',
        13=>'$',
        14=>'HK$',
        15=>'KRW'
    );
    public $room_setting = array(
        1=>"TV",
        2=>"Refrigerator",
        10=>"24hrhotwater",
        25=>"FreeParkinglot",
        32=>"SmokingAllowed",
        3=>"Airconditioning",
        33=>"Family/childrenacceptable",
        4=>"Hot-waterbottle",
        28=>"Kitchen",
        11=>"Shower",
        12=>"HotTub",
        35=>"Pets",
        13=>"Towel",
        14=>"Slippers",
        15=>"Disposabletoiletries",
        7=>"Washingmachine",
        23=>"Bookcarsservice",
        22=>"Bookticketsservice",
        16=>"Wifi",



        5=>"Hairdryer",
        8=>"Speakers",
        9=>"Privaterestroom",
        17=>"Internet",
        //18=>"免费早餐",
        19=>"Freeafternoontea",
        20=>"Freepickup/dropoff",
        21=>"Freebicycle",
        24=>"Storagelugguagesservice",
        26=>"Well-transportations",
        27=>"Swimmingpool",
        29=>"Teabags",
        30=>"Coffeebags",
        31=>"Mineralwater",
        34=>"ForPartyoractivity",
        36=>"nowindows",
    );
    public $base_price; //leon 基础价格
    public $base_room_info;
    public $room_field_info;
    private $field_bll;

    public function __construct($nid){
        $homestaybll = new Bll_Homestay_StayInfo();
        $r = $homestaybll->get_roominfo_by_nid($nid);
        $this->base_room_info = (object)$r[0];
        $this->get_base_info();
        $this->field_bll = new Bll_Field_Info();
        $this->get_field_info($nid);
        $this->get_minimumstay_date();
        $this->get_disc_info();
        $this->get_base_price($nid);
    }

    public function get_base_info(){
        $roomsetting = json_decode($this->base_room_info->roomsetting);
        foreach($roomsetting as $k => &$v){
            if($v==$k){
                $v = $this->room_setting[$k];
            }else{
                $v=null;
            }
        }
        $this->base_room_info->roomsetting = $roomsetting;
        return $this->base_room_info;
    }

    private function get_field_info($nid){
        if(empty($nid)) return false;
        $this->room_field_info = $this->field_bll->get_node_field_by_nids($nid);
        $this->room_field_info = $this->room_field_info[$nid];
        $this->room_field_info = (object)$this->room_field_info;
    }
    public function set_room_info($nid,$fiedls,$baseinfo, $user){

        $bll = new Bll_Room_Update();
        foreach($baseinfo['roomsetting'] as $k=>$v){
            $this->base_room_info->roomsetting->{$k} = $v;
        }
        $baseinfo['roomsetting'] = $this->base_room_info->roomsetting;
        foreach($baseinfo['roomsetting'] as $key => &$value){
            if($value){
                $value = $key;
            }
        }
        $baseinfo['roomsetting'] = json_encode($baseinfo['roomsetting'],true);
        $bll->update_room_record($nid,$fiedls,$baseinfo, $user);

    }//保存房间信息

    public function get_room_name(){
        return $this->base_room_info->title;
    }
    public function get_query_calendar_url() //给出日历url
    {
//        $domain = "http://taiwan.youyao.com";
//        $url = "/user/".$this->base_room_info->uid."/dconfig2?nid=".$this->base_room_info->nid;
//        if(0){ $url = $domain.$url; }

        $url = Util_Common::url("/v2/manage/homestay/".$this->base_room_info->uid."/room/status?rid=".$this->base_room_info->nid);
        return $url;
    }
    public function get_room_type(){
        $tid = $this->room_field_info->field_data_field_room_beds['field_room_beds_tid'];
        $room_type = ($this->field_bll->get_taxonomy_term_data($tid));
        return $room_type[$tid];
    }//获取房型(几人房)

    public function get_maxpeople(){
        $tid = $this->room_field_info->field_data_field_room_beds['field_room_beds_tid'];
        $room_type = ($this->field_bll->get_taxonomy_term_data($tid));
        return $room_type[$tid]+$this->base_room_info->add_bed_num;
    }

    public function get_room_mianji(){
        $result = $this->room_field_info->field_data_field_mianji['field_mianji_value'];
        return  $result;
    }
    public function get_room_bed_type(){
        $arr = array(
            '上下鋪'=>'Bunk bed',
            '单人床'=>'Single bed',
            '榻榻米'=>'Tatami',
            '大通舖'=>'Futon',
            '双人床'=>'Double bed',
        );
        $tid = $this->room_field_info->field_data_field__chuangxing['field__chuangxing_tid'];
        $room_type = ($this->field_bll->get_taxonomy_term_data($tid));
        return Trans::t($arr[$room_type[$tid]])." ".$this->base_room_info->bed_style_remark;
    }
    public function get_room_bed_num(){
        $tid = $this->room_field_info->field_data_field__chuangshu['field__chuangshu_tid'];
        $room_type = ($this->field_bll->get_taxonomy_term_data($tid));
        return $room_type[$tid];
    }
    public function get_room_bathrooms(){
        $tid = $this->room_field_info->field_data_field_weishengjian['field_weishengjian_tid'];
        $room_type = ($this->field_bll->get_taxonomy_term_data($tid));
        return $room_type[$tid].Trans::t('rooms')." ".$this->is_privatebathroom();
    }
    public function is_privatebathroom(){
        return Trans::t($this->base_room_info->roomsetting->{9});
    }
    public function get_room_floor(){
        return $this->base_room_info->room_floor.Trans::t('of the room')." ".$this->get_elevator();
    }
    public function get_elevator(){
        $arr=array(0=>'无电梯',1=>'有电梯');
        return $arr[$this->base_room_info->elevator];
    }
    public function get_window(){
        $k = $this->base_room_info->roomsetting->{36};
        if($k==null) $k = 'yes';
        return Trans::t($k);
    }
    public function get_wifi(){
        $k = $this->base_room_info->roomsetting->{16};
        if($k==null) $k = 'no';
        return Trans::t($k);
    }
    public function get_amenities(){
        $arr = array(
            1=>"TV",
            2=>"Refrigerator",
            10=>"24hrhotwater",
            25=>"FreeParkinglot",
            32=>"SmokingAllowed",
            3=>"Airconditioning",
            33=>"Family/childrenacceptable",
            4=>"Hot-waterbottle",
            28=>"Kitchen",
            11=>"Shower",
            12=>"HotTub",
            35=>"Pets",
            13=>"Towel",
            14=>"Slippers",
            15=>"Disposabletoiletries",
            7=>"Washingmachine",
            23=>"Bookcarsservice",
            22=>"Bookticketsservice",

        );
        //获取便利设施
        $_result='';
        foreach($this->room_setting as $k=>$v){
            $key = '';
            $key = $this->base_room_info->roomsetting->{$k};
            if($key == $arr[$k]){
                $_result.=' '.Trans::t($key);
            }
        }
        return $_result;
    }

    public function get_content($type){
        $result = $this->room_field_info->field_data_body['body_value'];
        // 把开头和结尾的换行符替换掉
        $match = array(
            '/^<p>/',
            '/<\/p>$/',
        );
        $content_value = preg_replace($match, '', $result);
        if($type == 'info'){
            $content_value = str_replace("\n", "<br/>", $content_value);
        }else{
            $content_value = str_replace(array("<br>","<br/>", "<p>"), "\n", $content_value);
            $content_value = strip_tags($content_value);
        }
        return $content_value;
    }
    public function get_room_images(){
//        $imagesbll = new Bll_Images_Info();
//        $imagesbll->get_images_byfid($imgfid);
        if($this->room_field_info->field_data_field_image['field_image_fid']){
            //说明只有一个图片
            $fids[]=$this->room_field_info->field_data_field_image['field_image_fid'];
        }else{
            foreach($this->room_field_info->field_data_field_image as $v){
                $fids[]=$v['field_image_fid'];
            }
        }

        $imagesbll = new Bll_Images_Info();
        $result = $imagesbll->get_images_byfid($fids);

//        foreach($result as &$uri){
//            $uri =Util_Image::imglink($uri, 'room210x130.jpg');
//        }
        return $result;
    }

    public function get_room_price_count(){
        //获取计价方式
        if($this->base_room_info->room_price_count_check==1){
            return Trans::t('room_price_count_2');
        }else{
            return Trans::t('room_price_count_3');
        }
    }
    public function get_minimum_stay(){
        return $this->base_room_info->minimum_stay;
    }

    public function get_minimumstay_date(){
        if($this->mini_dates){
            return $this->mini_dates;
        }
         $dao = new Dao_Minimumstay_Minimumstay();
         $ms_date = $dao->get_minimumstay_date_by_rid($this->base_room_info->nid);
         $this->mini_dates = $ms_date;
         return $ms_date;
    }
    public function get_speed_date(){
        if($this->speed_dates){
            return $this->speed_dates;
        }
        $dao = new Dao_Minimumstay_Minimumstay();
        $ms_date = $dao->get_speed_date_by_rid($this->base_room_info->nid);
        $this->speed_dates = $ms_date;
        return $ms_date;
    }
    public function is_speed_room(){
        if(!$this->base_room_info->speed_room){
            return 0;
        }else{
            if(count($this->get_speed_date())>0)
            {
                foreach($this->get_speed_date() as $k=>$v){
                    if(strtotime($v['end_date'])-time()>0){
                        return 1;
                    }else{
                        continue;
                    }
                }
            }else{
                return 1;
            }
        }
    }
    public function apply_for_speed_room() {
        return $this->base_room_info->speed_room_apply;
    }
    public function get_base_price($nid) { // leon 基础价格
        $price_bll = new Bll_Price_Price();
        $this->base_price = $price_bll->get_base_price($nid);
    }
    /*
     * alex
     */
    public function get_disc_info(){
        if($this->disc_info){
            return $this->disc_info;
        }
        $dao = new Bll_Disc_Info();
        $result = $dao->get_disc_info($this->base_room_info->nid);
        $this->promotion_dates = $result["date"];
        $this->disc =$result["disc"];
        $this->least_days=$result["least_days"];
        $this->disc_info =$result;
        return $result;
    }
    public function get_promotion_type(){
        $dao = new Bll_Disc_Info();
        $type=$dao->get_disc_type_by_nid($this->base_room_info->nid);
//        print_r($type);
//        print_r("fuck");
        return $type;
    }
    public function get_room_disc(){
        $dao = new Bll_Disc_Info();
        $disc =$dao->get_discrate_bynid($this->base_room_info->nid);
        return $disc;
    }
    public function is_ministay(){
        if($this->base_room_info->minimum_stay==1){
            return 1;
        }else{
            if(count($this->get_minimumstay_date())>0)
            {
                foreach($this->get_minimumstay_date() as $k=>$v){
                    if(strtotime($v['end_date'])-time()>0){
                        return $this->base_room_info->minimum_stay;
                    }else{
                        continue;
                    }
                }
                return 1;
            }else{
                return $this->base_room_info->minimum_stay;
            }
        }
    }
    public function get_people_check(){
        //允许加人
        $arr = array(
            1=>'Allow',
            2=>'no',
        );
        $yes = $arr[$this->base_room_info->add_bed_check];
        if($yes == 'Allow'){
            echo Trans::t("Allow add").$this->base_room_info->add_bed_num.Trans::t("guest USf")." ".$this->base_room_info->add_bed_price.Trans::t($this->money[$this->base_room_info->dest_id]);
        }
        else{
            echo Trans::t("no");
        }
    }
    public function get_beds_check(){
        //允许加床
        $arr = array(
            1=>'Allow',
            2=>'no',
        );
        $yes = $arr[$this->base_room_info->add_beds_check];
        if($yes == 'Allow'){
            echo Trans::t("Allow add").$this->base_room_info->add_beds_num.Trans::t("beds USf")." ".$this->base_room_info->add_beds_price.Trans::t($this->bed[$this->base_room_info->dest_id]);
        }
        else{
            echo Trans::t("no");
        }
    }
    public function get_nid(){
        return $this->base_room_info->nid;
    }
    public function get_uid(){
        return $this->base_room_info->uid;
    }


} //复杂房间类
