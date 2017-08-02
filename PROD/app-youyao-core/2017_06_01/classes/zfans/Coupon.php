<?php
apf_require_class("APF_DB_Factory");

class Zfans_Coupon {

	private $data = NULL;
	private $zfcLimits = NULL;
	private $daoZfans;

	private function __construct() {
		$this->daoZfans = new Dao_Activity_Zfans();
	}

	public static function getObject($coupon) {
        // 所有粉客优惠券失效
        return NULL;
        /*
		$slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$sql = 'SELECT t_zfans_coupons.*,t_zfans_refer.role FROM t_zfans_coupons LEFT JOIN t_zfans_refer ON t_zfans_coupons.uid=t_zfans_refer.uid WHERE coupon=:coupon';
		$stmt = $slave_pdo->prepare($sql);
		$stmt->execute(array('coupon' => $coupon));
		$data = $stmt->fetch(PDO::FETCH_OBJ);
		if ($data === FALSE) {
			return NULL;
		}

		$zfc = new Zfans_Coupon;
		$zfc->data = $data;
		return $zfc;
         */
	}

	public function getCouponStatus() {
		if ($this->data->coupon_status != 1) {
			return "invalid";
		}

		// 如果不是佣金转的优惠券，应用粉客共享优惠额度控制
		if ($this->data->coupon_type != 1) {
			$zfcLimits = $this->getZfansLimits();
			if ($zfcLimits && $zfcLimits['value'] >= $zfcLimits['limit']) {
				return "no_quota";
			}
		}

		// 如果是多次使用优惠券优惠券，判断次数是否使用完
		if ($this->data->coupon_quota > 1) {
			if ($this->data->coupon_used >= $this->data->coupon_quota) {
				return "no_quota";
			}
			return "ok";
		}

		// 其他情况，用正常优惠券逻辑判断
		$couponsdao = new Dao_Coupons_CouponsInfo();
		if ($couponsdao->get_valid_coupon($this->data->coupon)) {
			return "ok";
		}

		return "invalid";
	}

	public function getCouponDiscount() {
		$zfcLimits = $this->getZfansLimits();
		return Zfans_Coupon::get_coupon_discount($zfcLimits, $this->getRawCoupon());

	}

	public static function get_coupon_discount($zfcLimits, $coupon_obj) {
		if ($zfcLimits) {
			if ($zfcLimits['value'] >= $zfcLimits['limit']) {
				return 0.00;
			}

            if($coupon_obj->role == "blogger" && $coupon_obj->coupon_percent) {
                return 1 - $coupon_obj->coupon_percent;
            }

		    if (time() > strtotime('2016-01-01')) {
                return 0.01;
            } else {
                if (round($zfcLimits['value'] / $zfcLimits['limit'], 2) > 0.7) {
			    	return 0.01;
			    }
                return 0.05;
            }
		}

		if (time() > strtotime('2016-01-01')) return 0.01;

		return 0.05;
	}

	public function getCouponValue() {
		return $this->data->coupon_value;
	}

	public function getRawCoupon(){
		return $this->data;
	}

	public function useCoupon() {
		$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		$sql = "UPDATE t_zfans_coupons SET coupon_used = coupon_used + 1 WHERE coupon = :coupon";
		$stmt = $pdo->prepare($sql);
		$ret = $stmt->execute(array('coupon' => $this->data->coupon));
		if ($ret) {
			$this->data->coupon_used += 1;
		}

		return $ret;
	}

	private function getZfansLimits() {
		if (!$this->zfcLimits) {
            $type = $this->getRawCoupon()->role == "blogger" ? "zfans_tw_coupon_amount" : "coupon_amount";
			$this->zfcLimits = $this->daoZfans->get_zfans_coupon_limits($type);
		}

		return $this->zfcLimits;
	}
}
