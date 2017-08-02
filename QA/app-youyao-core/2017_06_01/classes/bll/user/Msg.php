<?php

class Bll_User_Msg {
	private $dao;

	public function __construct() {
		$this->dao = new Dao_User_Msg();
	}
	
    // 短信队列
	public function send_msg($params){
		$this->dao->send_msg($params);
	}

    // 发送一条单独的消息
    // 适用于自动发的、重用性差的消息
    // 可以给多个用户，也可一个单个用户
    // @todo 发送最好改成发mq吧
    public function send_new_marketing_msg($uids, $title, $content, $url=null, $tag=null, $dest_id=10, $user_type=0, $type=0, $category=1){
        if(!is_array($uids)) $uids = array($uids);
        if(!$uids) return true;

        $notify_number = count($uids);
        $tpl_id = self::create_msg_tpl($type, $user_type, $title, $content, $notify_number, $category, $url, $tag, $dest_id, 1);
        self::send_marketing_msg_by_sid($uids, $tpl_id);
    }

    // 创建一个信息内容
    /*
     * category 一级类别:0.未定义,1.程序自动发送,2.运营人员编辑发送,
     * type 二级类别:0.其他，1.链接，2.文字,
     * user_type 用户类型:0:不区分（全部）,1:民宿主人,2:客人,
     * title 消息的标题,
     * content 消息内容,
     * url 消息链接,
     * tag 消息标签,
     * confirm_number 确认数量,
     * notify_number 通知数量,
     * dest_id 目的地:多个目的地用,分隔,
     * is_publish 是否发布:0未发布,1发布(发布成功不能修改),
     * is_delete 是否删除:0否,1已删除,
     */
    public function create_msg_tpl($type, $user_type, $title, $content, $notify_number, $category, $url = null, $tag = null, $dest_id=10, $is_publish=0) {
        $data = array(
            'category'      => $category,
            'type'          => $type,
            'user_type'     => $user_type,
            'title'         => $title,
            'content'       => $content,
            'notify_number' => $notify_number,
            'create_at'     => date('Y-m-d H:i:s'),
        );
        if($url)        $data['url']        = $url;
        if($tag)        $data['tag']        = $tag;
        if($dest_id)    $data['dest_id']    = $dest_id;
        if($is_publish) {
            $data['is_publish'] = $is_publish;
            $data['publish_at'] = date('Y-m-d H:i:s');
        }

        $tpl_id = $this->dao->insert_message_main($data);
        return $tpl_id;
    }

    /* 修改模板信息
     * 编辑、发布、删除
     * */
    public function modify_tpl_by_id($id, $modify) {
        if(empty($modify)) return false;
        if($modify['is_publish']) {
            $modify['publish_at'] = date('Y-m-d H:i:s');
        }

        return $this->dao->update_message_main($id, $modify);
    }

    /*
     * 已读信息的动作
     * */
    public function read_message($id, $uid) {
        $change_count = $this->dao->set_message_read($id, $uid);
        if($change_count > 0 ){
            $this->add_msg_confirm($id);
        }
    }

    public function add_msg_confirm($id, $num=1) {
        return $this->dao->add_message_number($id, $num);
    }

    public function add_msg_notify($id, $num=1) {
        return $this->dao->add_message_number($id, $num, 2);
    }

    // 根据message_id发送私信
    private $message_info;
    public function send_marketing_msg_by_sid($uids, $message_id, $mobile_push=true) {
        if(!is_array($uids)) $uids = array($uids);
        if(empty($uids)) return true;
        $data = array();
        $already_record_uids = array();
        if(!($this->message_info)) {
            $message_info = $this->get_message_by_id($message_id);
            $this->message_info = $message_info;
        }
        foreach($uids as $uid) {
            $data[] = array(
                'message_id' => $message_id,
                'user_id'    => $uid,
                'is_delete'  => 0,
                'is_read'    => 0,
            );
            $already_record_uids[] = $uid;
            if(count($already_record_uids) > 2000)  { // 一次最多写入2000条
                $uids_over = array_values(array_diff($uids, $already_record_uids));
                self::send_marketing_msg_by_sid($uids_over, $message_id, $mobile_push);
                break;
            }
        }

        // 发推送
        if($mobile_push) {
            Util_Notify::push_message_to_multiple_client($already_record_uids, $this->message_info['title'], Util_Notify::get_push_mtype('msg_detail'), $message_id);
        }
        // 写入index表
        $this->dao->insert_message_index($data); 

    }

    /*
     * 根据用户id 查询消息列表
     * */
    public function get_message_list_byuid($uid, $is_delete=0) {
        return $this->dao->select_message_list_byuid($uid, $is_delete);
    }

    /*
     * 查询未读消息数量
     * */
    public function get_unread_message_num_byuid($uid) {
        return $this->dao->select_unread_message_num_byuid($uid, $is_delete);
    }

    /*
     * 根据message id 查询消息
     * */
    public function get_message_by_id($message_id) {
        return $this->dao->select_message_byid($message_id);
    }

    /*
     * 查询用户uid list
     * @type all homestay customer
     * 全部的用户uid会超过内存，需要使用请循环调用
     * */
    public function get_uids_list_bytype($type, $dest_ids, $offset=0, $limit=1000) {
        $user_dao = new Dao_User_UserInfoMemcache();

        $data = array();
        $uids = array();
        if($type == "all") {
            $function_name = "get_all_uid_list";
        }else if($type == "homestay") {
            $function_name = "get_homestay_uid_list";
        }else if($type == "customer" ) {
            $function_name = "get_customer_uid_list";
        }

        if(method_exists($user_dao, $function_name)) {
            $uids = $user_dao->$function_name($offset, $limit, $dest_ids);
        }

        return $uids;
    }

}
