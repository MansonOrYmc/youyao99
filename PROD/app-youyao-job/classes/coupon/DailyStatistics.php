<?php
class Coupon_DailyStatistics{
	public function run(){
		$date = date('Y-m-d',strtotime('yesterday'));
		$dao_coupon = new Dao_Coupons_CouponsInfo();
		$result = $dao_coupon->daily_statistics($date);
		$content='日期：'.$date.'<br />';
		$content.='领取次数（包含0元）：'.$result['count_num'].'<br />';
		$content.='领取金额：'.$result['amount'].'<br />';

		$result = $dao_coupon->daily_use_statistics($date);

		$content.='使用张数：'.$result['count_num'].'<br />';
		$content.='使用总金额：'.$result['amount'].'<br />';
		$content.='使用订单总金额：'.$result['order_amount'].'<br />';
		Util_SmtpMail::send('zhenghu@zizaike.com', '红包领取每日报告', $content, 'noreply@zizaike.com');
		Util_SmtpMail::send('kellyzeng@zizaike.com', '红包领取每日报告', $content, 'noreply@zizaike.com');
	}
}