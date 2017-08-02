<?php

/**
 * 同步Drupal消息到环信
 */
class Privatemsg_Syncdpem {
  const JOB_NAME = 'im_sync_drupal_easemob';

  public function run() {
    echo date('Y-m-d H:i:s ')."start sync drupal to easemob ...".PHP_EOL;

    list($usec, $sec) = explode(" ", microtime());
    $startSec = ((float)$usec + (float)$sec);

    $daoPm = new Dao_Privatemsg_PrivateMsgInfo();
    $daoBjt = new Dao_Batch_JobTrack();
    $lastProcessTime = $daoBjt->getDataJobLastProcessTime(self::JOB_NAME, '2015-12-05 00:00');
    $messages = $daoPm->getLatestMessages('drupal', $lastProcessTime);
    if (empty($messages)) {
      echo "no messages to process".PHP_EOL;
      exit();
    }
//    echo "get ".count($messages)." messages".PHP_EOL;
//    $messages = array_slice($messages, -10, 10);

    $count = 0;
    $emApi = Easemob_Api::create();
    $daoEm = new Dao_Im_Easemob();
    $daoIm = new Dao_Im_Messages();
    $bll_homestay = new Bll_Homestay_StayInfo();
    echo "start process ".count($messages)." messages ...".PHP_EOL;
    $jobId = $daoBjt->createDataJobTrack(self::JOB_NAME, "start process ".count($messages)." messages ...");
    foreach($messages as $msg) {
      $mid = $bll_homestay->get_master_uid_by_buid($msg->to);
      $mid = $mid ? $mid : $msg->to;
      $emc = $daoEm->getEasemobClientByUId($mid);
      if (empty($emc)) {
        echo $mid." have no easemob client".PHP_EOL;
        continue;
      }

      $this->validateEasemobUser($emApi, $msg->from);
      if ($this->validateEasemobUser($emApi, $mid) && !$daoIm->getSyncLog('drupal', $msg->msg_id)) {
        $count += 1;
        if($msg->format == "json") {
            $ret = $emApi->sendTxtMsg($msg->from, $mid, $msg->subject, array('provider' => 'drupal', 'timestamp' => $msg->sent_time, 'dst_branch_id' => $msg->to, 'zzk_type' => 'consult_detail', 'zzk_data' => json_decode($msg->body)));
        } else {
            $ret = $emApi->sendTxtMsg($msg->from, $mid, $msg->body, array('provider' => 'drupal', 'timestamp' => $msg->sent_time, 'dst_branch_id' => $msg->to));
        }
        if ($ret['status'] == 'ok') {
          $daoIm->addSyncLog('drupal', $msg->msg_id);
          echo 'SUCCESS! send easemob message '.$msg->msg_id.' from '.$msg->from.' to '.$mid.PHP_EOL;
        } else {
          echo 'FAILED! send easemob message '.$msg->msg_id.' from '.$msg->from.' to '.$mid.' msg '.$ret['msg'].PHP_EOL;
          var_dump($ret['result']);
        }
      }
    }
    $lastProcessTime = $messages[count($messages)-1]->sent_time;

    list($usec, $sec) = explode(" ", microtime());
    $endSec = ((float)$usec + (float)$sec);
    echo "elapsed ".($endSec - $startSec)." sec".PHP_EOL;
    $daoBjt->updateDataJobTrack($jobId, round($endSec - $startSec), 1, date('Y-m-d H:i:s', $lastProcessTime), "processed $count messages");
  }

  private function validateEasemobUser($emApi, $username) {
    if (empty($username)) return true;

    $ret = $emApi->getUser($username);
    if ($ret['status'] == 'ok') return true;

    $ret = $emApi->registerUser($username);
    return $ret['status'] == 'ok';
  }
}
