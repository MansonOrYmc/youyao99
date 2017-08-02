<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/8/4
 * Time: 下午5:12
 */
apf_require_class("APF_DB_Factory");

class Dao_Disc_Info {

    private $lky_pdo;
    private static $get_period_disc;

    public function __construct() {
        $this->lky_pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    }


    public function get_info($nid){
        $sql = "SELECT * FROM t_disc_info  WHERE  nid = :nid and status = 1 and least_days > 0 ORDER BY occ_date ASC ";
        $stmt = $this->lky_pdo->prepare($sql);
        $stmt->execute(array('nid' => $nid));
        return $stmt->fetchAll();
    }

    public function get_day_disc($day,$nid){
        $sql = <<<SQL
    select disc from t_disc_info where nid = :nid
    and occ_date = :day
    and status =1
    and least_days > 0
    limit 1;
SQL;
        $stmt = $this->lky_pdo->prepare($sql);
        $stmt->execute(array('nid' => $nid,'day'=>$day));
        return $stmt->fetch();
    }

     public function get_period_disc($sDate,$eDate,$nid)
     {
         if(self::$get_period_disc[$nid.'-'.$sDate.'-'.$eDate])
         {
             $result = self::$get_period_disc[$nid.'-'.$sDate.'-'.$eDate];
         }else{
             $memcache = APF_Cache_Factory::get_instance()->get_memcache();
             $key =  'get_period_discs-'.$nid.'-'.$sDate.'-'.$eDate;
             $result = $memcache->get($key);
             $result = null;
             if(empty($result) || !is_array($result)){
                 $sql = <<<SQL
    select disc,occ_date,least_days from t_disc_info where nid = :nid
    and (occ_date BETWEEN :sDate and :eDate  OR occ_date = 0)
    and status = 1
    and least_days > 0
SQL;
                 $stmt = $this->lky_pdo->prepare($sql);
                 $stmt->execute(array('nid' => $nid,'sDate'=>$sDate,'eDate'=>$eDate));
                 $result = $stmt->fetchAll();
                 foreach ($result as $v) {
                     $occ_dates[] = $v['occ_date'];
                 }
                 //$occ_dates=array_column('occ_date',$result);
                 foreach ($result as $disc_k => $v) {
                     if (date("Y-m-d", $v['occ_date']) == '1970-01-01') {
                         $s = $sDate;
                         while ($s <= $eDate) {
                             // 避免 单独设置的被全年设置覆盖
                             if (!in_array($s, $occ_dates))
                                 $result[] = array('disc' => $v['disc'], 'occ_date' => $s, 'least_days' => $v['least_days']);
                             $s = $s + 24 * 3600;
                         }
                         // 去除 occ_date为0   避免 其它地方只通过0 判断造成的错误
                         unset($result[$disc_k]);
                         break;
                     } else {

                         continue;
                     }
                 }
                 $memcache->set($key,$result,null,86400);
             }
             self::$get_period_disc[$nid.'-'.$sDate.'-'.$eDate] = $result;
         }
         return $result;
     }

    public function delete_info($occ_date,$nid){
        $sql = <<<SQL
update  t_disc_info set status =0
where `occ_date` = :occ_date
and `nid` = :nid
SQL;
        $stmt = $this->lky_pdo->prepare($sql);
        $result = $stmt->execute(array("occ_date"=>$occ_date,"nid"=>$nid));

        if (!$result) {
            return false;
        }
        return true;
    }

    public function delete_all_info($nid){
        $sql = <<<SQL
update  t_disc_info set status =0
where `nid` = :nid
SQL;
        $stmt = $this->lky_pdo->prepare($sql);
        $result = $stmt->execute(array("nid"=>$nid));
        if (!$result) {
            return false;
        }
        return true;
    }

    public function update_info($disc){
        if(empty($disc['occ_date'])) {$occ_date = '0';}
        else $occ_date = $disc['occ_date'];
        if(empty($disc['nid'])) return false;
        else $nid = $disc['nid'];

        if(self::delete_info($occ_date,$nid))
        {self::insert_info($disc);return true;}
        return false;
    }

    public function insert_info($disc){
        if(empty($disc['least_days'])) {$least_days='1';}
        else{$least_days = $disc['least_days'];}
        if(empty($disc['nid'])) return false;
        else $nid = $disc['nid'];
        if(empty($disc['occ_date'])) {$occ_date = '0';}
        else $occ_date = $disc['occ_date'];
        if(empty($disc['disc'])) return false;
        else $disc = $disc['disc'];

        $params = array(
            'nid' => $nid,
            'occ_date' => $occ_date,
            'occ_date_format' => date("Y-m-d",$occ_date),
            'disc' => $disc,
            'update_time' => time(),
            'least_days' => $least_days
        );

        $sql = <<<SQL
INSERT INTO t_disc_info
(`nid`,`occ_date`,`occ_date_format`,`disc`,`update_time`,`least_days`) VALUES
(:nid,:occ_date,:occ_date_format,:disc,:update_time,:least_days)
SQL;
        $stmt = $this->lky_pdo->prepare($sql);
        $result = $stmt->execute($params);

        if (!$result) {
            return false;
        }
        return true;
    }

    public function get_discrate_bynid($nid){
    $sql = "SELECT disc FROM t_disc_info  WHERE  nid = :nid and status = 1 and least_days > 0 ORDER BY occ_date ASC ";
    $stmt = $this->lky_pdo->prepare($sql);
    $stmt->execute(array('nid' => $nid));
    return $stmt->fetchColumn();
    }

    public function insert_room_disc($nid,$promotion,$least_days,$discount_rate,$promotion_dates){
        $user = Util_Signin::get_user();
        $admin_id = $user->uid;
        if(empty($admin_id)) $admin_id = 0;
        /*  [promotion] => 2
          [add_least_days] => 4
          [discount_rate] => 0.6
          [promotion_dates] => Array (
              [0] => Array ( [start_date] => 2015-12-04 [end_date] => 2015-12-09 )
          [1] => Array ( [start_date] => 2015-12-09 [end_date] => 2015-12-12 )
          [2] => Array ( [start_date] => 2015-12-12 [end_date] => 2015-12-12 ) )
        promotion_dates 为Array（）代表不打折
        为0代表特殊情况全部日期
      */

        try{
            $this->lky_pdo->beginTransaction();
            /*
             * 更新之前删除房间号的历史记录
             */
            self::delete_all_info($nid);
            //是否参与打折
            if($promotion!=0) {
                /*
                 * 插入 判断是全部日期还是部分时间段
                 */
                if (empty($promotion_dates)) {
                    $params = "(" . $nid . ",0,'1971-01-01','" . $discount_rate . "'," . time() . "," . $least_days . ",'".$admin_id."')";
                    $sql = "INSERT INTO t_disc_info
                      (`nid`,`occ_date`,`occ_date_format`,`disc`,`update_time`,`least_days`,`admin_id`) VALUES " . $params;
                } else {
                    $occ_date = array();
                    $occ_date_format = array();

                    foreach ($promotion_dates as $k => $v) {
                        $interval = intval((strtotime($v["end_date"]) - strtotime($v["start_date"])) / 86400) + 1;
                        //闭区间 所以开始结束同一天的日期不参与

                        for ($i = 0; $i < $interval; $i++) {
                            $occ_date[] = strtotime($v["start_date"]) + $i * 86400;
                            $occ_date_format[] = date("Y-m-d", strtotime($v["start_date"]) + $i * 86400);

                        }
                    }
                    $params = "";
                    for ($i = 0; $i < count($occ_date); $i++) {
                        if ($i == 0) {
                            $params .= ("(" . $nid . "," . $occ_date[$i] . ",'" . $occ_date_format[$i] . "','" . $discount_rate . "'," . time() . "," . $least_days . ",$admin_id)");
                        } else {
                            $params .= (",(" . $nid . "," . $occ_date[$i] . ",'" . $occ_date_format[$i] . "','" . $discount_rate . "'," . time() . "," . $least_days . ",$admin_id)");
                        }
                    }
                    $sql = "INSERT INTO t_disc_info
                   (`nid`,`occ_date`,`occ_date_format`,`disc`,`update_time`,`least_days`,`admin_id`) VALUES " . $params;

                }
                $stmt = $this->lky_pdo->prepare($sql);
                $result = $stmt->execute();
                $this->lky_pdo->commit();
            }else{
                $this->lky_pdo->commit();
                return 0;
            }
        }catch(Exception $e) {
            $this->lky_pdo->rollBack();
            print_r($e->getMessage());
        }
        return $stmt->rowCount();

    }

    public function get_disc_roomids_by_roomids($rids)
    {
        $sql = "SELECT DISTINCT(nid) FROM t_disc_info  WHERE  nid in (".Util_Common::placeholders("?",count($rids)).")  and status = 1 and least_days > 0 ORDER BY occ_date ASC ";
        $stmt = $this->lky_pdo->prepare($sql);
        $stmt->execute($rids);
        return $stmt->fetchAll();

    }


    public function bulk_insert_room_disc($nid, $days, $discount_rate, $least_days = 1, $admin_id = 1)
    {
        $occ_date = array();
        $occ_date_format = array();
        foreach ($days as $day) {
            $occ_date[] = strtotime($day);
            $occ_date_format[] = $day;
        }

      $params = "";
        for ($i = 0; $i < count($occ_date); $i++) {
            if ($i == 0) {
                $params .= ("(" . $nid . "," . $occ_date[$i] . ",'" . $occ_date_format[$i] . "','" . $discount_rate . "'," . time() . "," . $least_days . ",$admin_id)");
            } else {
                $params .= (",(" . $nid . "," . $occ_date[$i] . ",'" . $occ_date_format[$i] . "','" . $discount_rate . "'," . time() . "," . $least_days . ",$admin_id)");
            }
        }

        $sql = "INSERT INTO t_disc_info
                   (`nid`,`occ_date`,`occ_date_format`,`disc`,`update_time`,`least_days`,`admin_id`) VALUES " . $params;

         try {
           $stmt = $this->lky_pdo->prepare($sql);
           $stmt->execute($days);

               } catch (Exception $e) {
                         Util_Debug::zzk_debug(__METHOD__,  $e->getMessage());
              return false;
           }
             return true;
      }

    public function update_room_disc($nid, $days, $disc_rate, $least_days = 1, $admin_id = 1)
    {
        try {
            $sql = "update  t_disc_info  set status =1 ,update_time=" . time() . ",disc=" . $disc_rate . ",least_days=" . $least_days . " ,admin_id=" . $admin_id . " WHERE  nid=" . $nid . " and  occ_date_format in (" . Util_Common::placeholders("?", count($days)) . ") ";
            $stmt = $this->lky_pdo->prepare($sql);
            $stmt->execute($days);
        } catch (Exception $e) {
            Util_Debug::zzk_debug(__METHOD__,  $e->getMessage());
            return false;
        }
       return true;
    }

    public function discount_all_year($nid)
    {
        $sql = "select * from t_disc_info where status =1 AND  occ_date=0 AND disc<1 AND  least_days>1 AND nid =" . $nid;
        $stmt = $this->lky_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function close_all_year($nid,$admin_id)
    {
        try {
            $sql = "update  t_disc_info  set status =0,admin_id=".$admin_id." where status =1 AND  occ_date=0 AND disc<1   AND nid =" . $nid;
            $stmt = $this->lky_pdo->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            Util_Debug::zzk_debug(__METHOD__, $e->getMessage());
            return false;
        }
        return true;
    }


    public function get_room_disc_by_nid_days($nid, $days)
    {
        $sql = "select * from t_disc_info where nid=" . $nid . "  and  occ_date_format in (" . Util_Common::placeholders("?", count($days)) . ")";
        $stmt = $this->lky_pdo->prepare($sql);
        $stmt->execute($days);
        return $stmt->fetchAll();
    }

    public function unset_room_disc($nid, $days,$admin_id=1)
    {
        $sql = "update  t_disc_info  set status =0 ,admin_id=" . $admin_id . " WHERE  nid=" . $nid . " and  occ_date_format in (" . Util_Common::placeholders("?", count($days)) . ") and status =1 ";
        try {
            $stmt = $this->lky_pdo->prepare($sql);
            $stmt->execute($days);
        } catch (Exception $e) {
            Util_Debug::zzk_debug(__METHOD__, $e->getMessage());
            return false;
        }
        return true;
    }



}
