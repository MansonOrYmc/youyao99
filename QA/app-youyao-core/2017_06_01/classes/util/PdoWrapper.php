<?php

class Util_PdoWrapper {
  public static function fetchOne() {

  }

  public static function fetchAll($sql, $params, $fechMode) {
    $sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute($params);
    $red = $sth->fetchAll($fetchMode);
    $sth->execute(array(':calories' => 175, ':colour' => 'yellow'));
    $yellow = $sth->fetchAll();
  }
}

