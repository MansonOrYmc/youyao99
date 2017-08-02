<?php
/**
 * - _session_open()
 * - _session_close()
 * - _session_read()
 * - _session_write()
 * - _session_destroy()
 * - _session_garbage_collection()
 */

function session_initialize(){

    if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) {
        Util_Common::$is_https = TRUE;
        ini_set('session.cookie_secure', TRUE);
    }
    Util_Signin::$user = array('uid'=>1);
    $prefix = ini_get('session.cookie_secure') ? 'SSESS' : 'SESS';
    $session_name = APF::get_instance()->get_config('cookie_domain');

    // 和drupal保持一致
    ini_set('session.cache_limiter', 'none');
    ini_set('session.cookie_domain', $session_name);
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_lifetime', 2000000);
    ini_set('session.gc_divisor', 100);
    ini_set('session.gc_maxlifetime', 200000);
    ini_set('session.gc_probability', 1);
    ini_set('session.use_cookies', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    
    session_name($prefix . substr(hash('sha256', $session_name), 0, 32));

    session_set_save_handler('_session_open', '_session_close', '_session_read', '_session_write', '_session_destroy', '_session_garbage_collection');
    register_shutdown_function('session_write_close');

    $cookie_name = Util_Common::$is_https ? substr(session_name(), 1) : session_name();
    if($_COOKIE[$cookie_name]){
        session_start();
    } else {
        session_id(Util_Common::hash_base64(uniqid(mt_rand(), TRUE)));
        $params = session_get_cookie_params();
        $expire = $params['lifetime'] ? REQUEST_TIME + $params['lifetime'] : 0;
        setcookie($cookie_name, session_id(), $expire, $params['path'], $params['domain'], FALSE, $params['httponly']);
        $_COOKIE[$cookie_name] = session_id();
        session_start();
    }

}

function _session_open() {
    return TRUE;
}

function _session_close() {
    return TRUE;
}

function _session_read($sid) {

    // Write and Close handlers are called after destructing objects
    // since PHP 5.0.5.
    // Thus destructors can use sessions but session handler can't use objects.
    // So we are moving session closure before destructing objects.
    register_shutdown_function('session_write_close');
    
    // Handle the case of first time visitors and clients that don't store
    // cookies (eg. web crawlers).
    $insecure_session_name = substr(session_name(), 1);
    if (!isset($_COOKIE[session_name()]) && !isset($_COOKIE[$insecure_session_name])) {
//        return '';
    }
    $user = Util_Signin::$user;

    // Otherwise, if the session is still active, we have a record of the
    // client's session in the database. If it's HTTPS then we are either have
    // a HTTPS session or we are about to log in so we check the sessions table
    // for an anonymous session with the non-HTTPS-only cookie.
    if (Util_Common::$is_https) {
        $condition = "s.ssid = :ssid";
        $pdo_val = array(':ssid' => $sid);
        if (!$user) {
            if (isset($_COOKIE[$insecure_session_name])) {
                $condition = "s.sid = :sid AND s.uid = 0";
                $pdo_val = array(':sid' => $sid);
            }
        }
    } else {
        $condition = "s.sid = :sid";
        $pdo_val = array(':sid' => $sid);
    }
    $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
    $sql = "SELECT u.* FROM t_users u  WHERE u.uid=1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($pdo_val);
    $user = $stmt->fetchObject();

    // We found the client's session record and they are an authenticated,
    // active user.
    if ($user && $user->uid > 0 && $user->status == 1) {
        // This is done to unserialize the data member of $user.
        $user->data = unserialize($user->data);
        
        // Add roles element to $user.
        $user->roles = array();
        $user->roles[1] = 'authenticated user';
        $sql2 = "SELECT r.rid, r.name FROM drupal_role r INNER JOIN drupal_users_roles ur ON ur.rid = r.rid WHERE ur.uid = :uid";
        $pdo_val2 = array(':uid' => $user->uid);
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute($pdo_val2);
        $stmt2->setFetchMode(PDO::FETCH_NUM);
        $roles = array();
        foreach ($stmt2 as $record) {
            $roles[$record[0]] = $record[1];
        }
        $user->roles += $roles;
    } elseif ($user) {
        // The user is anonymous or blocked. Only preserve two fields from the
        // {sessions} table.
        $account = Util_Signin::anonymous_user();
        $account->session = $user->session;
        $account->timestamp = $user->timestamp;
        $user = $account;
    }
    else {
        // The session has expired.
        $user = Util_Signin::anonymous_user();
        $user->session = '';
    }
    
    // Store the session that was read for comparison in _drupal_session_write().
    $last_read = array(
        'sid' => $sid,
        'value' => $user->session,
    );
    Util_Common::$session_last_read = $last_read;
    Util_Signin::$user = $user;
    
    return $user->session;
}

function _session_write($sid, $value) {

    // The exception handler is not active at this point, so we need to do it
    // manually.
    $user = Util_Signin::$user;
    try {

        // Check whether $_SESSION has been changed in this request.
        //    $last_read = &drupal_static('drupal_session_last_read');
        $last_read = Util_Common::$session_last_read;
        $is_changed = !isset($last_read) || $last_read['sid'] != $sid || $last_read['value'] !== $value;
        
        // For performance reasons, do not update the sessions table, unless
        // $_SESSION has changed or more than 180 has passed since the last update.
        $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        if ($is_changed || !isset($user->timestamp) || time() - $user->timestamp > 180) {
            // Either ssid or sid or both will be added from $key below.
            if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {     
                $ip = $_SERVER['REMOTE_ADDR'];
            } 
            $fields = array(
                'uid' => $user->uid,
                'cache' => isset($user->cache) ? $user->cache : 0,
                'hostname' => $ip,
                'session' => $value,
                'timestamp' => time(),
            );
            
            // Use the session ID as 'sid' and an empty string as 'ssid' by default.
            // _drupal_session_read() does not allow empty strings so that's a safe
            // default.
            $key = array('sid' => $sid, 'ssid' => '');
            // On HTTPS connections, use the session ID as both 'sid' and 'ssid'.
            if (Util_Common::$is_https) {
                $key['ssid'] = $sid;
                // The "secure pages" setting allows a site to simultaneously use both
                // secure and insecure session cookies. If enabled and both cookies are
                // presented then use both keys.
                $insecure_session_name = substr(session_name(), 1);
                if (isset($_COOKIE[$insecure_session_name])) {
                    $key['sid'] = $_COOKIE[$insecure_session_name];
                }
            } else {
                unset($key['ssid']);
            }
            
            $pdo_val1 = array();
            $condition1 = "";
            foreach($key as $k=>$v) {
                $condition1 .= "`$k` = ?";
                $pdo_val1[] = $v; 
            }
            $sql1 = "select * from drupal_sessions where $condition1";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute($pdo_val1);
            $result = $stmt1->fetch();
            if(empty($result)) {
                $_fields = array_merge(array_keys($fields), array_keys($key));
                $placeholder = Util_Common::placeholders("?", count($_fields));
                $pdo_val2 = array_merge(array_values($fields),$pdo_val1);
                $sql2 = "insert drupal_sessions (".implode(',', $_fields).") values ($placeholder) ";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->execute($pdo_val2);
            } else {
                $_fields = '';
                $_condition = '';
                foreach($fields as $k=>$v) {
                    $_fields[] = "`$k` = ?";
                    $pdo_val3[] = $v;
                }
                foreach($key as $k=>$v) {
                    $_condition[] = "`$k` = ?";
                    $pdo_val3[] = $v;
                }
                $sql3 = "update drupal_sessions set ".implode(", ", $_fields)." where ".implode("and ", $_condition). ";";
                $stmt3 = $pdo->prepare($sql3);
                $stmt3->execute($pdo_val3);
            }
        }
        
        // Likewise, do not update access time more than once per 180 seconds.
        if ($user->uid && time() - $user->access > 180) {
            $sql4 = "update drupal_users set `access` = ? where `uid` = ? ;";
            $pdo_val4 = array(time(), $user->uid);
            $stmt4 = $pdo->prepare($sql4);
            $stmt4->execute($pdo_val4);
        }
        
        return TRUE;
    } catch (Exception $exception) {
        Util_Debug::zzk_debug("session_write_bug", print_r($exception->getMessage(), true));

        return FALSE;
    }
}

function _session_destroy($sid) {

    // Delete session data.
    $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
    if(Util_Signin::$is_https) {
        $k = "ssid";
    } else {
        $k = "sid";
    }
    $sql = "delete from drupal_sessions where `$k` = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($sid));
    
    // Reset $_SESSION and $user to prevent a new session from being started
    // in drupal_session_commit().
    $_SESSION = array();
    Util_Signin::$user = Util_Signin::anonymous_user();
    
    // Unset the session cookies.
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    if (Util_Common::$is_https) {
        setcookie(substr(session_name(), 1), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    return TRUE;
}

function _session_garbage_collection($lifetime) {
    return TRUE;
    // Be sure to adjust 'php_value session.gc_maxlifetime' to a large enough
    // value. For example, if you want user sessions to stay in your database
    // for three weeks before deleting them, you need to set gc_maxlifetime
    // to '1814400'. At that value, only after a user doesn't log in after
    // three weeks (1814400 seconds) will his/her session be removed.
    $pdo = APF_DB_Factory::get_instance()->get_pdo("master");
    $sql = "delete from drupal_sessions where `timestamp` < ? ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(time() - $lifetime));

    return TRUE;
}
