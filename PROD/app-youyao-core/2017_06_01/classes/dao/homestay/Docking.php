<?php
apf_require_class("APF_DB_Factory");

class Dao_HomeStay_Docking {

    private $pdo;
    private $slave_pdo;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
    }

    public function get_row_by_param($param) {

        if(empty($param)) return;
        foreach($param as $k => $v) {
            $condition[] = "`$k` = ?";
            $pdoVal[] = $v;
        }
        $sql = "select * from t_homestay_docking where ".implode(" and ", $condition);
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute($pdoVal);
        return $stmt->fetchAll();
    }

    public function get_all_active_list() {
        $sql = "select * from t_homestay_docking where status = 1";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function get_homestay_docking_by_channel($channel) {
        $sql = "select uid from t_homestay_docking where channel = ? and status = 1";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($channel));
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function get_room_list_by_channel($channel) {
        $sql = "select group_concat(rids separator \",\") from t_homestay_docking where channel = ? and status = 1 ";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array($channel));
        return $stmt->fetch();
    }

    public function update_rows_by_uid($uid, $params) {
        $fields = "";
        $pdo_value = array();
        foreach($params as $key=>$row) {
            $fields .= $fields ? $fields."," : "";
            $fields .= "set $key = ?";
            $pdo_value[] = $row;
        }
        $pdo_value[] = $uid;
        $sql = "update t_homestay_docking $fields where uid = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($pdo_value);
    }

    public function add_docking_record($uid, $rids, $channel, $admin_uid) {
        $sql = "insert into t_homestay_docking (`uid`, `rids`, `channel`, `operator_uid`, `status`, `create_time`) values (?, ?, ?, ?, '1', '".time()."') on duplicate key update status=status";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($uid, $rids, $channel, $admin_uid));
        return true;
    }

    public function add_aliholiday_mapping($room_id, $homestay_uid, $ali_itemid, $status=1) {
        $sql = "insert into t_aliholiday_out_itemid (`room_id`, `homestay_uid`, `ali_itemid`, `status`, `create_time`) values (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($room_id, $homestay_uid, $ali_itemid, $status, time()));
    }

    public function get_aliholiday_itemid_by_roomid($nid) {
        $sql = "select * from t_aliholiday_out_itemid where room_id = ? and status = 1 ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($nid));
        return $stmt->fetch();
    }

}
