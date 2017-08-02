<?php
class Util_ThemplateMail {

	public static function contact_user_mail($to, $params, $from) {
		global $bnb_dest_id;
		$bll_area_info = new Bll_Area_Area();
		$subject = '['.$bll_area_info->get_dest_language($bnb_dest_id,"youyao").'] '.$params['guest_name'].' '.$params['message_subject'].'(#'.$params['hash_id'].')';;
		if($params['hash_id']){
			$message_body_1 = 'Hello, '.$params['recipient']->name;
			$message_body_2 = "[自在客] ".$params['guest_name']." ".$params['message_subject']."";
			$message_body_3 = $params['message'];
			$message_body = '
           		<div style="font-size:26px; text-align:center; color: #0E74B2; font-weight:bold;">'.$message_body_2.'</div>
           		<div style="font-size:14px; padding:10px;color:#333;margin-top:10px;">'.$message_body_1.'</div>
           		<div style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;line-height:25px;">'.$message_body_3.'</div>
      			<div style="clear:both;">&nbsp;</div>';
			if($bnb_dest_id<11){
				$message_body .= '
		      <div style=" clear:both;background-color:#f6f6f6;  padding:10px;margin-top:40px;font-size:12px;line-height:22px;";>
		        <srong style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat -2px 0px;padding-left:17px;">温馨提示：</strong><br>
		        自在客爲您提供《台灣民宿》手機應用，讓您可以在手機上隨時隨地處理訂單。<br>
		        手機應用下載地址：<a href="http://pages.youyao.com/smart_phone" target="_blank">http://pages.youyao.com/smart_phone</a><br />
		      </div>
		        ';
			}
			$body = preg_replace('/[\n]/', '', $message_body);
		}else {
			$subject = '［自在客］客人已經下單1小時了，請及時處理哦';
			$message_body_2 = '［自在客］客人已經下單1小時了，請及時處理哦';
			$message_body_1 = '尊敬的 '.$params['uname'].' 您好';
			$message_body = '
	           <div style="font-size:26px; text-align:center; color: #0E74B2; font-weight:bold;">'.$message_body_2.'</div>
	           <div style="font-size:14px; padding:10px;color:#333;margin-top:10px;">'.$message_body_1.'</div>
	           <div style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;line-height:25px;">
	            訂單'.$params['oids'].' 已經等待1小時了，請及時處理讓客人快快付款，不要讓客人溜走哦！<br>（Tips: 可以直接點擊訂單號打開處理哦！^_^）
	           </div>
		      <div style="clear:both;">&nbsp;</div>
		      <div style=" clear:both;background-color:#efefef; height:200px; padding:10px;margin-top:40px;font-size:12px;line-height:22px;";>
		        <div style="float:left;">
		        <srong style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat -2px 0px;padding-left:17px;">温馨提示：</strong><br>
		        可以下載自在客的手機APP來隨手處理訂單哦！<br>
		        做一名移動達人～！<br>
		        </div>
		        <div style="float:right;">
		        <img src="http://wiki.youyao.com/images/9/9e/Viewfile.png"><br />
		        </div>
		      </div>
		        ';
			$body = preg_replace('/[\n]/', '', $message_body);
		}

		Util_SmtpMail::send($to, $subject, $body, $from);
	}

	public static function contact_user_check($to, $params, $from) {
		if($params['subject_title']){
			$subject = $params['subject_title'];
		}else{
			$subject = '[自在客] '.$params['recipient']->name.' 给您的咨询单确认邮件';
		}

		$message_str = '您好， '.$params['guest_name'].',';
		$message_con = $params['message_to_user'];
		$message_body = '
           <div style="font-size:26px; text-align:center; color: #0E74B2; font-weight:bold;">自在客咨询单确认邮件</div>
          	<div style="font-size:14px; padding:10px;color:#333;margin-top:10px;">'.$message_str.'</div>
      		<div style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;">
         	<div style="color:#333;margin-top:0px;font-size:14px;line-height:20px;">已收到您的咨询单，民宿主人将会在'.Util_Common::zzk_exchange_time().'之内与您联系，请您留意手机短信和邮件。</div>
      		</div>
      		<div style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;line-height:25px;">
        	'.$message_con.'
      		</div>
      		<div style="clear:both;">&nbsp;</div>
      		<div style=" clear:both;background-color:#f6f6f6;  padding:10px;margin-top:40px;font-size:12px;line-height:22px;";>
        	<srong style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat 0px;padding-left:17px;">温馨提示：</strong><br>
	         欢迎下载《自在客》手机应用，让您可以在手机上随时随地预订民宿，联系民宿主人，查看您的订单凭证。<br>
	         下载地址：<a href="http://www.youyao.com/v2/smart_phone" target="_blank">http://www.youyao.com/v2/smart_phone</a><br />
	      </div>
        ';
		$body = preg_replace('/[\n]/', '', $message_body);
		Util_SmtpMail::send($to, $subject, $body, $from);
	}

	public static function contact_user_order_payment($to, $params, $from) {
		$subject = '[自在客] 好消息！您的订单已经可以支付了';

		if(isset($params['order_message']) && !empty($params['order_message'])){
			$message_param = '<div style="background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat 0px;padding-left:17px;padding-bottom:10px;"><b>民宿主人说：</b>'.$params['order_message'].'<br></div>';
		}
		if($params['order']->guest_etc){
			$message_beizhu = '<li style="list-style:none;font-size:14px;"><strong>备注留言：</strong>'.$params['order']->guest_etc.'</li>';
		}
		//$url_code = $params['order']->id;
		$url_code = $params['order']->url_code;
		$child_number = $params['order']->guest_child_number ? '&nbsp;&nbsp;+ 儿童：'.$params['order']->guest_child_number .' 人 ' : '';
		if($params['order']->guest_child_age){
			$child_number .= '('.$params['order']->guest_child_age.")";
		}

		$homestay_util = new Util_HomeStayUtil();
		$rev_percent = $params['order']->rev_percent?$params['order']->rev_percent:100;
		if($rev_percent==100){
			$other_yf_price = '';
		}else{
			$dest_row = $homestay_util->get_dest_config($params['order']->dest_id);
			$yu_total_price = Util_Common::zzk_pay_price_format($params['order']->total_price*($rev_percent/100));
			$dao_total_price = Util_Common::zzk_pay_price_format($params['order']->total_price_tw*((100 - $rev_percent)/100));
			$other_yf_price .= '<li style="list-style:none;font-size:18px;"><strong>预付费用：</strong><font color="#ff6602">'.$yu_total_price.' 元(RMB)</font>，在自在客完成付款</li>';
			$other_yf_price .= '<li style="list-style:none;font-size:14px;"><strong>现付费用：</strong>'.$dao_total_price.' 元('.$dest_row['currency_ios_code'].')，入住民宿时才付款，请准备好现金</li>';
		}
		$total_order_guest_mumber = $params['order']->guest_number + $params['order']->guest_child_number;
		if($params['order']->add_bed_price && ($total_order_guest_mumber > ($params['order']->book_room_model*$params['order']->room_num))){
			$add_bed_price = ($total_order_guest_mumber - $params['order']->book_room_model*$params['order']->room_num) * $params['order']->add_bed_price * $params['order']->guest_days;
			$book_room_model_li .= '，含加人费用: '. $add_bed_price.'元 (RMB)(加 '.$params['order']->guest_days.' 天 * '.($total_order_guest_mumber - $params['order']->book_room_model*$params['order']->room_num).' 人 x '.$params['order']->add_bed_price.'元RMB/人)';
		}
		$message_body = '
           <div style="font-size:26px; text-align:center; color: #0E74B2; font-weight:bold;">自在客待支付邮件</div>       <div style="font-size:14px; padding:10px;color:#333;margin-top:10px;">您好，'.$params['order']->guest_name.'，已确认当天有房间，为了给您保留房间，需要您在今天支付房费。</div>
      <div style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;">
         <div style="color:#0E74B2;font-size:16px;">订单编号：#'.(empty($params['order']->hash_id)?$params['order']->id:$params['order']->hash_id).'</div>
             <div style="color:#333;margin-top:5px;font-size:14px;line-height:20px;">
             '.$message_param.'<div style="color:#333;margin-top:5px;margin-bottom:10px;font-size:14px;line-height:20px;"><a href="'.Const_Host_Domain.'/user/payment/'.$url_code.'" target="_blank" style="text-decoration: none;"><img src="http://pages.youyao.com/a/img/zzkpaybutton.png" /></a></div> 付款链接：<a href="'.Const_Host_Domain.'/user/payment/'.$url_code.'" target="_blank"><b><font color="#3D5E86">'.Const_Host_Domain.'/user/payment/'.$url_code.'</font></b></a><br><br><span style="background:url(http://wiki.youyao.com/images/b/b1/I2.png) no-repeat 0px 2px;padding-left:14px;color:#ccc;">如果无法点击上面的链接，您可以复制该地址，并粘帖在浏览器的地址栏中访问</span></div>
      </div>
      <ul style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;line-height:25px;">
            <li style="list-style:none;font-size:16px;margin-bottom:10px;color:#0E74B2;">订单信息</li>
            <li style="list-style:none;font-size:14px;"><strong>入住民宿：</strong>'.$params['order']->uname.'</li>
            <li style="list-style:none;font-size:14px;"><strong>入住房间：</strong>'.$params['order']->room_name.'</li>
            <li style="list-style:none;font-size:14px;"><strong>入住人数：</strong>成人：'.$params['order']->guest_number.'人'.$child_number.'</li>
            <li style="list-style:none;font-size:14px;"><strong>房间数量：</strong>'.$params['order']->room_num.'间</li>
            <li style="list-style:none;font-size:14px;"><strong>入住日期：</strong>'.Util_Common::zzk_date_format($params['order']->guest_date).'</li>
            <li style="list-style:none;font-size:14px;"><strong>退房日期：</strong>'.Util_Common::zzk_date_format($params['order']->guest_checkout_date).'</li>
            <li style="list-style:none;font-size:14px;"><strong>入住天数：</strong>'.$params['order']->guest_days.'天</li>
            <li style="list-style:none;font-size:14px;"><strong>邮箱地址：</strong>'.$params['order']->guest_mail.'</li>
            <li style="list-style:none;font-size:14px;"><strong>联系电话：</strong>'.$params['order']->guest_telnum.'</li>
            '.$message_beizhu.'
            <li style="list-style:none;font-size:14px;"><strong>总 &nbsp;房&nbsp; 费：</strong><font>'.$params['order']->total_price.'元（RMB）'.$book_room_model_li.'</font></li>
            '.$other_yf_price.'
      </ul>
      <div style="clear:both;">&nbsp;</div>
      <div style=" clear:both;background-color:#f6f6f6;  padding:10px;margin-top:40px;font-size:12px;line-height:22px;";>
        <srong style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat 0px;padding-left:17px;">温馨提示：</strong><br>
        欢迎下载《自在客》手机应用，让您可以在手机上随时随地预订民宿，联系民宿主人，查看您的订单凭证。<br>
        下载地址：<a href="http://www.youyao.com/v2/smart_phone" target="_blank">http://www.youyao.com/v2/smart_phone</a><br />
      </div>
        ';
		$body = preg_replace('/[\n]/', '', $message_body);
		Util_SmtpMail::send($to, $subject, $body, $from);
	}

	public static function contact_user_order_succ($to, $params, $from) {

		$dao_user_info = new Dao_User_UserInfo();

		$acc = $dao_user_info->load_user_info($params['order']->uid);
		$acc->mail = preg_replace('/\.zzk\.group\.[a-zA-Z0-9]+/', '', $acc->mail);

		$subject = '[自在客] '.$params['order']->uname.' 给您的订单确认邮件';

		$message['body'] = array();
		$params['order']->guest_etc = str_replace('来自iPhone客户端','',$params['order']->guest_etc);
		if($params['order']->guest_etc){
			$message_beizhu = '<li><strong>备注留言：</strong>'.$params['order']->guest_etc.'</li>';
		}
		//支付说明
		$params['order']->intro = str_replace('已经确定有房间，请尽快支付完成预订。','',$params['order']->intro);
		$params['order']->intro = str_replace('凭入住凭证入住','',$params['order']->intro);
		$message_intro = '<li class="master-say-li"><strong>民宿主人说：</strong><span class="master-say"><img src="http://pages.youyao.com/a/images/icon/notice_mark.png" alt="notice" style="margin-top:-3px;margin-right:3px;">'.$params['order']->intro.'  (请提前一天联系我，告知入住时间，谢谢！)</span></li>';
		$child_number = $params['order']->guest_child_number ? '&nbsp;&nbsp;+ 儿童：'.$params['order']->guest_child_number .' 人 ' : '';
		if($params['order']->guest_child_age){
			$child_number .= '('.$params['order']->guest_child_age.")";
		}

		$check_in_out = '';
		if(empty($acc->checkin_at)){
			$check_in_out .= '入住时间 15:00';
		}else{
			$check_in_out .= ($acc->checkin_at <= "12:00" ) ? '入住时间 ' : '入住时间';
			$check_in_out .= $acc->checkin_at;
		}if(empty($acc->checkin_stop)){
            $check_in_out .= '入住截止时间 20:00';
        }else{
            $check_in_out .= ($acc->checkin_stop <= "12:00" ) ? '入住截止时间 ' : '入住截止时间';
            $check_in_out .= $acc->checkin_stop;
        }
		if(empty($acc->checkout_at)){
			$check_in_out .= '，入住过后退房时间11:00';
		}else{
			$check_in_out .= ($acc->checkout_at <= "12:00" ) ? '，入住过后退房时间' : '，入住过后退房时间';
			$check_in_out .= $acc->checkout_at;
		}

		$homestay_util = new Util_HomeStayUtil();
		$snsweixin = !empty($acc->field_weixin['und'][0]['value']) ? '<li><strong>民宿微信：</strong>'.$acc->field_weixin['und'][0]['value'].'   (申请时请填写真实姓名)</li>' : '';
		$snsline = !empty($acc->field_line['und'][0]['value']) ? '<li><strong>民宿line：</strong>'.$acc->field_line['und'][0]['value'].'   (申请时请填写真实姓名)</li>' : '';
		$rev_percent = $params['order']->rev_percent?$params['order']->rev_percent:100;
		if($rev_percent==100){
			$other_yf_price = '';
		}else{
			//民宿主人
			$dest_row = $homestay_util->get_dest_config($params['order']->dest_id);
			$yu_total_price = Util_Common::zzk_pay_price_format($params['order']->total_price*($rev_percent/100));
			$dao_total_price = Util_Common::zzk_pay_price_format($params['order']->total_price_tw*((100 - $rev_percent)/100));
			$other_yf_price .= '<li style="list-style:none;font-size:14px;"><strong>预付费用：</strong><font color="#ff6602">'.$yu_total_price.' 元(RMB)</font>，在自在客完成付款</li>';
			$other_yf_price .= '<li><strong>现付费用：</strong><font>'.$dao_total_price.' 元('.$dest_row['currency_ios_code'].')</font>，入住民宿时才付款，请准备好现金</li>';
		}
		$total_order_guest_mumber = $params['order']->guest_number + $params['order']->guest_child_number;
		if($params['order']->add_bed_price && ($total_order_guest_mumber>($params['order']->book_room_model*$params['order']->room_num))){
			$add_bed_price = ($total_order_guest_mumber - $params['order']->book_room_model*$params['order']->room_num) * $params['order']->add_bed_price * $params['order']->guest_days;
			$book_room_model_li .= '，含加人费用: '. $add_bed_price.'元 (RMB)(加 '.$params['order']->guest_days.'天 * '.($total_order_guest_mumber - $params['order']->book_room_model*$params['order']->room_num).' 人 x '.$params['order']->add_bed_price.'元RMB/人)';
		}
		$message_body = '<div class="orderbyuser"><div class="order-back">
    	  <h2 class="order-title">民宿入住凭证</h2>
          <p class="order-id">订单编号：#'.$params['order']->id.'</p>
          <div id="edit-homestay-detail" class="form-wrapper" >
	    <div class="form-title"><span>民宿信息</span></div>
            <div class="fieldset-wrapper"><ul>
              <li><strong>入住民宿：</strong>'.$acc->name.'</li>
              <li><strong>入住房间：</strong>'.$params['order']->room_name.'</li>
              <li><strong>联系电话：</strong>'.$acc->tel_num.' , '.$acc->send_sms_telnum.'</li>
              <li><strong>联系邮箱：</strong>'.$acc->mail.'</li>
              '.$snsweixin.$snsline.'
              <li><strong>民宿地址：</strong>'.$acc->address.'</li>
              <li><strong>登记时间：</strong>'.$check_in_out.'</li>
              '.$message_intro.'
            </ul></div>
          </div>
          <div id="edit-order-detail" class="form-wrapper">
            <div class="form-title"><span>订单信息：</span></div>
	    <div class="fieldset-wrapper"><ul>
              <li><strong>入住客人：</strong>'.$params['order']->guest_name.'</li>
              <li><strong>入住日期：</strong>'.Util_Common::zzk_date_format($params['order']->guest_date).'</li>
              <li><strong>退房日期：</strong>'.Util_Common::zzk_date_format($params['order']->guest_checkout_date).'</li>
              <li><strong>客人电话：</strong>'.$params['order']->guest_telnum.'</li>
              <li><strong>预订时间：</strong>'.date('Y-m-d', $params['order']->last_modify_date).'</li>
              <li><strong>房间数量：</strong>'.$params['order']->room_num.'间</li>
              <li><strong>入住人数：</strong>成人：'.$params['order']->guest_number.'人'.$child_number.'</li>
              <li><strong>入住天数：</strong>'.$params['order']->guest_days.'天</li>
	      <li><strong>联系微信：</strong>'.$params['order']->guest_wechat.' </li>
              '.$message_beizhu.'
              <li class="order-totalprice"><strong>总&nbsp;房&nbsp;费：</strong><font class="order-price">'.$params['order']->total_price.'元（RMB）'.$book_room_model_li.'</font></li>
            '.$other_yf_price.'
            </ul></div>
          </div>
          <div class="order-notice">
            <span class="order-notice-title">温馨提示：</span>
      <div style=" clear:both;background-color:#f6f6f6;  padding:10px;margin-top:10px;font-size:12px;line-height:22px;";>
        <srong style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat  0px;padding-left:17px;">温馨提示：</strong><br>
        1、入台后若换手机号，请主动联系民宿。<br>
        2、台湾法律规定—屋顶底下请勿吸烟。<br>
        3、如条件允许,可给房东准备一份小礼物,或是在离开的时候写一张小纸条表达谢意。<br>
        欢迎下载《自在客》手机应用，让您可以在手机上随时随地预订民宿，联系民宿主人，查看您的订单凭证。<br>
        下载地址：<a href="http://www.youyao.com/v2/smart_phone" target="_blank">http://www.youyao.com/v2/smart_phone</a><br />
        请打印此邮件，作为入住凭证。您还可以直接联系民宿主人，了解游玩的相关信息以及注意事项等等。
        
      </div> 
<div><p style="text-indent:0;"><a href="http://wiki.youyao.com/index.php/%E8%B4%B9%E7%94%A8%E9%97%AE%E9%A2%98" target="_blank">自在客退订政策</a></p></div>
<a href="http://detail.tmall.com/item.htm?spm=a1z10.1.w5003-9351352352.3.RY2Fgj&id=41848831042&scene=taobao_shop" target="_bank"><img src="http://pages.youyao.com/a/img/banner/zzkwifibanner2.jpg" alt="zzkwifi" style="margin-left:-10px;"/></a>
<div><img src="http://pages.youyao.com/a/img/2dcodemail.jpg" width="400px;"></div>
</div><div style="clear:both"></div></div></div>
';
		$message_body .= '<style type="text/css">
.orderbyuser {width:580px;border-top:1px solid #dcdcdc;background:#fcfcfc;padding:10px;margin:0 auto;}
.order-back {background:#fff;border:1px solid #dcdcdc;}
#content .order-title {width:580px;text-align:center;margin-top:7px;font-size:18px;margin-bottom:7px;}
.order-id {width:554px;margin-left:10px;padding-left:6px;line-height:30px;color:#ff8800;background:#f5fafe;margin-bottom:0;font-size:16px;}
.orderbyuser .form-wrapper {margin-top:5px;width:558px;border:none;border-top:1px solid #d5eeff;margin-bottom:0;padding:0;position:static;margin-left:10px;top:0;border-radius:0;float:left;}
.orderbyuser .form-wrapper .form-title {position:static;background:#f5fafe;color:#ff8800;line-height:30px;border:none;border-radius:0;width:554px;font-size:16px;padding-left:5px;text-indent:0;font-weight:bold;float:left;height:28px;}
.orderbyuser .form-wrapper .fieldset-wrapper {float:left;margin:0;padding:0;}
.orderbyuser .form-wrapper .fieldset-wrapper ul {margin:0;padding:0;float:left;width:558px;}
.orderbyuser .form-wrapper .fieldset-wrapper ul li {margin-top:-1px;list-style-type:none;border:1px solid #d5eeff;padding:8px 10px;line-height:15px;width:537px;float:left;}
.orderbyuser .form-wrapper .fieldset-wrapper ul li strong {border-right:1px solid #d5eeff;width:85px;display:block;float:left;margin-right:10px;padding:8px 0;margin-top:-8px;margin-bottom:-8px;}
.order-checkinnotice {margin:0;float:left;margin-left:70px;padding-left:10px;border-left:1px solid #d5eeff;height:20px;margin-bottom:-7px;font-size:12px;color:#979797;}
.orderbyuser .form-wrapper .fieldset-wrapper ul .order-totalprice {border-top:3px solid #6dc0f4;}
.order-price {color:#fa6500;font-weight:bold}
.order-notice {float:left;margin-left:10px;padding:10px;background:#f5fafe;width:540px;margin-top:7px;margin-bottom:10px}
.orderbyuser .form-wrapper .fieldset-wrapper ul li .master-say { float: left; display: block; width: 430px; margin-left: -11px; border-left: 1px solid #d5eeff; padding-left: 10px; margin-bottom: -8px; padding-bottom: 8px; line-height: 20px;}
</style>';
		$body = preg_replace('/[\n]/', '', $message_body);

		Util_SmtpMail::send($to, $subject, $body, $from);
	}

	public static function contact_provider_order_succ($to, $params, $from) {

		global $bnb_dest_id ;
		$rev_percent = $params['order']->rev_percent?$params['order']->rev_percent:100;
		if($rev_percent<>100){
			$sub_title = '，現付';
		}
		$bll_area_info = new Bll_Area_Area();
		$subject .= '['.$bll_area_info->get_dest_language($bnb_dest_id,"youyao").']'.$params['order']->guest_name.$bll_area_info->get_dest_language($bnb_dest_id,"bookingsuccessful");

		$child_number = $params['order']->guest_child_number ? '&nbsp;&nbsp;+ '.$bll_area_info->get_dest_language($bnb_dest_id,"children").'：'.$params['order']->guest_child_number .' 人 ' : '';
		if($params['order']->guest_child_age){
			$child_number .= '('.$params['order']->guest_child_age.")";
		}
		$homestay_util = new Util_HomeStayUtil();
		$dest_row = $homestay_util->get_dest_config($params['order']->dest_id);
		if($rev_percent==100){
			$tw_other_yf_price = '';
			$tw_other_yf_price .= '<li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"totalfee").'：</strong>'.$params['order']->total_price_tw.$dest_row['currency_ios_code'].'，('.$bll_area_info->get_dest_language($bnb_dest_id,"thepricedonotbecut").')</li>';
		}else{
			//民宿主人
			$tw_yu_total_price = Util_Common::zzk_pay_price_format($params['order']->total_price_tw*($rev_percent/100));
			$tw_dao_total_price = Util_Common::zzk_pay_price_format($params['order']->total_price_tw*((100 - $rev_percent)/100));
			$tw_other_yf_price .= '<li style="list-style:none;font-size:14px;"><strong>總 房 費：</strong>'.$params['order']->total_price_tw.' 元('.$dest_row['currency_ios_code'].')</li>';
			$tw_other_yf_price .= '<li style="list-style:none;font-size:14px;"><strong>預付費用：</strong><font color="#ff6602">'.$tw_yu_total_price.' 元('.$dest_row['currency_ios_code'].')</font>，在自在客完成付款</li>';
			$tw_other_yf_price .= '<li style="list-style:none;font-size:14px;"><strong>現付費用：</strong><font>'.$tw_dao_total_price.' 元('.$dest_row['currency_ios_code'].')</font>，入住民宿时才付款</li>';
		}
		if($bnb_dest_id==11){
			$hello = $params['order']->uname.'様，'.$bll_area_info->get_dest_language($bnb_dest_id,"Hello");
		}else{
			$hello = $bll_area_info->get_dest_language($bnb_dest_id,"Hello").'，'.$params['order']->uname;
		}
		$message_body = '
           <div style="font-size:26px; text-align:center; color: #0E74B2; font-weight:bold;">['.$bll_area_info->get_dest_language($bnb_dest_id,"youyao").']'.$bll_area_info->get_dest_language($bnb_dest_id,"bookingsucces").'</div>
	       <div style="font-size:14px; padding:10px;color:#333;margin-top:10px;">'.$hello.'</div>
	      <div style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;">
	      <div style="color:#333;margin-top:5px;font-size:14px;line-height:20px;">'.$bll_area_info->get_dest_language($bnb_dest_id,"congratulation").'('.$params['order']->guest_name.')'.$bll_area_info->get_dest_language($bnb_dest_id,"alreadyhasbeenpay").'。<div style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat -2px 0px;padding-left:17px;padding-bottom:10px;margin-top:15px;">'.$bll_area_info->get_dest_language($bnb_dest_id,"keeproom").'</div>'.$bll_area_info->get_dest_language($bnb_dest_id,"anyquestion").'contact@youyao.com。</div>
	      </div>
	      <ul style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;line-height:25px;">
            <li style="list-style:none;font-size:16px;margin-bottom:10px;color:#0E74B2;">'.$bll_area_info->get_dest_language($bnb_dest_id,"thisisguest'sinfo").'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"bookingid2").'：</strong>#'.$params['order']->id.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"guestname").'：</strong>'.$params['order']->guest_name.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"checkinroom").'：</strong>'.$params['order']->room_name.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"checkPax").'：</strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"adult").'：'.$params['order']->guest_number.'人'.$child_number.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"roomnum").'：</strong>'.$params['order']->room_num.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"Check in date").'：</strong>'.Util_Common::zzk_date_format($params['order']->guest_date,$bnb_dest_id).'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"Chack out date").'：</strong>'.Util_Common::zzk_date_format($params['order']->guest_checkout_date,$bnb_dest_id).'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"Nights").'：</strong>'.$params['order']->guest_days.$bll_area_info->get_dest_language($bnb_dest_id,"days").'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"email").'：</strong>'.$params['order']->guest_mail.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"lianxidianhua").'：</strong>'.$params['order']->guest_telnum.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"homestayweixin").'：</strong>'.$params['order']->guest_wechat.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"beizhuliuyan").'：</strong>'.$params['order']->guest_etc.'</li>
            '.$tw_other_yf_price.'
      </ul>
      <div style="clear:both;">&nbsp;</div>';
		if($bnb_dest_id<11){
			$message_body .= '
	      <div style=" clear:both;background-color:#f6f6f6;  padding:10px;margin-top:40px;font-size:12px;line-height:22px;";>
	        <srong style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat -2px 0px;padding-left:17px;">温馨提示：</strong><br>
	        自在客爲您提供《台灣民宿》手機應用，讓您可以在手機上隨時隨地處理訂單。<br>
	        手機應用下載地址：<a href="http://pages.youyao.com/smart_phone" target="_blank">http://pages.youyao.com/smart_phone</a><br />
	      </div>
	        ';
		}
		$body = preg_replace('/[\n]/', '', $message_body);

		Util_SmtpMail::send($to, $subject, $body, $from);
	}

	public static function contact_provider_payment_succ($to, $params, $from) {
		$subject = "[財務]自在客預訂已匯款，收到請務必回復郵件";
		$body = '您好 '.$params['order']->uname.'，
       '.$params['order_message'].'

-------------------------------------------------------------------
 
為了提高客人的體驗，建議您收到匯款後及時聯繫客人，告知客人您民宿的交通資訊及其他能提供的服務內容！謝謝您！
 
客人入住資訊如下：
訂單編號：#'.$params['order']->id.'
客人姓名：'.$params['order']->guest_name.'
入住房間：'.$params['order']->room_name.'
入住人數：'.$params['order']->guest_number.'
房间數量：'.$params['order']->room_num.'
入住日期：'.Util_Common::zzk_date_format($params['order']->guest_date).'
退房日期：'.Util_Common::zzk_date_format($params['order']->guest_checkout_date).'
入住天數：'.$params['order']->guest_days.'
郵箱地址：'.$params['order']->guest_mail.'
聯系電話：'.$params['order']->guest_telnum.'
       ';

		Util_SmtpMail::send($to, $subject, $body, $from);
	}

	public static function contact_user_order_closed($to, $params, $from) {

		$subject .= '[自在客] 您的订单已取消';

		$message_str = (empty($params['order_message']) ? "您寻问的房间已满。" : $params['order_message']);
		$child_number = $params['order']->guest_child_number ? '&nbsp;&nbsp;+ 儿童：'.$params['order']->guest_child_number .' 人 ' : '';
		if($params['order']->guest_child_age){
			$child_number .= '('.$params['order']->guest_child_age.")";
		}

		$message_body = '
           <div style="font-size:26px; text-align:center; color: #0E74B2; font-weight:bold;">自在客订单取消邮件</div>       <div style="font-size:14px; padding:10px;color:#333;margin-top:10px;">您好，'.$params['order']->guest_name.'，您的订单(#'.(empty($params['order']->hash_id)?$params['order']->id:$params['order']->hash_id).')已经取消。</div>
      <div style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;">
         <div style="color:#333;margin-top:15px;font-size:14px;line-height:20px;"><div style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat -2px 0px;padding-left:17px;padding-bottom:10px;">取消原因是：'.$message_str.'</div>如果您需要推荐台湾民宿，请联系自在客客服人员，客服电话：4008-886-232。</div>
      </div>
      <ul style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;line-height:25px;">
            <li style="list-style:none;font-size:16px;margin-bottom:10px;color:#0E74B2;">订单信息</li>
            <li style="list-style:none;font-size:14px;"><strong>订单编号：</strong>#'.(empty($params['order']->hash_id)?$params['order']->id:$params['order']->hash_id).'</li>
            <li style="list-style:none;font-size:14px;"><strong>入住民宿：</strong>'.$params['order']->uname.'</li>
            <li style="list-style:none;font-size:14px;"><strong>入住房间：</strong>'.$params['order']->room_name.'</li>
            <li style="list-style:none;font-size:14px;"><strong>入住人数：成人：</strong>'.$params['order']->guest_number.'人'.$child_number.'</li>
            <li style="list-style:none;font-size:14px;"><strong>房间数量：</strong>'.$params['order']->room_num.'间</li>
            <li style="list-style:none;font-size:14px;"><strong>入住日期：</strong>'.Util_Common::zzk_date_format($params['order']->guest_date).'</li>
            <li style="list-style:none;font-size:14px;"><strong>退房日期：</strong>'.Util_Common::zzk_date_format($params['order']->guest_checkout_date).'</li>
            <li style="list-style:none;font-size:14px;"><strong>入住天数：</strong>'.$params['order']->guest_days.'天</li>
      </ul>
      <div style="clear:both;">&nbsp;</div>
      <div style=" clear:both;background-color:#f6f6f6;  padding:10px;margin-top:40px;font-size:12px;line-height:22px;";>
        <srong style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat  0px;padding-left:17px;">温馨提示：</strong><br>
        欢迎下载《自在客》手机应用，让您可以在手机上随时随地预订民宿，联系民宿主人，查看您的订单凭证。<br>
        下载地址：<a href="http://www.youyao.com/v2/smart_phone" target="_blank">http://www.youyao.com/v2/smart_phone</a><br />
      </div>
        ';
		$body = preg_replace('/[\n]/', '', $message_body);

		Util_SmtpMail::send($to, $subject, $body, $from);
	}

	public static function contact_provider_order_closed($to, $params, $from) {

		global $bnb_dest_id;
		$bll_area_info = new Bll_Area_Area();
		$subject = '['.$bll_area_info->get_dest_language($bnb_dest_id,"youyao").'] '.$params['order']->guest_name.' '.$bll_area_info->get_dest_language($bnb_dest_id,"bookingcancel");


		$message_str = (empty($params['order_message']) ? $bll_area_info->get_dest_language($bnb_dest_id,"cancelbyguest") : $params['order_message']);
		$child_number = $params['order']->guest_child_number ? '&nbsp;&nbsp;+ '.$bll_area_info->get_dest_language($bnb_dest_id,"children").'：'.$params['order']->guest_child_number .' 人 ' : '';
		if($params['order']->guest_child_age){
			$child_number .= '('.$params['order']->guest_child_age.")";
		}
		if($bnb_dest_id==11){
			$hello = $params['order']->uname.'様，'.$bll_area_info->get_dest_language($bnb_dest_id,"Hello");
		}else{
			$hello = $bll_area_info->get_dest_language($bnb_dest_id,"Hello").'，'.$params['order']->uname;
		}

		$message_body = '
           <div style="font-size:26px; text-align:center; color: #0E74B2; font-weight:bold;">'.$bll_area_info->get_dest_language($bnb_dest_id,"youyao").$params['order']->guest_name.$bll_area_info->get_dest_language($bnb_dest_id,"'sbookingcancel").'</div>
       <div style="font-size:14px; padding:10px;color:#333;margin-top:10px;">'.$hello.'<br> '.$params['order']->guest_name.' '.$bll_area_info->get_dest_language($bnb_dest_id,"s'booking").'(#'.(empty($params['order']->hash_id)?$params['order']->id:$params['order']->hash_id).')'.$bll_area_info->get_dest_language($bnb_dest_id,"hasbeencancel").'</div>
      <div style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;">
         <div style="color:#333;margin-top:15px;font-size:14px;line-height:20px;"><div style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat -2px 0px;padding-left:17px;padding-bottom:10px;">'.$bll_area_info->get_dest_language($bnb_dest_id,"reasonofcancel").'：'.$message_str.'</div>'.$bll_area_info->get_dest_language($bnb_dest_id,"anyquestion").'contact@youyao.com。</div>
      </div>
      <ul style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;line-height:25px;">
            <li style="list-style:none;font-size:16px;margin-bottom:10px;color:#0E74B2;">'.$bll_area_info->get_dest_language($bnb_dest_id,"thisisguest'sinfo2").'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"bookingid2").'：</strong>#'.(empty($params['order']->hash_id)?$params['order']->id:$params['order']->hash_id).'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"guestname").'：</strong>'.$params['order']->guest_name.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"checkinroom").'：</strong>'.$params['order']->room_name.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"checkPax").'：</strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"adult").'：'.$params['order']->guest_number.'人'.$child_number.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"roomnum").'：</strong>'.$params['order']->room_num.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"Check in date").'：</strong>'.Util_Common::zzk_date_format($params['order']->guest_date,$bnb_dest_id).'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"Chack out date").'：</strong>'.Util_Common::zzk_date_format($params['order']->guest_checkout_date,$bnb_dest_id).'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"checkindays").'：</strong>'.$params['order']->guest_days.$bll_area_info->get_dest_language($bnb_dest_id,"days").'</li>
      </ul>
      <div style="clear:both;">&nbsp;</div>';

		if($bnb_dest_id < 11) {
			$message_body .= '
      <div style=" clear:both;background-color:#f6f6f6;  padding:10px;margin-top:40px;font-size:12px;line-height:22px;";>
        <srong style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat -2px 0px;padding-left:17px;">温馨提示：</strong><br>
        自在客爲您提供《台灣民宿》手機應用，讓您可以在手機上隨時隨地處理訂單。<br>
        手機應用下載地址：<a href="http://pages.youyao.com/smart_phone" target="_blank">http://pages.youyao.com/smart_phone</a><br />
      </div>
        ';
		}
		$body = preg_replace('/[\n]/', '', $message_body);

		Util_SmtpMail::send($to, $subject, $body, $from);
	}

    public function order_contact_verdify($params, $to='dl-publish@youyao.com', $from='noreply@youyao.com') {
        $subject = '有新民宿提交信息，请认证！';
        $body = '编辑，您好！<br/> <a href="'.Util_Common::url('/v2/homestay/'.$params['uid']).'/center">'.$params['name'].'</a> 民宿 在'.date('Y-m-d',time()).'完成了民宿信息编辑，并提交了审核需求，请及时帮民宿确认认证信息。如有问题，请及时联系民宿！ <br/> 地址：'.$params['address'].'<br/>';

        Util_SmtpMail::send($to, $subject, $body, $from);
    }

    public function order_user_reg_mail($params, $to, $from='noreply@youyao.com') {
        $subject = '待審核認證提示郵件';
        $body = $params['name'].' 民宿，您好！<br/>
您的信息已經提交成功，我們將在1-3個工作日幫您完成審核認證。如有問題，我們將及時聯系您！<br/>
如超過3個工作日尚未收到任何審核有關的郵件，您可以發郵件到 contact@youyao.com，會有工作人員及時跟進處理。 ';

        Util_SmtpMail::send($to, $subject, $body, $from);
    }

}
?>
