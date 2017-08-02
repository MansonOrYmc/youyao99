<?php

class Easemob_Api {
  const ORG_NAME = "youyao";
  #const APP_NAME = "zzkhx";
  #const CLIENT_ID = "YXA6ycLKkHPWEeW4XCVRbho4BQ";
  #const CLIENT_SECRET = "YXA6IW7oDFd5i57xI0_ehmj--Iq42nU";
  const TOKEN_KEY = "easemob_token";

  private $token = "";

  private function __construct() {}

  public static function create()
  {
    $obj = new Easemob_Api();
    if ($obj->init()) return $obj;

    return NULL;
  }

  public static function url()
  {
    return "https://a1.easemob.com/".self::ORG_NAME."/".APF::get_instance()->get_config('easemob_app_name');
  }

  /**
   * 获得指定时间到现在所有环信消息
   * @param $fromTime
   * @param string $cursor
   * @param int $limit
   * @return array|bool|mixed
   */
  public function getMessages($fromTime, $cursor = "", $limit = 20) {
    if (empty($fromTime) && empty($cursor)) return array();

    $data = array('limit' => $limit);
    if (!empty($fromTime)) {
      $data['ql'] = "select * where timestamp>{$fromTime}";
    }
    if (!empty($cursor)) {
      $data['cursor'] = $cursor;
    }
    $url = self::url()."/chatmessages";
    $result = Util_Curl::get($url, $data, array(
                            'Content-Type' => Util_Curl::JSON_CONTENT_TYPE,
                            'Authorization' => 'Bearer '.$this->token));
    if ($result['code'] == 200) {
      return json_decode($result['content']);
    } else {
      $this->refreshToken();
    }

    return array();
  }

  public function getUser($userName) {
    if (empty($userName)) {
      return array('status' => 'error', 'msg' => 'invalid parameter');
    }

    $url = self::url()."/users/{$userName}";
    $result = Util_Curl::get($url, array(), array(
        'Content-Type' => Util_Curl::JSON_CONTENT_TYPE,
        'Authorization' => 'Bearer '.$this->token));
    if ($result['code'] == 200) {
      $result = json_decode($result['content']);
      if (empty($result->error)) {
        return array('status' => 'ok', 'msg' => '', 'result' => $result);
      }
      return array('status' => 'failed', 'msg' => $result->error_description, 'result' => $result);
    } else {
      $this->refreshToken();
    }

    return array('status' => 'failed', 'msg' => 'curl error code:'.$result['code'], 'result' => $result['content']);
  }

  public function registerUser($userName, $nickName = "") {
    if (empty($userName)) {
      return array('status' => 'error', 'msg' => 'invalid parameter');
    }

    $url = self::url()."/users";
    $data = array(
        'username' => $userName,
        'password' => 'WIsX5ixlgUVN',
    );
    if (!empty($nickName)) {
      $data['nickname'] = $nickName;
    }
    $result = Util_Curl::post($url, $data, array(
                            'Content-Type' => Util_Curl::JSON_CONTENT_TYPE,
                            'Authorization' => 'Bearer '.$this->token));
    if ($result['code'] = 200) {
      $result = json_decode($result['content']);
      if (empty($result->error)) {
        return array('status' => 'ok', 'msg' => '', 'result' => $result);
      } else if (preg_match("/duplicate_unique/", $result->error)) {
        return array('status' => 'ok', 'msg' => 'user exist', 'result' => $result);
      }
      return array('status' => 'failed', 'msg' => $result->error_description, 'result' => $result);
    } else {
      $this->refreshToken();
    }

    return array('status' => 'failed', 'msg' => 'curl error code:'.$result['code'], 'result' => $result['content']);
  }

  public function sendTxtMsg($from, $to, $msg, $ext = array()) {
    if (empty($to)) {
      return array('status' => 'error', 'msg' => 'invalid parameter');
    }

    $url = self::url()."/messages";
    $data = array(
      'target_type' => 'users',
      'target' => array($to),
      'msg' => array('type' => 'txt', 'msg' => $msg),
    );
    if (!empty($from)) {
      $data['from'] = $from;
    }
    if (!empty($ext)) {
      $data['ext'] = $ext;
    }

    $result = Util_Curl::post($url, $data, array(
        'Content-Type' => Util_Curl::JSON_CONTENT_TYPE,
        'Authorization' => 'Bearer '.$this->token));
    if ($result['code'] = 200) {
      $result = json_decode($result['content']);
      if (empty($result->error)) {
        return array('status' => 'ok', 'msg' => '', 'result' => $result);
      }
      return array('status' => 'failed', 'msg' => $result->error_description, 'result' => $result);
    } else {
      $this->refreshToken();
    }

    return array('status' => 'failed', 'msg' => 'curl error code:'.$result['code'], 'result' => $result['content']);
  }

  public function activateUser($username) {
    $url = self::url()."/users/$username/activate";
    $result = Util_Curl::post($url, $data, array(
        'Content-Type' => Util_Curl::JSON_CONTENT_TYPE,
        'Authorization' => 'Bearer '.$this->token
    ));
    if ($result['code'] == 200) {
        return array('status' => 'ok', 'msg' => '', 'result' => $result);
    } else {
        $this->refreshToken();
        return array('status' => 'false', 'msg' => '', 'result' => $result);
    }
  }

  public function deactivateUser($username) {
    $url = self::url()."/users/$username/deactivate";
    $result = Util_Curl::post($url, $data, array(
        'Content-Type' => Util_Curl::JSON_CONTENT_TYPE,
        'Authorization' => 'Bearer '.$this->token
    ));
    if ($result['code'] == 200) {
        return array('status' => 'ok', 'msg' => '', 'result' => $result);
    } else {
        $this->refreshToken();
        return array('status' => 'false', 'msg' => '', 'result' => $result);
    }
  }

  public static function fetchTokenFromEasemob() {
    $url = self::url()."/token";
    $data = array(
        'grant_type' => 'client_credentials',
        'client_id' => APF::get_instance()->get_config('easemob_client_id'),
        'client_secret' => APF::get_instance()->get_config('easemob_client_secret'),
    );

    $result = Util_Curl::post($url, $data, array('Content-Type' => Util_Curl::JSON_CONTENT_TYPE));
    if ($result['code'] = 200) {
      return json_decode($result['content']);
    }

    return false;
  }

  public static function getTokenFromDB()
  {
    $kvStore = new Dao_Keyvalue();
    $v = $kvStore->popValue(self::TOKEN_KEY);
    if ($v) {
      return $v['value'];
    }

    return false;
  }

  public static function saveTokenToDB($token, $expireIn)
  {
    $kvStore = new Dao_Keyvalue();
    if ($expireIn > 7*24*60*60) {
      $expireIn = 120*60;
    }
    $kvStore->pushValue(self::TOKEN_KEY, $token, $expireIn);
  }

  public function refreshToken() {
    $result = self::fetchTokenFromEasemob();
    if ($result) {
      $this->token = $result->access_token;
      self::saveTokenToDB($result->access_token, $result->expires_in);
    }
  }

  private function init() {
    // init token
    $token = self::getTokenFromDB();
    if ($token) {
      $this->token = $token;
    } else {
      $this->refreshToken();
    }
    if (empty($this->token)) return false;

    return true;
  }
}
