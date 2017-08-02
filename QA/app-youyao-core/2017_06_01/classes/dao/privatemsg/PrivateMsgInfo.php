<?php
class Dao_Privatemsg_PrivateMsgInfo {

	private $pdo;
    private $slave_pdo;
    private $one_pdo;
    private $one_slave_pdo;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
    }

	public function get_privatemsg_bydate($start, $end) {
		$sql = "select * from drupal_pm_message where timestamp between $start and $end ";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

    public function get_list_by_uid($uids, $start_time = null, $limit = 100) {
        if(empty($uids)) return;
        $time_condition = "";
        $tmp_value = array_merge($uids, $uids);
        foreach($uids as $uid) {
            $join_condition .= " and index_b.recipient != ? ";
        }
        $query_values = array();
        if($start_time) {
            $time_condition = "where message.timestamp > ?";
            $que_values[] = $start_time;
        }
        $limit_condition = "";
        if($limit !== null) {
            $limit_condition = "limit $limit";
        }
        $rand = substr(md5(json_encode($uids).mt_rand(1,1000).time()), 7);
        //  根据多个uid，对应index表里的recipient 查出mid,然后再查index表的另一个recipient（既另一个用户的uid）。因为只需要一个list，所以需要distinct uid 以及最新的一条消息，但是mysql的group，distinct只能取到主键最小的一条，都无法取到最新一条，所以需要在关联一下自己。然后在关联系啊message表取出时间和subject
        // 这种查法，如果自己的两个分馆账号之间聊天，就查不出来
        $create_sql = "
create temporary table tmp_message_$rand ( 
    mid int(11) , 
    a_uid int(11), 
    b_uid int(11), 
    unread_count int(11), 
    KEY `mid` (`mid`)
) engine=InnoDB charset=utf8 
select 
    max(index_b.mid) as mid,
    index_a.recipient as a_uid, 
    index_b.recipient as b_uid, 
    sum(index_a.is_new) as unread_count 
from 
    drupal_pm_index index_a 
left join 
    drupal_pm_index index_b on index_a.mid = index_b.mid $join_condition 
where 
    index_a.recipient in (".Util_Common::placeholders("?",count($uids)).")  and 
    index_b.mid is not null 
group by 
    index_b.recipient 
order by 
    mid desc
$limit_condition";
        $query_sql = "select message.*,tmp.a_uid as branch_uid,tmp.b_uid as other_uid,tmp.unread_count from tmp_message_$rand tmp left join drupal_pm_message message on tmp.mid = message.mid and tmp.mid is not null $time_condition order by message.timestamp desc ;";
        $drop_sql = "drop table if exists tmp_message_$rand";

        try{
            $this->one_pdo->beginTransaction();
            $stmtTmp = $this->one_pdo->prepare($create_sql);
            $stmtQue = $this->one_pdo->prepare($query_sql);
            $stmtDrp = $this->one_pdo->prepare($drop_sql);
            $stmtTmp->execute($tmp_value);
            $stmtQue->execute($que_values);
            $stmtDrp->execute();
            $this->one_pdo->commit();
            return $stmtQue->fetchAll();
        }catch(Exception $e) {
            print_r($e->getMessage());
            $this->one_pdo->rollBack();
        }
    }

    public function get_message_by_both($me_uids, $other_uids, $offset, $limit) {
        $sql = "SELECT  distinct(pm_message.mid), author, body, timestamp, is_new,recipient,format FROM one_db.drupal_pm_message pm_message, one_db.drupal_pm_index pm_index WHERE
    ((pm_message.author in (".Util_Common::placeholders("?", count($me_uids)).") AND pm_index.recipient in (".Util_Common::placeholders("?", count($other_uids)).") ) OR (pm_message.author in (".Util_Common::placeholders("?", count($other_uids)).") AND pm_index.recipient in (".Util_Common::placeholders("?", count($me_uids))."))) AND pm_message.mid = pm_index.mid ORDER BY pm_message.mid desc limit $offset, $limit";
        $pdoVal = array_merge(
                    array_values($me_uids),array_values($other_uids),array_values($other_uids),array_values($me_uids)
                );
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute($pdoVal);
        return $stmt->fetchAll();
    }

	public function get_recipient_bymid($mids) {
		$midStr = implode(",", $mids);
		$sql = "select * from drupal_pm_index where mid in ($midStr) ";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_all_privatemsg_by_uid($uid, $limit=0) {
		$sql = "SELECT pm_index.mid FROM drupal_pm_message pm_msg, drupal_pm_index pm_index where pm_msg.mid = pm_index.mid and pm_index.recipient = $uid and pm_index.deleted = 0 order by pm_msg.mid desc";
		if($limit) {
			$sql .= " limit ".$limit;
		}
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function get_one_recipient_bymid($mid, $uid) {
		$sql = "SELECT recipient FROM drupal_pm_index where mid = $mid and recipient <> $uid";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	public function get_data_by_midnrecipient($recipient, $mids) {
		$midStr = implode(",", $mids);
		$sql = "select * from drupal_pm_index where mid in ($midStr) and recipient = $recipient";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->rowCount();
	}

	public function get_privatemsg_list($limit = 10) {
		$sql = "select * from drupal_pm_message order by mid desc limit $limit";
//print_r($sql);
try{
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
}catch(Exception $e) {
	print_r($e->getMessage());
}
	}

	public function insert_into_pm_message($params) {
		$sql = "insert into drupal_pm_message (author, subject, body, format, timestamp, client_ip)  values (
	'".$params['author']."',
	'".$params['subject']."',
	'".$params['body']."',
	'full_html',
	'".$params['timestamp']."',
	'".$params['client_ip']."'
)";
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute();
		return $this->one_pdo->lastInsertId();
	}

	public function insert_into_pm_index($params) {
		$sql = "insert into drupal_pm_index (mid, thread_id, recipient, type, is_new) 
			values (
				'".$params['mid']."',
				'".$params['mid']."',
				'".$params['recipient']['user']."',
				'user',
				'1'
			)
			, (
				'".$params['mid']."',
				'".$params['mid']."',
				'".$params['recipient']['homestay']."',
				'user',
				'1'
			) ";
		$stmt = $this->one_pdo->prepare($sql);
		return $stmt->execute();
	}

	public function insertEasemobMsg($msg) {
		$this->one_pdo->beginTransaction();
		$sql = "insert into drupal_pm_message (author, `subject`, body, `format`, `timestamp`, provider)  values (:author, :subject, :body, :format, :timestamp, :provider)";
		$stmt = $this->one_pdo->prepare($sql);
		if (!$stmt->execute(array(
			'author' => $msg->from,
			'subject' => $msg->subject,
			'body' => $msg->body,
			'format' => 'plain_text',
			'timestamp' => $msg->sent_time,
			'provider' => $msg->provider,
		))) return false;

		$mid = $this->one_pdo->lastInsertId();

		$sql = "insert into drupal_pm_index (mid, thread_id, recipient, type, is_new) values (:mid, :thread_id, :recipient, :type, :is_new)";
		$stmt = $this->one_pdo->prepare($sql);
		if (!$stmt->execute(array(
				'mid' => $mid,
				'thread_id' => $mid,
				'recipient' => $msg->from,
				'type' => 'user',
				'is_new' => 0,
		))) return false;

		if (!$stmt->execute(array(
				'mid' => $mid,
				'thread_id' => $mid,
				'recipient' => $msg->to,
				'type' => 'user',
				'is_new' => $msg->is_read == 1 ? 0 : 1,
		))) return false;

		$this->one_pdo->commit();

		return true;
	}

	public function getLatestMessages($provider, $fromTime)
	{
		$sql = "select a.mid msg_id, a.format, a.provider, a.author `from`, b.recipient `to`, b.thread_id, a.timestamp sent_time, a.subject, a.body, b.is_new, b.deleted from drupal_pm_message a inner join drupal_pm_index b on (a.mid = b.mid and a.author <> b.recipient) where a.provider = ? and a.timestamp > ? order by a.timestamp asc";
		$stmt = $this->one_slave_pdo->prepare($sql);
		$stmt->execute(array($provider, $fromTime));
		return $stmt->fetchAll(PDO::FETCH_OBJ);
	}

	/**
	 * 设置from到to的消息，to一方为已读状态
	 * @param $from
	 * @param $to
	 * @return int -1 出错，0~n 更新条数
	 */
	public function readMessages($from, $to) {
		$sql = "select distinct a.mid from drupal_pm_message a inner join drupal_pm_index b on a.mid = b.mid where a.author = ? and b.recipient = ? and b.is_new = 1";
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute(array($from, $to));
		$rows = $stmt->fetchAll(PDO::FETCH_OBJ);
		if (empty($rows)) return 0;

		$mids = array();
		foreach ($rows as $row) {
			$mids[] = $row->mid;
		}

		$sql = "update drupal_pm_index set is_new = 0 where mid in (".implode(',', $mids).")";
		$stmt = $this->one_pdo->prepare($sql);
		if ($stmt->execute()) {
			return count($rows);
		}

		return -1;
	}

    public function get_mids_by_uid($uids) {
        $mark = Util_Common::placeholders("?", count($uids), ",");
        $sql = "select mid from drupal_pm_index where recipient in ($mark)";
        $stmt = $this->one_pdo->prepare($sql);
        $stmt->execute($uids);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function get_recipient_by_mids($mids, $limit=300) {
        $mark = Util_Common::placeholders("?", count($mids), ",");
        $sql = "select * from drupal_pm_index where mid in ($mark) order by mid desc limit $limit";
        $stmt = $this->one_pdo->prepare($sql);
        $stmt->execute($mids);

        return $stmt->fetchAll();
    }

    public function get_message_by_mids($mids, $limit=300) {
        $mark = Util_Common::placeholders("?", count($mids), ",");
        $sql = "select * from drupal_pm_message where mid in ($mark) order by timestamp desc limit $limit";
        $stmt = $this->one_pdo->prepare($sql);
        $stmt->execute($mids);

        return $stmt->fetchAll();
    }

    public function get_message_by_uids($uids) {
        $mark = Util_Common::placeholders("?", count($uids), ",");
        $sql = "select * from drupal_pm_message where author in ($mark) order by timestamp asc";
        $stmt = $this->one_pdo->prepare($sql);
        $stmt->execute($uids);

        return $stmt->fetchAll();
    }

    public function get_newest_pmsg_by_both($auth, $to) {
        $sql = "select * from drupal_pm_message msg left join drupal_pm_index indx on indx.mid = msg.mid and indx.recipient != ? where msg.author = ? and indx.recipient = ? order by timestamp desc ";
        $stmt = $this->one_pdo->prepare($sql);
        $stmt->execute(array($auth, $auth, $to));

        return $stmt->fetch();
    }

	public function  have_chat_by_home_guest($homestay_uid,$guest_uid){
		$sql = "select a.mid from `drupal_pm_index` as a join `drupal_pm_index` as b on a.mid=b.mid  where a.`recipient`=? and b.`recipient`=? limit 1";
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute(array($homestay_uid,$guest_uid));
		return $stmt->fetch();

	}

    public function get_block_user($author, $status=null) {
        $sql = "select * from one_db.drupal_pm_block_user where author = ?";
        if($status) $sql .= " and status = $status";
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute(array($author));
		return $stmt->fetch();
    }

    public function add_block_user($author, $recipient=0) {
        $sql = "insert into one_db.drupal_pm_block_user (author, recipient, status) values (?, ?, 1)";
        if($status) $sql .= " and status = $status";
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute(array($author, $recipient));
    }

    public function update_block_user($author, $status, $recipient=0) {
        $sql = "update one_db.drupal_pm_block_user set status = ?,recipient = ?,updated = ? where author = ?";
		$stmt = $this->one_pdo->prepare($sql);
		$stmt->execute(array($status, $recipient, date('Y-m-d H:i:s'), $author));
    }
}
