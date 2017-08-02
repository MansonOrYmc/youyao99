<?php
apf_require_class("APF_DB_Factory");

class Dao_Im_Easemob {

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

  public function logEasemobClient($data) {
    if (empty($data['uid']) || empty($data['email'])) {
      return array("status" => "error", "msg" => "uid and email must not null!");
    }

    // check exist of duplicated client
    $selectColum = array('uid' => $data['uid']);
    $whereSql = " where uid = :uid";
    if (!empty($data['devid'])) {
      $whereSql .= " and device_id = :device_id";
      $selectColum['device_id'] = $data['devid'];
    } else if (!empty($data['bdcid'])) {
      $whereSql .= " and baidu_channel_id = :baidu_channel_id";
      $selectColum['baidu_channel_id'] = $data['bdcid'];
    } else if (!empty($data['jgpid'])) {
      $whereSql .= " and jgpush_id = :jgpush_id";
      $selectColum['jgpush_id'] = $data['jgpid'];
    }
    $stmt = $this->pdo->prepare("select * from t_easemob_clients".$whereSql);
    $stmt->execute($selectColum);
    $row = $stmt->fetch(PDO::FETCH_OBJ);
    $stmt->closeCursor();

    $columnData = array(
      'uid' => $data['uid'],
      'email' => $data['email'],
      'em_uid' => empty($data['emuid']) ? $data['uid'] : $data['emuid'],
    );
    $columnSql = "set uid = :uid, email = :email, em_uid = :em_uid";
    if (!empty($data['devid'])) {
      $columnData['device_id'] = $data['devid'];
      $columnSql .= ", device_id = :device_id";
    }
    if (!empty($data['bduid'])) {
      $columnData['baidu_user_id'] = $data['bduid'];
      $columnSql .= ", baidu_user_id = :baidu_user_id";
    }
    if (!empty($data['bdcid'])) {
      $columnData['baidu_channel_id'] = $data['bdcid'];
      $columnSql .= ", baidu_channel_id = :baidu_channel_id";
    }
    if (!empty($data['jgpid'])) {
      $columnData['jgpush_id'] = $data['jgpid'];
      $columnSql .= ", jgpush_id = :jgpush_id";
    }
    if (!empty($data['os'])) {
      $columnData['os'] = $data['os'];
      $columnSql .= ", os = :os";
    }

    try {
      if ($row) {
        $sql = "update t_easemob_clients $columnSql, create_time=unix_timestamp()".$whereSql;
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($columnData)) {
          return array('status' => 'Ok', 'msg' => 'updated');
        }
      } else {
        $sql = "insert into t_easemob_clients $columnSql, create_time=unix_timestamp()";
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($columnData)) {
          return array('status' => 'Ok', 'msg' => 'inserted');
        }
      }
    }
    catch (PDOException $error) {
      return array('status' => 'Failed', 'msg' => $error->getMessage());
    }

    return array('status' => 'Failed', 'msg' => '数据库失败');
  }

  public function getEasemobClientByUId($uid) {
    if (empty($uid)) return false;

    $sql = "select * from t_easemob_clients where uid = ? and `status` = 1";
    $stmt = $this->slave_pdo->prepare($sql);
    $stmt->execute(array($uid));
    return $stmt->fetchAll(PDO::FETCH_OBJ);
  }
}
