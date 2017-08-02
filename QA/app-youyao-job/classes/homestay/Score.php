<?php

class Homestay_Score
{

    private static $dest_id;
    private static $dest_exchange_rate;
    private $pdo;
    private $mini_price;

    public function __construct()
    {
        $this->pdo = APF_DB_Factory::get_instance()
            ->get_pdo("lkymaster");
        $this->mini_price = 100;
    }

    public function run()
    {
        $sql = <<<SQL
DELETE FROM LKYou.t_homestay_score_hist
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $sql = <<<SQL
INSERT LKYou.t_homestay_score_hist SELECT * FROM LKYou.t_homestay_score
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $sql = <<<SQL
SELECT users.uid,users.created,users.pm_ht_rate,users.dest_id,t_weibo_poi_tw.local_code
FROM one_db.drupal_users users
LEFT JOIN one_db.drupal_users_roles users_roles ON users.uid=users_roles.uid
LEFT JOIN LKYou.t_weibo_poi_tw ON users.uid=LKYou.t_weibo_poi_tw.uid
WHERE users_roles.rid=5
AND users.poi_id>0
AND users.status=1
AND users.uid NOT IN (40080)
ORDER BY users.uid ASC
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $user) {
            $this->update_homestay_score($user);
        }
    }

    /**
     * 更新民宿评分
     *
     * @param $user
     */
    public function update_homestay_score($user)
    {
        list($order_num_score, $order_num_score_context, $order_price_score) = $this->get_order_score($user['uid']);

        if ($user['created'] > strtotime('-2 months') && $user['created'] < time()) {
            $create_time_score = 10;
        } elseif ($user['created'] <= strtotime('-2 months') && $user['created'] > strtotime('-1 year')) {
            $create_time_score = 5;
        } elseif ($user['created'] <= strtotime('-1 year')) {
            $create_time_score = 5 + intval((time() - $user['created']) / (365 * 24 * 3600));
        } else {
            $create_time_score = 0;
        }

        list($cancel_order_score, $cancel_order_score_context) = $this->get_cancel_order_score($user['uid']);
        list($comment_score, $comment_score_context) = $this->get_comment_score($user['uid']);

        $pm_score = intval(($user['pm_ht_rate'] >= 0 ? $user['pm_ht_rate'] : 100) / 10);
        $pm_score_context = array('pm_ht_rate' => $user['pm_ht_rate']);

        $room_price_score = $this->get_room_price_score($user['uid'], $user['dest_id']);
        $zzk_rec_score = round($this->get_zzk_recscore($user['uid']), 2);
        $speed_room = $this->is_speed_room($user['uid']);
        $homestay_speed_score = $this->get_speed_score($user['uid']);
        $other_service_score = $this->get_other_service_score($user['uid']) ? 10 : 0;

        list($price_score, $price_score_context, $time_score, $time_score_context) = $this->get_order_price_score($user['uid']);
        $discount_score = $this->discount_score($user['uid']);
        list($commission_rate_score, $commission_rate_score_context) = $this->get_commission_rate_score($user['uid']);

        $score_dao = new Dao_Homestay_Score();
        $score_arr = $score_dao->get_score_weight();
//        $score_arr = array(
//            'order_num_score' => 0.2,
//            'order_price_score' => 0.1,
//            'create_time_score' => 0.1,
//            'cancel_order_score' => 0.2,
//            'comment_score' => 0.2,
//            'pm_score' => 0.1,
//            'room_price_score' => 0.1,
//            'homestay_speed_score' => 0.1,
//            'zzk_rec_score' => 0.1,
//            'other_service_score' => 0.1,
//            'price_score' => 0.2,
//            'time_score' => 0.1,
//            'discount_score' => 0.1,
//            'commission_rate_score' => 0.1,
//        );
print_r($score_arr);

        $total_score = 0;
        $score_detail = array();
        foreach ($score_arr as $key => $weight) {
            $score_value = ($$key > 10 and $key != "zzk_rec_score") ? 10 : $$key;
            $total_score += $score_value * $weight;
            $score_detail[] = array(
                'name' => $key,
                'value' => $score_value,
                'weight' => $weight,
                'misc' => ${$key . '_context'},
            );
        }
        $columns = array_merge(array_keys($score_arr), array(
            'uid',
            'score',
            'detail',
            'local_code',
        ));
        $update_columns = array_merge(array_keys($score_arr), array(
            'score',
            'detail',
            'local_code',
        ));
        $sql_format = /** @lang text */
        "INSERT INTO LKYou.t_homestay_score ( %s ) VALUES ( %s ) ON DUPLICATE KEY UPDATE %s";
        $sql = vsprintf($sql_format, array(
            join(',', $columns),
            join(',', array_map(function ($v) {
                return ':' . $v;
            }, $columns)),
            join(',', array_map(function ($v) {
                return $v . '=:' . $v;
            }, $update_columns)),
        ));
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute(array(
            'uid' => $user['uid'],
            'local_code' => $user['local_code'],
            'order_num_score' => $order_num_score > 10 ? 10 : $order_num_score,
            'order_price_score' => $order_price_score > 10 ? 10 : $order_price_score,
            'create_time_score' => $create_time_score > 10 ? 10 : $create_time_score,
            'cancel_order_score' => $cancel_order_score > 10 ? 10 : $cancel_order_score,
            'comment_score' => $comment_score > 10 ? 10 : $comment_score,
            'pm_score' => $pm_score > 10 ? 10 : $pm_score,
            'room_price_score' => $room_price_score > 10 ? 10 : $room_price_score,
            'homestay_speed_score' => $homestay_speed_score > 10 ? 10 : $homestay_speed_score,
            'zzk_rec_score' => $zzk_rec_score,
            'other_service_score' => $other_service_score,
            'price_score' => $price_score,
            'time_score' => $time_score,
            'discount_score' => $discount_score,
            'commission_rate_score' => $commission_rate_score,
            'score' => $total_score,
            'detail' => json_encode($score_detail),
        ));

        echo $user['uid'] . "\n";

    }

    private function get_dest_exchange_rate($dest_id)
    {
        if (!empty(self::$dest_id) && self::$dest_id == $dest_id) {
            return self::$dest_exchange_rate;
        } else {
            $sql = 'SELECT exchange_rate FROM LKYou.t_dest_config WHERE dest_id=:dest_id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array('dest_id' => $dest_id));
            $exchange_rate = $stmt->fetchColumn();
            self::$dest_id = $dest_id;
            self::$dest_exchange_rate = $exchange_rate;

            return self::$dest_exchange_rate;
        }
    }

    private function get_room_price_score($uid, $dest_id)
    {
        $score = 10;
        $low_room_list = $this->check_tracs_lowprice($uid, $dest_id);

        if (!empty($low_room_list)) { //tracs 优先级最高
            $score = 0;

            return $score;
        }

        $lowrp_room_list = $this->check_rpconfig_lowprice($uid, $dest_id);

        if (!empty($lowrp_room_list)) { //暂时过滤tracs 没有设置的民宿
            $exchange_rate = $this->get_dest_exchange_rate($dest_id);
            $min_local_price = $this->mini_price * $exchange_rate;
            $sql = <<<SQL
SELECT id,tracs.nid FROM LKYou.t_room_status_tracs tracs
JOIN one_db.drupal_node node ON node.nid=tracs.nid
WHERE node.status=1 AND node.uid=:uid
AND room_num>0
AND tracs.room_date BETWEEN CURRENT_DATE AND current_date+INTERVAL 3 MONTH
SQL;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(
                'uid' => $uid,
            ));
            $result = $stmt->fetch();
            if (empty($result)) {
                $score = 0;
            }
        }

        return $score;
    }

    private function check_tracs_lowprice($uid, $dest_id)
    {
        $exchange_rate = $this->get_dest_exchange_rate($dest_id);
        $min_local_price = $this->mini_price * $exchange_rate;
        $sql = <<<SQL
SELECT id,tracs.nid FROM LKYou.t_room_status_tracs tracs
JOIN one_db.drupal_node node ON node.nid=tracs.nid
WHERE node.status=1 AND node.uid=:uid
AND room_num>0 AND room_price BETWEEN 1 AND :price
AND tracs.room_date BETWEEN CURRENT_DATE AND current_date+INTERVAL 3 MONTH
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(
            'uid' => $uid,
            'price' => $min_local_price,
        ));
        $result = $stmt->fetchAll();

        return $result;
    }

    private function check_rpconfig_lowprice($uid, $dest_id)
    {
        $exchange_rate = $this->get_dest_exchange_rate($dest_id);
        $min_local_price = $this->mini_price * $exchange_rate;
        $sql = <<<SQL
SELECT * FROM  LKYou.t_rpconfig_v2 WHERE uid=:uid
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(
            'uid' => $uid,
        ));
        $result_rp = $stmt->fetch();
        $room_price = json_decode($result_rp['room_price']);

        $room_price_list = array();
        foreach ($room_price as $key => $value) {
            $prices = explode(',', $value->price);
            $prices = array_filter($prices);
            sort($prices);
            $price = 0;
            $price = array_shift($prices);
            if ($price < $min_local_price && $price > 0) {
                $room_price_list[] = $value->rid;
            }
        }

        return $room_price_list;
    }

    /**
     * 获取民宿点评评分
     *
     * @param $uid
     *
     * @return float
     */
    private function get_comment_score($uid)
    {
        $sql = <<<SQL
SELECT format(CASE WHEN avg(whole_exp) IS NULL THEN '0.0' ELSE avg(whole_exp) END, 1)
FROM LKYou.t_comment_info
WHERE status = 1 AND pid IS NULL AND whole_exp > 0 AND nid = :uid
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        $avg_score = $stmt->fetchColumn();
        $sql = 'SELECT count(id) FROM LKYou.t_comment_info WHERE status = 1 AND pid IS NULL AND nid=:uid';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        $total_comment_num = $stmt->fetchColumn();
        if ($total_comment_num > 0) {
            $num_score = 0.01 *  $total_comment_num;
            $num_score = $num_score > 5 ? 5 : $num_score;
        } else {
            $num_score = 5;
        }

        return array(
            floatval($avg_score) + $num_score,
            array(
                'avg_score' => $avg_score,
                'total_comment_num' => $total_comment_num,
            ),
        );
    }

    /**
     * 获取民宿房态准确率评分
     *
     * @param $uid
     *
     * @return int
     */
    private function get_cancel_order_score($uid)
    {
        $sql = <<<SQL
SELECT count(DISTINCT booking.id)
FROM
    LKYou.t_homestay_booking booking
        LEFT JOIN
    LKYou.log_homestay_booking_trac trac ON booking.id = trac.bid
        AND booking.guest_uid != trac.admin_uid
        AND trac.status IN (3 , 5)
        AND trac.content NOT LIKE '%超过%'
        AND trac.content NOT LIKE '%到 0元%'
        LEFT JOIN
    LKYou.log_homestay_booking_trac trac_traded ON booking.id = trac_traded.bid
        AND trac_traded.status = 2
WHERE
    booking.uid = :uid
        AND booking.closed_reasons IN (0 , 21, 22)
        AND booking.status IN (3 , 5)
        AND trac.tid IS NOT NULL
        AND trac_traded.tid IS NULL
        AND create_time > UNIX_TIMESTAMP(CURDATE() - INTERVAL 3 MONTH)
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        $cancel_order_num = $stmt->fetchColumn();
        $sql = <<<SQL
SELECT count(id) FROM LKYou.t_homestay_booking
WHERE uid=:uid AND create_time > unix_timestamp(curdate() - INTERVAL 3 MONTH)
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        $total_order_num = $stmt->fetchColumn();
        $cancel_rate = $cancel_order_num / $total_order_num;
        $cancel_order_score = $cancel_rate >= 0 ? 10 - intval($cancel_rate * 100 / 5) : 5;

        return array(
            $cancel_order_score < 0 ? 0 : $cancel_order_score,
            array(
                'cancel_rate' => $cancel_rate,
                'cancel_order_num' => $cancel_order_num,
                'total_order_num' => $total_order_num,
            ),
        );
    }

    /**
     * 获取民宿订单评分
     *
     * @param $uid
     *
     * @return array
     */
    private function get_order_score($uid)
    {
        $month = 3;
        $sql = <<<SQL
SELECT * FROM LKYou.t_homestay_booking WHERE uid=:uid AND status IN (2,6)
AND create_time > unix_timestamp(curdate() - INTERVAL :month MONTH)
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(
            'uid' => $uid,
            'month' => $month,
        ));
        $order_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $order_success_num = count($order_arr);
        if ($order_success_num > 0) {
            $order_amount = 0;
            $count_unit = 0;
            foreach ($order_arr as $order) {
                $order_amount += $order['total_price'];
                if ($order['room_price_count_check'] == 1) {
                    $count_unit += $order['guest_days'] * $order['room_num'];
                } elseif ($order['room_price_count_check'] == 2) {
                    $count_unit += $order['guest_days'] * $order['guest_number'];
                }
            }
            $avg_price = $order_amount / $count_unit;
            if ($avg_price >= 400) {
                $order_price_score = 5 + ($avg_price - 400) / 100;
            } elseif ($avg_price >= 150) {
                $order_price_score = 5 - (400 - $avg_price) / 100;
            } else {
                $order_price_score = 0;
            }
        } else {
            $order_price_score = 0;
        }

        return array(
            $order_success_num / 30,
            array(
                'order_success_num' => $order_success_num,
            ),
            $order_price_score,
        );
    }

    private function get_speed_score($uid)
    {
        $sql = <<<SQL
SELECT 
    SUM(speed_room) / COUNT(*) AS rate
FROM
    one_db.drupal_node
WHERE
    uid = :uid AND status = 1
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        $rate = $stmt->fetchColumn();
        if($rate >= 1) {
            return 10;
        } else if($rate >= 0.5) {
            return 5;
        } else {
            return 1;
        }
    }

    private function is_speed_room($uid)
    {
        $sql = <<<SQL
SELECT nid,speed_room FROM one_db.drupal_node
WHERE uid=:uid AND status=1 AND speed_room=1
ORDER BY nid DESC
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        $room_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($room_arr)) {
            return false;
        }
        foreach ($room_arr as $room) {
            $sql = <<<SQL
SELECT * FROM LKYou.t_speedroom_date
WHERE status = 1 AND rid = :rid
AND end_date>current_date
AND start_date<DATE_ADD(current_date, INTERVAL 3 MONTH)
LIMIT 1
SQL;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array('rid' => $room['nid']));
            $result = $stmt->fetch();
            if (empty($result)) {
                return $room['speed_room'];
            } else {
                return true;
            }
        }

        return false;
    }

    private function get_zzk_recscore($uid)
    {
        $sql = <<<SQL
SELECT score
FROM t_homestay_recscore
WHERE  uid = :uid
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));

        return $stmt->fetchColumn();
    }

    private function get_other_service_score($uid)
    {
        $sql = 'SELECT count(id) FROM LKYou.t_additional_service WHERE uid = :uid AND status=1 AND free=1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));

        return $stmt->fetchColumn();
    }

    private function get_order_price_score($uid)
    {
        $sql = <<<SQL
SELECT
(SELECT create_date-t_homestay_booking.create_time FROM log_homestay_booking_trac WHERE status IN (3,4,5) AND bid=t_homestay_booking.id AND admin_uid=:uid ORDER BY tid ASC LIMIT 1) self_time,
(SELECT price_tw FROM log_homestay_booking_trac WHERE status=4 AND bid=t_homestay_booking.id ORDER BY tid DESC LIMIT 1) latest_price,
t_homestay_booking.total_price_tw,t_homestay_booking_addition.original_price,
hour(FROM_UNIXTIME(t_homestay_booking.create_time)) create_hour,
t_homestay_booking.status
FROM t_homestay_booking
LEFT JOIN t_homestay_booking_addition ON t_homestay_booking.id=t_homestay_booking_addition.order_id
WHERE uid=:uid AND create_time > UNIX_TIMESTAMP(curdate()-INTERVAL 3 MONTH);
SQL;
        $sql_price_changed = <<<SQL
SELECT 
    COUNT(DISTINCT booking.hash_id) AS cnt
FROM
    LKYou.t_homestay_booking booking
        LEFT JOIN
    LKYou.log_homestay_booking_trac trac1 ON booking.id = trac1.bid
        AND trac1.status = 0
        LEFT JOIN
    LKYou.log_homestay_booking_trac trac2 ON booking.id = trac2.bid
        AND trac2.status = 4
WHERE
    booking.uid = :uid
        AND booking.create_time > UNIX_TIMESTAMP(CURDATE() - INTERVAL 3 MONTH)
        AND trac1.price_tw IS NOT NULL
        AND trac1.price_tw != 0
        AND trac2.price_tw IS NOT NULL
        AND trac1.price_tw != trac2.price_tw;
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        $order_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        #$price_count = 0;
        foreach ($order_arr as $order) {
            #if ($order['total_price_tw'] != null && $order['latest_price'] != null && $order['total_price_tw'] != $order['latest_price']) {
            #    $price_count++;
            #}
            if ($order['self_time'] && $order['create_hour'] <= 21 && $order['create_hour'] >= 9) {
                $self_time[] = $order['self_time'];
            }
        }
        $stmt = $this->pdo->prepare($sql_price_changed);
        $stmt->execute(array('uid' => $uid));
        $price_count = $stmt->fetchColumn();

        $total_order_count = count($order_arr);
        if ($total_order_count > 0) {
            $price_score = 10 - ($price_count / $total_order_count) / 0.05;
            $minus_score = 0;
        } else {
            $price_score = 5;
            $minus_score = 10;
        }
        if (!empty($self_time)) {
            $average_time = array_sum($self_time) / count($self_time);
            if (($average_time - 3600) > 0) {
                $minus_score = ($average_time - 3600) / 600;
            }
        }
        $time_score = 10 - $minus_score;

        return array(
            $price_score > 0 ? $price_score : 0,
            array(
                'price_rate' => $price_count / $total_order_count,
                'price_count' => $price_count,
                'total_order_count' => $total_order_count,
            ),
            $time_score > 0 ? $time_score : 0,
            array(
                'average_time' => $average_time,
            ),
        );
    }

    private function discount_score($uid)
    {
        $sql = <<<SQL
SELECT count(id) FROM t_disc_info
LEFT JOIN one_db.drupal_node ON t_disc_info.nid=one_db.drupal_node.nid
WHERE one_db.drupal_node.uid=:uid
AND disc<=0.95
AND t_disc_info.status=1
AND one_db.drupal_node.status=1
AND (occ_date BETWEEN UNIX_TIMESTAMP(current_date) AND unix_timestamp(DATE_ADD(CURDATE(), INTERVAL 1 YEAR)) OR occ_date=0)
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            return 10;
        } else {
            return 5;
        }
    }

    private function get_commission_rate_score($uid)
    {
        $sql = <<<SQL
SELECT rebate_num FROM t_weibo_poi_tw WHERE uid=:uid
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        $commission_rate = $stmt->fetchColumn();
        $commission_rate_score = $commission_rate - 5;
        $commission_rate_score = $commission_rate_score < 0 ? 0 : $commission_rate_score;
        $commission_rate_score = $commission_rate_score > 10 ? 10 : $commission_rate_score;
        return array(
            $commission_rate_score,
            array(
                'commission_rate' => $commission_rate,
            ),
        );
    }
}
