<?php
/**
 * Created by PhpStorm.
 * User: xue
 * Date: 16/8/4
 * Time: 下午2:42
 */
class Bll_Theme_Banner{

    public function get_bookOrderBanner_by_lang($multi_lang = 12,$dest_id)
    {
        $banner_dao = new Dao_Theme_Banner();
        $banners = $banner_dao->get_bookOrderBanner_by_lang($multi_lang,$dest_id);
        return $banners;
    }

}