<?php
apf_require_class("APF_DB_Factory");

class Dao_Activity_ZfansAdmin
{
	private $pdo;
	private $slave_pdo;

	public function __construct()
    {
		$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
	}

////////////////////////交易管理-business///////////////////////////////待定
    //////获取交易信息 未使用//////
    public function get_business_info($limit, $offset, $order_by_columns, $like_str)
    {
        $sql = <<<SQL
SELECT *
FROM t_zfans_balance_log
AND $like_str
ORDER BY $order_by_columns
LIMIT :limit OFFSET :offset
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

////////////////////////优惠券-coupon///////////////////////////////////
    //////获取优惠券列表-coupon-Manage//////
    public  function  get_coupon_column($limit, $offset, $order_by_columns, $like_str)
    {
        $sql = <<<SQL
SELECT t_zfans_coupons.uid,t_zfans_coupons.email,t_zfans_refer.uname,t_zfans_coupons.coupon,t_zfans_coupons.coupon_quota,t_zfans_coupons.coupon_used,t_zfans_coupons.coupon_percent,t_zfans_coupons.coupon_status,t_zfans_coupons.id,t_zfans_refer.role
FROM t_zfans_coupons  INNER JOIN t_zfans_refer  ON t_zfans_coupons.uid = t_zfans_refer.uid
WHERE t_zfans_coupons.uid > 0
AND $like_str
ORDER BY $order_by_columns
LIMIT :limit OFFSET :offset
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //优惠券过滤数量
    public function count_filter_coupon($like_str)
    {
        $sql = <<<SQL
SELECT a.uid,a.email,b.uname,a.coupon,a.coupon_quota,a.coupon_used,a.coupon_status,a.id
FROM t_zfans_coupons a INNER JOIN t_zfans_refer b ON a.uid = b.uid
WHERE a.uid > 0
AND $like_str
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $stmt = $this->slave_pdo->prepare('SELECT FOUND_ROWS()');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    //获取优惠券数量
    public function count_all_coupon()
    {
        $sql = <<<SQL
SELECT COUNT(a.id)
FROM t_zfans_coupons a INNER JOIN t_zfans_refer b ON a.uid = b.uid
WHERE a.uid > 0
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

/////////////////////////数据管理-data/////////////////////////////////
    //////获取粉客总排行(按间夜)-data-charts//////
    public function getTotalChart($begin,$end)
    {
        $sql = <<<SQL
select (@rowNO := @rowNo+1) AS id, a.* from (select b.uname ,cast(b.weixin_id as char(20)) weixin_id,cast(a.uid as char(20)) UID, b.email, b.city_name, sum(a.order_room_num*a.order_guest_days)  rn
from LKYou.t_zfans_balance_log a
inner join LKYou.t_zfans_refer b on a.uid = b.uid where a.type = 1 and a.status = 1 and (a.amount+a.points) > 0 and b.email not like '%@youyao%'
and order_succ_time>=UNIX_TIMESTAMP('$begin') AND order_succ_time<UNIX_TIMESTAMP('$end')
group by a.uid
order by rn desc
LIMIT 15) a,(select @rowNO :=0) b
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
    //获取粉客总排行(按金额)
    public function getTotalChartByPrice($end)
    {
        $endTime = strtotime($end)+24*60*60;

        $sql = <<<SQL
select (@rowNO := @rowNo+1) AS id, a.* from (select b.uname ,cast(b.weixin_id as char(20)) weixin_id,cast(a.uid as char(20)) UID, b.email, b.city_name,sum(a.order_total_price) total_price, sum(a.order_room_num*a.order_guest_days)  rn
from LKYou.t_zfans_balance_log a
inner join LKYou.t_zfans_refer b on a.uid = b.uid where a.type = 1 and a.status = 1 and (a.amount+a.points) > 0 and b.email not like '%@youyao%'
AND order_succ_time< ?
group by a.uid
order by total_price desc
LIMIT 15) a,(select @rowNO :=0) b
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($endTime));
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //获取粉客总排行(按金额)
    public function getBonusChartByPrice()
    {
        $sql = <<<SQL
select (@rowNO := @rowNo+1) AS id, c.* from (select uid, (select cast(weixin_id as char(20)) weixin_id from t_zfans_refer where uid = a.uid) weixin_id, sum(amount) amount from t_zfans_balance_log a where status = 1 and a.uid <> 72147 and type > 0 group by 2 order by amount desc limit 15) as c, (select @rowNO :=0) b;
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //获取九月成交风云榜(按间夜)
    public function getChartsTwo($begin,$end)
    {
        $sql = <<<SQL
select (@rowNO := @rowNo+1) AS id, a.* from (select  b.uname  ,cast(b.weixin_id as char(20)) weixin_id,cast(a.uid as char(20)) UID, b.email, b.city_name, sum(a.order_room_num*a.order_guest_days)  rn,sum(a.order_total_price) total_price
from LKYou.t_zfans_balance_log a
inner join LKYou.t_zfans_refer b on a.uid = b.uid where a.type = 1 and a.status = 1 and b.email not like '%@youyao%'
and order_succ_time>=UNIX_TIMESTAMP('$begin') AND order_succ_time<UNIX_TIMESTAMP('$end')
group by a.uid
order by rn desc
LIMIT 15) a,(select @rowNO :=0) b
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //获取十月成交风云榜(按金额)
    public function getWindAndCloundChart($begin,$end)
    {
        $beginTime = strtotime($begin);
        $endTime = strtotime($end) + 24*60*60;

        $sql = <<<SQL
select (@rowNO := @rowNo+1) AS id, a.* from (select  b.uname  ,cast(b.weixin_id as char(20)) weixin_id,cast(a.uid as char(20)) UID, b.email, b.city_name, sum(a.order_room_num*a.order_guest_days) rn, sum(a.order_total_price) total_price
from LKYou.t_zfans_balance_log a
inner join LKYou.t_zfans_refer b on a.uid = b.uid where a.type = 1 and a.status = 1 and (a.amount+a.points) > 0 and b.email not like '%@youyao%'
and order_succ_time>=? AND order_succ_time< ?
group by a.uid
order by total_price desc
LIMIT 15) a,(select @rowNO :=0) b
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($beginTime, $endTime));
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //获取十月积分榜
    public function getPointChart($begin,$end)
    {
        $beginTime = strtotime($begin);
        $endTime = strtotime($end) + 24*60*60;

        $sql = <<<SQL
select (@rowNO := @rowNo+1) AS id, a.* from (select  b.uname  ,cast(b.weixin_id as char(20)) weixin_id,cast(a.uid as char(20)) UID, b.email, b.city_name, sum(a.order_room_num*a.order_guest_days) rn, sum(a.order_total_price) total_price,sum(a.points) points
from LKYou.t_zfans_balance_log a
inner join LKYou.t_zfans_refer b on a.uid = b.uid where a.type = 1 and a.status = 1 and b.email not like '%@youyao%'
and order_succ_time>=? AND ?
group by a.uid
having points > 0
order by points desc
LIMIT 15) a,(select @rowNO :=0) b
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($beginTime, $endTime));
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //获取链接排行榜
    public function getRefChart($begin,$end)
    {
        $sql = <<<SQL
select (@rowNO := @rowNo+1) AS id, a.* from (SELECT t1.uid, t1.uname,t1.weixin_id, t1.rn rn, ifnull(t2.rn, 0) + ifnull(t4.rn,0) ljrn, ifnull(t3.rn,0) mrn
FROM (
  SELECT a.uid, b.uname,b.weixin_id, sum(a.order_room_num*a.order_guest_days) rn
  FROM t_zfans_balance_log a INNER JOIN t_zfans_refer b ON a.uid = b.uid
  WHERE  b.email not like '%@youyao.com%' and a.type = 1 AND a.status = 1 AND a.order_succ_time>=UNIX_TIMESTAMP('$begin') AND a.order_succ_time<UNIX_TIMESTAMP('$end')
  GROUP BY a.uid) t1
LEFT JOIN (
  SELECT a.uid, b.uname, sum(a.order_room_num*a.order_guest_days) rn
  FROM t_zfans_balance_log a INNER JOIN t_zfans_refer b ON a.uid = b.uid
  WHERE  b.email not like '%@youyao.com%' and a.type = 1 AND a.status = 1 AND a.order_succ_time>=UNIX_TIMESTAMP('$begin') AND a.order_succ_time<UNIX_TIMESTAMP('$end') AND a.share_method = 1
  GROUP BY a.uid) t2 ON t2.uid = t1.uid
LEFT JOIN (
  SELECT a.uid, b.uname, sum(a.order_room_num*a.order_guest_days) rn
  FROM t_zfans_balance_log a INNER JOIN t_zfans_refer b ON a.uid = b.uid
  WHERE  b.email not like '%@youyao.com%' and a.type = 1 AND a.status = 1 AND a.order_succ_time>=UNIX_TIMESTAMP('$begin') AND a.order_succ_time<UNIX_TIMESTAMP('$end') AND a.share_method = 2
  GROUP BY a.uid) t3 ON t3.uid = t1.uid
LEFT JOIN (
  SELECT a.uid, b.uname, sum(a.order_room_num*a.order_guest_days) rn
  FROM t_zfans_balance_log a INNER JOIN t_zfans_refer b ON a.uid = b.uid
  WHERE  b.email not like '%@youyao.com%' and a.type = 1 AND a.status = 1 AND a.order_succ_time>=UNIX_TIMESTAMP('$begin') AND a.order_succ_time<UNIX_TIMESTAMP('$end') AND a.share_method = 3
  GROUP BY a.uid) t4 ON t4.uid = t1.uid
 ORDER BY 5 DESC
LIMIT 15) a,(select @rowNO :=0) b
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //获取月极限挑战榜
    public function getChartsFour($type,$begin,$end)
    {
        $sql = <<<SQL
select (@rowNO := @rowNo+1) AS id, a.* from(SELECT
  CASE WHEN a2.uid IN (
    64877,
    8125,
    129354,
    141972,
    111380,
    273118,
    201376,
    259485,
    279812,
    132787,
    101614,
    38767,
    135649,
    139688,
    119338,
    261289,
    280230,
    35123
  )
    THEN '5'
  WHEN a2.uid IN (
    286561,
    85437,
    138879,
	98316,
	114810,
	118804,
	141325,
	140061,
	142542,
	94665
  )
    THEN '4'
  WHEN a2.uid IN (
    283341,
    198595,
    105968,
    285746,
    273841,
    83418,
    125874,
    116848,
    129857,
    115086,
    261697,
    66742
  )
   THEN '3'
  WHEN a2.uid IN (
    116848,
    131064,
    273841,
    85437,
    115086
  )
    THEN '2'
  WHEN a2.uid IN (
    29692,
    66742,
    83418,
    105968,
    114810,
    198595,
    261697
  )
    THEN '1'
  ELSE 'error' END AS type,
  a2.*,
  ifnull(a1.rn, 0)    rn

FROM
  (
    SELECT
      uname,
      cast(weixin_id AS CHAR(20)) weixin_id,
      cast(uid AS CHAR(20))       uid,
      email,
      city_name
    FROM LKYou.t_zfans_refer
    WHERE uid IN (
    64877,
    8125,
    129354,
    141972,
    111380,
    273118,
    201376,
    259485,
    279812,
    132787,
    101614,
    38767,
    135649,
    139688,
    119338,
    261289,
    280230,
    35123,

    286561,
    85437,
    138879,
	98316,
	114810,
	118804,
	141325,
	140061,
	142542,
	94665,

	283341,
    198595,
    105968,
    285746,
    273841,
    83418,
    125874,
    116848,
    129857,
    115086,
    261697,
    66742,

    116848,
    131064,
    273841,
    85437,
    115086,

    29692,
    66742,
    83418,
    105968,
    114810,
    198595,
    261697
    )

  ) a2
  LEFT JOIN

  (
    SELECT
      b.uname,
      cast(b.weixin_id AS CHAR(20))              weixin_id,
      cast(a.uid AS CHAR(20))                    uid,
      b.email,
      b.city_name,
      sum(a.order_room_num * a.order_guest_days) rn
    FROM LKYou.t_zfans_balance_log a
      INNER JOIN LKYou.t_zfans_refer b ON a.uid = b.uid
    WHERE a.type = 1 AND a.status = 1 AND b.email NOT LIKE '%@youyao%'
          AND order_succ_time >= UNIX_TIMESTAMP('2015-09-01') AND order_succ_time<UNIX_TIMESTAMP('$end')
    GROUP BY a.uid
    ORDER BY rn DESC
  ) a1 ON a1.uid = a2.uid
ORDER BY type ASC, rn DESC) a ,(select @rowNO :=0) b where type='$type'
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //获取九月进步榜(按间夜)
    public function getImproveChart($end)
    {
        $sql = <<<SQL
select (@rowNO := @rowNo+1) AS id, a.* from (select t1.uid, t1.weixin_id, t1.uname, t1.city_name, t1.zfans_ct, t1.rn 8月间夜, t2.rn 9月间夜, t2.rn - t1.rn diff, t2.latest_ot 最近成交
from (select b.uid, b.weixin_id, b.uname, b.city_name, date(from_unixtime(b.create_time)) zfans_ct, ifnull(sum(a.order_room_num*a.order_guest_days),0) rn
from t_zfans_refer b
left join t_zfans_balance_log a
on (a.uid = b.uid and a.status = 1 and a.type = 1 and a.order_succ_time >= unix_timestamp('2015-08-01') and a.order_succ_time < unix_timestamp('2015-09-01'))
where b.status = 1 group by b.uid order by rn desc) t1
inner join (select b.uid, b.uname, b.city_name, date(from_unixtime(b.create_time)) zfans_ct, ifnull(sum(a.order_room_num*a.order_guest_days),0) rn, date(from_unixtime(max(a.order_succ_time))) latest_ot
from t_zfans_refer b left join t_zfans_balance_log a
on (a.uid = b.uid and a.status = 1 and a.type = 1 and a.order_succ_time >= unix_timestamp('2015-09-01') and a.order_succ_time < unix_timestamp('$end'))
where b.status = 1
group by b.uid order by rn desc) t2
on t2.uid = t1.uid where t2.rn > 0 and (t2.rn - t1.rn)>0 order by diff desc, t2.rn desc, t1.rn desc LIMIT 15) a,(select @rowNO :=0) b
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //获取十月进步表(按金额)
    public function getImproveChart1510($end)
    {
        $sql = <<<SQL
SELECT
	(@rowNO := @rowNo + 1) AS id,
	a.*
FROM
	(
		SELECT
			t1.uid,
			t1.weixin_id,
			t1.uname,
			t1.city_name,
			t1.zfans_ct,
			t1.total_price 8月金额,
			t1.rn 8月间夜,
			t2.total_price 9月金额,
			t2.rn 9月间夜,
			t2.total_price - t1.total_price diff,
			t2.rn - t1.rn diffrn,
			t2.latest_ot 最近成交
		FROM
			(
				SELECT
					b.uid,
					b.weixin_id,
					b.uname,
					b.city_name,
					date(from_unixtime(b.create_time)) zfans_ct,
					ifnull(
						sum(
							a.order_room_num * a.order_guest_days
						),
						0
					) rn,
					ifnull(
						sum(
							a.order_total_price
						),
						0
					) total_price
				FROM
					t_zfans_refer b
				LEFT JOIN t_zfans_balance_log a ON (
					a.uid = b.uid
					AND a. STATUS = 1
					AND a.type = 1
                    AND (a.amount+a.points) > 0 
					AND a.order_succ_time >= unix_timestamp('2015-09-01')
					AND a.order_succ_time < unix_timestamp('2015-09-30')
				)
				WHERE
					b. STATUS = 1
				GROUP BY
					b.uid
			) t1
		INNER JOIN (
			SELECT
				b.uid,
				b.uname,
				b.city_name,
				date(from_unixtime(b.create_time)) zfans_ct,
				ifnull(
					sum(
						a.order_room_num * a.order_guest_days
					),
					0
				) rn,
				ifnull(
						sum(
							a.order_total_price
						),
						0
					) total_price,
				date(
					from_unixtime(max(a.order_succ_time))
				) latest_ot
			FROM
				t_zfans_refer b
			LEFT JOIN t_zfans_balance_log a ON (
				a.uid = b.uid
				AND a. STATUS = 1
				AND a.type = 1
                AND (a.amount+a.points) > 0
				AND a.order_succ_time >= unix_timestamp('2015-10-01')
				AND a.order_succ_time < unix_timestamp('$end')
			)
			WHERE
				b. STATUS = 1
			GROUP BY
				b.uid
		) t2 ON t2.uid = t1.uid
		WHERE
			t2.total_price > 0
		AND (t2.total_price - t1.total_price) > 0
		ORDER BY
			diff DESC,
			t2.total_price DESC,
			t1.total_price DESC
		LIMIT 15
	) a,
	(SELECT @rowNO := 0) b
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }


    public function getMonthlyImprovedData($dateStr)
    {
        $thisEnd = strtotime($dateStr);
        $thisBegin = mktime(0, 0, 0, date('n', $thisEnd), 1, date("Y", $thisEnd));
        $lastBegin = mktime(0, 0, 0, date('n', $thisEnd)-1, 1, date("Y", $thisEnd));
        $lastEnd = $thisBegin;
        $thisEnd += 24*60*60;

        $sql = <<<SQL
SELECT
	(@rowNO := @rowNo + 1) AS id,
	a.*
FROM
	(
		SELECT
			t1.uid,
			t1.weixin_id,
			t1.uname,
			t1.city_name,
			t1.zfans_ct,
			t1.total_price 8月金额,
			t1.rn 8月间夜,
			t2.total_price 9月金额,
			t2.rn 9月间夜,
			t2.total_price - t1.total_price diff,
			t2.rn - t1.rn diffrn,
			t2.latest_ot 最近成交
		FROM
			(
				SELECT
					b.uid,
					b.weixin_id,
					b.uname,
					b.city_name,
					date(from_unixtime(b.create_time)) zfans_ct,
					ifnull(
						sum(
							a.order_room_num * a.order_guest_days
						),
						0
					) rn,
					ifnull(
						sum(
							a.order_total_price
						),
						0
					) total_price
				FROM
					t_zfans_refer b
				LEFT JOIN t_zfans_balance_log a ON (
					a.uid = b.uid
					AND a. STATUS = 1
					AND a.type = 1
                    AND (a.amount+a.points) > 0
					AND a.order_succ_time >= :lastBegin
					AND a.order_succ_time < :lastEnd
				)
				WHERE
					b. STATUS = 1
				GROUP BY
					b.uid
			) t1
		INNER JOIN (
			SELECT
				b.uid,
				b.uname,
				b.city_name,
				date(from_unixtime(b.create_time)) zfans_ct,
				ifnull(
					sum(
						a.order_room_num * a.order_guest_days
					),
					0
				) rn,
				ifnull(
						sum(
							a.order_total_price
						),
						0
					) total_price,
				date(
					from_unixtime(max(a.order_succ_time))
				) latest_ot
			FROM
				t_zfans_refer b
			LEFT JOIN t_zfans_balance_log a ON (
				a.uid = b.uid
				AND a. STATUS = 1
				AND a.type = 1
                AND (a.amount+a.points) > 0
				AND a.order_succ_time >= :thisBegin
				AND a.order_succ_time < :thisEnd
			)
			WHERE
				b. STATUS = 1
			GROUP BY
				b.uid
		) t2 ON t2.uid = t1.uid
		WHERE
			t2.total_price > 0
		AND (t2.total_price - t1.total_price) > 0
		ORDER BY
			diff DESC,
			t2.total_price DESC,
			t1.total_price DESC
		LIMIT 15
	) a,
	(SELECT @rowNO := 0) b
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array(
            'lastBegin' => $lastBegin,
            'lastEnd' => $lastEnd,
            'thisBegin' => $thisBegin,
            'thisEnd' => $thisEnd,
        ));
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

/////////////////////////订单管理-order/////////////////////////////////
    //////获取订单详情列表-order-search//////
    public function get_order_detail($limit, $offset, $like_str)
    {
        $sql = <<<SQL
SELECT * FROM (SELECT a.id,a.uid,a.coupon_uid,a.zfansref_uid,a.type,a.status,a.share_method,a.oid,a.order_total_price,date(from_unixtime(a.order_succ_time)) ct,b.uname as homestayname, b.guest_mail,b.guest_name,b.guest_wechat,b.coupon,b.url_code
FROM t_zfans_balance_log as a
INNER JOIN t_homestay_booking b on a.oid=b.id
WHERE type IN (1,2) and a.status=1
ORDER BY a.order_succ_time DESC) as a
WHERE $like_str
LIMIT :limit OFFSET :offset
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //获取优惠券所属用户信息
    public  function getZfansByCouponUID($couponUID)
    {
        $sql = <<<SQL
SELECT uid,weixin_id,uname,date(from_unixtime(create_time)) create_time FROM t_zfans_refer
WHERE uid=$couponUID
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //获取链接所属用户信息
    public  function getZfansByRefUID($refUID)
    {
        $sql = <<<SQL
SELECT uid,weixin_id,uname,date(from_unixtime(create_time)) create_time FROM t_zfans_refer
WHERE uid=$refUID
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }


/////////////////////////粉客管理-user/////////////////////////////////
    //////获取粉客详情列表-user-manage//////
    public function get_zfans_detail($limit, $offset, $order_by_columns, $like_str)
    {
        $sql = <<<SQL
SELECT a.id,a.uid,a.uname,b.uname as name,a.role,a.phone_num,a.weixin_id,a.weixin_added,a.wxgroup_added,a.refer_uid,a.zfans_level,a.level_comment,a.comment,a.city_name,a.email,a.status,a.join_info,a.advantage_info,a.refer_info,a.self_media,a.source_info,a.self_intro,a.promotion_info,a.suggestion_info,a.update_time,a.create_time,a.blog,a.facebook
FROM t_zfans_refer as a
left join t_zfans_refer as b
on a.refer_uid=b.uid and a.refer_uid!=0
WHERE $like_str
ORDER BY $order_by_columns
LIMIT :limit OFFSET :offset
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //返回粉客过滤数量
    public function count_filter_zfans($like_str)
    {
        $sql = <<<SQL
SELECT id,uname,phone_num,weixin_id,weixin_added,wxgroup_added,city_name,email,status,self_intro,promotion_info
FROM  t_zfans_refer
WHERE $like_str
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $stmt = $this->slave_pdo->prepare('SELECT FOUND_ROWS()');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    //获取所有粉客数量
    public function count_all_zfans()
    {
        $sql = 'SELECT count(id) FROM t_zfans_refer';
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    //微信标记为已加
    public function wechat_active($id)
    {
        $sql = "UPDATE t_zfans_refer SET weixin_added=1 WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array('id' => $id));
    }

    //微信标记为未加
    public function wechat_cancel($id)
    {
        $sql = "UPDATE t_zfans_refer SET weixin_added=0 WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array('id' => $id));
    }

    //标记为已入群
    public function wxgroup_active($id)
    {
        $sql = "UPDATE t_zfans_refer SET wxgroup_added=1 WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array('id' => $id));
    }

    //标记为未入群
    public function wxgroup_cancel($id)
    {
        $sql = "UPDATE t_zfans_refer SET wxgroup_added=0 WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array('id' => $id));
    }

    //用户认证通过
    public function user_auth_confirm($id)
    {
        $sql = "UPDATE t_zfans_refer SET status=1 WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array('id' => $id));
    }

    //用户认证取消
    public function user_auth_cancel($id)
    {
        $sql = "UPDATE t_zfans_refer SET status=0 WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array('id' => $id));
    }

    //标记为大V粉客
    public function level_up($id)
    {
        $sql = "UPDATE t_zfans_refer SET zfans_level=1 WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array('id' => $id));
    }

    //标记为普通粉客
    public function level_down($id)
    {
        $sql = "UPDATE t_zfans_refer SET zfans_level=0 WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array('id' => $id));
    }

    //保存大V备注
    public function save_V_comment($id,$levelComment)
    {
        $sql = "UPDATE t_zfans_refer SET level_comment=:levelComment WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array('id' => $id,'levelComment' => $levelComment));
    }

    //保存备注
    public  function  save_comment($id,$comment)
    {
        $sql = "UPDATE t_zfans_refer SET comment=:comment WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array('id' => $id,'comment' => $comment));
    }

    //////获取我的粉客信息-user-Myzfans//////
    //获取我的信息
    public function get_myInfo($uid)
    {
        $sql = <<<SQL
SELECT id,uname,phone_num,weixin_id,weixin_added,wxgroup_added,refer_uid,zfans_level,city_name,email,status,join_info,advantage_info,refer_info,self_media,source_info,self_intro,promotion_info,suggestion_info,update_time,create_time
FROM t_zfans_refer
WHERE  uid=$uid
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //粉客审核通过 未使用
    public function zfans_active($id)
    {
        $sql = "UPDATE t_zfans_refer SET status=1 WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array('id' => $id));
    }

    //粉客审核不通过 未使用
    public function zfans_cancel($id)
    {
        $sql = "UPDATE t_zfans_refer SET status=-1 WHERE id=:id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array('id' => $id));
    }

    //////订单查询-user-Order//////
    //根据订单id， hash_id, 粉客的优惠码，客人邮箱，客人电话，查询订单信息
    public function getOrderList($key,$value)
    {
        $sql = <<<SQL
SELECT
	a.id,
	a.hash_id,
	a.city_name,
	a.uname,
	a.total_price,
	FROM_UNIXTIME(b.order_succ_time, '%m-%d %H:%i') as ordertime,
	a.zfansref,
	a.coupon,
    b.order_guest_days,
	b.order_room_num,
	b.status,
	b.share_method,
	b.guest_state,
	b.amount,
    b.points,
	a.guest_name,
	a.guest_mail,
	a.guest_telnum,
	a.guest_wechat,
	a.order_source,
	a.payment_type,
	b.update_time
FROM
	t_homestay_booking a
LEFT JOIN t_zfans_balance_log b ON a.id = b.oid
WHERE a.$key="$value"
ORDER BY update_time Desc
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }


    //////获取粉客统计-user-Statistics//////
    public function getOurDataInfo($limit, $offset, $order_by_columns, $like_str,$begin,$end)
    {
        $sql = <<<SQL
select b.id, b.uid, b.uname,b.weixin_id,b.city_name, b.email, count(distinct a.oid) orders, sum(a.order_room_num*a.order_guest_days) rn, sum(a.order_room_num*a.order_guest_days) / ((unix_timestamp() - min(a.order_succ_time)) / (24*60*60)) avg_rn,sum(amount) amount
from t_zfans_refer b
left join t_zfans_balance_log a
on (a.uid = b.uid and a.status = 1 and a.type = 1 )
where b.role ='zo' and b.status = 1
and (FROM_UNIXTIME(a.order_succ_time) > '$begin' and FROM_UNIXTIME(a.order_succ_time)<'$end' or a.order_succ_time is null)
group by b.uid
order by orders desc, b.id asc
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //获取发展的粉客数和大V数
    public function get_refer_stat($uid)
    {
        $sql = "select zfans_level, count(*) cnt from t_zfans_refer where status = 1 and refer_uid = :uid group by zfans_level";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = array(0, 0);
        foreach($data as $row) {
            if ($row['zfans_level'] > 0) {
                $result[1] += $row['cnt'];
            } else {
                $result[0] += $row['cnt'];
            }
        }
        return $result;
    }

    //获取所有发展粉客的成交间夜
    public function get_refer_roomnights($uid)
    {
        $sql = "select count(distinct oid), sum(order_room_num*order_guest_days) rn, sum(amount) amount
        from t_zfans_balance_log a
        INNER JOIN t_zfans_refer b
        ON (a.uid = b.uid and b.refer_uid=:uid)
        where a.type = 1 and a.status = 1 and a.oid > 0 ";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = array(0, 0);
        foreach($data as $row) {
            $result[0] += $row['rn'];
        }
        return $result;
    }

    //获取累计成交
    public function get_amount($begin,$end)
    {
        $sql = <<<SQL
select count(distinct oid) ordernum, sum(order_room_num*order_guest_days) rnnum, sum(amount) amount
from t_zfans_balance_log
where type = 1 and status = 1 and oid > 0 and uid <> 116280
and FROM_UNIXTIME(order_succ_time) > '$begin' and FROM_UNIXTIME(order_succ_time) < '$end'
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //获取所有粉客的成交业绩
    public function getAllDataInfo($limit, $offset, $order_by_columns, $like_str,$begin,$end)
    {
        $sql = <<<SQL
select * from (select b.id, b.uid, b.uname, b.weixin_id, b.phone_num, b.email, b.city_name, date(from_unixtime(b.create_time)) zfans_ct, date(from_unixtime(max(a.order_succ_time))) latest_ot, count(distinct a.oid) orders, sum(a.order_room_num*a.order_guest_days) rn, sum(amount) amount
from t_zfans_refer b
left join t_zfans_balance_log a on (a.uid = b.uid and a.status = 1 and a.type = 1 )
where b.status = 1
and FROM_UNIXTIME(order_succ_time) > '$begin' and FROM_UNIXTIME(order_succ_time) < '$end'
GROUP BY b.uid
ORDER BY orders DESC ,b.id ASC) as c
where $like_str
LIMIT :limit OFFSET :offset
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //所有粉客的成交过滤数量
    public function filterAllDataInfo($begin,$end,$like_str)
    {
        $sql = <<<SQL
select * from (select b.id, b.uid, b.uname, b.weixin_id, b.phone_num, b.email, b.city_name, date(from_unixtime(b.create_time)) zfans_ct, date(from_unixtime(max(a.order_succ_time))) latest_ot, count(distinct a.oid) orders, sum(a.order_room_num*a.order_guest_days) rn, sum(amount) amount
from t_zfans_refer b
left join t_zfans_balance_log a on (a.uid = b.uid and a.status = 1 and a.type = 1 )
where b.status = 1
and FROM_UNIXTIME(order_succ_time) > '$begin' and FROM_UNIXTIME(order_succ_time) < '$end'
GROUP BY b.uid
ORDER BY orders DESC ,b.id ASC) as c
WHERE $like_str
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $stmt = $this->slave_pdo->prepare('SELECT FOUND_ROWS()');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    //获取所有粉客的成交数量
    public function countAllDataInfo($begin,$end)
    {
        $sql = <<<SQL
select COUNT(*) from (select b.id, b.uid, b.uname, b.weixin_id, b.phone_num, b.email, b.city_name, date(from_unixtime(b.create_time)) zfans_ct, date(from_unixtime(max(a.order_succ_time))) latest_ot, count(distinct a.oid) orders, sum(a.order_room_num*a.order_guest_days) rn, sum(amount) amount
from t_zfans_refer b
left join t_zfans_balance_log a on (a.uid = b.uid and a.status = 1 and a.type = 1 )
where b.status = 1
and FROM_UNIXTIME(order_succ_time) > '$begin' and FROM_UNIXTIME(order_succ_time) < '$end'
GROUP BY b.uid
ORDER BY orders DESC ,b.id ASC) as c
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    //获取我推荐的粉客信息
    public function getRecomZfansInfo($limit, $offset, $order_by_columns, $like_str,$begin,$end,$uid)
    {
        $sql = <<<SQL
select * from (select b.id,
b.uid,
b.uname,
b.weixin_id,
b.phone_num,
b.email, b.city_name,
date(from_unixtime(b.create_time)) zfans_ct,
date(from_unixtime(max(a.order_succ_time))) latest_ot,
count(distinct a.oid) orders, sum(a.order_room_num*a.order_guest_days) rn,
sum(amount) amount
from t_zfans_refer b
left join t_zfans_balance_log a
on (a.uid = b.uid and a.status = 1 and a.type = 1 and a.order_succ_time >= unix_timestamp('2015-05-01'))
where b.status = 1 and b.refer_uid=:uid
group by b.uid
order by orders desc, b.id asc) as c
where zfans_ct > '$begin' and zfans_ct < '$end'
-- LIMIT :limit OFFSET :offset
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    //////获取成交明细-user-Transaction//////
    public function getTransctionDetail($like_str,$begin,$end,$uid)
    {
        $sql = <<<SQL
SELECT * from (select a.id,a.uname as homestayname, a.guest_mail,a.guest_name, a.status, a.campaign_code, a.order_source, a.room_num*a.guest_days rn, date(from_unixtime(b.order_succ_time)) ct,substr(from_unixtime(b.order_succ_time),6,11) cts, a.zfansref, a.coupon, c.uname, c.uid
from t_homestay_booking a
inner join t_zfans_balance_log b on (a.id = b.oid and b.type = 1 and b.status = 1)
inner join t_zfans_refer c on b.uid = c.uid
WHERE b.uid=$uid order by b.order_succ_time DESC ) as d
where ct > '$begin' and ct < '$end'
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        //$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }


    //
    public function countFilterTransctionDetail($like_str,$uid)
    {
        $sql = <<<SQL
SELECT * from (select a.id, substr(from_unixtime(b.order_succ_time),6,11) ct, a.guest_mail,a.guest_name, a.status, a.campaign_code, a.order_source, a.room_num*a.guest_days rn, a.zfansref, a.coupon, c.uname, c.uid
from t_homestay_booking a
inner join t_zfans_balance_log b on a.id = b.oid
inner join t_zfans_refer c on b.uid = c.uid
WHERE b.uid=$uid order by b.order_succ_time DESC ) as c
WHERE $like_str
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $stmt = $this->slave_pdo->prepare('SELECT FOUND_ROWS()');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    //
    public function countAllTransctionDetail($uid)
    {
        $sql = <<<SQL
SELECT count(*) from (select a.id, substr(from_unixtime(b.order_succ_time),6,11) ct, a.guest_mail,a.guest_name, a.status, a.campaign_code, a.order_source, a.room_num*a.guest_days rn, a.zfansref, a.coupon, c.uname, c.uid
from t_homestay_booking a
inner join t_zfans_balance_log b on a.id = b.oid
inner join t_zfans_refer c on b.uid = c.uid
WHERE b.uid=$uid order by b.order_succ_time DESC ) as c
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

////////////////////////支付宝审批-withdraw-Account////////////////////////////////////
    public function wdaccount_active($id) {
		$sql = "UPDATE t_zfans_withdraw_account SET status=1 WHERE id=:id";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute(array('id' => $id));
	}

	public function wdaccount_cancel($accound_id) {
		$sql = 'UPDATE t_zfans_withdraw_account SET status=-1 WHERE id=:id';
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute(array('id' => $accound_id));
	}

    //保存提现账户备注
    public function save_account_comments($id,$comments){
        $sql = <<<SQL
UPDATE t_zfans_withdraw_account SET comments='$comments' WHERE id=$id;
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        return $stmt->execute();

    }

	public function count_all_withdraw_account() {
		$sql = 'SELECT count(id) FROM t_zfans_withdraw_account';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	public function count_filter_withdraw_account($like_str) {
		$sql = <<<SQL
SELECT t_zfans_refer.uname, t_zfans_refer.email, t_zfans_refer.weixin_id,
t_zfans_withdraw_account.id,zfb_account,zfb_name,t_zfans_withdraw_account.status,t_zfans_withdraw_account.comments,from_unixtime(t_zfans_withdraw_account.create_time) create_time
FROM t_zfans_withdraw_account
INNER JOIN t_zfans_refer ON t_zfans_withdraw_account.uid=t_zfans_refer.uid
WHERE $like_str
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute();
		$stmt = $this->slave_pdo->prepare('SELECT FOUND_ROWS()');
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	public function get_withdraw_account($limit, $offset, $order_by_columns, $like_str) {
		$sql = <<<SQL
SELECT t_zfans_refer.uname, t_zfans_refer.email, t_zfans_refer.weixin_id,
t_zfans_withdraw_account.id,zfb_account,zfb_name,bank_master_name,bank_master_code,bank_branch_name,bank_branch_code,bank_account,bank_username,t_zfans_withdraw_account.status,t_zfans_withdraw_account.comments,from_unixtime(t_zfans_withdraw_account.create_time) create_time
FROM t_zfans_withdraw_account
INNER JOIN t_zfans_refer ON t_zfans_withdraw_account.uid=t_zfans_refer.uid
WHERE $like_str
ORDER BY $order_by_columns
LIMIT :limit OFFSET :offset
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}

///////////////////////////提现账户详情-withdraw-Detail///////////////////////////////////
    //
    public function get_withdraw_record_info($id) {
        $sql = 'SELECT * FROM t_zfans_balance_log WHERE id=:id AND type=-1';
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('id' => $id));
        return $stmt->fetch();
    }

    //
    public function get_user_balance_log($uid, $timestamp) {
#        $sql = <<<SQL
#SELECT id,uid,type,amount,status,bonus_rate,FROM_UNIXTIME(bonus_valid_time,'%Y-%m-%d') bonus_valid_time,
#oid,order_total_price,order_status
#FROM t_zfans_balance_log
#WHERE type IN (1,2) AND uid=:uid AND bonus_valid_time<:timestamp AND status=1
#ORDER BY id DESC
#SQL;
        $sql = <<<SQL
SELECT id,uid,type,amount,status,bonus_rate,FROM_UNIXTIME(bonus_valid_time,'%Y-%m-%d') bonus_valid_time,
oid,order_total_price,order_status
FROM t_zfans_balance_log
WHERE type IN (1,2) AND uid=:uid AND status=1
ORDER BY id DESC
SQL;

        $stmt = $this->slave_pdo->prepare($sql);
        #$stmt->execute(array('uid' => $uid, 'timestamp' => $timestamp));
        $stmt->execute(array('uid' => $uid));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //
    public function count_user_balance_log($uid, $timestamp) {
        #$sql = 'SELECT count(id) FROM t_zfans_balance_log WHERE type IN (1,2) AND uid=:uid AND bonus_valid_time<:timestamp AND status=1';
        $sql = 'SELECT count(id) FROM t_zfans_balance_log WHERE type IN (1,2) AND uid=:uid AND status=1';
        $stmt = $this->slave_pdo->prepare($sql);
        #$stmt->execute(array('uid' => $uid, 'timestamp' => $timestamp));
        $stmt->execute(array('uid' => $uid));
        return $stmt->fetchColumn();
    }

    //
    public function get_withdraw_record_detail($id) {
        $sql = <<<SQL
SELECT a.id,a.uid,b.uname,from_unixtime(a.create_time) create_time,a.amount,
a.status,c.zfb_account,c.zfb_name,c.status account_status
FROM t_zfans_balance_log a
INNER JOIN t_zfans_refer b ON a.uid = b.uid
LEFT JOIN t_zfans_withdraw_account c ON a.uid = c.uid AND c.status=1
WHERE a.type = -1 AND a.id=:id
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('id' => $id));
        return $stmt->fetch();
    }

///////////////////////////提现账户待审批-withdraw-Record///////////////////////////////////
    //////获取待审列表
	public function get_withdraw_record($limit, $offset, $order_by_columns, $like_str)
    {
		$sql = <<<SQL
SELECT a.id,a.uid,a.remittance_code,b.uname,from_unixtime(a.create_time) create_time,a.amount,a.status,
(SELECT zfb_name FROM t_zfans_withdraw_account WHERE uid = a.uid AND status=1 order by id desc limit 1) zfb_name,
(SELECT zfb_account FROM t_zfans_withdraw_account WHERE uid = a.uid AND status=1 order by id desc limit 1) zfb_account,
(SELECT bank_master_name FROM t_zfans_withdraw_account WHERE uid = a.uid AND status=1 order by id desc limit 1) bank_master_name,
(SELECT bank_master_code FROM t_zfans_withdraw_account WHERE uid = a.uid AND status=1 order by id desc limit 1) bank_master_code,
(SELECT bank_branch_name FROM t_zfans_withdraw_account WHERE uid = a.uid AND status=1 order by id desc limit 1) bank_branch_name,
(SELECT bank_branch_code FROM t_zfans_withdraw_account WHERE uid = a.uid AND status=1 order by id desc limit 1) bank_branch_code,
(SELECT bank_account FROM t_zfans_withdraw_account WHERE uid = a.uid AND status=1 order by id desc limit 1) bank_account,
(SELECT bank_username FROM t_zfans_withdraw_account WHERE uid = a.uid AND status=1 order by id desc limit 1) bank_username,
(SELECT status FROM t_zfans_withdraw_account WHERE uid = a.uid AND status=1 order by id desc limit 1) account_status
FROM t_zfans_balance_log a
INNER JOIN t_zfans_refer b ON a.uid = b.uid
WHERE a.type = -1 AND ($like_str)
ORDER BY $order_by_columns
LIMIT :limit OFFSET :offset
SQL;
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

    //提现过滤数量
    public function count_filter_withdraw_record($like_str)
    {
        $sql = <<<SQL
SELECT a.id,a.uid,b.uname,from_unixtime(a.create_time) create_time,a.amount,
a.status
FROM t_zfans_balance_log a
INNER JOIN t_zfans_refer b ON a.uid = b.uid
WHERE a.type = -1 AND ($like_str)
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        $stmt = $this->slave_pdo->prepare('SELECT FOUND_ROWS()');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    //获取提现总数
    public function count_all_withdraw_record()
    {
        $sql = <<<SQL
SELECT count(a.id) FROM t_zfans_balance_log a
INNER JOIN t_zfans_refer b ON a.uid = b.uid
LEFT JOIN t_zfans_withdraw_account c ON a.uid = c.uid AND c.status=1
WHERE type = -1
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    //提现审核通过
    public function withdraw_pass($id)
    {
        $sql = 'UPDATE t_zfans_balance_log SET status=1 WHERE id=:id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array('id' => $id));
    }

    //提现审核不通过
    public function withdraw_not_pass($id)
    {
        $sql = 'UPDATE t_zfans_balance_log SET status=-1 WHERE id=:id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array('id' => $id));
    }



}
