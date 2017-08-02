<?php
class  Bll_Room_CalendarSync {

    private $dao_calendar;
    public function __construct() {
        $this->dao_calendar = new Dao_Room_CalendarSync();
    }

    public function sync_calendar_to_zzk($nid, $url) {
        //Logger::info(__FILE__, __CLASS__, __LINE__, var_export(array("nid"=> $nid, "url"=> $url), TRUE));
        $trac_data = self::sync_ical($nid,$url); // 获取日历并写入日历表
        if(!$trac_data) return false;
        $status_data = self::sync_ical_date($nid, $trac_data['need_to_add'], $trac_data['need_to_remove']); // 写入日历的房态表
        unset($trac_data); $trac_data = null;
        // 写入自在客的房态表
        $close_days = array();
        $open_days = array();
        foreach($status_data['add_close_days'] as $row) {
            $close_days[] = $row['date'];
        }
        foreach($status_data['remove_close_days'] as $row) {
            $open_days[]  = $row['date'];
        }
        unset($status_data); $status_data = null;

        // 房态日志1
        $room_bll = new Bll_Room_Status();
        $token = md5(time()."8".$nid);
        $days = array_values(array_diff($close_days, $open_days));
        if(!empty($days)) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export(array(
                "nid" => $nid,
                "open"=> $open_days, 
                "close"=> $close_days,
                "log" => $days,
            ), TRUE));
            $room_bll->set_multiple_days_logs($nid, $days, 1, "127.0.0.1", 1, 8, $token);
        }

        // 先恢复在关房!!
        self::restore_room_status($nid, $open_days);
        self::sync_calendar_to_room_status($nid, $close_days);

        // 房态日志2
        if(!empty($days)) {
            $room_bll->set_multiple_days_logs($nid, $days, 1, "127.0.0.1", 2, 8, $token);
        }

        unset($open_days); $open_days = null;
        unset($close_days); $close_days = null;
        $this->dao_calendar->update_calendar_sync_record(array("last_update"=>time()), array("rid"=>$nid));
    }

    public function get_all_calendar_info(){
        return $this->dao_calendar->get_all_calendar_info();
    }

    public function get_calendar_sync_info_byrid($rid, $status=1) {
        return $this->dao_calendar->get_calendar_sync_info_byrid($rid, $status);
    }

    public function get_token_bynid($nid) {
        return md5("calendar_".$nid."heishifujuanxinsujiankanghaochi");
    }

    public function add_calendar_sync_record($rid, $uid, $url, $name) {
        $token = self::get_token_bynid($rid);
        return $this->dao_calendar->add_calendar_sync_record($rid, $uid, $url, $name, $token);
    }

    // 将日历保存到 日历库
    public function sync_ical($nid, $url) {
        $new_data = self::get_ical_file_byurl($url);
        if($new_data['code']!=200) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export(
                array(
                    'nid' => $nid,
                    'url' => $url,
                    'info'   => "request failed",
                    'result' => $new_data,
                ), TRUE));
            return false;
        }
        $ical = new Ical($new_data['content']);
        if(!$ical->is_ical) {
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export(
                array(
                    'nid' => $nid,
                    'url' => $url,
                    'info'   => "ical verify failed",
                    'result' => $ical,
                ), TRUE));
            return false;
        }
        $content = $ical->events();
        unset($new_data); $new_data = null;
        unset($ical); $ical = null;
        $old_data = self::get_ical_by_nid($nid, 1);
        $format_date = array();
        $need_to_add = array();
        $need_to_remove = array();
        foreach($old_data as $k=>$v) {
            $format_date[] = $v['start'].$v['end'];
        }  // 只要开始日期和结束日期重复就算已经导入
        unset($old_data); $old_data = null;
        foreach($content as $k=>$v) {
            $start = date('Ymd', strtotime($v['DTSTART']));
            $end = date('Ymd', strtotime($v['DTEND']));
            if(in_array($start.$end, $format_date)) {
                unset($format_date[array_search($start.$end, $format_date)]);
                continue;
            }
            $need_to_add[] = array(
                    "nid"         => $nid,
                    "start"       => $start,
                    "end"         => $end,
                    "summary"     => $v['SUMMARY'],
                    "description" => $v['DESCRIPTION'],
                    "loction"     => $v['LOCATION'],
                    "create_time" => time(),
                    "status"      => 1,
                );
        }

        foreach($format_date as $row) {
            $need_to_remove[] = array(
                    "nid"   => $nid,
                    "start" => substr($row, 0, 8),
                    "end"   => substr($row, 8),
                );
        }
        unset($format_data); $format_data = null;

        $result = array(
                'need_to_add'    => $need_to_add,
                'need_to_remove' => $need_to_remove,
            );
        //Logger::info(__FILE__, __CLASS__, __LINE__, var_export($result, TRUE));
        foreach($need_to_remove as $r) {
            $this->dao_calendar->disable_ical_trac($r['nid'], $r['start'], $r['end']);
        }
        $this->dao_calendar->add_ical_trac($need_to_add);

        return $result; 

    }

    public function unlink_ical_sync($nid) {
        if(!$nid) return;
        $old_data = self::get_ical_by_nid($nid, 1);
        $need_to_remove = array();
        // 移除ical_date 上的房态
        foreach($old_data as $row) {
            for($day=strtotime($row['start']); $day<strtotime($row['end']); $day+=60*60*24) {
                $remove_close_days[] = array(
                        "nid"         => $nid,
                        "date"        => date("Y-m-d", $day),
                    );
                $open_days[]  = date("Y-m-d", $day);
            }
        }
        $this->dao_calendar->disable_all_ical_date_bynid($nid);
        $this->dao_calendar->disable_all_ical_trac_bynid($nid);
        //恢复我们的房态
        self::restore_room_status($nid, $open_days);
        $this->dao_calendar->update_calendar_sync_record(array("status"=>0), array("rid"=>$nid));
        return;

    }

    // 将日历上的房态同步到专门的房态表
    public function sync_ical_date($nid, $need_to_add= array(), $need_to_remove = array()) {
        $close_days = array();
        $open_days = array();
        foreach($need_to_add as $row) {
            for($day=strtotime($row['start']); $day<strtotime($row['end']); $day+=60*60*24) {
                $add_close_days[] = array(
                        "nid"         => $nid,
                        "date"        => date("Y-m-d", $day),
                        "create_time" => time(),
                        "status"      => 1,
                    );
            }
        }

        foreach($need_to_remove as $row) {
            for($day=strtotime($row['start']); $day<strtotime($row['end']); $day+=60*60*24) {
                $remove_close_days[] = array(
                        "nid"         => $nid,
                        "date"        => date("Y-m-d", $day),
                    );
            }
        }

        //Logger::info(__FILE__, __CLASS__, __LINE__, var_export($remove_close_days, TRUE));
        //Logger::info(__FILE__, __CLASS__, __LINE__, var_export($add_close_days, TRUE));
        // 先删在加！！
        foreach($remove_close_days as $row) {
            $this->dao_calendar->disable_ical_date($row['nid'], $row['date']);
        }
        $this->dao_calendar->add_ical_date($add_close_days);
        
        return array("add_close_days" => $add_close_days, "remove_close_days" => $remove_close_days);
    }

    public function get_ical_by_nid($nid, $status=1) {
        if(!$nid) return array();
        return $this->dao_calendar->get_ical_by_nid($nid, $status);
    }


    // 将日历表里的房态同步到我们的房态  仅保存有效日期
    public function sync_calendar_to_room_status($nid, $days) {
        if(empty($days)) return;
        $room_bll = new Bll_Room_RoomInfo(); 
        $params = array(
                "nid" => $nid,
                "uid" => 1,  //处理人id
                "room_num" => 0,
                "days" => $days,
            );
        return $room_bll->update_room_state_by_date($params);
    }

    // 恢复我们日历表的房态
    public function restore_room_status($nid, $days) {
        if(empty($days)) return;
        $room_bll = new Bll_Room_RoomInfo(); 
        $roominfo = $room_bll->get_room_detail_by_nid($nid);
        $params = array(
                "nid" => $nid,
                "uid" => 1,  //处理人id
                "days" => $days,
            );
        // 需要判断是按人卖还是按房卖， 按人卖的话恢复人数的房态
        if($roominfo['room_price_count_check']==2) {
            $fieldbll = new Bll_Field_Info();
            $room_model_tid = reset($fieldbll->get_field($params['nid'], "room_beds"));
            $tax = $fieldbll->get_taxonomy_term_data($room_model_tid['field_room_beds_tid']);
            $params['room_num'] = reset($tax);
        }else {
            $params['room_num'] = "1";
        }

        return $room_bll->update_room_state_by_date($params);
    }

    public function get_ical_avaliable_date_by_nid($nid) {
        return $this->dao_calendar->get_ical_date_by_nid($nid, 1, date("Y-m-d"));
    }

    public function get_ical_file_byurl($url) {
        $extension = pathinfo($url, PATHINFO_EXTENSION);
        if(!preg_match("/^ics/", $extension)) {
            return array(
                    "status" => "400",
                    "content" => null,
                );
        }

        // 需要代理，单独写了curl
        // $file = Util_Curl::get($url);
        do{ // 因为用了代理， 302需要手动follow
            $response = self::curl_get($url);
            $match = preg_match_all('/^Location:(.*)$/mi', $response['header'], $matches);
            $url = trim($matches[1][0]);
            if($response['code'] == 302 && $match) {
//                Logger::info(__FILE__, __CLASS__, __LINE__, "follow_redirect_url", var_export($url, true));
            }
        } while($response['code'] == 302 && $match);

        return $response;

    }

    private function curl_get($url) {

        if(strpos($url, 'https') !== false) {
            $is_https = true;
            $url = str_replace("https", "http", $url);
            $proxy = "http://139.196.139.35:8443";
        } else {
            $is_https = false;
            $proxy = "139.196.139.35:8080";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setOpt($ch, CURLOPT_HTTPGET, true);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //手动做跳转
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
//        curl_setopt($ch, CURLOPT_VERBOSE, 1); // debug
        curl_setopt($ch, CURLOPT_HEADER, 1); // 需要返回头信息， 之后再区分body
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // 尝试连接时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 总时间
        $output = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($output, 0, $header_size);
        $body = substr($output, $header_size);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($responseCode != 200) {
            $error = curl_error($ch);
        }
        curl_close($ch);

        return array('code' => $responseCode, 'content' => $body, 'header' => $header, 'error'=> $error );

    }

}
