<?php
apf_require_class("APF_DB_Factory");

class Dao_Room_CalendarSync {

    private $pdo;
    private $one_pdo;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
    }


    // t_calendar_sync 民宿同步日历相关
    public function get_calendar_sync_info_byrid($rid, $status) {
        $sql = "select * from t_calendar_sync where rid = ? and status = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($rid, $status));
        return $stmt->fetchAll();
    }

    public function get_all_calendar_info() {
        $sql = "select * from t_calendar_sync where status = 1 order by update_date";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function add_calendar_sync_record($rid, $uid, $url, $name, $token) {
        $sql = "insert into t_calendar_sync (rid, uid, name, url, token, last_update, status) values (?, ?, ?, ?, ?, ?, ?) on duplicate key update name=values(name), url=values(url), last_update=values(last_update), status=values(status)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($rid, $uid, $name, $url, $token, time(), 1 ));
    }

    public function update_calendar_sync_record($data, $field) {
        $fields_list = array(
                "rid",
                "uid",
                "name",
                "url",
                "last_update",
                "status",
            );
        $question_mark  = array();
        $condition_mark = array();
        foreach($data as $key=>$row) {
            if(!in_array($key, $fields_list)) continue;
            $question_mark[] = " set $key = ?";
            $pdoValue[] = $row;
        }
        foreach($field as $key=>$row) {
            if(!in_array($key, $fields_list)) continue;
            $condition_mark[] = " $key = ?";
            $pdoValue[] = $row;
        }
        if(empty($question_mark) || empty($condition_mark)) return;
        $sql = "update t_calendar_sync ".implode(" ,", $question_mark). " where ".implode(" and ", $condition_mark);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($pdoValue);
    }


    // t_sync_ical_trac 格式化后的日历相关
    public function get_ical_by_nid($nid, $status) {
        $sql = "select * from t_sync_ical_trac where nid = ? and status = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($nid, $status));
        return $stmt->fetchAll();
    }

    public function add_ical_trac($params) {
        $field_list = array(
                "nid",
                "start",
                "end",
                "summary",
                "description",
                "loction",
                "create_time",
                "status",
            );
        $insert_values = array();
        foreach($params as $key=>$value) {
            $question_marks[] = "(" . Util_Common::placeholders("?", count($value)) . ")";
            $insert_values = array_merge($insert_values, array_values($value));
        }

        if(empty($insert_values)) return;
        $sql = "insert into t_sync_ical_trac (".implode(", ", $field_list).") values ".implode(", ", $question_marks);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($insert_values);

    }

    public function disable_ical_trac($nid, $start, $end) {
        if(!$nid || !$start || !$end) return;
        $sql = "update t_sync_ical_trac set status = 0 where nid = ? and start = ? and end = ? and status =1";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($nid, $start, $end));

    }

    public function disable_all_ical_trac_bynid($nid) {
        if(!$nid) return;
        $sql = "update t_sync_ical_trac set status = 0 where nid = ? and status =1";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($nid));
    }


    // t_sync_ical_roomstatus_date 具体房态记录
    public function add_ical_date($params) {
        $field_list = array(
                'nid',
                'date',
                'create_time',
                'status',
            );
        $insert_values = array();
        foreach($params as $key=>$value) {
            $question_marks[] = "(" . Util_Common::placeholders("?", count($value)) . ")";
            $insert_values = array_merge($insert_values, array_values($value));
        }

        if(empty($insert_values)) return;
        $sql = "insert into t_sync_ical_roomstatus_date (".implode(", ", $field_list).") values ".implode(", ", $question_marks);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($insert_values);
    }

    public function disable_ical_date($nid, $date) {
        if(!$nid || !$date) return;
        $sql = "update t_sync_ical_roomstatus_date set status = 0 where nid = ? and date = ? and status =1";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($nid, $date));

    }

    public function disable_all_ical_date_bynid($nid) {
        if(!$nid) return;
        $sql = "update t_sync_ical_roomstatus_date set status = 0 where nid = ? and status =1";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($nid));
    }

    public function get_ical_date_by_nid($nid, $status=1, $date=null) {
        if(!$nid) return array();
        if(!date) $date = date("Y-m-d");
        $sql = "select * from t_sync_ical_roomstatus_date where nid = ? and status = ? and unix_timestamp(date) >= unix_timestamp(?) order by date asc";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($nid, $status, $date));
        return $stmt->fetchAll();
    }

}
