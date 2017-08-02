<?php

class Bll_Homestay_StayInfo
{

    private $staydao;
    private $imagesbll;
    public function __construct()
    {
        $this->staydao = new Dao_Homestay_StayMemcache();
        $this->imagesbll = new Bll_images_Info();
        $this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
        $this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
    }

    public function get_stay_by_loc_typecode($tcode)
    {
        return $this->staydao->get_stay_by_loc_typecode($tcode);
    }

    public function get_whole_stay_info_by_id($uid)
    { // 读取民宿所有的信息
        $userbll = new Bll_User_UserInfo();
        $fieldbll = new Bll_Field_Info();

        $userInfo = $userbll->get_whole_user_info($uid);
        $poiInfo = self::get_stayinfo_by_id($uid);
        if (!$poiInfo) {
            $poiInfo = array( // 有用的信息之后都会让用户自己编辑， 所以只写入基本信息吧
                'poiid' => "B209465DD26BA0F8479F_308_" . trim($userInfo['name']) . "_" . trim($userInfo['name']),
                'title' => $userInfo['name'],
                'address' => $userInfo['address'],
                'uid' => $uid,
                'customer_level' => 7,
           );
            // 所有日本的民宿都设成bnb
            if ($userInfo['dest_id'] == '11') {
                $poiInfo['type'] = 15;
            }
            // 大陆默认大陆银行卡
            if ($userInfo['dest_id'] == '12' ) {
                $poiInfo['bank_type_use'] = 2;
            }
            $poiid = $this->insert_weibo_poi_tw($poiInfo);
        }
        $fieldInfo = $fieldbll->get_user_field_by_uids($uid);
        $taxonomy = $fieldbll->get_taxonomy_term_data();
        $tags = self::get_user_tags_byuid($uid);
        $checktime = $this->get_checkin_time($uid);
        $offer_breakfast = $this->staydao->get_zaocan_byuid($uid);
        $holiday = $this->staydao->get_holiday_byuid($uid);
        $baoche = $this->get_baoche_explain_byuid($uid, null, 1);
        $other_service = $this->get_other_service_by_uid($uid);
        $disable_service = $this->get_other_service_by_uid($uid, 2);
        unset($checktime['uid']);

        foreach (reset($fieldInfo) as $k => $v) {
            if ($userInfo[$k]) {
                continue;
            }

            if (is_array(reset($v)) || in_array($k, array('field_data_field_jiaotongtu', 'field_data_field_image'))) { //图片的参数是二位数组
                if (is_array(reset($v))) {
                    $userInfo[$k] = $v;
                } else {
                    $userInfo[$k][] = $v;
                }
            } else {
                $key = array_keys($v);
                // 如果键名包含tid 需要到另外一个配置表里取数据
                if (strpos($key[0], 'tid')) {
                    $userInfo[$k][array_search($reset($v), $taxonomy)] = $taxonomy[reset($v)];
                } else {
                    $userInfo[$k] = reset($v);
                }

            }
        }

        foreach ($poiInfo as $k => $v) {
            if (isset($userInfo[$k])) {
                continue;
            } else {
                $userInfo[$k] = $v;
            }

        }

        $userInfo['user_tags'] = $tags;
        $userInfo['checktime'] = $checktime;
        $userInfo['offer_breakfast'] = $offer_breakfast['value'];
        $userInfo['holiday'] = $userInfo['status'] ? $holiday['take_holiday'] : 4;
        $userInfo['holiday'] = $userInfo['holiday'] ? $userInfo['holiday'] : 0;
        $userInfo['baoche'] = $baoche;
        $userInfo['other_service'] = array_merge($disable_service,$other_service);

        $dao = new Dao_User_UserInfo();
        $uri = $dao->get_t_img_managed($userInfo['picture'], $userInfo['picture_version']);
        $uri = $uri['uri'];
        $uri = "http://img1.zzkcdn.com/" . $uri . ($userInfo['picture_version'] ? "/2000x1500.jpg" : '') . "-userphoto.jpg";
        $userInfo['headimg'] = $uri;

        return $userInfo;

    }

    public function get_user_tags_byuid($uid)
    {
        $data = $this->staydao->get_user_tags_byuid($uid);
        $tags = array();
        foreach ($data as $row) {
            $tags[] = $row['tag_id'];
        }

        return $tags;
    }

    /*
    public function get_stayinfo_by_ids($uids){
    return $this->staydao->get_stayinfo_by_ids($uids);
    }
     */

    public function log_homestay_booking_email($info)
    {
        return $this->staydao->dao_log_homestay_booking_email($info);
    }

    public function get_homestay_booking($id, $nid, $guest_date, $guest_checkout_date)
    {
        return $this->staydao->dao_get_homestay_booking($id, $nid, $guest_date, $guest_checkout_date);

    }

    public function update_homestay_booking_out_order_by_id($out_order, $id)
    {
        return $this->staydao->dao_update_homestay_booking_out_order_by_id($out_order, $id);
    }

    public function update_homestay_booking_by_id($price_tw_pay, $payment_type, $id)
    {
        return $this->staydao->dao_update_homestay_booking_by_id($price_tw_pay, $payment_type, $id);
    }

    public function get_h_favorite_status($uid, $hid)
    {
        return (bool) $this->staydao->get_h_favorite($uid, $hid);
    }

    public function get_staylist_eleven()
    {
        return $this->staydao->get_staylist_eleven();
    }

    public function get_stay_booking_count($guest_mail, $create_time)
    {
        return $this->staydao->get_stay_booking_count($guest_mail, $create_time);
    }

    public function get_homestay_booking_count_by_bid($bid)
    {
        return $this->staydao->dao_get_homestay_booking_count_by_bid($bid);
    }

    public function update_add_bed_price_info($info)
    {
        return $this->staydao->dao_update_add_bed_price_info($info);
    }

    public function zzk_home_detail($id = 0)
    {
        $n = new stdClass;
        $solr = Util_SolrCenter::zzk_get_tw_user_se_service();
        $params = array(
            'qf' => 'id^1000',
            'wt' => 'json',
            'sort' => 'score desc, changed desc',
            'defType' => 'dismax',
        );
        $search_results = $solr->search($id, 0, 1, $params);
        $se_docs = $search_results->response->docs;
        if (isset($se_docs[0])) {
            return $se_docs[0];
        }
        return $n;
    }

    public function get_homestay_room_price($uid, $nid, $mydate, $dest_id = 10, $pre_price_info = null)
    {
        $p_config = array();
        if (!$uid) {
            return $p_config;
        }
        if ($pre_price_info !== null) {
            $price_info = $pre_price_info;
        } else {
            $dao_groceries_info = new Dao_Groceries_GroceriesInfo();
            $price_info = $dao_groceries_info->price_config_v2($uid);
        }
        $room_date = $price_info['room_date'];
        $room_price = $price_info['room_price'];

        $p_config = $this->rpd_parse_v3($room_date, $room_price, $nid, $mydate, $dest_id);
        if (count($p_config)) {
            return $p_config;
        }
        return false;
    }

    public function rpd_parse_v3($room_date, $room_price, $nid, $datei = "2013-07-31", $dest_id = 10)
    {
        if (empty($room_date) || empty($room_price)) {
            return array();
        }
        $column = -1;
        $wname = "";
        $weight = -1;
        $w = date("w", strtotime($datei));
        if ($w == 0) {
            $w = 7;
        }
        list($year, $month, $day) = explode('-', $datei);
        $md = "$month$day";
        $room_date = json_decode($room_date);
        $room_price = json_decode($room_price);
        foreach ($room_date as $k => $rd) {
            foreach ($rd as $kk => $rdd) {
                foreach ($rdd as $rdd2) {
                    $rdd2->QDate = preg_replace('/\-/', '', $rdd2->QDate);
                    $QDateArr = explode('|', $rdd2->QDate);
                    foreach ($QDateArr as $qdak => $qda) {
                        list($s, $e) = explode(',', $qda);
                        if ($s <= $md && $md <= $e) {
                            if (preg_match('/' . $w . '/', $rdd2->WDate)) {
                                $column = $k;
                                $wname = $rdd2->QName;
                                $weight = $rdd2->qx;
                            }
                        }
                    }
                }
            }
        }

        $i_price = 0;
        if ($column >= 0) {
            foreach ($room_price as $rp) {
                if ((int) $rp->rid == (int) $nid) {
                    $prices = explode(',', $rp->price);
                    $i_price = isset($prices[$column]) ? (int) $prices[$column] : 0;
                }
            }
        }
        if ($i_price > 0) {
            $price_format = Util_Common::zzk_tw_price_convert($i_price, $dest_id) . "元";
            return array(
                'date' => $datei,
                'price_format' => $price_format,
                'price' => $i_price,
                'w_name' => $wname,
                'q_name' => '',
            );
        }
        return array();
    }

    public function get_stayinfo_by_id($uid)
    {
        return $this->staydao->get_stayinfo_by_id($uid);
    }

    public function get_whole_homestayinfo_by_uid($uid)
    {
        $bll = new Bll_User_UserInfo();
        $user_info = $bll->get_whole_user_info($uid);
        $poi_info = self::get_stayinfo_by_id($uid);
        $poi_info['loc_typecode'] = str_replace('1,8,553,', '', $poi_info['loc_typecode']);
        $user_info['mail'] = preg_replace('/\.zzk\.group\.[a-zA-Z0-9]+/', '', $user_info['mail']);
        $areabll = new Dao_Area_Area();
        $area = $areabll->get_area_by_locid($poi_info['loc_typecode'], $user_info['dest_id']);
        $poi_info['type_name'] = $area['type_name'];

        return $user_info + $poi_info;
    }
    //vruan 专用函数
    public function get_whole_roominfo_by_uid($uid, $status = 'all', $room_name)
    {
        if ($status == '0') {
            $where_sta = " and status=0";
        } elseif ($status == '1') {
            $where_sta = " and status=1";
        } elseif ($status == 'not_deleted') {
            $where_sta = " and status!=3";
        } else {
            $where_sta = "";
        }
        if (!empty($room_name)) {
            $where_sta .= " and title like ?";
        }
        $sql = "select * from drupal_node where status <> 3 and uid='$uid' " . $where_sta;
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array("%" . $room_name . "%"));
        return $stmt->fetchAll();
    }

    //vruan 专用函数
    public function get_roominfo_by_nid($nid)
    {
        $sql = "select * from one_db.drupal_node where nid='$nid' ;";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }
    //vruan 专用函数
    public function update_room_status($nid)
    {
        if ($_REQUEST['room_status'] == 3) {
            $sql = "update drupal_node set status = '3' where nid = '$nid'";
            $stmt = $this->one_pdo->prepare($sql);
            $stmt->execute();
            $bll = new Bll_Homestay_StayInfo();
            $hid = Bll_Room_Static::get_uid_by_nid($nid);
            $title = Bll_Room_Static::get_room_title_by_nid($nid);
            $bll->insert_homestay_log($hid, array("将房间($nid:" . $title . ")删除了"));
            return true;
        }
        //先取出这个房间的状态，然后搞反
        $sql = "select status from drupal_node where nid = '$nid'";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute();
        $status = $stmt->fetch();
        if ($status['status'] == '0') {
            $status = '1';
        } else {
            $status = '0';
        }
        $sql = "update drupal_node set status = '$status' where nid = '$nid'";
        $stmt = $this->one_pdo->prepare($sql);
        $stmt->execute();
    }

    public function get_homestay_branch_by_uid($uid)
    {
        $bll = new Bll_User_UserInfo();
        $branch = $bll->get_mult_uids($uid);
        foreach ($branch as $v) {
            if ($uid == $v['b_uid']) {
                continue;
            }

            $uids[] = $v['b_uid'];
        }
        $branch_data = $bll->get_whole_user_info($uids);
        return $branch_data;
    }

    public function get_all_branch_list_by_bid($uid)
    { // 通过任意分馆uid 获得所有分馆信息
        if (empty($uid)) {
            return;
        }

        $userbll = new Bll_User_UserInfo();
        $m_uid = $this->staydao->get_master_uid_byuid($uid);
        if (!$m_uid) {
            return array();
        }

        $uids = $this->get_branch_list_by_mid($m_uid);
        $branch_data = $userbll->get_whole_user_info($uids);
        return $branch_data;
    }

    public function get_branch_list_by_mid($uid)
    { //获得分馆uid
        //                $m_uid = $this->staydao->get_master_uid_byuid($uid);
        $m_uid = $uid;
        $userbll = new Bll_User_UserInfo();
        if ($m_uid) {
            $list = $userbll->get_mult_uids($m_uid);
        }
        $n = 0; // n参数是为了保证主馆为第一条记录
        foreach ($list as $v) {
            if ($v['m_uid'] == $v['b_uid']) {
                $uids[0] = $v['b_uid'];
            }else{
                $n++;
                $uids[$n] = $v['b_uid'];
            }
        }
        if(empty($uids)) $uids = array($uid);

        return array_values($uids);
    }

    public function get_master_uid_by_buid($uid)
    { //获得主馆uid
        if(!$uid) return;
        return $this->staydao->get_master_uid_byuid($uid);
    }

    public function verify_is_branch($uid, $b_uid)
    { //验证分馆

        $m_uid = $this->get_master_uid_by_buid($b_uid);
        if ($m_uid == $uid) {
            return true;
        } else {
            return false;
        }

    }

    public function get_all_branch($limit, $offset = 0, $verify = null, $name = null, $mail = null, $dest_id = null)
    {
        if (!$limit || !is_numeric($limit)) {
            $limit = 100;
        }

        if (!is_numeric($offset)) {
            $offset = 0;
        }

        $condition = array();
        if ($verify === 1) {
            $condition[] = ' users.poi_id > 0 ';
        }

        if ($verify === 0) {
            $condition[] = ' users.poi_id = 0 ';
        }

        if ($name) {
            $condition[] = " ( users.name like '%" . trim($name) . "%' or userst.name like '%" . trim($name) . "%' )";
        }

        if ($mail) {
            $condition[] = " ( users.mail like '%" . trim($mail) . "%' or userst.mail like '%" . trim($mail) . "%' )";
        }

        if ($dest_id) {
            $condition[] = " users.dest_id = '$dest_id' ";
        }

        $result = $this->staydao->get_all_branch($limit, $offset, $condition);

        return $result;
    }

    public function get_weibo_column()
    { // 获得t_weibo_poi_tw 的field list
        return $this->staydao->get_weibo_column();
    }

    private $writeParams; // 储存已经整理后的写入数据

    public function write_homestay_record($uid, $params)
    { // 写入民宿数据
        if (empty($uid) || empty($params)) {
            return;
        }

        // 更新第三方民宿信息
        Util_Docking::update_homestay($uid, $params);
        Util_Docking::update_rateplan($uid, $params);

        $this->match_field_params($params); // 依次查询各表字段匹配， 优先级由上到下
        $this->match_user_params($params);
        $this->match_t_weibo_params($params);
        $this->match_other_params($params);

        if ($params) {
//                    Util_Debug::zzk_debug("write homestay unmatch params:", print_r($params, true));
        }
//                    Util_Debug::zzk_debug("params:", print_r($this->writeParams, true));

        foreach ($this->writeParams as $key => $row) {
            if (empty($row)) {
                continue;
            }

            switch ($key) {

                case 'userRecord':
                    $userbll = new Bll_User_UserInfo();
                    $master_uid = $this->get_master_uid_by_buid($uid); //检查分馆的情况
                    if (!$master_uid) { // 如果没有分馆直接更新
                        $args = $row;
                        $args['uid'] = $uid;
                        $userbll->update_user_info($args);
                    } else {
                        $uids = $this->get_branch_list_by_mid($master_uid);
                        $branchArgs = array_intersect_key($row, array(
                            'picture' => 0,
                            'picture_version' => 1,
                        )); // 头像信息需要更新所有的分馆表
                        unset($uids[array_search($uid, $uids)]);
                        if ($branchArgs || $uids) {
                            $userbll->update_multi_user_info($branchArgs, $uids);
                        }

                        $masterArgs = $row;
                        $masterArgs['uid'] = $uid;
                        $userbll->update_user_info($masterArgs); // 有分馆需要更新主馆信息 并更新分馆部分信息
                    }
                    if($row['dest_id']) {
                        $room_bll = new Bll_Room_Update();
                        $room_bll->update_room_record($uid, array(), array('dest_id'=> $row['dest_id']), array(), 'user');
                    }
                    break;

                case 'weiboRecord':
                    $args = $row;
                    $args['uid'] = $uid; // 由于每个民宿都是一个认证，所以认证不考虑分馆情况
                    $this->staydao->update_weibo_poi_tw_byuid($args);
                    break;

                case 'fieldRecord':
                    $fieldbll = new Bll_Field_Info();
                    $userbll = new Bll_User_UserInfo();
                    $args = $row;
                    $args['entity_id'] = $uid;
                    $master_uid = $this->get_master_uid_by_buid($uid);
                    if (!$master_uid) {
                        $fieldbll->write_field_record($args);
                    } else {
                        $branchArgs = array_intersect_key($row, array( // 一些数据需更新所有分馆表
                            'field_data_field_nickname' => 0,
                            'field_data_field_weixin' => 0,
                            'field_data_field__qq' => 0,
                            'field_data_field_skype' => 0,
                        ));
                        $uids = $this->get_branch_list_by_mid($master_uid);
                        unset($uids[array_search($master_uid, $uids)]);
                        if ($branchArgs) {
                            $fieldbll->write_multi_field_record($branchArgs, $uids);
                        }

                        $args = $row;
                        $args['entity_id'] = $uid;
                        $fieldbll->write_field_record($args);
                    }
                    break;

                case 'tagRecord':
                    $args = $row;
                    $args['uid'] = $uid;
                    $this->staydao->write_user_tag_record($args);
                    break;

                case 'checktimeRecord':
                    $args = $row;
                    $args['uid'] = $uid;
                    $this->staydao->write_checkin_time($args);
                    break;

                case 'holidayRecord':
                    $args = $row;
                    $args['uid'] = $uid;
                    $this->staydao->write_holiday($args);
                    break;

                case 'zaocanRecord':
                    $args = $row;
                    $args['uid'] = $uid;
                    $this->staydao->write_zaocan($args);
                    break;

                case 'baocheRecord':
                    $baoche = $row['baoche'];
/*
                    if ($baoche['baoche_explain_free']) {
                        $type = 0;
                        $content = $baoche['baoche_explain_free'];
                        $this->update_baoche_explain($content, $type, $uid);
                    }
*/
                    if ($baoche['baoche_explain_fee']) {
                        $type = 1;
                        $content = $baoche['baoche_explain_fee'];
                        $this->update_baoche_explain($content, $type, $uid);
                    }
                    break;

                case 'serviceRecord':
                    $other_service = $row;
                    if ($row['new_service']) {
                        $this->insert_service_row($row['new_service'], $uid);
                    }

                    if ($row['modify_service']) {
                        $this->update_other_service($row['modify_service'], $uid);
                    }

                    break;
            }
        }

        if(!empty($this->writeParams)) {
            Util_Common::real_time_update_solr($uid);
            Util_Common::real_time_update_solr($uid, "node");
        }
    }

    public function match_field_params(&$params)
    {

        $fieldbll = new Bll_Field_Info();
        $fieldRecord = array();
//        $taxonomy = $fieldbll->get_taxonomy_term_data();
        $keys = array_keys($params);
        $tables = $fieldbll->get_field_table_column('user', 'user'); // 获得和民宿相关信息表 的字段名和对应的表名;
        foreach ($tables as $fieldArr) {
            foreach ($fieldArr as $tableName => $column) {
                if (in_array($tableName, $keys)) {
                    $value = $params[$tableName];
/*
if(strpos(reset($column),'tid')) {
$value = array_search($params[$tableName], $taxonomy);
}else{
$value = $params[$tableName];
}
 */
                    $fieldRecord[$tableName][reset($column)] = $value;
                    unset($params[$tableName]);
                }
            }
        }
        $this->writeParams['fieldRecord'] = $fieldRecord;

    }

    public function match_user_params(&$params)
    { // 获得表字段后作匹配
        $userbll = new Bll_User_UserInfo();
        $fieldList = $userbll->get_user_column();
        $userRecord = array();
        foreach ($params as $k => $v) {
            if (in_array($k, $fieldList)) {
                $userRecord[$k] = $v;
                unset($params[$k]);
            }
        }

        $this->writeParams['userRecord'] = $userRecord;
    }

    public function match_t_weibo_params(&$params)
    {
        $fieldList = $this->get_weibo_column();
        $weiboRecord = array();
        foreach ($params as $k => $v) {
            if (in_array($k, $fieldList)) {
                $weiboRecord[$k] = $v;
                unset($params[$k]);
            }
        }

        $this->writeParams['weiboRecord'] = $weiboRecord;
    }

    public function match_other_params(&$params)
    { // 写死配置表

        foreach ($params as $k => $v) {
            if ($k == 'user_tags') {
                $tagRecord['tag_id'] = $v;
                unset($params[$k]);
            }
            if ($k == 'checktime') {
                $checktimeRecord['checktime'] = $v;
                unset($params[$k]);
            }
            if ($k == 'holiday') {
                $holiday['holiday'] = $v;
                unset($params[$k]);
            }
            if ($k == "offer_breakfast") {
                $zaocan['zaocan'] = $v;
                unset($params[$k]);
            }
            if ($k == 'baoche_explain') {
                $baoche['baoche'] = $v;
                unset($params[$k]);
            }
            if ($k == 'other_service') {
                $other_service = $v;
                unset($params[$k]);
            }
        }

        $this->writeParams['tagRecord'] = $tagRecord;
        $this->writeParams['checktimeRecord'] = $checktimeRecord;
        $this->writeParams['holidayRecord'] = $holiday;
        $this->writeParams['zaocanRecord'] = $zaocan;
        $this->writeParams['baocheRecord'] = $baoche;
        $this->writeParams['serviceRecord'] = $other_service;
    }

    public function insert_weibo_poi_tw($params)
    {
        $this->staydao->update_weibo_poi_tw_byuid($params);
    }

    public function insert_branch_data($m_uid, $b_uid)
    {
        $this->staydao->insert_branch_data($m_uid, $b_uid);
    }

    public function get_homestay_by_id($id, $status = 1)
    {
        if(!$id) return null;
        $solr = Util_SolrCenter::zzk_get_tw_user_se_service();
        $query = "id:$id";
        if (!is_null($status)) {
            $query = $query . " AND status:1";
        }

        $params = array("wt" => "json");
        try {
            $results = $solr->search($query, 0, 1, $params);
        } catch (Exception $e) {
            return null;
        }
        $docs = $results->response->docs;
        if (isset($docs[0])) {
            return $docs[0];
        }
        return null;
    }

    /**
     * get nearby homestay by latlng from solr
     * @author genyiwang <genyiwang@youyao.com>
     * @param $uid
     * @param $latlng
     * @param $distance
     * @param $sort = "asc" asc|desc
     * @param $ret_format = "json"
     * @return json
     */
    public function get_nearby_homestay($uid, $latlng, $distance, $sort = "desc", $limit = 10, $format = "json")
    {
        $solr = Util_SolrCenter::zzk_get_tw_room_se_service();
        $query = "NOT(uid:$uid) AND id:[* TO 2000000000]";
        $params = array(
            "fq" => "{!geofilt pt=$latlng sfield=latlng_p d=$distance}",
            "fl" => "*, distance:geodist(latlng_p,$latlng)",
            "group" => "true",
            "group.field" => "uid",
            "group.limit" => 100,
            "sort" => "geodist(latlng_p,$latlng) asc",
            "wt" => $format,
        );
        $results = $solr->search($query, 0, $limit, $params);
        $docs = $results->grouped->uid->groups;
        return $docs;
    }

    public function get_homestay_story_list($uid = 0, $l = 3, $type = 'all')
    {
        return $this->staydao->get_homestay_story_list($uid, $l, $type);
    }

    public function get_filter_homestay_list($query)
    {
        $condition = array();
        $page = $query['page'] ? $query['page'] : 1;
        $limit = $query['limit'] * ($page - 1) . "," . $query['limit'];
        foreach ($query['filter'] as $k => $v) {
            switch ($k) {
                case 'destId':
                    if ($v != 0) {
                        $condition[] = "dest_id = $v";
                    }
                    break;
                case 'homestayStatus':
                    if ($v == 0) {
                        $holiday = array(0, 1, 2, 3, 4);
                    } elseif ($v == 1) {
                        $holiday = array(0, 1, 2, 3, 4); // 已上架的民宿要不存在于后面序列
                        $limit = ($query['limit'] + 20) * $page; // 需要做特殊处理
                        $condition[] = "status = 1";
                    } else {
                        $v = ($v - 1); // radio对应的值比数据库值大1
                        $holiday = array($v);
                        $holiday_list = $this->get_homestay_list_by_holiday($holiday, "holiday");
                        if (!empty($holiday_list[$v]));
                        $condition[] = "uid in (" . implode(",", $holiday_list[$v]) . ")";
                        if ($v == 4) {
                            $condition[] = "status = 0";
                        } else {
                            $condition[] = "status = 1";
                        }

                    }
                    break;
                case 'verifyStatus':
                    if ($v == 1) {
                        $condition[] = "poi_id > 0";
                    } elseif ($v == 2) {
                        $holiday = array(5);
                        $condition[] = "poi_id = 0";
                    } elseif ($v == 3) {
                        $condition[] = "poi_id = 0";
                    }
                    break;
                case 'signStatus':
                    if ($v == 1) {
                        $condition[] = "is_signed = 1";
                    } elseif ($v == 2) {
                        $condition[] = "is_signed = 0";
                    }
                    break;
                case 'homestayName':
                    if ($v) {
                        $condition[] = "name like '%" . trim($v) . "%'";
                    }

                    break;
                case 'homestayMail':
                    if ($v) {
                        $condition[] = "mail like '%" . trim($v) . "%'";
                    }

                    break;
                case 'phoneNum':
                    if ($v) {
                        $condition[] = " ( send_sms_telnum like '%" . trim($v) . "%' or phone_num like '%" . trim($v). "%')";
                    }

                    break;
                case 'otherServers':
                    if ($v==1) {
                        $condition[] = " ((jiesong_server_check = 1) or (other_server_check = 1) or (baoche_server_check = 1)) ";
                    }
                    if ($v==2) {
                        $condition[] = " (jiesong_server_check = 1) and (other_server_check <> 1) and (baoche_server_check <> 1) ";
                    }
                    if ($v==3) {
                        $condition[] = " jiesong_server_check = 1 ";
                    }
                    if ($v==4) {
                        $condition[] = " huwai_server_check=1 ";
                    }
                    if ($v==5) {
                        $condition[] = " daiding_server_check=1 ";
                    }
                    if ($v==6) {
                        $condition[] = " zaocan_server_check=1 ";
                    }
                    if ($v==7) {
                        $condition[] = " baoche_server_check=1 ";
                    }
                    if ($v==8) {
                        $condition[] = " other_server_check=1 ";
                    }

                    break;
                default:
                    //        if($v) $condition[] = "$k like '%$v%'";
            }

        }

        $holiday_data = $this->get_homestay_list_by_holiday($holiday, "uid");
        list($numFound, $user_list) = $this->staydao->get_filter_homestay_list($condition, $query['sort'], $limit);
        if ($query['filter']['homestayStatus'] == 1) { //  做特殊处理 取出更多地数据 用php作分页
            $key = 1;
            $k = 0;
            foreach ($user_list as $v) {
                if ($holiday_data[$v['uid']] > 0) {
                    continue;
                }

                $k++;
                if ($k <= ($page - 1) * $query['limit']) {
                    continue;
                }

                if ($key > $query['limit']) {
                    break;
                }

                $key++;
                $holiday = $holiday_data[$v['uid']];
                if (!$holiday) {
                    $holiday = 0;
                }

                $row = $v;
                $row['holiday'] = $holiday;
                $result[] = $row;
            }
        } else {
            foreach ($user_list as $k => $v) {
                $row = $v;
                $holiday = $holiday_data[$v['uid']];
                if ($v['status'] == 0) {
                    $holiday = 4;
                }
                //下架状态
                if (!$holiday) {
                    $holiday = 0;
                }

                $row['holiday'] = $holiday;
                $result[] = $row;
            }
        }

        $result['numFound'] = $numFound;

        return $result;

    }

    public function get_homestay_list_by_holiday($holiday, $group)
    {
        if (!isset($holiday)) {
            return;
        }

        if (!is_array($holiday)) {
            $holiday = array($holiday);
        }
        $holiday_list = $this->staydao->get_homestay_list_by_holiday($holiday);
        if ($group == "uid") {
            foreach ($holiday_list as $row) {
                $result[$row['uid']] = $row['take_holiday'];
            }
        } elseif ($group == "holiday") {
            foreach ($holiday_list as $row) {
                $result[$row['take_holiday']][] = $row['uid'];
            }
        } else {
            $result = $holiday_list;
        }
        return $result;
    }

    public function get_user_jiaotongtu($uid)
    {
        return $this->staydao->get_user_jiaotongtu($uid);
    }

    public function get_baoche_explain_byuid($uid, $type = null, $status = null)
    {
        return $this->staydao->get_baoche_explain_byuid($uid, $type, $status);
    }

    public function get_baoche_explain_byids($ids)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        if (empty($ids)) {
            return;
        }

        return $this->staydao->get_baoche_explain_byids($ids);
    }

    public function update_baoche_explain($content, $type, $uid)
    {
        if ($type == 0) {
            $params[] = array(
                'uid' => $uid,
                'status' => 1,
                'type' => $type,
                'content' => $content,
                'create_time' => time(),
            );

            if ($this->get_baoche_explain_byuid($uid, 0)) {
                return $this->staydao->update_baoche_explain(array('content' => $content, 'status' => 1), array('uid' => $uid, 'type' => $type));
            } else {
                return $this->staydao->insert_baoche_explain($params);
            }
        } else {
            foreach ($content as $k => $v) {
                $params[] = array(
                    'uid' => $uid,
                    'status' => 1,
                    'type' => $type,
                    'price' => $v['price'],
                    'content' => $v['content'],
                    'create_time' => time(),
                );
            }
            if ($this->get_baoche_explain_byuid($uid, null)) {
                $this->staydao->update_baoche_explain(array('status' => 0), array('uid' => $uid));
                return $this->staydao->insert_baoche_explain($params);
            } else {
                return $this->staydao->insert_baoche_explain($params);
            }
        }

    }

    public function get_classify_service_by_uid($uid, $status = 1) {
        $result = array();
        $data = $this->get_other_service_package_by_uid($uid, $status);
        foreach($data as $row) {
            $ids[] = $row['id'];
        }
        $images = $this->get_other_service_images_byids($ids);
        foreach($data as $row) {
            $package = $row;
            $package['images'] = isset($images[$row['id']]) ? $images[$row['id']] : array();
            $result[$row['category']][] = $package;
        }

        return $result;
    }

    public function get_other_service_by_uid($uid, $status = 1)
    {
        $data = $this->get_other_service_package_by_uid($uid, $status);
        $result = array();
/*
$service_ids = array();
foreach($data as $k=>$v) {
if(empty($result[$v['service_id']])) {
$result[$v['service_id']]['service_id']   = $v['service_id'];
$result[$v['service_id']]['service_name'] = $v['service_name'];
}
$result[$v['service_id']]['package_list'][] = array(
'package_id' => $v['id'],
'title'      => $v['title'],
'free'       => $v['free'],
'price'      => $v['price'],
'content'    => $v['content'],
);
}
 */
        foreach ($data as $row) {
            $ids[] = $row['id'];
        }

        $images = $this->get_other_service_images_byids($ids);
        foreach($data as $row) {
            $package = $row;
            $package['images'] = isset($images[$row['id']]) ? $images[$row['id']] : array();
            $result[$row['service_id']][] = $package;
        }

        return $result;
    }

    public function get_other_service_package_by_uid($uid, $status = 1) {
        return $this->staydao->get_other_service_by_uid($uid, $status);
    }

    public function   get_other_service_by_id($id,$status=1){
    return $this->staydao->get_other_service_by_id($id,$status);
}

    public function get_service_package_by_ids($ids) {
        if(empty($ids)) return;
        $data = $this->staydao->get_service_package_by_ids($ids);
        $result = array();
        foreach($data as $row) {
            $result[$row['id']] = $row;
        }

        return $result;
    }

    public function get_other_service_images_byids($pids) {
        if(!is_array($pids)) $pids = array($pids);
        if(empty($pids)) return;
        $fids_value = $this->staydao->get_other_service_fids_byids($pids);
        foreach($fids_value as $f) {
            $fids[] = $f['fid'];
        }
        if(empty($fids)) 
            return array();
        $img_dao = new Dao_Images_Info();
        $data = $img_dao->get_multi_t_img_managed($fids);
        $uri_by_fid = array();
        foreach($data as $r) {
            $uri_by_fid[$r['fid']] = $r['uri'];
        }
        foreach($fids_value as $v) {
            $result[$v['pid']][$v['fid']] = $uri_by_fid[$v['fid']];
        }

        return $result;
    }

    public function insert_service_row($params, $uid) {
        $max_id = $this->staydao->get_max_service_id();
        $max_id = $max_id ? $max_id : 0;
        $service_name = array();
        $ids = array();
        foreach ($params as $row) {
            if (!in_array($row['service_id'], $ids)) {
                $ids[] = $row['service_id'];
                $max_id++;
            }
            $picture = $row['picture'];
            unset($row['service_id']);
            unset($row['picture']);   
            $result = array_merge(
                array(
                    'service_id' => $max_id,
                    'uid' => $uid,
                    'status' => 1,
                    'create_time' => time(),
                    'dest_id' => 10,
                    'alone_buy' => 0,
                ),
                $row);
            try{
                $package_id = $this->staydao->insert_other_service($result);
                $user_dao = new Dao_User_UserInfo();
                $opera_uid = Util_Signin::$user->uid;
                foreach($picture as $v) {
                    if(is_numeric($v) || !$v) continue;
                    $picture_fid[] = $user_dao->insert_t_img_manage($v, $opera_uid);
                }
                $this->insert_other_serivce_images($picture_fid, $package_id);
            } catch (Exception $e) {
                print_r($e->getMessage());
            }
        }
        return true;
//        return $this->staydao->update_other_service($result);
    }

    public function update_other_service($params, $uid)
    {
        $field = array(
            'id',
            'category',
            'uid',
            'status',
            'service_id',
            'service_name',
            'title',
            'free',
            'price',
            'content',
            'create_time',
            'dest_id',
            'alone_buy',
        );
        $user_dao = new Dao_User_UserInfo();
        $user_id = Util_Signin::$user->uid;
        foreach ($params as $row) {
            $data = array();
            foreach ($row as $k => $v) {
                if (in_array($k, $field)) {
                    $data[$k] = $v;
                }

            }
            if (!empty($data)) {
                $base_result[] = array_merge(
                    array(
                        'id' => null,
                        'create_time' => time(),
                        'uid' => $uid,
                    ),
                    $data);
            }
            $images = $row['images'];
            $pid = $row['id'];
            if($images == -1) {
                $this->staydao->update_other_service_images($pid, 0);
                continue;
            }
            $img = array();
            foreach($images as $n=>$fid) {
                if(!is_numeric($fid)) {
                    $img[] = $user_dao->insert_t_img_manage($fid, $user_id);
                }else{
                    $img[] = $fid;
                }
            }
            if($img) {
                $this->staydao->update_other_service_images($pid, 0);
                $this->insert_other_serivce_images($img, $pid);
            }

        }
        
        return $this->staydao->update_other_service($base_result);
    }

    public function insert_other_serivce_images($fids, $pid, $status=1) {
        if(empty($fids) || !$pid) return;
        foreach($fids as $k=>$fid) {
            $data[] = array(
                'pid'         => $pid,
                'status'      => $status,
                'fid'         => $fid,
                'delta'       => $k,
                'create_time' => time(),
            );
        }
        $fields = array(
                'pid',
                'status',
                'fid',
                'delta',
                'create_time',
            );
        return $this->staydao->insert_other_service_images($fields, $data);
    }

    public function get_homestay_trac($uid, $pid = 0)
    {
        if (!$uid) {
            return;
        }

        $user_trac = $this->get_homestay_log($uid);
        if ($pid == 0) {
            $poi_data = $this->staydao->get_stayinfo_by_id($uid);
            $pid = $poi_data['pid'];
        }
        $poi_data = $this->get_weibo_log($pid);

        $result = array();
        $user_info_dao = new Dao_User_UserInfoMemcache();
        foreach ($user_trac as $k => $v) {
            $result[] = array(
                'content' => $v['content'],
                'create_date' => $v['create_date'],
                'uid' => $v['uid'],
                'uname' => $v['uname'],
                'client_ip' => $v['client_ip'],
            );
        }

        foreach ($poi_data as $k => $v) {
            $result[] = array(
                'content' => $v['content'],
                'create_date' => $v['create_date'],
                'uid' => $v['uid'],
                'uname' => $user_info_dao->get_username_by_uid($v['uid']),
                'client_ip' => $v['client_ip'],
            );
        }

        usort($result, function ($a, $b) {
            return $b['create_date'] - $a['create_date'];
        });

        return $result;
    }

    public function get_homestay_log($uid)
    {
        return $this->staydao->get_homestay_log($uid);
    }

    public function insert_homestay_log($hid, $messages)
    {
        return $this->staydao->insert_homestay_log($hid, $messages);
    }

    public function get_weibo_log($pid)
    {
        return $this->staydao->get_weibo_log($pid);
    }

    public function get_review_homestay_log($uid) {
        $result = reset($this->staydao->get_specific_homestay_log($uid, "审核不通过-"));
        $content = str_replace("审核不通过-", "", $result['content']);
        return $content;
    }

    public function get_holiday_byuid($uid) {
        if(!$uid) return array();
        return $this->staydao->get_holiday_byuid($uid);
    }

    public function remove_holiday_byuid($uid) {
        if(!$uid) return array();
        return $this->staydao->remove_holiday_byuid($uid);
    }

    public function get_exist_rooms_by_uid($uid)
    {
        return $this->staydao->get_exist_rooms_by_uid($uid);
    }
    
    public function get_bnd_room_by_uid($uid){
        return $this->staydao->get_bnd_room_by_uid($uid);
    }

    public function remove_branch_row_by_buid($buid)
    {
        if (!$buid) {
            return;
        }

        return $this->staydao->remove_branch_row_by_buid($buid);
    }

    public function add_branch($muid, $buid)
    {
        if (!$muid || !$buid) {
            return;
        }

        return $this->staydao->add_branch($muid, $buid);
    }

    public function get_leatest_room_id($uid) {
        $sql = 'SELECT nid FROM one_db.drupal_node WHERE uid=:uid ORDER BY nid DESC LIMIT 1';
        $stmt = $this->one_pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        return $stmt->fetchColumn();
    }

    public function get_checkin_time($uid) {
        return $this->staydao->get_checkin_time($uid);
    }

    public function get_user_jiaotongzixun($uid) {
        if(!$uid) return;
        $dao_user = new Dao_User_UserInfo();
        return $dao_user->get_user_jiaotongzixun($uid);
    }

}
