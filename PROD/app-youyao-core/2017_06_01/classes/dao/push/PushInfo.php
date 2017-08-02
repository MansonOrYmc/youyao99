<?php
apf_require_class("APF_DB_Factory");

class Dao_Push_PushInfo {

	private $lky_pdo;
	private $lky_slave_pdo;
	private $one_pdo;

	public function __construct() {
		$this->lky_pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$this->lky_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
	}

	public function dao_send_mobile_notify($send_info) {
		$sql = "insert into t_mobile_push_queue(device_token, payload, time_queued) values(?, ?, ?)";
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute($send_info);
	}

	public function dao_query_devices($email) {
 		$sql = "select distinct(deviceid),baidu_channel_id,baidu_user_id,os,jgpush_id from t_mobile_device where status =1 and deviceid is not null and email = ?";

		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute(array($email));
		return $stmt->fetchAll();
	}


    public function dao_send_mobile_notify_baidu($send_info)
    {
        $sql = "insert into t_mobile_push_queue(device_token,baidu_id, payload, time_queued) values('',?, ?, ? )";
        $stmt = $this->lky_pdo->prepare($sql);
        return $stmt->execute($send_info);

    }

    public function dao_query_baidu_device($email){
        $sql = "select distinct(baidu_channel_id)  from t_mobile_device where status=1 and baidu_channel_id is not null and email = ?";
        $stmt = $this->lky_slave_pdo->prepare($sql);
        $stmt->execute(array($email));
        return $stmt->fetchAll();
    }

	public function get_pushinfo_byemail($email) {
		$sql = "select distinct(deviceid) from t_mobile_device where deviceid is not null and email = '$email'";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function save_apple_token_by_jpush($appe_token, $jpush_id)
	{
		$sql = "insert into t_mobile_device ( deviceid, os, create_time, status, jgpush_id) values (
		'$appe_token',
		'iphone',
		UNIX_TIMESTAMP(now()),
		'1',
		'$jpush_id')";
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute();
	}

	public function update_apple_token_by_jpush($apple_token, $jpush_id)
	{
		$sql = "update t_mobile_device set deviceid ='$apple_token' ,os='iphone' where jgpush_id='$jpush_id'";
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute();
	}

	public function  get_device_by_jpush_id($jpush_id)
	{
		$sql = "select * from t_mobile_device where   jgpush_id='$jpush_id'";
		$stmt = $this->lky_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function update_jfdevice($email,$jgpush_id) {
		$sql = "update t_mobile_device set status = 1, jgpush_id = '$jgpush_id' where email = '$email'";
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute();
	}

	public function insert_jfdevice($email, $uid, $deviceid, $jgpush_id) {
		$sql = "insert into t_mobile_device (email, uid, deviceid, os, create_time, status, jgpush_id) values (
		'$email',
		'$uid',
		'$deviceid',
		'iphone',
		UNIX_TIMESTAMP(now()),
		'1',
		'$jgpush_id')";
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute();

	}

	public function get_ios_push_queue(){
		$sql = "select * from t_mobile_push_queue  WHERE LENGTH(device_token) = 64 AND time_queued > UNIX_TIMESTAMP() - 3600  AND time_sent IS NULL order by message_id LIMIT 20";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_jg_register_id($deviceid) {
		if(empty($deviceid)) {
			return ;
		}
		$device = implode(",", $deviceid);
		$sql = "select * from t_mobile_device where deviceid in ($device) and status = 1 and jgpush_id is not NULL";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function push_complete_update($ids) {
		if(!$ids) return;
		$id = implode(",", $ids);
		$sql = "UPDATE t_mobile_push_queue SET time_sent = NOW() WHERE message_id in ($id)";
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute();
	}

	public function insert_push_device_token($guid, $token, $type, $os)
	{	$timestamp = date('Y-m-d G:i:s');
		$sql = "insert into t_mobile_guid_token (guid ,token,type,os,status,create_time,update_time) VALUES (
		'$guid','$token','$type','$os','1','$timestamp','$timestamp'
		)  on DUPLICATE KEY UPDATE token='$token',type='$type',os='$os',status=1;";
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute();
	}

	public function update_push_device_token($guid, $token, $type)
	{   $timestamp = date('Y-m-d G:i:s');
		$sql = "update t_mobile_guid_token set token='$token',type='$type',status=1 ,update_time='$timestamp' where guid='$guid'";
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute();
	}

	public function delete_push_device_token_by_guid($guid)
	{   $timestamp = date('Y-m-d G:i:s');
		$sql = "update t_mobile_guid_token set status =0 ,update_time='$timestamp' where guid='$guid'";
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute();

	}

	public function get_device_token_by_guid($guid)
	{
		$sql = "select * from t_mobile_guid_token where guid ='$guid'";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}
	public function get_device_by_token($token,$type){

		$sql = "select * from t_mobile_guid_token where token ='$token' AND type='$type'";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();

	}

    public function user_bind_guid($uid,$guid){
        $sql ="insert into user_guid (uid,guid,status) VALUES (
            '$uid','$guid','1'
        ) on DUPLICATE KEY UPDATE uid='$uid',status=1;";
		$stmt = $this->lky_pdo->prepare($sql);
		return $stmt->execute();
    }


    public function get_binded_guid($guid){
        $sql="select * from user_guid where  guid='$guid' ";
        $stmt=$this->lky_pdo->prepare($sql);
		$stmt->execute();
        return $stmt->fetchAll();
    }
    public function update_binded_user_guid($uid,$guid){
        $sql="update user_guid set status =1 , uid='$uid' where  guid='$guid' ";
        $stmt=$this->lky_pdo->prepare($sql);
        return $stmt->execute();
    }

    public function close_push($guid){
        $sql="update t_mobile_guid_token set status =0 where guid='$guid'";
        $stmt=$this->lky_slave_pdo->prepare($sql);
        return $stmt->execute();
    }
	public function unbind_guid($guid){
		$sql="update user_guid set status =0 where guid='$guid'";
		$stmt=$this->lky_slave_pdo->prepare($sql);
		return $stmt->execute();
	}

	public function get_guid_by_uid($uid, $status = 1)
	{
		$sql = "select guid from user_guid where uid='$uid' and status='$status'";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_token_by_guid($guid, $status = 1)
	{
		$sql = "select * from t_mobile_guid_token where guid='$guid' and status='$status'";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function send_push_message($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "insert into t_mobile_push_queue (device_token,baidu_id, payload, time_queued) values (?,?,?,?)";
		$stmt = $pdo->prepare($sql);
		return $stmt->execute(array($params['device_token'], $params['baidu_id'], $params['payload'],$params['time_queued']));
	}

	public function get_pushqueue_list(){
		$sql = "select * from t_mobile_push_queue where time_queued > UNIX_TIMESTAMP() - 3600 limit 20";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_pushinfo_bytoken($token){
		$sql = "select * from t_mobile_device where deviceid=? and jgpush_id is not NULL";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute(array($token));
		return $stmt->fetch();
	}

	public function get_tokeninfo_byguid($guid){
		$sql = "select * from t_mobile_guid_token where guid=? AND  status =1";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute(array($guid));
		return $stmt->fetch();
	}

	public function send_push_message_new($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "insert into t_push_queue (token,os, mtype, title,ext,created) values (?,?,?,?,?,?)";
		$stmt = $pdo->prepare($sql);
		return $stmt->execute(array($params['token'], $params['os'], $params['mtype'],$params['title'],$params['ext'],$params['created']));
	}

	public function send_push_message_to_multiple($params) {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $placeholders = array();
        $pdo_val = array();
        foreach($params as $row) {
            $placeholders[] = "(?,?,?,?,?,?)";
            $pdo_val = array_merge(
                $pdo_val,
                array(
                    $row['token'],
                    $row['os'],
                    $row['mtype'],
                    $row['title'],
                    $row['ext'],
                    $row['created']
                )
            );
        } 
        if(empty($placeholders))  return;
		$sql = "insert into t_push_queue (token,os, mtype, title, ext, created) values " . implode(",", $placeholders);
		$stmt = $pdo->prepare($sql);
		return $stmt->execute($pdo_val);
	}

	public function get_tokeninfo_byuid($uid){
		$sql = "select  DISTINCT(a.token) as token ,a.os ,a.type    from t_mobile_guid_token a left JOIN user_guid b on a.guid=b.guid where b.uid=? and b.status=1 and a.status =1";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute(array($uid));
		return $stmt->fetchAll();
	}

    public function get_tokeninfo_by_multiuid($uids) {
        if(empty($uids)) return;
        $uid_str = Util_Common::placeholders("?", count($uids));
        $pdo_val = array_values($uids);
		$sql = "select  DISTINCT(a.token) as token ,a.os ,a.type from t_mobile_guid_token a left JOIN user_guid b on a.guid=b.guid where b.uid in (" . $uid_str . ") and b.status=1 and a.status =1";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute($pdo_val);
		return $stmt->fetchAll();
    }

	public function get_queue_list(){
		$sql = "select * from t_push_queue where id >13465 and updated=0 and created > UNIX_TIMESTAMP()-3600  and length(token)!=64 order by id asc limit 20";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function update_queue_status($id){
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql ="update t_push_queue set updated = UNIX_TIMESTAMP() where id=?";
		$stmt=$pdo->prepare($sql);
		return $stmt->execute(array($id));
	}

}
