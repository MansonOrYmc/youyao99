<?php
/**
 * Created by PhpStorm.
 * User: LCY
 * Date: 15/9/10
 * Time: 下午6:01
 */
class Bll_Sitemap_SiteMap
{
    private $dao;

    public function __construct()
    {
        $this->dao    = new Dao_Sitemap_SiteMap();
    }

    //获取地区列表
    public function getDistrictList($dest_id)
    {
        return  $this->dao->getDistrictList($dest_id);
    }

    //获取该地区下所有民宿列表
    public function getHomestayList($locid)
    {
        return  $this->dao->getHomestayList($locid);
    }
    //获取民宿信息
    public function getHomestayInfo($poiid)
    {
        return  $this->dao->getHomestayInfo($poiid);
    }
    //获取该民宿下的房间列表
    public function getRoomList($uid)
    {
        return $this->dao->getRoomList($uid);
    }
}