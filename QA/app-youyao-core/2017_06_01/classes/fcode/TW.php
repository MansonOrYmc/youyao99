<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 16/2/17
 * Time: 下午12:04
 */
class Fcode_TW{
    //处理台湾地区Fcode逻辑,用户注册成功后,调用该函数
    public static function insert($s_uid,$t_uid,$fcode,$phone,$coupon,$channel,$register_type){
        $create_time = time();
        $update_time = time();
        $sql = <<<SQL
INSERT INTO t_fcode
(`fcode`,`s_uid`,`t_uid`,`create_time`,`update_time`,`channel`,`register_type`,`phone`,`coupon`) VALUES
('$fcode','$s_uid','$t_uid','$create_time','$update_time','$channel','$register_type','$phone','$coupon')
SQL;
        return DB::execSql($sql);
    }

    public static function update($id,$params){
        $params = So_NiceClean::clean_Array($params,array('point_id','is_coupon_used','status'));
        $set = '';
        foreach($params as $k=>$v){
            $set .= " , `$k` = '$v' ";
        }
        $update_time = time();
        $sql = <<<SQL
UPDATE t_fcode set `update_time` = '$update_time' $set where `id` = '$id';
SQL;
        return DB::execSql($sql);
    }

    public static function insert_points($id,$uid,$order_id){
        $sql_user = "select dest_id from one_db.drupal_users where uid = '$uid'";
        $dest_id = reset(reset(DB::execSql($sql_user)));
        if($dest_id != 10) $dest_id = 12;
        $point_config = APF::get_instance()->get_config('point','fcode');
        $point_config = $point_config[$dest_id];
        $point = $point_config['value'];
        $remark = $point_config['remark'];
        $source = $point_config['source'];
        $create_time = time();
        $validate_time = time();
        $sql = <<<SQL
INSERT INTO t_user_points
(`uid`,`point`,`expire_time`,`create_time`,`validate_time`,`remark`,`order_id`,`source`) VALUES
('$uid','$point','$create_time','$create_time','$validate_time','$remark','$order_id','$source')
SQL;
        $lastId = DB::execSql($sql,true);
        if($lastId){
            self::update($id,array('point_id'=>$lastId,'is_coupon_used'=>'1'));
        }
        else{
            //exit('wrong');
        }
    }


    public static function select_all(){
        $sql = "select fcode.*,src_users.dest_id src_dest_id,src_users.mail src_mail,src_users.phone_num src_phone_num,dst_users.name as dst_name,dst_users.mail as dst_mail,dst_users.phone_num as dst_guest_telnum,nickname.field_nickname_value as dst_nickname from LKYou.t_fcode fcode left join one_db.drupal_users src_users on fcode.s_uid=src_users.uid left join one_db.drupal_users dst_users on fcode.t_uid=dst_users.uid left join one_db.drupal_field_data_field_nickname nickname on dst_users.uid=nickname.entity_id where fcode.status = 1 and fcode.point_id is null";
        return DB::execSql($sql);
    }

    public static function get_already_get_point_by_uid($uid, $used=true) {
        if(!$uid) return;
        $where = "";
        if($used) {
            $where = "and f.point_id > 0";
        }
        apf_require_class("APF_DB_Factory");
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from t_fcode f left join t_coupons c on f.coupon = c.coupon where f.s_uid = ? $where and f.status = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($uid));
        return $stmt->fetchAll();
    }

    public static function get_fcode_byuid($uid) {
 // 由于 fcodev2 和粉客码用的是同一个id， 所以要检验一下正确性 只用数据库查询，并不用算法生成
        if(!$uid) return false;
        apf_require_class("APF_DB_Factory");
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select fcode from t_fcode_v2 where uid = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($uid));
        $fcode = $stmt->fetchColumn();
        return $fcode;
    }

    public static function get_record_by_fcode($fcode) {
        if(!$fcode) return false;
        apf_require_class("APF_DB_Factory");
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $sql = "select * from t_fcode_v2 where fcode = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($fcode));
        $record = $stmt->fetch();
        return $record;
    }

}
