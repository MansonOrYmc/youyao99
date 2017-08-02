<?php
class Bll_Performance_Log {
    private $dao;

    public function __construct() {
        $this->dao = new Dao_Perfmance_Log();
    }

    public function create_table($time=null) {
        $time = $time ? $time : time() + 30 * 86400;
        return $this->dao->create_table($time);
    }

    public function drop_table($time=null) {
        $time = $time ? $time : time() - 7 * 30 * 86400;
        return $this->dao->drop_table($time);
    }

    public function insert_record(
        $parent_id,
        $type,
        $site,
        $guid,
        $page_name,
        $url,
        $ip,
        $country,
        $region,
        $city,
        $as_num,
        $user_agent,
        $perf_data
    ) {
        return $this->dao->insert_record(
            $parent_id,
            $type,
            $site,
            $guid,
            $page_name,
            $url,
            $ip,
            $country,
            $region,
            $city,
            $as_num,
            $user_agent,
            $perf_data
        );
    }
}
