<?php
apf_require_class("APF_DB_Factory");

class Dao_Keyvalue
{

  private $pdo;
  private $slave_pdo;

  public function __construct()
  {
    $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
  }

  /**
   * @param $key
   * @param string $sort 取值 expire, create, 如果同一个key有多个value, 根据什么排序
   */
  public function popValue($key, $sort = "expire")
  {
    $columnData = array();
    $columnData['key'] = $key;
    $sql = "SELECT * FROM t_key_values WHERE status = 1 and `key` = :key";
    if ($sort == 'expire') {
      $sql .= " and expire_time > (unix_timestamp()+300) order by expire_time desc";
    } else {
      $sql .= " order by create_at desc";
    }
    $stmt = $this->slave_pdo->prepare($sql);
    $stmt->execute($columnData);
    $result = $stmt->fetchAll();
    if (empty($result)) return false;

    return $result[0];
  }

  public function popKeyvalues($type, $sort = "expire")
  {
    // TODO 获得一组同类型的Key-values
  }

  public function pushValue($key, $value, $expireIn = 0, $type = "")
  {
    if (empty($key)) return false;

    $columnData = array(
        'key' => $key,
        'value' => $value,
    );
    $columnSql = "set `key` = :key, `value` = :value";
    if ($expireIn > 0) {
      $columnData['expireIn'] = $expireIn;
      $columnSql .= ", expire_time = (unix_timestamp() + :expireIn)";
    } else {
      $columnSql .= ", expire_time = unix_timestamp()";
    }
    if (!empty($type)) {
      $columnData['type'] = $type;
      $columnSql .= ", `type` = :type";
    }

    $sql = "insert into t_key_values $columnSql, create_time = unix_timestamp()";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute($columnData);
  }
}