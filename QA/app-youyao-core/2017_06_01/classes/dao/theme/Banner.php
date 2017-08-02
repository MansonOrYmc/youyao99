<?php
/**
 * Created by PhpStorm.
 * User: xue
 * Date: 16/8/4
 * Time: 下午2:20
 */
apf_require_class("APF_DB_Factory");

class Dao_Theme_Banner
{
    private $slave_pdo;
    private $lky_pdo;

    public function __construct()
    {
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->lky_pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    }

    public function get_bookOrderBanner_by_lang($lang_id=12,$dest_id)
    {
        $sql = <<<SQL
SELECT * FROM t_app_order_recommended_theme where status=1 AND  multilang=:multilang AND dest_id=:dest_id  AND page_name='book_order_success' order by priority
SQL;
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute(array(
            'multilang' => $lang_id,
            'dest_id' =>$dest_id,
        ));
        return $stmt->fetchAll();

    }

}