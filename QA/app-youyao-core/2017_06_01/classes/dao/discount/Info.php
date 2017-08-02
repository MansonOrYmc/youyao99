<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/7/27
 * Time: 下午5:12
 */
apf_require_class("APF_DB_Factory");

class Dao_Discount_Info {

    private $lky_pdo;

    public function __construct() {
        $this->lky_pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    }


    public function get_info($nid){

        $sql = "SELECT * FROM t_discount_info  WHERE  status = 0 AND nid = :nid ";
        $stmt = $this->lky_pdo->prepare($sql);
        $stmt->execute(array('nid' => $nid));
        return $stmt->fetchAll();
    }

    public function get_day_discounts($day,$nid){
        $sql = <<<SQL
    select discount from t_discount_info where nid = :nid and status = 0
    and start_date <= :day
    and end_date >= :day
    limit 1;
SQL;
        $stmt = $this->lky_pdo->prepare($sql);
        $stmt->execute(array('nid' => $nid,'day'=>$day));
        return $stmt->fetch();
    }

    public function get_month_discounts($nid,$month,$year)
    {
        //echo "\nyear:".$year;
        //echo "\nmonth:".$month;
        $days_in_month = date('t',mktime(0,0,0,$month,1,$year));//这个月一共有多少天
        //$date_s = strtotime(date_create("$year-$month-1 00:00:00"));
        $date_s = strtotime("$year-$month-1 00:00:00");
        $date_e = strtotime("$year-$month-$days_in_month 00:00:00");
        $sql = <<<SQL
    select discount,start_date,end_date from t_discount_info where nid = :nid and status = 0
    and end_date between :date_s and :date_e
    ;
SQL;
        $stmt = $this->lky_pdo->prepare($sql);
        //echo "\ndate_s:".$date_s;
        //echo "\ndate_e:".$date_e;
        $stmt->execute(array('nid' => $nid,'date_s'=>$date_s,'date_e'=>$date_e));
        //exit(var_dump($stmt));
        $dis_conf =  $stmt->fetchAll();
        //exit(var_dump($dis_conf));
        for($list_day = 1; $list_day <= $days_in_month; $list_day++)
        {
            $list_date = "$year-$month-$list_day 00:00:00";
            //$date = date_create($list_date);
            $list_date_time = strtotime($list_date);

            $result[$list_day] = 1;
            foreach($dis_conf as $key => $value)
            {
                if($value['start_date'] <= $list_date_time  &&  $value['end_date'] >= $list_date_time)
                {
                    $x = date('w',$list_date_time);
                    if($x > 0 ) {$x--;} else {$x = 6;}
                    $result[$list_day] =  empty(explode('_',$value['discount'])[$x])?1:explode('_',$value['discount'])[$x];
                }
            }
        }
        return $result;
    }

    public function update_info_status($ids,$status){

        $sql = "UPDATE t_discount_info SET `status` = :status WHERE `id` in (".implode(',',$ids).")";

        $stmt = $this->lky_pdo->prepare($sql);
        try{$result = $stmt->execute(array('status'=>$status));}
        catch(Exception $e) {
            return false;
        }
        return $result;
    }

    public function delete_info($id){
        return self::update_info_status(array($id),1);
    }

    public function update_info($discount){
        if(empty($discount['id'])) return false;
        if(self::delete_info($discount['id']))
        if(self::insert_info($discount)) return true;
        return false;

    }

    public function insert_info($discount){
        if(empty($discount['nid'])) return false;
        else $nid = $discount['nid'];
        if(empty($discount['start_date'])) return false;
        else $start_date = $discount['start_date'];
        if(empty($discount['end_date'])) return false;
        else $end_date = $discount['end_date'];
        if(empty($discount['discount'])) return false;
        else $discount = $discount['discount'];
        if(empty($discount['status'])) $status=0;
        else $status = $discount['status'];


        $params = array(
            'nid' => $nid,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'discount' => $discount,
            'status' => $status,
            'update_time' => time()
        );

        $sql = <<<SQL
INSERT INTO t_discount_info
(`nid`,`start_date`,`end_date`,`discount`,`status`,`update_time`) VALUES
(:nid,:start_date,:end_date,:discount,:status,:update_time)
SQL;
        $stmt = $this->lky_pdo->prepare($sql);
        $result = $stmt->execute($params);

        if (!$result) {
            return false;
        }
        return true;
    }

}