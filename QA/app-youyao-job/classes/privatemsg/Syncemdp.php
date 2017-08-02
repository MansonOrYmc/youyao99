<?php

/**
 * 同步环信消息到drupal
 */
class Privatemsg_Syncemdp {
  const JOB_NAME = 'im_sync_easemob_drupal';

  public function run() {
    echo date('Y-m-d H:i:s ')."start sync easemob to drupal ...".PHP_EOL;

    list($usec, $sec) = explode(" ", microtime());
    $startSec = ((float)$usec + (float)$sec);

    $daoIm = new Dao_Im_Messages();
    $daoBjt = new Dao_Batch_JobTrack();
    $lastProcessTime = $daoBjt->getDataJobLastProcessTime(self::JOB_NAME, '2015-12-05 16:56:18');
//    $lastProcessTime = strtotime('2015-12-05 16:56:18');
    $messages = $daoIm->getLatestMessages('easemob', $lastProcessTime);
    if (empty($messages)) {
      echo "no messages to process".PHP_EOL;
      exit();
    }
//    echo "get ".count($messages)." messages".PHP_EOL;
//    $messages = array_slice($messages, -10, 10);

    $count = 0;
    $daoEm = new Dao_Im_Easemob();
    $daoPm = new Dao_Privatemsg_PrivateMsgInfo();

    echo "start process ".count($messages)." messages ...".PHP_EOL;
    $jobId = $daoBjt->createDataJobTrack(self::JOB_NAME, "start process ".count($messages)." messages ...");
    foreach($messages as $msg) {
      if ($daoIm->getSyncLog('easemob', $msg->msg_id)) continue;

      $count += 1;
      $emc = $daoEm->getEasemobClientByUId($msg->to);
      #if (empty($emc)) { // 没有环信客户端，通过mapi发送并推送drupal私信
      if(True) {
        echo $msg->to." has no easemob client, send to drupal".PHP_EOL;
        $ret = $this->sendDrupalMsg($msg);
        if ($ret['status'] == 'ok') {
          $daoIm->addSyncLog('easemob', $msg->msg_id);
          echo 'SUCCESS! send drupal message '.$msg->msg_id.' from '.$msg->from.' to '.$msg->to.PHP_EOL;
        } else {
          echo 'FAILED! send drupal message '.$msg->msg_id.' from '.$msg->from.' to '.$msg->to.' msg:'.$ret['msg'].PHP_EOL;
          var_dump($ret['result']);
        }
      } else { // 有环信客户端，仅复制到drupal私信表
        echo $msg->to." has easemob client, copy to drupal".PHP_EOL;
        try {
          $daoPm->insertEasemobMsg($msg);
          $daoIm->addSyncLog('easemob', $msg->msg_id);
          echo 'SUCCESS! copy drupal message '.$msg->msg_id.' from '.$msg->from.' to '.$msg->to.PHP_EOL;
        } catch (Exception $e) {
          echo 'FAILED! copy drupal message '.$msg->msg_id.' from '.$msg->from.' to '.$msg->to.PHP_EOL;
        }
      }
    }
    $lastProcessTime = $messages[count($messages)-1]->sent_time;

    list($usec, $sec) = explode(" ", microtime());
    $endSec = ((float)$usec + (float)$sec);
    echo "elapsed ".($endSec - $startSec)." sec".PHP_EOL;
    $daoBjt->updateDataJobTrack($jobId, round($endSec - $startSec), 1, date('Y-m-d H:i:s', $lastProcessTime), "processed $count messages");
  }

  private function sendDrupalMsg($msg, $isNew = 1, $provider = 'easemob') {
    $result = Util_Curl::get("http://www".APF::get_instance()->get_config("cookie_domain")."/mapi.php", array(
      'fun' => 'sendmessage',
      'author' => $msg->from,
      'recipient' => $msg->to,
      'text' => $msg->body,
      'thread' => $msg->thread_id,
      'is_new' => $isNew,
      'provider' => $provider,
      'sent_time' => $msg->sent_time,
    ), array('Content-Type' => Util_Curl::JSON_CONTENT_TYPE));

    if ($result['code'] = 200) {
      $result = json_decode($result['content']);
      if ($result->result == 1) {
        return array('status' => 'ok', 'msg' => '', 'result' => $result);
      }
      return array('status' => 'failed', 'msg' => 'mapi exec failed', 'result' => $result);
    }

    return array('status' => 'failed', 'msg' => 'curl error code:'.$result['code'], 'result' => $result['content']);
  }
}
