<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 16/3/1
 * Time: 下午12:14
 */
class So_SimpleHotel extends So_SimpleUser
{
    private $location;
    private $minprice;
    private $info;
    private $pid;
    public  $address;
    private $aboutme;
    private $pics;
    private $rooms;
    private $isteached;
    public $locid;
    public $checkin_policy;
    public $checkout_policy;

    /**
     * @return mixed
     */
    public function getIsteached()
    {
        if(!isset($this->isteached))
        {
            DB::execSql("select * from LKYou.t_teacher_share where user_id = '{$this->uid}' and active = 1 ");
            return $this->isteached;
        }
    }


    /**
     * @return mixed
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * @param mixed $room
     * 新添加一个房间
     */
    public function addRoom(So_SimpleRoom $room)
    {
        if(empty($room->id)){
            $this->rooms[] = $room;
        }
        return $this;
    }

    /**
     * @param mixed $pics
     */
    public function setPics($pics)
    {
        $this->pics = $pics;
        return $this;
    }

    /**
     * @param mixed $aboutme
     */
    public function setAboutme($aboutme)
    {
        $this->aboutme = $aboutme;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        if(empty($this->address)){
           $this->address = $this->info['address'];
        }
        return $this->address;
    }

    /**
     * @return mixed
     */
    public function getPid()
    {
        if(empty($this->pid)){
            if(empty($this->info['pid'])){
                $r = DB::execSql(" select pid from LKYou.t_weibo_poi_tw where uid = {$this->uid} limit 1;");
                $this->pid = $r[0]['pid'];
            }else{
                $this->pid = $this->info['pid'];
            }
        }
        return $this->pid;
    }

    /**
     * So_SimpleHotel constructor.
     * @param $uid
     */
    public function __construct($uid=null)
    {
        parent::__construct($uid);
        if(!empty($uid)){
            $this->info = $this->getInfo();
        }
    }
    public function __destruct()
    {
        $time = time();
        if(empty($this->uid)){
            if($this->changed){
                parent::__destruct();
                if(!empty($this->uid)){
                    DB::execSql("insert ignore into one_db.drupal_users_roles (uid, rid) values ('{$this->uid}', '5')",true);
                    DB::execSql("insert into one_db.drupal_field_data_field_aboutme (entity_type, bundle,entity_id,revision_id,language,delta,field_aboutme_value)
                                                                            values ('user', 'user','{$this->uid}','{$this->uid}','und','0','{$this->aboutme}')",true);
                    if(!empty($this->pics)){
                        foreach(explode(',',$this->pics) as $key => $pic){
                            $src = Util_Curl::http_get_data($pic);
                            $tmpimg = "/tmp/tmp.png";
                            $fp = fopen($tmpimg,'w');
                            fwrite($fp,$src);
                            $hashcode=Util_Curl::upload_curl_pic($tmpimg);
                            $hashcode = json_decode($hashcode);
                            $hashcode = $hashcode->url[0];
                            $sql = "INSERT INTO  `t_img_managed` (`uid`,`uri`,`timestamp`,`status`,`source`) ";
                            $sql .= " values ('198611','$hashcode','" . time() . "','0','0')";
                            $r = DB::execSql($sql,true);
                            $sql = "insert into one_db.drupal_field_data_field_image ";
                            $sql .= " (`entity_type`,`bundle`,`entity_id`,`revision_id`,`language`,`delta`,`field_image_fid`,`field_image_version`) ";
                            $sql .= " values ( 'user','user','{$this->uid}','{$this->uid}','und','$key','$r','1') ";
                            DB::execSql($sql,true);
                        }
                    }

                    if(!empty($this->address)){
                      $lon_lat = Util_Common::curl_get("http://api.youyao.com/dest/address?address={$this->address}");
                      $lon_lat = json_decode($lon_lat,true);
                        if($lon_lat['status']=="OK"){

                            $lon = $lon_lat['results'][0]['geometry']['location']['lng'];
                            $lat = $lon_lat['results'][0]['geometry']['location']['lat'];
                        }else{
                            $lon = 0;
                            $lat = 0;
                        }
                        if(strpos($this->address, '札幌') !== false){
                            $this->locid = '110013000171';
                        }
                    }

                    //日本默认是bnb模式,不要bnb模式
                        $sql="insert into LKYou.t_weibo_poi_tw (pid,poiid,type,uid,lon,lat,local_code) VALUES (null,'{$time}_{$this->name}_{$this->name}',0,{$this->uid},'{$lon}','{$lat}','{$this->locid}')";
                        $poiID=DB::execSql($sql,true);
                        DB::execSql("update one_db.drupal_users set poi_id = '$poiID' where uid = '{$this->uid}'",true);

                    if(!empty($this->checkin_policy) && !empty($this->checkout_policy)){
                        DB::execSql("insert into  one_db.drupal_checkin_time (uid,checkin_at,checkout_at) VALUES ('{$this->uid}','{$this->checkin_policy}','{$this->checkout_policy}')");
                    }

                    foreach($this->rooms as $room){
                        $room->hotelID = $this->uid;
                    }
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getMinprice()
    {
        if(empty($this->minprice)){
            $this->minprice = $this->info['int_room_price_tw'];
        }
        return $this->minprice;
    }

    public function getName()
    {
        if(empty($this->info['username'])){
            return parent::getName();
        }
        return $this->info['username'];
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
            if(empty($this->location)){
                if(empty($this->info['location_typename'][0])){
                    $r= DB::execSql("select type_name as location from one_db.drupal_users du,t_weibo_poi_tw tw,t_loc_type tp where du.uid = {$this->uid} and du.uid = tw.uid and (tp.type_code = tw.local_code or tp.locid = tw.loc_typecode);");
                    $this->location = $r[0]['location'];
                }else{
                    $this->location = $this->info['location_typename'][0];
                }
            }
        return $this->location;
    }

    private function getInfo(){
        $solr_url = "http://".APF::get_instance()->get_config('solr_host').":".APF::get_instance()->get_config('solr_port')."/search/user/select?";
        $solr_url .= "q=id:{$this->uid}&wt=json";
        $result = Util_Common::curl_get($solr_url);
        $result = json_decode($result,true);
        $result = $result['response']['docs'][0];
        unset($result['_version_']);
        return $result;
    }

}
