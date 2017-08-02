<?php

class Zfans_CustomerStat {

  public function run()
  {
    $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
    $sql = "select b.guest_uid, b.guest_mail, b.guest_telnum, b.status, a.order_succ_time, b.create_time, b.campaign_code, a.share_method, b.total_price from t_zfans_balance_log a inner join t_homestay_booking b on a.oid = b.id where a.status = 1 and a.type = 1 and a.order_succ_time >= unix_timestamp('2015-09-01') and a.order_succ_time < unix_timestamp('2015-10-01') order by b.create_time asc";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute();

    $guestMails = array(array(), array(), array());
    $campMails = array(array(), array(), array());
    $guestTotals = array(0, 0, 0);    
    $shareModes = array(array('l' => 0, 'm' => 0, 'lm' => 0), array('l' => 0, 'm' => 0, 'lm' => 0), array('l' => 0, 'm' => 0, 'lm' => 0));
    $daysCount = array();
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
      if (in_array($row->guest_mail, $guestMails[0]) ||
        in_array($row->guest_mail, $guestMails[1]) ||
        in_array($row->guest_mail, $guestMails[2])) {
        continue;
      }
      List($guestState, $days) = $this->findGuestState($row->guest_mail, $row->create_time);
      if (isset($daysCount[$days])) {
        $daysCount[$days] += 1;
      } else {
        $daysCount[$days] = 1;
      }
      $guestMails[$guestState][] = $row->guest_mail;
      if (strlen($row->campaign_code) > 0) {
        $campMails[$guestState][] = $row->guest_mail;
      }
      switch ($row->share_method) {
        case 1:
          $shareModes[$guestState]['l'] += 1;
          break;

        case 2:
          $shareModes[$guestState]['m'] += 1;
          break;

        case 3:
          $shareModes[$guestState]['lm'] += 1;
          break;

        default:
          # code...
          break;
      }
      $guestTotals[$guestState] += $row->total_price;
    }
    echo "类型\t 交易额\t 总数 \t campcode \t 链 \t 码 \t 链码\n";
    foreach ($guestMails as $key => $gms) {
      echo "$key \t".$guestTotals[$key]."\t ".count($gms)."\t ".count($campMails[$key])."\t ".$shareModes[$key]['l']."\t ".$shareModes[$key]['m']."\t ".$shareModes[$key]['lm']."\n";
    }
    foreach ($daysCount as $key => $value) {
      echo "$key \t $value \n";
    }
  }

  private function findGuestState($guest_mail, $create_time)
  {
    $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
    $sql = "select * from t_homestay_booking where guest_mail = :guest_mail and create_time < :create_time order by create_time asc";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array('guest_mail' => $guest_mail, 'create_time' => $create_time));
    $rows = $sth->fetchAll(PDO::FETCH_OBJ);
    if (empty($rows)) return array(0, 0);

    $days = round(($create_time - $rows[0]->create_time) / (24*60*60) + 0.5);
    $orderCount = 0;
    $succCount = 0;
    foreach ($rows as $row) {
      if ($this->isZFansOrder($row)) {
        break;
      }
      if ($row->status == 2 || $row->status == 6) {
        $succCount += 1;
        break;
      }
      $orderCount += 1;
    }

    $guestState = $succCount > 0 ? 2 : ($orderCount > 0 ? 1 : 0);
    return array($guestState, $guestState == 0 ? 0 : $days);
  }

  private function isZFansOrder($order) {
    if ($order->zfansref > 0) {
      echo "is zfans order, zfansref: $order->zfansref \n";
      return true;
    }
    if (empty($order->coupon)) {
      return false;
    }

    return $this->isZFansCoupon($order->coupon);
  }

  private function isZFansCoupon($coupon) {
    $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
    $sql = "select * from t_zfans_coupons where coupon = :coupon";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array('coupon' => $coupon));
    $rows = $sth->fetchAll(PDO::FETCH_OBJ);

    if (empty($rows)) {
      echo "not zfans coupon $coupon \n";
      return false;
    }
    echo "is zfans order, coupon $coupon \n";

    return true;
  }
}
