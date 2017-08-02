<?php
apf_require_class("APF_DB_Factory");

class Dao_Admin_ZFans {
  
  private $lkymaster;
  private $lkyslave;

  public function __construct() {
    $this->lkymaster = APF_DB_Factory::get_instance()->get_pdo("lkymaster"); // LKYou master
    $this->lkyslave = APF_DB_Factory::get_instance()->get_pdo("lkyslave"); // LKYou slave
    // $pdo = APF_DB_Factory::get_instance()->get_pdo("master"); // one_db master
    // $pdo = APF_DB_Factory::get_instance()->get_pdo("slave"); // one_db slave
  }

  public function isAdminUser($uid) {
    $adminUsers = array(59, 72147);
    return in_array($uid, $adminUsers);
  }

  public function getZFansRefers($searchArgs) {
    $sql = 'SELECT * FROM t_zfans_refer';
    $stmt = $this->lkyslave->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }


  public function check_user($uid, $email) {
    $sql = 'SELECT * FROM t_zfans_refer WHERE uid=:uid AND email=:email AND status=1';
    $stmt = $this->slave_pdo->prepare($sql);
    $stmt->execute(array('uid' => $uid, 'email' => $email));
    return $stmt->fetch();
  }

  public function stat_order_num($uid) {
    $sql = <<<SQL
SELECT count(id) FROM t_homestay_booking
WHERE zfansref = :uid AND exists(SELECT *
  FROM log_homestay_booking_trac
  WHERE t_homestay_booking.id=log_homestay_booking_trac.bid
  AND log_homestay_booking_trac.status=2)
SQL;
    $stmt = $this->slave_pdo->prepare($sql);
    $stmt->execute(array('uid' => $uid));
    return $stmt->fetchColumn();
  }

  public function stat_order_amount($uid, $rate) {
    $sql = <<<SQL
SELECT sum(round(total_price*:rate,2)) FROM t_homestay_booking
WHERE zfansref = :uid AND status IN (2,6) AND exists(SELECT *
  FROM log_homestay_booking_trac
  WHERE t_homestay_booking.id=log_homestay_booking_trac.bid
  AND log_homestay_booking_trac.status=2)
SQL;
    $stmt = $this->slave_pdo->prepare($sql);
    $stmt->execute(array('uid' => $uid, 'rate' => $rate));
    return $stmt->fetchColumn();
  }

  public function stat_order_amounted($uid, $rate) {
    $sql = <<<SQL
SELECT sum(round(total_price*:rate,2)) FROM t_homestay_booking
WHERE zfansref = :uid AND status IN (2,6) AND DATE_ADD(guest_checkout_date,INTERVAL 1 DAY)<now() AND exists(SELECT *
  FROM log_homestay_booking_trac
  WHERE t_homestay_booking.id=log_homestay_booking_trac.bid
  AND log_homestay_booking_trac.status=2)
SQL;
    $stmt = $this->slave_pdo->prepare($sql);
    $stmt->execute(array('uid' => $uid, 'rate' => $rate));
    return $stmt->fetchColumn();
  }

  public function stat_order($uid, $limit = 10, $offset = 0) {
    $sql = <<<SQL
SELECT (
  SELECT FROM_UNIXTIME(create_date,'%Y-%m-%d')
  FROM log_homestay_booking_trac
  WHERE t_homestay_booking.id=log_homestay_booking_trac.bid
  AND log_homestay_booking_trac.status=2
  ORDER BY tid LIMIT 1
) AS transaction_date,t_homestay_booking.*
FROM t_homestay_booking
WHERE zfansref = :uid AND exists(SELECT *
  FROM log_homestay_booking_trac
  WHERE t_homestay_booking.id=log_homestay_booking_trac.bid
  AND log_homestay_booking_trac.status=2)
ORDER BY id DESC
LIMIT :limit OFFSET :offset
SQL;
    $stmt = $this->slave_pdo->prepare($sql);
    $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }
}