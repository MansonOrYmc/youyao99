<?php
apf_require_class("APF_DB_Factory");

class Dao_Coupons_CouponsInfo {

    private $lky_pdo;
	private $lky_slave_pdo;
    private $one_pdo;
	private $get_a_coupon_used;
	private $get_conpon_raw_data;

    public function __construct() {
        $this->lky_pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
	    $this->lky_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
    }


    public function dao_update_conpon($oid, $conpon) {
        $sql = "UPDATE t_coupons SET status = 1, submittedby = ? WHERE submittedby = '' AND status = 0 AND coupon = ?";
        $stmt = $this->lky_pdo->prepare($sql);
        return $stmt->execute(array($oid, $conpon));
    }

	public function get_valid_coupon($code){
		$sql = "SELECT * FROM  t_coupons WHERE coupon = ? AND status=0 AND expirydate >= DATE_FORMAT(NOW(),'%Y-%m-%d') LIMIT 1";
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute(array($code));
		return $stmt->fetch();
	}

    public function get_coupon_by_uid($uid) {
        $sql = 'SELECT * FROM t_coupons WHERE uid = :uid';
        $stmt = $this->lky_slave_pdo->prepare($sql);
        $stmt->execute(array('uid'=>$uid));
        $result = $stmt->fetchAll();
        return $result;
    }
    //计算用户评论表的活动月之内评论数
    public function count_coupon($uid){
        $sql = "SELECT COUNT(*) FROM t_comment_info WHERE uid = :uid AND Date(create_time) > '2015-07-06' AND Date(create_time) <'2015-08-07'";
        $stmt = $this->lky_slave_pdo->prepare($sql);
        $stmt->execute(array('uid'=>$uid));
        $result = $stmt->fetchColumn();
        return $result;
    }

//计算用户通过某活动的代金券的数量
    public function count_promo_coupon($uid, $owner)
    {
        $sql = 'SELECT count(*) FROM t_coupons WHERE uid = :uid and ownner=:owner';
        $stmt = $this->lky_slave_pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid, 'owner' => $owner));
        $result = $stmt->fetchColumn();
        return $result;

    }


	public function give_coupon($uid, $value, $expire_date, $owner = 'kelly', $coupon_type = 1, $category=0, $min_use_price=0) {
		//owner 默认值为了兼容旧代码调用
		if (empty($uid)) {
			return FALSE;
		}

		$coupon_code = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 7);
		$datetime = new DateTime();
		$lastused = $datetime->format('Y-m-d');
		$datetime = $datetime->format('Y-m-d h:m:s');

		$params = array(
			'uid' => $uid,
			'coupon' => $coupon_code,
			'lastused' => $lastused,
			'expirydate' => $expire_date,
			'submittedby' => '',
			'success' => 0,
			'fail' => 0,
			'status' => 0,
			'create_date' => time(),
			'update_date' => $datetime,
			'pvalue' => $value,
			'locked' => 1,
			'ownner' => $owner,
			'coupon_type' => $coupon_type,
            'category' => $category,
            'min_use_price' => $min_use_price,
		);
		$sql = <<<SQL
INSERT INTO LKYou.t_coupons
(uid,coupon,lastused,expirydate,submittedby,success,fail,status,create_date,update_date,pvalue,locked,ownner,coupon_type,category,min_use_price) VALUES
(:uid,:coupon,:lastused,:expirydate,:submittedby,:success,:fail,:status,:create_date,:update_date,:pvalue,:locked,:ownner,:coupon_type,:category,:min_use_price)
SQL;
		$stmt = $this->lky_pdo->prepare($sql);
		$result = $stmt->execute($params);
		if ($result) {
			return $coupon_code;
		}
		else {
			return FALSE;
		}
	}

	public function daily_statistics($date){
		$sql = <<<SQL
select
count(id) as count_num,sum(`activity_value`) as amount,
FROM_UNIXTIME(create_time,'%Y-%m-%d') as create_date
from `t_activity_record` where uid not in
(
select uid from one_db.drupal_users where created>UNIX_TIMESTAMP('2015-3-9')
and mail like '%@sina.cn'
and login=0
)
and FROM_UNIXTIME(create_time,'%Y-%m-%d')=:date
SQL;
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute(array('date'=>$date));
		return $stmt->fetch();
	}

	public function daily_use_statistics($date){
		$sql=<<<SQL
select count(t_coupons.id) as count_num,sum(pvalue) as amount,sum(t_homestay_booking.total_price) as order_amount
from `t_coupons`
left join `t_homestay_booking` on t_coupons.`submittedby`=t_homestay_booking.id
where t_coupons.uid not in
(
select uid from one_db.drupal_users where created>UNIX_TIMESTAMP('2015-3-9')
and mail like '%@sina.cn'
and login=0
)
and FROM_UNIXTIME(t_homestay_booking.`create_time`,'%Y-%m-%d')=:date
and ownner='huzheng'
and `submittedby`!=''
SQL;
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->execute(array('date'=>$date));
		return $stmt->fetch();
	}
	
    public function set_conpon_use($order_id) {
        $sql = "UPDATE t_coupons_use SET use_date = UNIX_TIMESTAMP(NOW()) where  order_id = ? order by id desc limit 1";
        $stmt = $this->lky_pdo->prepare($sql);
        return $stmt->execute(array($order_id)); 
    }
    
    public function add_conpon_use($params) {
		$sql = "INSERT INTO t_coupons_use (order_id, coupons,account,activity,activity_discount,point_account,point_uid,create_date)
		VALUES (?, ?, ?, ?,?,?,?,?)";
		$stmt = $this->lky_pdo->prepare($sql);
	    $stmt->execute(array(
		    $params['order_id'],
		    $params['coupons'],
		    $params['account'],
		    $params['activity'],
		    $params['activity_discount'],
		    $params['point_account'],
		    $params['point_uid'],
		    time()
	    ));
        return $this->lky_pdo->lastInsertId();
    }
    
    public function get_conpon_use($order_id) {
        $sql = "select * from  t_coupons_use where order_id = ? order by id desc limit 1";
        $stmt = $this->lky_slave_pdo->prepare($sql);
        $stmt->execute(array($order_id));
        return $stmt->fetch();
    }
    
    public function get_canuse_conpons($uid) {
        $sql = <<<SQL
SELECT ifnull(t_zfans_coupons.id,0) zfans_coupon,t_coupons.* FROM LKYou.t_coupons
LEFT JOIN LKYou.t_zfans_coupons ON LKYou.t_zfans_coupons.coupon = LKYou.t_coupons.coupon
WHERE LKYou.t_coupons.uid = :uid
AND expirydate > DATE_FORMAT(NOW(),'%Y-%m-%d')
ORDER BY expirydate ASC
SQL;
        $stmt = $this->lky_slave_pdo->prepare($sql);
	    $stmt->execute(array('uid' => $uid));
        return $stmt->fetchAll();
    }

	public function get_used_coupons($uid, $limit = 100) {
		$sql = <<<SQL
SELECT ifnull(coupons_use.id,0) used,order_id,coupons.* FROM LKYou.t_coupons coupons
LEFT JOIN LKYou.t_coupons_use coupons_use ON coupons.coupon=coupons_use.coupons AND use_date>0
WHERE uid=:uid AND coupons_use.id>0
LIMIT :limit
SQL;
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->bindParam('uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam('limit', $limit, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function get_expire_coupons($uid, $limit = 100) {
		$sql = <<<SQL
SELECT * FROM LKYou.t_coupons WHERE expirydate<curdate() AND uid = :uid
LIMIT :limit
SQL;
		$stmt = $this->lky_slave_pdo->prepare($sql);
		$stmt->bindParam('uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam('limit', $limit, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

    public function get_conpon_raw_data($code) {
		if($this->get_conpon_raw_data[$code])
		{
			return $this->get_conpon_raw_data[$code];
		}
        $sql = "select * from  t_coupons where coupon = ? limit 1";
        $stmt = $this->lky_slave_pdo->prepare($sql);
        $stmt->execute(array($code));
        $this->get_conpon_raw_data[$code] = $stmt->fetch();
		return $this->get_conpon_raw_data[$code];
    }

	public function get_a_coupon_used($code) {
		if($this->get_a_coupon_used[$code])
		{
			return $this->get_a_coupon_used[$code];
		}
		$sql = "select * from  t_coupons_use where coupons = '$code' and use_date>0 limit 1";
		$r = DB::execSql($sql);
		$this->get_a_coupon_used[$code] = $r;
		return $r;
	}

    public function get_coupon_category() {
        $sql = "select * from t_coupons_category where status = 1";
        $stmt = $this->lky_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function get_coupon_category_byid($id) {
        $sql = "select * from t_coupons_category where id = ?";
        $stmt = $this->lky_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }


}
