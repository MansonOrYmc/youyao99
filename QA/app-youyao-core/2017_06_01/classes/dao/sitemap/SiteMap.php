<?php
/**
 * Created by PhpStorm.
 * User: LCY
 * Date: 15/9/10
 * Time: 下午5:59
 */
apf_require_class("APF_DB_Factory");

class Dao_Sitemap_SiteMap
{

    private $pdo;
    private $slave_pdo;
    private $one_pdo;
    private $one_slave_pdo;

    public function __construct()
    {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
    }

    public function getDistrictList($dest_id)
    {
        $sql = 'SELECT id,type_name,name_code,locid FROM t_loc_type WHERE dest_id=:dest_id AND status=1';
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array('dest_id' => $dest_id));
        return $stmt->fetchAll();
    }

    public function getHomestayList($locid)
    {
        $sql = "SELECT uid,title,address,pid FROM t_weibo_poi_tw WHERE loc_typecode like '%$locid%' and title!='' and address!='' and uid>0";
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getHomestayInfo($poiid)
    {
        $sql = "SELECT uid,name FROM drupal_users WHERE poi_id=:poiid";
        $stmt = $this->one_pdo->prepare($sql);
        $stmt->execute(array('poiid' => $poiid));
        return $stmt->fetchAll();
    }

    public function getRoomList($uid)
    {
        $sql = "SELECT nid,title FROM drupal_node where uid=:uid";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array('uid' => $uid));
        return $stmt->fetchAll();
    }
}