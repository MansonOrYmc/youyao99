<?php
class Bll_User_Simple {
    private $user_info;
    public $pass;
    private $bll;
    private $dao;
    private $field_data_field_nickname;
    private $picture;
    private $field_data_field_line;
    private $field_data_field_weixin;
    private $field_data_field__qq;
    private $field_data_field_skype;
    private $uid;

    public  function __construct($uid , $pass=null) {
        $this->bll  = new Bll_Homestay_StayInfo();
        $this->dao = new Dao_User_UserInfo();
        $this->uid = $uid;
        $this->pass = $pass;
    }
    public function get_weixin(){
        return $this ->field_data_field_weixin;
    }
    public function set_weixin($weixin){
        $this ->field_data_field_weixin = $weixin;
        $this->user_info['field_data_field_weixin']=$this->field_data_field_weixin;
    }
    public function get_qq(){
        return $this ->field_data_field__qq;
    }
    public function set_qq($qq){
        $this ->field_data_field__qq = $qq;
        $this->user_info['field_data_field__qq']=$this->field_data_field__qq;
    }
    public function get_skype(){
        return $this ->field_data_field_skype;
    }
    public function set_skype($skype){
        $this ->field_data_field_skype = $skype;
        $this->user_info['field_data_field_skype']=$this->field_data_field_skype;
    }

    public function get_nickname(){
        return $this ->field_data_field_nickname;
    }
    public function set_nickname($nickname){
        $this ->field_data_field_nickname = $nickname;
        $this->user_info['field_data_field_nickname']=$this->field_data_field_nickname;
    }
    public function get_line(){
        return $this ->field_data_field_line;
    }
    public function set_line($line){
        $this ->field_data_field_line = $line;
        $this->user_info['field_data_field_line']=$this->field_data_field_line;
    }
    public function get_picture(){
        return $this->picture;
    }
    public function set_picture($picture_hash){
        //前端调用传过来的是一段hash,这段hash需要传入头像关联表里面
        $this->picture = $this->dao->insert_t_img_manage($picture_hash,$this->uid);
        $this->user_info['picture']=$this->picture;
        $this->user_info['picture_version']=1;
    }

    public function get_pass(){
        return $this->pass;
    }
    public function set_pass($old_pass,$new_pass){
        require_once CORE_PATH . 'classes/includes/password.inc';
        if(user_check_password($old_pass,$this))
        {
            $this->bll->write_homestay_record($this->uid,array('pass'=>$new_pass));
            echo json_encode(array('status'=>'true','word'=>'修改密码成功！'));
            exit;
        }
        else{
            echo json_encode(array('status'=>'false','word'=>'你输入的密码不正确！'));
            exit;
        }
    }
    public function set_user(){
        $this->bll->write_homestay_record($this->uid,$this->user_info);
    }
}
