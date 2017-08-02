<?php
class Bll_Sale_Schedule {
     private $dao;
     public function __construct() {
        $this->dao    = new Dao_Sale_Schedule();
    }

    public function get_sales_schedule_byperiod($start=null, $end=null) {
        if(!$start) $start =  date('Y-m-d', strtotime('monday this week'));
        if(!$end)   $end   =  date('Y-m-d', strtotime('next sunday ') + 60*60*24*7);
        return $this->dao->get_sales_schedule_byperiod($start, $end);
    }

    public function update_schedule_by_mid_date($mid, $data, $status, $operator_uid) {
        return $this->dao->insert_update_mid_bydate($mid, $data, $status, $operator_uid);
    }

    public function get_sales_schedule_bydate($date) {
        return $this->dao->get_sales_schedule_bydate($date);
    }

    public function get_on_leave_by_date($date=null) {
        if(!$date) $date = date('Y-m-d');
        $schedule = $this->get_sales_schedule_bydate($date);
        $result = array();
        foreach($schedule as $row) {
            if($row['status']==0)$result[] = $row['mid'];
        }

        return $result;
    }
}
