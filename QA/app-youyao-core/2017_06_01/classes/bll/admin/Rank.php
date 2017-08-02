<?php
class Bll_Admin_Rank {
    public function __construct() {
        $this->dao = new Dao_Admin_Rank();
    }
    public function get_homestay_score($uid){
       return $this->dao->get_homestay_score($uid);
    }
    public function score_rank($local_code, $uid){
        return$this->dao->score_rank($local_code,$uid);
    }
}
