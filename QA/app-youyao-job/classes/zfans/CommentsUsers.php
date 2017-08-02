<?php

class Zfans_CommentsUsers {

  public function run()
  {
    $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
    $sql = "select uid, count(*) cnt, group_concat(content separator '|') comments from t_comment_info where uid > 0 and status > 0 and whole_exp > 4 and is_recommend > 0 and (pid is null) and create_time >= '2015-03-01' group by 1 having cnt >= 1 order by cnt desc;";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute();
    $uids = array();
    $commenters = array();
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
      $commenters[$row->uid] = $row;
      $uids[] = $row->uid;
    }

    $sql = "select guest_uid uid, group_concat(distinct guest_name separator '|') names, group_concat(distinct guest_wechat separator '|') wechats, group_concat(distinct guest_mail separator '|') emails, group_concat(distinct guest_telnum separator '|') phones from t_homestay_booking where guest_uid in(".implode(',', $uids).") group by 1";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute();
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
      $cmt = $commenters[$row->uid];
      $cmt->names = $row->names;
      $cmt->wechats = $row->wechats;
      $cmt->emails = $row->emails;
      $cmt->phones = $row->phones;
    }

    $fpw = fopen(dirname(__FILE__).'/commenters.csv', 'w');
    $lineStr = "uid, 名字, 点评数, 微信, 邮箱, 电话, 点评内容\n";
    fwrite($fpw, $lineStr);
    echo $lineStr;
    foreach ($commenters as $key => $cmt) {
      $lineItems = array();
      $lineItems[] = $cmt->uid;
      $lineItems[] = $cmt->names;
      $lineItems[] = $cmt->cnt;
      $lineItems[] = $cmt->wechats;
      $lineItems[] = $cmt->emails;
      $lineItems[] = $cmt->phones;
      $comments = str_replace(PHP_EOL, ';', $cmt->comments);
      $comments = str_replace(array("\r\n", "\r", "\n"), ";", $cmt->comments);
      $lineItems[] = strtr($comments, array(',' => ';'));

      $lineStr = implode(',', $lineItems)."\n";
      fwrite($fpw, $lineStr);
      echo $lineStr;
    }
    fclose($fpw);
  }
}
