<?php

class Bll_Privatemsg_PrivateMsgInfo {
    private $pmInfoDao;

    public function __construct() {
        $this->pmInfoDao = new Dao_Privatemsg_PrivateMsgInfo();
    }

    public function get_recipient_list_byuid($uid, $time=null) {
        if(!$uid) return;
        $homebll = new Bll_Homestay_StayInfo();
        $m_uid = $homebll->get_master_uid_by_buid($uid);
        $uids = $homebll->get_branch_list_by_mid($m_uid);
        if(!$m_uid) {
            $m_uid = $uid;
            $uids = array($uid);
        }

        $data = $this->pmInfoDao->get_list_by_uid($uids, $time);
        $result = array();
        foreach($data as $row) {
            $result[] = array(
                'other_uid'    => $row['other_uid'],
                'master_uid'   => $uid,
                'branch_uid'   => $row['branch_uid'],
                'subject'      => $row['subject'],
                'last_time'    => $row['timestamp'],
                'unread_count' => $row['unread_count'],
            );
        }

        return $result;

/*
        $mids = $this->pmInfoDao->get_mids_by_uid($uids);
        if(empty($mids)) return ;
        $msg_list = $this->pmInfoDao->get_message_by_mids($mids);
        $recipient_list = $this->pmInfoDao->get_recipient_by_mids($mids);

        $mid_uid = array();
        $msg_byid = array();
        foreach($msg_list as $row) {
            $msg_byid[$row['mid']] = $row;
        }
        foreach($recipient_list as $row) { // 先取出发送方uid
            if($row['delete']>0) continue;
            if(in_array($row['recipient'], $uids)) {
                $mid_uid[$row['mid']] = $row['recipient'];
            }
        }


        $result = array();
        $recipient = array();
        foreach($recipient_list as $row) {
            if($row['recipient'] == 0) continue;
            if($row['delete']>0) continue;
            if(in_array($row['recipient'], $uids)) continue;
            if($result[$row['recipient']]['last_time'] > $msg_byid[$row['mid']]['timestamp']) continue;
            $result[$row['recipient']] = array(
                    'other_uid' => $row['recipient'],
                    'master_uid' => $m_uid,
                    'branch_uid' => $mid_uid[$row['mid']],
                    'subject' => $msg_byid[$row['mid']]['subject'],
                    'last_time' => $msg_byid[$row['mid']]['timestamp'],
                );
        }

        return array_values($result);
*/

    }

    public function get_both_message_by_uid($me, $other, $page = 1, $limit = 300) { // 替代下面的
        $home_bll = new Bll_Homestay_StayInfo();
        $m_uid1 = $home_bll->get_master_uid_by_buid($me);
        $m_uid2 = $home_bll->get_master_uid_by_buid($other);
        $b_uids1 = array();
        $b_uids2 = array();
        if($m_uid1) $b_uids1 = $home_bll->get_branch_list_by_mid($m_uid1);
            else $b_uids1 = array($me);
        if($m_uid2) $b_uids2 = $home_bll->get_branch_list_by_mid($m_uid2);
            else $b_uids2 = array($other);
        $uid_list = array_values(array_unique(array_merge(array_values($b_uids1), array_values($b_uids2))));

        $offset = ($page-1) * $limit;
        $pm_list = $this->pmInfoDao->get_message_by_both($b_uids1, $b_uids2, $offset, $limit);

        $result = $pm_list;
        krsort($result);
        $result = array_values($result);

        return $result;
    }

    public function get_message_list_by_both($me, $other, $page = 1, $limit = 300) {
        $home_bll = new Bll_Homestay_StayInfo();
        $m_uid1 = $home_bll->get_master_uid_by_buid($me);
        $m_uid2 = $home_bll->get_master_uid_by_buid($other);
        $b_uids1 = array();
        $b_uids2 = array();
        if($m_uid1) $b_uids1 = $home_bll->get_branch_list_by_mid($m_uid1);
            else $b_uids1 = array($me);
        if($m_uid2) $b_uids2 = $home_bll->get_branch_list_by_mid($m_uid2);
            else $b_uids2 = array($other);
        $uid_list = array_values(array_unique(array_merge(array_values($b_uids1), array_values($b_uids2))));

        $pmsg_list = $this->get_message_by_uids($uid_list);
        if(empty($pmsg_list)) return array();
        $mids = array();
        foreach($pmsg_list as $r) {
            $mids[] = $r['mid'];
        }

        $index_list = $this->pmInfoDao->get_recipient_by_mids($mids);
        $bad_mids = array();
        foreach($index_list as $m) {
            if(!in_array($m['recipient'], $uid_list)) {
                $bad_mids[] = $m['mid'];
                continue;
            }
            $index[$m['mid']][] = $m;
        }

        foreach($index as $row) {

        }

        $offset1 = ($page-1) * $limit;
        $offset2 = $page * $limit;
        $result = array();
        $k = 0;
        foreach($pmsg_list as $j) {
            if(in_array($j['mid'], $bad_mids)) continue;
            $k++;
            if($k <= $offset1) continue;
            #if($k > $offset2) break;
            $data = array(
                'mid' => $j['mid'],
                'body' => $j['body'],
                'timestamp' => $j['timestamp'],
            );

//print_r($j);
//print_r($index[$j['mid']][0]['recipient']);
//print_r($b_uids1);
//exit();
            if($j['author'] == $index[$j['mid']][0]['recipient']) {
                $index1 = $index[$j['mid']][0];
                $index2 = $index[$j['mid']][1];
            } else {
                $index1 = $index[$j['mid']][1];
                $index2 = $index[$j['mid']][0];
            }
            $data['author']    = $j['author'];
            $data['recipient'] = $index2['recipient'];
            $data['is_new']    = $index1['is_new'];
            if(in_array($j['author'], $b_uids1)) {
            }else{
                $data['is_new']    = $index2['is_new'];
            }
/*
            if(in_array($j['author'], $m_uid1)) {
                $data['recipient'] = $
            }else{
            }
*/

            
            $result[] = $data;
        }

        return $result;

    }

    public function get_message_by_uids($uids) {
        if(!is_array($uids)) $uids = array($uids);
        if(empty($uids)) return ;
        return $this->pmInfoDao->get_message_by_uids($uids);
    }

	public function get_noreplay_privatemsg() {
		if(empty($time)) $time = time();
		$pm_list = $this->pmInfoDao->get_privatemsg_bydate($time-60*60, $time-60*30);
//		$pm_list = $this->pmInfoDao->get_privatemsg_list(50);
		$is_count = array();
		foreach($pm_list as $row) {
			if(in_array($row['mid'], $is_count)) continue;
			$is_count[] = $row['mid'];
			$userDao = new Dao_User_UserInfo();
			$bookingDao = new Dao_Order_OrderInfo();
			$isMerchant = $userDao->isAdmin($row['author']);
			if($isMerchant) continue;
			$homestay_id = $this->pmInfoDao->get_one_recipient_bymid($row['mid'], $row['author']);
			$havebooking = $bookingDao->get_order_by_uid_homestay_id($row['author'], $homestay_id);
			if($havebooking) continue;
			$all_list = $this->pmInfoDao->get_all_privatemsg_by_uid($row['author'], 100);
			foreach($all_list as $v) {
				$mid_list[] = $v['mid'];
			}
			
			$count = $this->pmInfoDao->get_data_by_midnrecipient($homestay_id, $mid_list);
			if($count < 2) {
				$data[] = array(
							'mid' => $row['mid'],
							'body' => $row['body'],
							'user'=>$row['author'],
							'homestay'=>$homestay_id
					);
			}

		}

		return $data;
	}

	public function get_noorder_privatemsg() {

		if(empty($time)) $time = time();
        $pm_list = $this->pmInfoDao->get_privatemsg_bydate($time-60*30, $time-60*10);
		$is_count = array();
		foreach($pm_list as $row) {
			if(in_array($row['mid'], $is_count)) continue;
			$is_count[] = $row['mid'];
			$userDao = new Dao_User_UserInfo();
			$bookingDao = new Dao_Order_OrderInfo();
			$isMerchant = $userDao->isAdmin($row['author']);
			if($isMerchant) continue;
			$homestay_id = $this->pmInfoDao->get_one_recipient_bymid($row['mid'], $row['author']);
			$havebooking = $bookingDao->get_order_by_uid_homestay_id($row['author'], $homestay_id);
			if($havebooking) continue;
			
			$data[] = array(
						'mid' => $row['mid'],
						'body' => $row['body'],
						'user'=>$row['author'],
						'homestay'=>$homestay_id
				);

		}

		return $data;
		
	}

	public function insert_privatemsg($data) {

		$dao = new Dao_Privatemsg_PrivateMsgInfo();
		$msgArgs = array(
			'author' => $data['homestay'],
			'subject' => '系统消息',
			'body' => '您好，民宿主人暂时无法及时回复您的消息，建议您先下预约单保留房间一段时间，民宿主人会尽快与您联系，谢谢。【系统消息】',
			'timestamp' => time(),
			'client_ip' => Util_NetWorkAddress::get_client_ip(),
		);
		$mid = $dao->insert_into_pm_message($msgArgs);
		$indexArgs = array(
			'mid' => $mid,
			'recipient' => array(
							'user' => $data['user'],
							'homestay' => $data['homestay'],
							)
		);

		return $dao->insert_into_pm_index($indexArgs);
	}

	public function send_mail($data) {

		$dao = new Dao_Privatemsg_PrivateMsgInfo();
		$index = $dao->get_recipient_bymid(array($data['mid']));
		$thread_id = $index[0]['thread_id'];
		
		$subject = '30分钟还未回复私信提醒';
		$body = "私信内容：".$data['body']."<br/><br/>";
		$body .= "连接 http://taiwan.youyao.com/messages/view/".$thread_id;
		$form = 'noreplay@youyao.com';
		foreach($data['to'] as $to) {
			Util_SmtpMail::send($to, $subject, $body, $from);	
		}
	}

	public function add_pmsg_customer($data) {

		$bll = new Bll_Sale_DispatchCustomer();
		$cusbll = new Bll_Customer_Info();
		
		$dispatch = $bll->get_next_sale($data['dest_id'], $data['phone'], $data['uid'], $data['email']);
		if(!$dispatch['cid']) {
			$params = array(
				'name' => $data['name'],
				'dest_id'  => $data['dest_id'],
				'campaign_code' => isset($_COOKIE['campaign_code']) && !empty($_COOKIE['campaign_code']) ? $_COOKIE['campaign_code'] : '',
				'zzkcamp' => isset($_COOKIE['zzkcamp']) && !empty($_COOKIE['zzkcamp']) ? $_COOKIE['zzkcamp'] : '',
				'zfansref' => isset($_COOKIE['zfansref']) && !empty($_COOKIE['zfansref']) ? (int)$_COOKIE['zfansref'] : 0,
//				'sales_flag' => 'G',
//				'first_admin_uid' => 12903,
//				'last_admin_uid' => 12903,
				'sales_flag' => $dispatch['group'],
				'first_admin_uid' => $dispatch['mid'],
				'last_admin_uid' => $dispatch['mid'],
			);
			$params['email'] = !strpos($data['email'],'zzkzzk') ? $data['email'] : '';
			$params['mobile'] = !$data['phone'] == '93112345678' ? $data['phone'] : '';

			$cusbll->insert_new_customer($params);
		 }
	}

    public function get_newest_pmsg_by_both($auth, $to) {
        return $this->pmInfoDao->get_newest_pmsg_by_both($auth, $to);
    }

    public function  have_chat_by_home_guest($homestay_uid, $guest_uid)
    {
        return $this->pmInfoDao->have_chat_by_home_guest($homestay_uid, $guest_uid);
    }

    public function add_block_user($author, $recipient=0) {
        $blocked_author = $this->pmInfoDao->get_block_user($author);
        if($blocked_author) {
            return $this->pmInfoDao->update_block_user($author, 1, $recipient);
        } else {
            return $this->pmInfoDao->add_block_user($author, $recipient);
        }
    }

    public function del_block_user($author) {
        return $this->pmInfoDao->update_block_user($author, 0, $recipient);
    }
}
