<?php

/**
 * Class Privatemsg_SyncEasemob
 * 同步环信的消息
 */
class Privatemsg_SyncEasemob {
  const JOB_NAME = 'im_sync_easemob';

  public function run() {
    echo date('Y-m-d H:i:s ')."start sync easemob messages ...".PHP_EOL;

    list($usec, $sec) = explode(" ", microtime());
    $startSec = ((float)$usec + (float)$sec);

    $daoBjt = new Dao_Batch_JobTrack();
    $lastMilisecond = $daoBjt->getLastMiliseconds(self::JOB_NAME);
//    $lastProcessTime = strtotime('2015-12-03 00:00');
    $emApi = Easemob_Api::create();
    $result = $emApi->getMessages($lastMilisecond);
    if (empty($result) || empty($result->entities)) {
      echo "no new messages".PHP_EOL;
      return;
    }

    $count = 0;
    $daoIm = new Dao_Im_Messages();
    $messages = array();
    $jobId = $daoBjt->createDataJobTrack(self::JOB_NAME, date('Y-m-d H:i:s ')."start process ...");

    do {
      echo "----------- get ".count($result->entities)." messages".PHP_EOL;
      foreach($result->entities as $msg) {
        $count += 1;
//        echo "#$count ".date('Y-m-d H:i:s', $msg->timestamp/1000)." {$msg->msg_id} ".$msg->from." say: ".$msg->payload->bodies[0]->msg.PHP_EOL;
        echo "#$count ".date('Y-m-d H:i:s', $msg->timestamp/1000)." {$msg->timestamp} : {$msg->msg_id} ".$msg->from." say: ".$msg->payload->bodies[0]->msg.PHP_EOL;
        if ($msg->payload->bodies[0]->type == 'txt') {
          $subject = substr($msg->payload->bodies[0]->msg, 20);
          $body = $msg->payload->bodies[0]->msg;
        } else if ($msg->payload->bodies[0]->type == 'img') {
          $subject = "image message";
          $body = $msg->payload->bodies[0]->url;
        } else { //other type ignore: loc, audio
          continue;
        }
        $messages[] = array(
            'provider' => empty($msg->payload->ext->provider) ? 'easemob' : $msg->payload->ext->provider,
            'msg_id' => $msg->msg_id,
            'msg_type' => $msg->payload->bodies[0]->type,
            'from' => empty($msg->payload->ext->src_branch_id) ? $msg->from : $msg->payload->ext->src_branch_id,
            'to' => $msg->to,
            'subject' => $subject,
            'body' => $body,
            'sent_time' => round($msg->timestamp/1000),
        );
      }
      $lastMilisecond = $result->entities[count($result->entities)-1]->timestamp;
      $cursor = $result->cursor;

      if (count($messages) > 100) {
        $dbResult = $daoIm->logEasemobMessages($messages);
        echo $dbResult['status']." ".$dbResult['msg'].PHP_EOL;
        unset($messages);
        $messages = array();
      }

      if (empty($cursor)) break;

      $result = $emApi->getMessages($lastMilisecond, $cursor);
    } while(!empty($result) && !empty($result->entities));

    if (!empty($messages)) {
      $dbResult = $daoIm->logEasemobMessages($messages);
      echo $dbResult['status']." ".$dbResult['msg'].PHP_EOL;
    }

    list($usec, $sec) = explode(" ", microtime());
    $endSec = ((float)$usec + (float)$sec);
    echo "--- latest msg timestamp $lastMilisecond".PHP_EOL;
    echo "elapsed ".($endSec - $startSec)." sec".PHP_EOL;
    $daoBjt->updateDataJobTrack($jobId, round($endSec - $startSec), 1, date('Y-m-d H:i:s', round($lastMilisecond/1000)), "processed $count messages", $lastMilisecond);
  }

}
