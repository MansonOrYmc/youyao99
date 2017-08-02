<?php
apf_require_class("APF_DB_Factory");

class Dao_User_Register {

	private $pdo;
	private $bbs_pdo;

	public function __construct() {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$this->bbs_pdo = APF_DB_Factory::get_instance()->get_pdo("bbs");
		$this->blog_pdo = APF_DB_Factory::get_instance()->get_pdo("blog");
	}

	public function check_bbs_center_register($username) {
		$sql = 'SELECT `uid` FROM `bbs_zzkcenter_members` WHERE `username`=:username';
		$stmt = $this->bbs_pdo->prepare($sql);
		$stmt->execute(array('username' => $username));
		return $stmt->fetchColumn();
	}

	public function check_bbs_register($username) {
		$sql = 'SELECT `uid` FROM `bbs_common_member` WHERE `username`=:username';
		$stmt = $this->bbs_pdo->prepare($sql);
		$stmt->execute(array('username' => $username));
		return $stmt->fetchColumn();
	}

	public function check_www_register($email) {
		$sql = 'SELECT `email` FROM `t_user` WHERE `email`=:email';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array('email' => $email));
		return $stmt->fetchColumn();
	}

	public function check_blog_register($uid) {
		$sql = 'SELECT `ID` FROM `awp_users` WHERE `ID`=:uid';
		$stmt = $this->blog_pdo->prepare($sql);
		$stmt->execute(array('uid' => $uid));
		return $stmt->fetchColumn();
	}

	public function insert_bbs_center_user($account) {
		$sql = 'INSERT INTO `bbs_zzkcenter_members` '
			. ' (`uid`,`username`,`password`,`email`,`myid`,`myidkey`,`regip`,`regdate`,`lastloginip`,`lastlogintime`,`salt`,`secques`)'
			. ' VALUES(:uid,:username,:password,:email,:myid,:myidkey,:regip,:regdate,:lastloginip,:lastlogintime,:salt,:secques)';
		$stmt = $this->bbs_pdo->prepare($sql);
		return $stmt->execute($account);
	}

	public function insert_bbs_user($account) {
		$sql = 'INSERT INTO `bbs_common_member`'
			. '(`uid` , `email` , `username` , `password` , `status` , `emailstatus` , `avatarstatus` , `videophotostatus` , `adminid` , `groupid` , `groupexpiry` , `extgroupids` , `regdate` , `credits` , `notifysound` , `timeoffset` , `newpm` , `newprompt` , `accessmasks` , `allowadmincp` , `onlyacceptfriendpm` , `conisbind` )'
			. ' VALUES(:uid ,:email ,:username ,:password ,:status ,:emailstatus ,:avatarstatus ,:videophotostatus ,:adminid ,:groupid ,:groupexpiry ,:extgroupids ,:regdate ,:credits ,:notifysound ,:timeoffset ,:newpm ,:newprompt ,:accessmasks ,:allowadmincp ,:onlyacceptfriendpm ,:conisbind)';
		$stmt = $this->bbs_pdo->prepare($sql);
		return $stmt->execute($account);
	}

	public function insert_blog_user($account) {
		$sql = 'INSERT INTO `awp_users`'
			. '(`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_status`, `display_name`)'
			. 'VALUES(:ID,:user_login,:user_pass,:user_nicename,:user_email,:user_url,:user_registered,:user_status,:display_name)';
		$stmt = $this->blog_pdo->prepare($sql);
		return $stmt->execute($account);
	}

	public function insert_awp_usermeta($usermeta) {
		$sql = 'INSERT INTO `awp_usermeta` '
			. ' (`user_id`,`meta_key`,`meta_value`) '
			. ' VALUES(:user_id,:meta_key,:meta_value)';
		$stmt = $this->bbs_pdo->prepare($sql);
		return $stmt->execute($usermeta);
	}

	public function insert_www_user($account) {
		$sql = 'INSERT INTO `t_user`'
			. '(`id`, `email`, `nickname`, `password`, `created`, `regdate`, `pwd_old`) '
			. ' VALUES (`id`, `email`, `nickname`, `password`, `created`, `regdate`, `pwd_old`)';
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($account);
	}
}