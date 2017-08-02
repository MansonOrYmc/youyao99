<?php
apf_require_class("APF_DB_Factory");

class Dao_Im_Messages {

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

  public function logMessage($data)
  {
    if (empty($data['provider']) || empty($data['msg_id']) ||
        empty($data['from']) || empty($data['to'])) {
      return array("status" => "error", "msg" => "missing required data elements!");
    }

    $stmt = $this->pdo->prepare("SELECT * FROM t_instant_messages WHERE provider = ? and msg_id = ?");
    $stmt->execute(array($data['provider'], $data['msg_id']));
    $row = $stmt->fetch(PDO::FETCH_OBJ);
    $stmt->closeCursor();
    if ($row) {
      return array("status" => "error", "msg" => "message already exists, msg_id=".$data['msg_id']);
    }

    $columnData = array(
        'provider' => $data['provider'],
        'msg_id' => $data['msg_id'],
        'from' => $data['from'],
        'to' => $data['to'],
    );
    $columnSql = "set provider = :provider, msg_id = :msg_id, `from` = :from, `to` = :to";
    if (!empty($data['msg_type'])) {
      $columnData['msg_type'] = $data['msg_type'];
      $columnSql .= ", msg_type = :msg_type";
    }
    if (!empty($data['thread_id'])) {
      $columnData['thread_id'] = $data['thread_id'];
      $columnSql .= ", thread_id = :thread_id";
    }
    if (!empty($data['subject'])) {
      $columnData['subject'] = $data['subject'];
      $columnSql .= ", subject = :subject";
    }
    if (!empty($data['body'])) {
      $columnData['body'] = $data['body'];
      $columnSql .= ", body = :body";
    }
    if (!empty($data['from_status'])) {
      $columnData['from_status'] = $data['from_status'];
      $columnSql .= ", from_status = :from_status";
    }
    if (!empty($data['to_status'])) {
      $columnData['to_status'] = $data['to_status'];
      $columnSql .= ", to_status = :to_status";
    }
    if (!empty($data['is_read'])) {
      $columnData['is_read'] = $data['is_read'];
      $columnSql .= ", is_read = :is_read";
    }
    if (!empty($data['sent_time'])) {
      $columnData['sent_time'] = $data['sent_time'];
      $columnSql .= ", sent_time = :sent_time";
    }

    try {
      $sql = "insert into t_instant_messages $columnSql, create_time=unix_timestamp()";
      $stmt = $this->pdo->prepare($sql);
      if ($stmt->execute($columnData)) {
        return array('status' => 'Ok', 'msg' => '');
      }
    }
    catch (PDOException $error) {
      return array('status' => 'Failed', 'msg' => $error->getMessage());
    }

    return array('status' => 'Failed', 'msg' => '数据库失败');
  }

  public function test() {
    $stmt = $this->pdo->prepare("SELECT msg_id FROM t_instant_messages");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
    foreach ($rows as $row) {
      $msgIds[] = $row->msg_id;
    }
    return $msgIds;
  }

  public function logEasemobMessages($messages)
  {
    if (empty($messages)) return array('status' => 'Ok', 'msg' => 'no messages to log');

    $msgIds = array();
    foreach ($messages as $msg) {
      $msgIds[] = "'".$msg['msg_id']."'";
    }
    $stmt = $this->pdo->prepare("SELECT msg_id FROM t_instant_messages WHERE provider = 'easemob' and msg_id in (".implode(',', $msgIds).")");
    $stmt->execute(array());
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
      $msgIds[] = $row['msg_id'];
    }
    // filter exists messages
    $insertMsgs = array();
    foreach ($messages as $msg) {
      if (in_array($msg['msg_id'], $msgIds)) continue;
      $insertMsgs[] = $msg;
    }
    if (empty($insertMsgs)) return array('status' => 'Ok', 'msg' => 'input messages already exists');

    $values = array();
    $columnData = array();
    $n = 0;
    $insertSql = "INSERT INTO t_instant_messages (provider, msg_id, msg_type, `from`, `to`, `subject`, body, sent_time, create_time) VALUES ";
    foreach ($insertMsgs as $msg) {
      $n += 1;
      $values[] = "(:provider{$n}, :msg_id{$n}, :msg_type{$n}, :from{$n}, :to{$n}, :subject{$n}, :body{$n}, :sent_time{$n}, unix_timestamp())";
      $columnData["provider{$n}"] = $msg['provider'];
      $columnData["msg_id{$n}"] = $msg['msg_id'];
      $columnData["msg_type{$n}"] = $msg['msg_type'];
      $columnData["from{$n}"] = $msg['from'];
      $columnData["to{$n}"] = $msg['to'];
      $columnData["subject{$n}"] = $msg['subject'];
      $columnData["body{$n}"] = $msg['body'];
      $columnData["sent_time{$n}"] = $msg['sent_time'];
    }
    $insertSql .= implode(',', $values);

    try {
      $stmt = $this->pdo->prepare($insertSql);
      if ($stmt->execute($columnData)) {
        return array('status' => 'Ok', 'msg' => 'inserted '.count($insertMsgs)." messages");
      }
    }
    catch (PDOException $error) {
      return array('status' => 'Failed', 'msg' => $error->getMessage());
    }

    return array('status' => 'Failed', 'msg' => '数据库失败');
  }

  public function getLatestMessages($provider, $fromTime)
  {
    $sql = "select * from t_instant_messages where provider = ? and sent_time > ? order by sent_time asc";
    $stmt = $this->slave_pdo->prepare($sql);
    $stmt->execute(array($provider, $fromTime));
    return $stmt->fetchAll(PDO::FETCH_OBJ);
  }

  public function getSyncLog($provider, $msg_id) {
    if (empty($msg_id) || empty($provider)) {
      return array();
    }

    $stmt = $this->pdo->prepare("SELECT * FROM t_im_sync_log WHERE provider = ? and msg_id = ?");
    $stmt->execute(array($provider, $msg_id));
    $row = $stmt->fetch(PDO::FETCH_OBJ);
    $stmt->closeCursor();
    return $row;
  }

  public function addSyncLog($provider, $msg_id) {
    if (empty($msg_id) || empty($provider)) {
      return false;
    }

    $stmt = $this->pdo->prepare("insert into t_im_sync_log set provider = ?, msg_id = ?, create_time = unix_timestamp()");
    return $stmt->execute(array($provider, $msg_id));
  }

}
