<?php
apf_require_class("APF_DB_Factory");

class Dao_User_Msg {
	
//    private $pdo;
//
//    public function __construct() {
//        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
//        //$pdo = APF_DB_Factory::get_instance()->get_pdo("mkmaster");
//    }
	
    public function send_msg($params) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "insert into t_sms_queue (oid,sid,uid,mobile,content,retry,area,create_time) 
        values (?, ?, ?, ?, ?, ?,?,?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array($params['oid'], $params['sid'], $params['uid'],$params['mobile'], $params['content'], 
        $params['retry'],$params['area'],time()));
    }

    /*
     * 入参@params和field_list类似
     * */
    public function insert_message_main($params) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");

        // 防sql注入，或有不存在的字段写入
        $field_list = array(
            //'id',
            'category',
            'type',
            'user_type',
            'title',
            'content',
            'url',
            'tag',
            'confirm_number',
            'notify_number',
            'dest_id',
            'is_publish',
            'is_delete',
            'create_at',
            'publish_at',
            'update_at',
        );
        $diff_arr = array_diff(array_keys($params), $field_list);
        if(!empty($diff_arr)) {
            return false;
        }
        $key_list = implode(",", array_keys($params));
        $pdo_value = array_values($params);
        $sql = "insert into t_send_message ($key_list) values (" . Util_Common::placeholders("?", count($pdo_value)) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($pdo_value);
        return $pdo->lastInsertId();
    }

    public function update_message_main($id, $params) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");

        // 防sql注入，或有不存在的字段写入
        $field_list = array(
            //'id',
            'category',
            'type',
            'user_type',
            'title',
            'content',
            'url',
            'tag',
            'confirm_number',
            'notify_number',
            'dest_id',
            'is_publish',
            'is_delete',
            'create_at',
            'publish_at',
            'update_at',
        );
        $diff_arr = array_diff(array_keys($params), $field_list);
        if(!empty($diff_arr)) {
            return false;
        }

        $pdo_val = array();
        foreach($params as $k=>$v) {
           $placeholder[] = " $k = ? ";
           $pdo_val[] = $v;
        }
        if(empty($pdo_val)) return false;
        $pdo_val[] = $id;
        $sql = "update t_send_message set " . implode(", ", $placeholder). " where id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($pdo_val);
        
    }

    /*
     * @params = array(  二维数组
     *    array(
     *      'message_id' => @,
     *      'user_id'    => @,
     *      'is_delete'  => @, // 非必填
     *      'is_read'    => @, // 非必填
     *    )
     * )
     * */
    public function insert_message_index($params) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $pdo_value = array();
        foreach($params as $row) {
            $placeholders[] = "(?, ?, ?, ?, ?)";
            $values = array(
                $row['message_id'],
                $row['user_id'],
                ($row['is_delete'] ? $row['is_delete'] : 0),
                ($row['is_read']   ? $row['is_read']   : 0),
                date('Y-m-d H:i:s'),
            );
            $pdo_value = array_merge($pdo_value, $values);
        }
        if(empty($pdo_value)) return false;

        $sql = "insert into t_send_message_user (message_id, user_id, is_delete, is_read, create_at) values " . implode(",", $placeholders);
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($pdo_value);
    }

    public function select_message_list_byuid($uid, $is_delete=0) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select 
            message.*, 
            inde.is_read, inde.user_id 
        from t_send_message_user inde left join t_send_message message on inde.message_id = message.id where inde.user_id = ? and inde.is_delete = ? ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($uid, $is_delete));
        return $stmt->fetchAll();
    }

    public function select_unread_message_num_byuid($uid) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select count(*)
            from t_send_message_user where user_id = ? and is_read = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($uid));
        return $stmt->fetchColumn();
    }

    public function set_message_read($id, $uid) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "update t_send_message_user set is_read = 1 where message_id = ? and user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($id, $uid));
        $count = $stmt->rowCount();
        return $count;
    }

    public function add_message_number($id, $number, $type=1) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        if($type == 1) {
            $field = "confirm_number";
        }elseif($type == 2) {
            $field = "notify_number";
        }else{
            return false;
        }
        $sql = "update t_send_message set $field = $field + ? where id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(array((int)$number, $id));
    }

    public function select_message_byid($message_id) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select * from t_send_message where id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($message_id));
        return $stmt->fetch();
    }

    /*
     * 获得运营人员消息
     * */
    public function get_operation_message_list($user_type=null, $start=null, $end=null, $status=null, $keywords=null, $dest_ids=array()) {
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");

        $condition = array();
        $pdo_value = array();
        $condition[] = ' category = 2';
        if($user_type=='customer') {
            $condition[] = " user_type = 2";
        }elseif($user_type=='homestay') {
            $condition[] = " user_type = 1";
        }

        if($start) {
            $condition[] = " unix_timestamp(publish_at) > ? ";
            $pdo_value[] = strtotime($start);
        }
        if($end) {
            $condition[] = " unix_timestamp(publish_at) < ? ";
            $pdo_value[] = strtotime($end);
        }

        if($status == 'published') {
            $condition[] = " is_publish = 1 ";
        }elseif($status == 'notpublish'){
            $condition[] = " is_publish = 0 ";
        }

        if($keywords) {
            $condition[] = " ( title like ? or content like ? ) ";
            $pdo_value[] = "%" . $keywords . "%";
            $pdo_value[] = "%" . $keywords . "%";
        }

        foreach($dest_ids as $dest_id) {
            $dest_condition[] = " dest_id like ? ";
            $pdo_value[] = "%" . $dest_id . "%";
        }
        if($dest_condition) {
            $condition[] = "( ".implode(" or ", $dest_condition).")";
        }
        $condition[] = " is_delete = 0";

        $sql = "select * from t_send_message where " . implode(" and ", $condition);
//        print_r($sql);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($condition);
        return $stmt->fetchAll();

    }


}
