<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/11/11
 * Time: 下午5:35
 */
class So_NiceEncryption{
    // 公钥
    protected static $key = 'vruan_is_nice';
    private static function keyED($txt,$encrypt_key){
        $encrypt_key = md5($encrypt_key);
        $ctr=0;
        $tmp = '';
        for ($i=0;$i<strlen($txt);$i++){
            if ($ctr==strlen($encrypt_key)){
                $ctr=0;
            }
            $tmp.= substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1);
            $ctr++;
        }
        return $tmp;
    }

    public static function encrypt($txt,$key=''){
        if(empty($key)){
            $key=self::$key;
        }
        srand((double)microtime()*1000000);
        $encrypt_key = md5(rand(0,32000));
        $ctr=0;
        $tmp = '';
        for ($i=0;$i<strlen($txt);$i++)  {
            if ($ctr==strlen($encrypt_key)){
                $ctr=0;
            }
            $tmp.= substr($encrypt_key,$ctr,1).(substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1));
            $ctr++;
        }
        return self::keyED($tmp,$key);
    }

    public static function decrypt($txt,$key=''){
        if(empty($key)){
            $key=self::$key;
        }

        $txt = self::keyED($txt,$key);
        $tmp = '';
        for ($i=0;$i<strlen($txt);$i++){
            $md5 = substr($txt,$i,1);
            $i++;
            $tmp.= (substr($txt,$i,1) ^ $md5);
        }
        return $tmp;
    }
    private static $f_code_config = array(
        '0' =>array('b','f'),
        '1' =>array('d','r'),
        '2' =>array('g','h'),
        '3' =>array('j','l'),
        '4' =>array('m','n'),
        '5' =>array('p','q'),
        '6' =>array('s','t'),
        '7' =>array('v','w'),
        '8' =>array('y','o'),
        '9' =>array('c','x'),
        '10' =>array('z','k','a','i','e')
    );
    public static function f_code_encode($uid){
        apf_require_class("APF_DB_Factory");
        $uid  = (string)$uid;
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select fcode from t_fcode_v2 where uid = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($uid));
        $result = $stmt->fetchColumn();
        if($result) 
            return $result;
        $orign_uid = $uid;

        $len = strlen($uid);
        while($len > 0){
            $len -- ;
            $uid[$len] = self::$f_code_config[$uid[$len]][rand(0,1)];
            if(!$uid[$len]){ return false;}
        }
        $len = strlen($uid);
        while($len < 6){
            $uid[$len] = self::$f_code_config[10][rand(0,2)];
            $len++;
        }

        $sql2 = "insert into t_fcode_v2 (uid, fcode) values (?, ?)";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute(array($orign_uid, $uid));

        return $uid;
    }
    public static function f_code_decode($str){
        apf_require_class("APF_DB_Factory");
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "select uid from t_fcode_v2 where fcode = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($str));
        $result = $stmt->fetchColumn();
        if($result)
            return $result;
        $result='';
        $i=0;
        while(isset($str[$i])){
            foreach(self::$f_code_config as $key => $value){
                if ($key < 10) {
                    if(in_array($str[$i],$value))
                    {
                        ($result .= $key);
                    }
                }
            }
            $i++;
        }
        return $result;
    }
}
