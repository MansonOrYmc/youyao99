<?php
apf_require_class("APF_DB_Factory");
class Dao_Homestay_Score {

    private $pdo;
    private $slave_pdo;
    private $one_pdo;
    private $one_slave_pdo;
    private $score_list;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
        $this->score_list = array(
                'order_num_score' ,
                'order_price_score' ,
                'create_time_score' ,
                'cancel_order_score' ,
                'comment_score' ,
                'pm_score' ,
                'room_price_score' ,
                'homestay_speed_score' ,
                'other_service_score' ,
                'price_score' ,
                'time_score' ,
                'discount_score' ,
                'commission_rate_score' ,
                'zzk_rec_score' ,
            );
    }

    public function sore_config() {
        $config = array(
                'order_num_score'        => '订单数',
                'order_price_score'      => '订单间夜均价',
                'create_time_score'      => '上架时间',
                'cancel_order_score'     => '无房取消',
                'comment_score'          => '点评',
                'pm_score'               => '私信',
                'room_price_score'       => '100元以下房间',
                'homestay_speed_score'   => '速订评分',
                'other_service_score'    => '特色服务',
                'price_score'            => '改价率',
                'time_score'             => '自助处理时长',
                'discount_score'         => '营销活动',
                'commission_rate_score'  => '佣金比例',
                'zzk_rec_score'          => '自在客推荐',
            );
        return $config;
    }

    public function get_score_weight() {
        $sql = "select * from t_score_weight order by delta asc, id asc";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return array_column($result, 'weight', 'score');
    }

    public function get_score_log() {
        $sql = "select weight.*,users.name from log_score_weight weight left join one_db.drupal_users users on weight.admin_uid = users.uid order by update_date desc";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update_score_weight($score, $weight) {

        if(!$score || !$weight) return;
        if(!in_array($score, $this->score_list)) return;
        $sql = "update t_score_weight set weight = ? where score = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($weight, $score)); 

    }

    // 在修改之前记录
    public function insert_score_log($score, $weight, $admin_uid=0) {
        if(!$admin_uid) {
            $user = Util_Signin::get_user();
            $admin_uid = $user->uid;
        }
         
        $sql = "insert into log_score_weight (score, old_weight, new_weight, admin_uid) 
            select score,weight, ?, ? from t_score_weight where score = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($weight, $admin_uid, $score));
    }

    public function update_zzk_rec($uid, $score,$admin_uid=0) {
        if(!$admin_uid) {
            $user = Util_Signin::get_user();
            $admin_uid = $user->uid;
        }

        $sql = "insert t_homestay_recscore (`uid`, `score`, `mid`, `updated`) values (?, ?, ?, unix_timestamp()) on duplicate key update `score`=values(score), `mid`=values(mid), `updated`=values(updated); ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($uid, $score, $admin_uid));
    }

    public function get_zzk_recscore($uid)
    {
        $sql = <<<SQL
SELECT score
FROM t_homestay_recscore
WHERE  uid = :uid
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));

        return $stmt->fetchColumn();
    }

    public function get_homestay_score($uid, $hist=false)
    {
        $score_table = $hist ? "t_homestay_score_hist" : "t_homestay_score";
        $sql = "SELECT * FROM $score_table WHERE uid=:uid";
        $stmt = APF_DB_Factory::get_instance()
            ->get_pdo('lkyslave')
            ->prepare($sql);
        $stmt->execute(array('uid' => $uid));

        $raw_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $local_code = $raw_result['local_code'];
        $detail = json_decode($raw_result['detail'], true);
        foreach ($detail as $v) {
            $score_detail[$v['name']] = $v;
        }
        $score_detail['total_score'] = $raw_result['score'];

        return array($local_code, $score_detail);
    }

    public function score_rank($local_code, $uid, $hist=false)
    {
        $pdo = APF_DB_Factory::get_instance()
            ->get_pdo('lkyslave');
        $score_table = $hist ? "t_homestay_score_hist" : "t_homestay_score";

        $sql = 'SELECT type_name FROM t_loc_type WHERE type_code=:code';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array('code' => $local_code));
        $type_name = $stmt->fetchColumn();

        $sql = <<<SQL
SELECT rank FROM (
SELECT uid, score, @rank := @rank + 1 AS rank
FROM $score_table, (SELECT @rank := 0) a
WHERE local_code LIKE :code
ORDER BY score DESC ) rank_table WHERE uid=:uid;
SQL;
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            'code' => $local_code . '%',
            'uid' => $uid,
        ));
        $rank = $stmt->fetchColumn();
        return array("type_name"=>$type_name, "rank" => $rank);
    }

    public function get_score_rank_arr($homestay_uid, $hist=false) {
        list($local_code, $homestay_score) = $this->get_homestay_score($homestay_uid, $hist);
        if(empty($local_code)) {
            return null;
        }
        for (; strlen($local_code) >= 7; $local_code = substr($local_code, 0, -5)) {
            $rank[$local_code] = $this->score_rank($local_code, $homestay_uid, $hist);
        }
        ksort($rank);
        return $rank;
    }

}
