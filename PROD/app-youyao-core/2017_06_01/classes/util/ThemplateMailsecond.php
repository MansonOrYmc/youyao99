<?php
class Util_ThemplateMailsecond {

	public static function contact_user_order_succ($to, $params, $from, $preview=0) {

		$dao_user_info = new Dao_User_UserInfo();
		$paladin = new Bll_Area_Area;
		$locid = $paladin->get_area_by_cityname($params['order']->city_name);
		if($locid['locid']>0 && $params['order']->dest_id==10){
			$rbo = new Recommend_ByOrder($params['order']->id);
			$response = $rbo->recommend();
			$recommendlist = $response['hsItems'];
		}
		if(!empty($recommendlist)) {
			$recommend = '<p class="largetitle middle">猜你喜欢</p>';
			$recommend .= "<ul class='recommend-bnb'>";
			shuffle($recommendlist);
			$recommendlist = array_slice($recommendlist, 0 , 4);
			foreach($recommendlist as $v) {
				$recommend .= "<li><a target='_blank' href='http://taiwan.youyao.com/h/".$v->uid."?utm_source=insite_recomm&utm_medium=byordersuccma   il&zzkcamp=rcm_ordersuccmail'>".$v->username."</a><label>".$v->loc_typename."</label><em>￥".$v->int_price."</em></li>";
			}
			$recommend .= "</ul>";
		}

		$acc = $dao_user_info->load_user_info($params['order']->uid);
        $acc->jiaotongzixun = $dao_user_info->get_user_jiaotongzixun($params['order']->uid);
        $acc->jiaotongzixun = $acc->jiaotongzixun['field__jiaotongzixun_value'];
        $acc->jiaotongzixun = str_replace("\n",'<br/>',$acc->jiaotongzixun);
        if($acc->jiaotongzixun){
        $acc->jiaotongzixun = '<li><strong>交通咨询：</strong><br>'.$acc->jiaotongzixun.'</li>';
        }
        $acc->zhuyishixiang = $dao_user_info->get_user_zhuyishixiang($params['order']->uid);
        $acc->zhuyishixiang = $acc->zhuyishixiang['field_zhuyishixiang_value'];
        $acc->zhuyishixiang = str_replace("\n",'<br/>',$acc->zhuyishixiang);
        if($acc->zhuyishixiang){
            $acc->zhuyishixiang = '<li><strong>注意事项：</strong><br>'.$acc->zhuyishixiang.'</li>';
        }

        if($locid['locid']>0 && $params['order']->dest_id==11){
            $download='<li><a href="http://7xnql6.dl1.z0.glb.clouddn.com/%E6%97%A5%E6%9C%AC%E4%BD%8F%E5%AE%BF%E7%A4%BC%E4%BB%AA.pdf">日本住宿礼仪须知，请点击下载</a></li>';
        }else{
            $download='';
        }

        $acc->jiaotongtu = $dao_user_info->get_user_jiaotongtu($params['order']->uid);
        if($acc->jiaotongtu){
            $acc->jiaotongtu['uri']='<li><strong>交通图：</strong><img src="'.$acc->jiaotongtu['uri'].'"></li>';
        }

//电话号码处理
		$hsCallNumber = Util_ZzkCommon::phone_format_numbers($params['order']->dest_id, $acc->send_sms_telnum, $acc->tel_num);
		if (empty($hsCallNumber)) {
			$hsCallNumber = Util_ZzkCommon::phone_combine_numbers($params['order']->dest_id, $acc->send_sms_telnum, $acc->tel_num);
		}
		if (empty($hsCallNumber)) {
			$hsCallNumberStr = "";
		} else if (count($hsCallNumber) > 1) {
			$twAreaCodeInfo = "";
			if ($params['order']->dest_id == 10) {
				$twAreaCodeInfo = "(台湾区号00886)<br />";
			}
            $hsCallNumber = array_values($hsCallNumber);
			$hsCallNumberStr = $twAreaCodeInfo.' <span>'.$hsCallNumber[0].'</span><br /> <span>'.$hsCallNumber[1].'</span>';
		} else {
			$twAreaCodeInfo = "";
			if ($params['order']->dest_id == 10) {
				$twAreaCodeInfo = "(台湾区号00886)";
			}
			$hsCallNumberStr = '<span>'.$hsCallNumber[0]."</span> ".$twAreaCodeInfo;          
		}
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
		$message_intro = '<li class="master-say-li"><strong>民宿主人说：</strong><span class="master-say">'.$params['order']->intro.'  (请提前一天联系我，告知入住时间，谢谢！)</span></li>';
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
         $message_body = '<div class="orderbyuser"><div class="top-back"><img src="http://pages.youyao.com/a/img/homepage3/logo_15_03_7.png" /></div><div class="order-back">
    	  <h2 class="order-title">民宿入住凭证</h2>
          <p class="order-id">（订单编号：#'.($params['order']->hash_id?$params['order']->hash_id:$params['order']->id).'）</p>
          <div id="edit-homestay-detail" class="form-wrapper" >
	    <div class="form-title"><span>民宿信息</span></div>
            <div class="fieldset-wrapper"><ul>
              <li><strong>入住民宿：</strong>'.$acc->name.'</li>
              <li><strong>入住房间：</strong>'.$params['order']->room_name.'</li>
              <li><strong>联系电话：</strong><label class="phonestr">'.$hsCallNumberStr.'</label></li>
              <li><strong>联系邮箱：</strong>'.$acc->mail.'</li>
              '.$snsweixin.$snsline.'
              <li><strong>民宿地址：</strong>'.$acc->address.'</li>
              <li><strong>登记时间：</strong>'.$check_in_out.'</li>'.
              $acc->jiaotongzixun.
               $acc->jiaotongtu['uri'].
              $acc->zhuyishixiang.
             $download.
             '

              '.$message_intro.'
            </ul></div>
          </div>
          <div id="edit-order-detail" class="form-wrapper">
            <div class="form-title"><span>订单信息</span></div>
	    <div class="fieldset-wrapper"><ul>
              <li><strong>入住客人：</strong>'.$params['order']->guest_name.'</li>
              <li><strong>入住日期：</strong>'.Util_Common::zzk_date_format($params['order']->guest_date).'</li>
              <li><strong>退房日期：</strong>'.Util_Common::zzk_date_format($params['order']->guest_checkout_date).'</li>
              <li><strong>客人电话：</strong>'.self::customer_info_hide($params['order']->guest_telnum).'</li>
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
		  <div style="clear:both"></div>
		  <span class="print">-----------------------请打印此邮件，作为入住凭证-----------------------</span>
          <p class="middle">随时联系民宿主人，请下载自在客手机APP</p>
		  <img class="dcode" src="http://pages.youyao.com/a/2dcode/app.jpg"/>
		  <p class="scan-word middle">扫一扫即刻下载APP</p>
			'.$recommend.'
		  <h2 class="largetitle middle">出游须知</h2>
<!--
		  <p class="middle"><label class="large-font">免费奉送</label>“自在客独家台湾自由行必备贴士”，<a class="downloadlink">点此下载</a></p>
-->
		  <p class="middle">在线查看订单及入住凭证,获取更多详细攻略及旅游地图，<br/>请关注自在客官方微信公众号<em>iyouyao</em></p>
		  <p class="middle"><em>自在客官方公共微信平台</em></p>
	      <img src="http://pages.youyao.com/a/2dcode/wechat.jpg" class="dcode" />
		<img src="http://pages.youyao.com/a/img/liveadaylikealocal.png" style="margin:0 auto;display:block;margin-top:20px;" />
        <div class="tips">
        <srong style="font-size:14px;line-height:25px;">Tips：</strong><br>
        1、入台后若换手机号，请主动联系民宿。<br>
        2、台湾法律规定—屋顶底下请勿吸烟。<br>
        3、如条件允许,可给房东准备一份小礼物,或是在离开的时候写一张小纸条表达谢意。<br>
		<a style="margin-top:5px;color:#0066cc" href="http://wiki.youyao.com/index.php/%E8%B4%B9%E7%94%A8%E9%97%AE%E9%A2%98" target="_blank">自在客退订政策</a>
</div>
<span class="bottom-line"></span>
<p class="middle">自在客团队</p>
<div style="clear:both"></div></div></div>
';
         $message_body .= '<style type="text/css">
.orderbyuser {width:600px;background:#fcfcfc;padding:10px;margin:0 auto;}
.top-back {background:#d6595b}
.top-back img {margin:0 auto;display:block}
.order-back {background:#fff;}
.order-title {width:600px;text-align:center;margin-top:15px;font-size:20px;margin-bottom:3px;color:#ff5459}
.order-id {width:600px;margin-bottom:0;font-size:14px;text-align:center;margin-bottom:10px;}
.orderbyuser .form-wrapper {margin-top:5px;width:558px;border:none;margin-bottom:0;padding:0;position:static;margin-left:10px;top:0;border-radius:0;float:left;}
.orderbyuser .form-wrapper .form-title {position:static;color:#ff5459;line-height:30px;border:none;border-radius:0;width:570px;font-size:16px;padding-left:5px;text-indent:0;font-weight:bold;float:left;height:28px;border-bottom:1px solid #ccc;padding-bottom:5px;}
.orderbyuser .form-wrapper .fieldset-wrapper {float:left;margin:0;padding:0;}
.orderbyuser .form-wrapper .fieldset-wrapper ul {margin:0;padding:0;float:left;width:558px;}
.orderbyuser .form-wrapper .fieldset-wrapper ul li {list-style-type:none;padding:8px 10px;line-height:15px;width:537px;float:left;}
.print {display:block;text-align:center;font-weight:bold;margin:50px 0 ;}
.order-back .recommend-bnb {margin:0;padding:0 25px;}
.recommend-bnb:after {content:".";height:0;visibility:hidden;clear:both;display:block;}
body .recommend-bnb li {float:left;list-style:none;width:550px;border-bottom:1px dashed #ccc;line-height:60px;font-size:16px;}
.recommend-bnb a {float:left;width:200px;color:#0066cc;text-overflow:ellipsis;overflow:hidden;white-space:nowrap}
.recommend-bnb label {float:left;width:40px;color:#979797;}
.recommend-bnb em {font-style:normal;float:right;}
.orderbyuser .form-wrapper .fieldset-wrapper ul li strong {width:85px;display:block;float:left;margin-right:10px;padding:8px 0;margin-top:-8px;margin-bottom:-8px;color:#67655f}
.order-checkinnotice {margin:0;float:left;margin-left:70px;padding-left:10px;height:20px;margin-bottom:-7px;font-size:12px;color:#979797;}
.order-price {font-weight:bold}
.orderbyuser .order-back .middle {width:600px;text-align:center;line-height:25px;font-size:16px;}
.orderbyuser .order-back .middle em {font-style:normal;color:#ff5459}
.orderbyuser .order-back .largetitle {font-size:20px;margin-top:35px;margin-bottom:15px;}
.dcode {display:block;margin:0 auto;}
.scan-word {color:#ff5459}
.tips {width:350px;border:1px solid #ccc;margin:20px auto;border-radius:3px;padding:15px;}
.phonestr {display:inline-block;line-height:20px;}
.phonestr span {color:#0066cc}
.bottom-line {width:80%;height:1px;display:block;background:#ccc;margin:10px auto;}
.orderbyuser .form-wrapper .fieldset-wrapper ul li .master-say { float: left; display: block; width: 430px; margin-left: -11px; padding-left: 10px; margin-bottom: -8px; padding-bottom: 8px; line-height: 20px;}
</style>';
		$body = preg_replace('/[\n]/', '', $message_body);

		if($preview==0){
Util_Debug::zzk_debug('succmail',print_r(array('to'=>$to,'subject'=>$subject,'from'=>$from),true));
			Util_SmtpMail::send($to, $subject, $body, $from);
			return true;
		}else{
			return $body;
		}
	}

	public static function contact_provider_order_succ($to, $order, $from, $bnb_dest_id = 10) {

		$rev_percent = $order->rev_percent?$order->rev_percent:100;
		if($rev_percent<>100){
			$sub_title = '，現付';
		}
		$bll_area_info = new Bll_Area_Area();
		$subject .= '['.$bll_area_info->get_dest_language($bnb_dest_id,"youyao").']'.$order->guest_name.$bll_area_info->get_dest_language($bnb_dest_id,"bookingsuccessful");

		$child_number = $order->guest_child_number ? '&nbsp;&nbsp;+ '.$bll_area_info->get_dest_language($bnb_dest_id,"children").'：'.$order->guest_child_number .' 人 ' : '';
		if($order->guest_child_age){
			$child_number .= '('.$order->guest_child_age.")";
		}
		$homestay_util = new Util_HomeStayUtil();
		$dest_row = $homestay_util->get_dest_config($order->dest_id);
		if($rev_percent==100){
			$tw_other_yf_price = '';
			$tw_other_yf_price .= '<li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"totalfee").'：</strong>'.$order->total_price_tw.$dest_row['currency_ios_code'].'，('.$bll_area_info->get_dest_language($bnb_dest_id,"thepricedonotbecut").')</li>';
		}else{
			//民宿主人
			$tw_yu_total_price = Util_Common::zzk_pay_price_format($order->total_price_tw*($rev_percent/100));
			$tw_dao_total_price = Util_Common::zzk_pay_price_format($order->total_price_tw*((100 - $rev_percent)/100));
			$tw_other_yf_price .= '<li style="list-style:none;font-size:14px;"><strong>總 房 費：</strong>'.$order->total_price_tw.' 元('.$dest_row['currency_ios_code'].')</li>';
			$tw_other_yf_price .= '<li style="list-style:none;font-size:14px;"><strong>預付費用：</strong><font color="#ff6602">'.$tw_yu_total_price.' 元('.$dest_row['currency_ios_code'].')</font>，在自在客完成付款</li>';
			$tw_other_yf_price .= '<li style="list-style:none;font-size:14px;"><strong>現付費用：</strong><font>'.$tw_dao_total_price.' 元('.$dest_row['currency_ios_code'].')</font>，入住民宿时才付款</li>';
		}
		if($bnb_dest_id==11){
			$hello = $order->uname.'様，'.$bll_area_info->get_dest_language($bnb_dest_id,"Hello");
		}else{
			$hello = $bll_area_info->get_dest_language($bnb_dest_id,"Hello").'，'.$order->uname;
		}
          $message_body = '
           <div style="font-size:26px; text-align:center; color: #0E74B2; font-weight:bold;">['.$bll_area_info->get_dest_language($bnb_dest_id,"youyao").']'.$bll_area_info->get_dest_language($bnb_dest_id,"bookingsucces").'</div>
	       <div style="font-size:14px; padding:10px;color:#333;margin-top:10px;">'.$hello.'</div>
	      <div style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;">
	      <div style="color:#333;margin-top:5px;font-size:14px;line-height:20px;">'.$bll_area_info->get_dest_language($bnb_dest_id,"congratulation").'('.$order->guest_name.')'.$bll_area_info->get_dest_language($bnb_dest_id,"alreadyhasbeenpay").'。<div style="font-size:14px;background:url(http://wiki.youyao.com/images/5/50/I4.png) no-repeat -2px 0px;padding-left:17px;padding-bottom:10px;margin-top:15px;">'.$bll_area_info->get_dest_language($bnb_dest_id,"keeproom").'</div>'.$bll_area_info->get_dest_language($bnb_dest_id,"anyquestion").'contact@youyao.com。</div>
	      </div>
	      <ul style="padding:10px;clear:both;border:#efefef 1px solid; margin-top:10px;line-height:25px;">
            <li style="list-style:none;font-size:16px;margin-bottom:10px;color:#0E74B2;">'.$bll_area_info->get_dest_language($bnb_dest_id,"thisisguest'sinfo").'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"bookingid2").'：</strong>#'.$order->id.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"guestname").'：</strong>'.$order->guest_name.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"checkinroom").'：</strong>'.$order->room_name.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"checkPax").'：</strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"adult").'：'.$order->guest_number.'人'.$child_number.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"roomnum").'：</strong>'.$order->room_num.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"Check in date").'：</strong>'.Util_Common::zzk_date_format($order->guest_date,$bnb_dest_id).'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"Chack out date").'：</strong>'.Util_Common::zzk_date_format($order->guest_checkout_date,$bnb_dest_id).'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"Nights").'：</strong>'.$order->guest_days.$bll_area_info->get_dest_language($bnb_dest_id,"days").'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"email").'：</strong>'.$order->guest_mail.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"lianxidianhua").'：</strong>'.$order->guest_telnum.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"homestayweixin").'：</strong>'.$order->guest_wechat.'</li>
            <li style="list-style:none;font-size:14px;"><strong>'.$bll_area_info->get_dest_language($bnb_dest_id,"beizhuliuyan").'：</strong>'.$order->guest_etc.'</li>
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
		
		if($preview==0) {     
			Util_SmtpMail::send($to, $subject, $body, $from);
			return true;
		}else{
			return $body;
		}
	}

	private function customer_info_hide($str) {
        $cp_count = strlen($str);
        $cp_replace = str_pad("", (int)($cp_count/2), "*");
        $cp_replace_position = "-".(int)($cp_count*3/4);
        $sub_cus_phone = substr_replace($str, $cp_replace, $cp_replace_position, strlen($cp_replace));
		return $sub_cus_phone;
	}
    
}
?>
