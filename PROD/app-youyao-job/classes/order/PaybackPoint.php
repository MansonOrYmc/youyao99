<?php
class Order_PaybackPoint {

    public function __construct() {
    	$this->pdo_master = APF_DB_Factory::get_instance()->get_pdo("master");
    }

    public function run() {
        $date_to = date('Y-m-d', time() - 15 * 86400);
        $userlist = self::get_userlist($date_to);
        foreach($userlist as $user) {
            $point = floor($user['total_payed'] / 100);
            if($point <= 0) {
                continue;
            }
            var_dump($user);
            self::insert_point($user['guest_uid'], $point);
            self::mark_payback_delivered($user['guest_uid'], $date_to);
        }
    }

    public function insert_point($uid, $point) {
        $ts_now = time();
        $ts_expire = $ts_now + 86400 * 365 * 2;
        $sql = <<<EOF
INSERT INTO LKYou.t_user_points 
(uid,point,create_time,validate_time,expire_time,remark,order_id,type,admin_uid,source,status) 
VALUES 
("$uid",$point,$ts_now,$ts_now,$ts_expire,"入住返积分",0,1,0,"batch job add",1)
EOF;
        $stmt = $this->pdo_master->prepare($sql);
        $stmt->execute();
    }

    public function mark_payback_delivered($uid, $date_to) {
        $sql = <<<EOF
UPDATE LKYou.t_homestay_booking t1
        LEFT JOIN
    LKYou.t_refund t2 ON t2.bid = t1.id 
SET 
    t1.payback_delivered = 1,
    t1.update_date = t1.update_date
WHERE
    t1.status IN (2,6) AND t2.id IS NULL
        AND t1.guest_uid = $uid
        AND t1.guest_checkout_date < '$date_to'
EOF;
        $stmt = $this->pdo_master->prepare($sql);
        $stmt->execute();
    }

    public function get_userlist($date_to) {
        $sql = <<<EOF
SELECT 
    guest_uid,
    guest_telnum,
    COUNT(*) AS cnt,
    SUM(total_price - point_new - coupon_new) AS total_payed
FROM
    (SELECT 
        t1.id,
            t1.guest_uid,
            t1.guest_telnum,
            t1.total_price,
            CASE
                WHEN t2.point IS NULL THEN 0
                ELSE t2.point
            END AS point_new,
            CASE
                WHEN t3.account IS NULL THEN 0
                ELSE t3.account
            END AS coupon_new
    FROM
        LKYou.t_homestay_booking t1
    LEFT JOIN LKYou.t_user_points t2 ON t2.order_id = t1.id
        AND t2.source = 'pay callback'
    LEFT JOIN LKYou.t_coupons_use t3 ON t3.order_id = t1.id AND t3.use_date > 0
    LEFT JOIN LKYou.t_refund t4 ON t4.bid = t1.id AND t4.refund_status != 0
    WHERE
        t1.status IN (2 , 6) AND t4.id IS NULL
            AND t1.guest_uid NOT IN (0 , 116280, 385798, 4482)
            AND (t1.payback_delivered != 1
            OR t1.payback_delivered IS NULL)
            AND t1.guest_checkout_date < "$date_to") tt1
GROUP BY guest_uid
ORDER BY total_payed DESC
EOF;
        $stmt = $this->pdo_master->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetchAll();
        return $ret;
    }
}
