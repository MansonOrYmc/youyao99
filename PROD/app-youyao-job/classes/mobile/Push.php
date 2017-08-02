<?php
require_once 'vendor/autoload.php';
use JPush\Model as M;
use JPush\JPushClient;
use JPush\Exception\APIConnectionException;
use JPush\Exception\APIRequestException;

class mobile_Push {

	public function run() {

		
		$app_key = "288229ef4cc6120b47411e07";
		$master_secret = "a58fdc76262084349b6cfed1";
		
		$bll = new Bll_Push_PushInfo();
		$queue = $bll->get_push_queue('jg');
		$br = "\n";
		$client = new JPushClient($app_key, $master_secret);
		
		foreach($queue as $row){
			try {
				$success_id[] = $row['message_id'];
				if($pushid == $row['register_id'] &&
                   $time > ($row['time_queued']-1) &&
                   $time < ($row['time_queued']+1) &&
                   $payload == $row['payload']
                ) continue;
				$time = $row['time_queued'];
				$payload = $row['payload'];
				$pushid = $row['register_id'];
				$result = $client->push()
				    ->setPlatform(M\all)
//				    ->setAudience(M\audience(M\registration_id(array('0716dc61fb2'))))
				    ->setAudience(M\audience(M\registration_id(array($row['register_id']))))
//				    ->setNotification(M\notification("测试推送"))
				    ->setNotification(M\notification($row['payload']))
//					->setMessage(M\message('Message Content', 'Message Title', 'Message Type', array("key1"=>"value1", "key2"=>"value2")))
				    ->send();
				echo 'Push Success.' . $br;
				echo 'sendno : ' . $result->sendno . $br;
				echo 'msg_id : ' .$result->msg_id . $br;
				echo 'Response JSON : ' . $result->json . $br;
			} catch (APIRequestException $e) {
			    echo 'Push Fail.' . $br;
			    echo 'Http Code : ' . $e->httpCode . $br;
			    echo 'code : ' . $e->code . $br;
			    echo 'Error Message : ' . $e->message . $br;
			    echo 'Response JSON : ' . $e->json . $br;
			    echo 'rateLimitLimit : ' . $e->rateLimitLimit . $br;
			    echo 'rateLimitRemaining : ' . $e->rateLimitRemaining . $br;
			    echo 'rateLimitReset : ' . $e->rateLimitReset . $br;
			} catch (APIConnectionException $e) {
			    echo 'Push Fail: ' . $br;
			    echo 'Error Message: ' . $e->getMessage() . $br;
			    //response timeout means your request has probably be received by JPUsh Server,please check that whether need to be pushed again.
			    echo 'IsResponseTimeout: ' . $e->isResponseTimeout . $br;
			}
		}
		
		$bll->update_jgpush($success_id);
	}

}

