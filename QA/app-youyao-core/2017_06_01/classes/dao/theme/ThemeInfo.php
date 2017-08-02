<?php
/**
 * Created by PhpStorm.
 * User: LCY
 * Date: 15/7/27
 * Time: 下午2:18
 */
apf_require_class("APF_DB_Factory");

class Dao_Theme_ThemeInfo
{

    private $slave_pdo;
    private $lky_pdo;

    public function __construct()
    {
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->lky_pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    }

    public function get_comment_by_uid($uid)
    {
        $sql = "select COUNT(*) from t_comment_info where status = 1 and nid = $uid";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $result = $stmt->fetchColumn();
    }

    public function get_theme_by_id($theme_id){
        $sql = <<<SQL
SELECT * FROM LKYou.t_app_theme
WHERE id=:theme_id
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array(
            'theme_id' => $theme_id,
        ));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function get_theme_by_dest_id($dest_id, $multi_lang = 12) {
        $sql = <<<SQL
SELECT * FROM LKYou.t_app_theme
WHERE dest_id=:dest_id AND multilang = :multilang AND status =1
ORDER BY delta
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array(
            'dest_id' => $dest_id,
            'multilang' => $multi_lang
        ));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count_theme_list($theme_id_arr)
    {
        if (empty($theme_id_arr)) {
            return false;
        }
        $sql = "SELECT count(LKYou.t_app_theme_homestay.homestay_uid) AS homestay_count,t_app_theme_relation.theme_id
FROM LKYou.t_app_theme_homestay
left join LKYou.t_app_theme_relation on LKYou.t_app_theme_homestay.list_id=LKYou.t_app_theme_relation.list_id
WHERE t_app_theme_relation.theme_id IN (".join(',', $theme_id_arr).") AND t_app_theme_homestay.status=1
GROUP BY t_app_theme_relation.theme_id";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function get_homestay_by_theme_id($theme_id)
    {
        $sql = <<<SQL
SELECT * FROM LKYou.t_app_theme_homestay
LEFT JOIN LKYou.t_app_theme_relation ON LKYou.t_app_theme_homestay.list_id=LKYou.t_app_theme_relation.list_id
WHERE LKYou.t_app_theme_relation.theme_id = :theme_id AND LKYou.t_app_theme_homestay.status = 1
ORDER BY delta
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array(
            'theme_id' => $theme_id,
        ));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_data_by_lang($lang_id)
    {
        $sql = <<<SQL
SELECT * FROM t_app_global where status=1 AND  multilang=:multilang order by priority
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array(
            'multilang' => $lang_id
        ));
        return $stmt->fetchAll();

    }
    public function get_total_global_data(){
        $sql = <<<SQL
SELECT * FROM t_app_global GROUP BY category , priority,status
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();

    }

    public function insert_global_index_data($data)
    {
        try {
            $this->lky_pdo->beginTransaction();
            $sql = <<<SQL
insert t_app_global ( title ,img_url,type,category,status , ext ,start,end ,priority ,multilang   )
value
( :title,:img_url,:type,:category,:status,:ext,:start,:end ,:priority,:multilang )
SQL;
            $stmt = $this->lky_pdo->prepare($sql);
            $result = $stmt->execute(array($data));
            $this->lky_pdo->commit();
        } catch (Exception $e) {
            $this->lky_pdo->rollBack();
            Util_Debug::zzk_debug(__METHOD__, $e->getMessage());
        }
        return $stmt->rowCount();

    }
    //添加offnow
//insert 't_app_global' ( 'title' ,'img_url','type','category','status' , 'ext' ,'start','end' ,'priority' ,'multilang'   )
//value
//( '','','homestay','offnow','1', '{ "homestay_uid": 399630, "nid":187779 }','2016-06-01','2017-06-01',1,12 )


    public function unset_global_index_data($id)
    {
        $sql = <<<Sql
    update t_app_global set status =0 where id =:id
Sql;
        $stmt = $this->lky_pdo->prepare($sql);
        $result = $stmt->execute(array('id' => $id));
        if ($result) return true;
        else return false;
    }

    public function update_global_index_data($id, $attributes)
    {
        $keys = array('title', 'img_url', 'type', 'category', 'status', 'ext', 'start', 'end', 'priority', 'multilang');

        foreach ($attributes as $k => $v) {
            if (!in_array($k, $keys))
                unset($attributes[$k]);
        }

        $sql_format = 'update %s set %s where id=:id';
        $sql = vsprintf($sql_format, array(
            't_app_global',
            join(',', array_map(function ($field) {
                return $field . '=:' . $field;
            }, array_keys($attributes))),
        ));
        $stmt = $this->lky_pdo->prepare($sql);
        return $stmt->execute($attributes + array('id' => $id));
    }


}
