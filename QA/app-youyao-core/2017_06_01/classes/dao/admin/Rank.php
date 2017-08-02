<?php
apf_require_class("APF_DB_Factory");
class Dao_Admin_Rank {
    private $slave_pdo;
    public function __construct() {

        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
    }

    public function get_homestay_score($uid)
    {
        $sql = 'SELECT * FROM t_homestay_score WHERE uid=:uid';
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));

        $raw_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $local_code = $raw_result['local_code'];
        $detail = json_decode($raw_result['detail'], true);
        foreach ($detail as $v) {
            $score_detail[$v['name']] = $v;
        }

        return array($local_code, $score_detail);
    }

    public function score_rank($local_code, $uid)
    {
        $sql = 'SELECT type_name FROM t_loc_type WHERE type_code=:code';
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('code' => $local_code));
        $type_name = $stmt->fetchColumn();

        $sql = <<<SQL
SELECT rank FROM (
SELECT uid, score, @rank := @rank + 1 AS rank
FROM t_homestay_score, (SELECT @rank := 0) a
WHERE local_code LIKE :code
ORDER BY score DESC ) rank_table WHERE uid=:uid;
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array(
            'code' => $local_code . '%',
            'uid' => $uid,
        ));
        $rank = $stmt->fetchColumn();
        return array($type_name, $rank);
    }


}