<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 16/3/1
 * Time: 上午11:37
 */

class So_SimpleUser{
    protected $changed;
    protected $collect_h ;
    protected $uid ;
    protected $name;
    protected $photo;
    public $languages;

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
        return $this;
    }
    protected $dest_id;
    protected $base_info;
    protected $pass;
    protected $mail;
    protected $address;
    protected $tels;
    protected $refund_rule = null;

    /**
     * @return mixed
     */
    public function getTels()
    {
        return $this->tels;
    }

    /**
     * @param mixed $tels
     */
    public function setTels($tels)
    {
        $this->tels = $tels;
    }

    /**
     * @return mixed
     */
    public function getRefundRule()
    {
        return $this->refund_rule;
    }

    /**
     * @param mixed $refund_rule
     */
    public function setRefundRule($refund_rule)
    {
        $this->refund_rule = $refund_rule;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @param mixed $languages
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMail()
    {
        if(empty($this->mail)){
            $this->mail = $this->base_info[0]['mail'];
        }
        return $this->mail;
    }

    /**
     * @param mixed $mail
     */
    public function setMail($mail)
    {
        if(!empty($mail)){
            $this->changed = true;
            $this->mail = $mail;
        }else{
            return false;
        }
        return $this;
    }

    public function setPass($pass)
    {
        if (!empty($pass)) {
            // Allow alternate password hashing schemes.
            require_once CORE_PATH . 'classes/includes/password.inc';
            $this->pass = user_hash_password(trim($pass), 15);
            if (!$this->pass ) {
                return false;
            }
            $this->changed = true;
        }else{
            return false;
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDestId()
    {
        if(empty($this->dest_id)){
            $this->dest_id = $this->base_info[0]['dest_id'];
        }
        return $this->dest_id;
    }

    /**
     * @param mixed $dest_id
     */
    public function setDestId($dest_id)
    {
        if(!empty($dest_id)){
            $this->changed = true;
            $this->dest_id = $dest_id;
        }else{
            return false;
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        if(empty($this->photo)){
            $userInfo = $this->bll_userInfo->get_user_head_pic_by_uid($this->uid);
            if(!$userInfo){
                $userInfo = Util_Avatar::dispatch_avatar($this->uid);
            }
            $this->photo =$userInfo;
        }
        return $this->photo;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        if(empty($this->name)){
            $this->name = $this->base_info[0]['name'];
        }
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        if(!empty($name)){
            $this->changed = true;
            $this->name = $name;
        }
        return $this;
    }



    /**
     * So_SimpleUser constructor.
     * @param $uid
     */
    public function __construct($uid)
    {
        $this->changed = false;
        if(!empty($uid)){
            $this->uid = $uid;
            $this->bll_userInfo = new Bll_User_UserInfo();
            $this->base_info = DB::execSql("select * from one_db.drupal_users where uid = {$this->uid}");
        }else{
            $this->changed = true;
        }
    }
    public function __destruct()
    {
        $time = time();
        // 析构函数去保存这个用户信息
        if($this->changed and empty($this->uid) and !empty($this->name) and !empty($this->pass) and !empty($this->dest_id)){
            $dao_user = new Dao_User_UserInfo();
            $uid = $dao_user->next_user_id();
            //需要新增民宿
            $sql=  "insert into one_db.drupal_users ";
            $sql.= "( `uid`, `name` , `pass` , `mail` , `signature_format` , `created` , `init` , `address` , `tel_num` , `dest_id`,`follow_language`,`refund_rule` )";
            $sql.= " values ( $uid ,'{$this->name}' , '{$this->pass}' , '{$this->mail}' , 'full_html' , '$time' , '{$this->mail}' , '{$this->address}' , '{$this->tels}' , '{$this->dest_id}','{$this->languages}','{$this->refund_rule}' ) ";
            $sql.= " ON DUPLICATE KEY UPDATE `name`='{$this->name}' , ";
            $sql.= " `uid`='{$uid}' , ";
            $sql.= " `pass`='{$this->pass}' , ";
            $sql.= " `mail`='{$this->mail}' , ";
            $sql.= " `signature_format`='full_html' , ";
            $sql.= " `address`='{$this->address}' , ";
            $sql.= " `tel_num`='{$this->tels}' , ";
            $sql.= " `dest_id`='{$this->dest_id}' , ";
            $sql.= " `follow_language`='{$this->languages}' ,";
            $sql.= " `refund_rule`='{$this->refund_rule}' ;";
            DB::execSql($sql,true);
            $this->uid = $uid;
            if(!empty($this->uid) and !empty($this->photo)){
                $src = Util_Curl::http_get_data($this->photo);
                $tmpimg = "/tmp/tmp.png";
                $fp = fopen($tmpimg,'w');
                fwrite($fp,$src);
                $hashcode=Util_Curl::upload_curl_pic($tmpimg);
                $hashcode = json_decode($hashcode);
                $hashcode = $hashcode->url[0];
                $sql = "INSERT INTO  `t_img_managed` (`uid`,`uri`,`timestamp`,`status`,`source`) ";
                $sql .= " values ('{$this->uid}','$hashcode','" . time() . "','0','0')";
                $r = DB::execSql($sql,true);
                $sql = "UPDATE one_db.drupal_users SET picture='$r' , picture_version=1 WHERE uid={$this->uid}";
                DB::execSql($sql,true);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getCollectH($page)
    {
        if(empty($this->collect_h[$page])){
            $num = ($page-1) * 10;
            if(!is_numeric($num)){
                $num = 0;
            }
            $this->collect_h[$page] = DB::execSql(" select * from LKYou.t_collect where uid = {$this->uid} and status =1 and type = 'h' and !isnull(hid) GROUP BY hid order by create_at desc limit $num,10 ;");
        }

        return $this->collect_h[$page];
    }

}
