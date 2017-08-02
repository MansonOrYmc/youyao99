<?php
/**
 * Created by PhpStorm.
 * User: lixiangyang
 * Date: 16/6/30
 * Time: 11:07
 */



apf_require_class("APF_DB_Factory");

class UserUtil
{

    /**
     * 返回用户使用的货币类型
     * @param $uid
     * @return null
     */
    public static function getUserCurrency($uid = 0, $multiprice = 10)
    {
        try {
            if($uid) {
                $pdo = \APF_DB_Factory::get_instance()->get_pdo("master");
                $sql = 'select currency_type_id from drupal_users where uid = ?';
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array($uid));
                $res = $stmt->fetch();
                $currency_type_id = $res['currency_type_id'] ? $res['currency_type_id'] : $multiprice;
            } else {
                $currency_type_id = $multiprice;
            }
//            $all = self::getAllcurrencyType();
//            $currency_type = null;
//            foreach($all as $v) {
//                if($v['dest_id'] == $currency_type_id) {
//                    $currency_type = $v;
//                    break;
//                }
//            }
//            return $currency_type ? $currency_type : $all[0];
            return $currency_type_id ? $currency_type_id : 12;
        } catch(\Exception $e) {
            return null;
        }
    }

    /**
     * 目前获取支持的币种,
     * @return array
     */
    public static function getAllcurrencyType()
    {
        return array(
            array('dest_id' => 12, 'currency_name'=> '人民币', 'currency_code' => 'rmb'),
            array('dest_id' => 10, 'currency_name'=> '台币', 'currency_code' => 'NT$') ,
        );
    }

    /**
     * 更改用户的币种
     * @param $uid
     * @param $multiPrice
     * @return bool
     */
    public static function updateUserCurrency($uid, $currency_type_id)
    {
        $pdo = \APF_DB_Factory::get_instance()->get_pdo("master");
        try {
            $pdo->beginTransaction();
            $sql = 'update drupal_users set currency_type_id = :currency_type_id where uid= :uid';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':currency_type_id', $currency_type_id);
            $stmt->bindValue('uid', $uid);
            $stmt->execute();
            $pdo->commit();
            return true;
        } catch(\Exception $e) {
            //纪录错误日志
            //TODO
            Logger::info(__FILE__, __CLASS__, __LINE__, var_export($e->getMessage(), true));
            $pdo->rollBack();
        }
        return false;
    }
}
