<?php
require_once CORE_PATH . 'classes/Solr/Service.php';

class Bll_Theme_ThemeInfo
{

    private $solr_host;
    private $solr_port;
    private $homeinfo;
    public function __construct()
    {
        $this->solr_host = APF::get_instance()->get_config('solr_host');
        $this->solr_port = APF::get_instance()->get_config('solr_port');
        $this->homeinfo = new Bll_Homepage_HomepageInfo();
    }

    public function acquire_theme_info_by_data($items)
    {
        $ids = "";
        foreach ($items as $item) {
            $ids = $ids . $item['homeId'] . " OR ";
        }
        $solr = new Apache_Solr_Service($this->solr_host, $this->solr_port, '/search/user/');
        $query = "*:*";
        $params = array(
            'wt' => ' json',
            'fq' => "id:(" . $ids . " 0)",
        );
        $solr_result = $solr->search($query, 0, 100, $params);

        $data = json_decode($solr_result->getRawResponse(), true);
        $data = $data['response']['docs'];
        $results = array();
        foreach ($data as $key => $value) {
            $result = array();
            $result['uid'] = $value['id'];
            $result['name'] = $value['username'];
            $region = end($value['location_typename']);
            $result['region'] = empty($region) ? $value['loc_typename'] : $region;
            $result['sid'] = $value['pid'];
            $result['headPic'] = Util_Image::imglink($value['user_photo_file'], "userphotomedium.jpg");
            $result['speed_room'] = $value['hs_speed_room_i'] ? 1 : 0;
            $result['address'] = $value['address'];

            $home_stay_images = $this->homeinfo->get_home_stay_images($value['id']);
            $home_stay_images = array_map(function ($row) {
                if($row['new_uri']) {
                    $row['uri'] = Util_Image::get_imgsrc_by_name($row['new_uri'], 'roompic.jpg');
                }else{
                    $row['uri'] = Util_Image::get_imgsrc_by_name($row['uri'], 'roompic.jpg');
                }
                return $row;
            }, $home_stay_images);
            $result['cover'] = $home_stay_images[0]['uri'];
            $result['lowestPrice'] = Util_Common::zzk_cn_price_convert($value['int_room_price'], $_REQUEST['multiprice']);
            $result['commentCount'] = $value['hs_comments_num_i'];
            $results[$result['uid']] = $result;
        }
        return $results;
    }

    public function get_theme_list_by_dest_id($dest_id, $multi_lang = 12)
    {
        $theme_dao = new Dao_Theme_ThemeInfo();
        $raw_theme = $theme_dao->get_theme_by_dest_id($dest_id, $multi_lang);
        $theme_id_arr = array_column($raw_theme, 'id');
        $theme_count_arr = $theme_dao->count_theme_list($theme_id_arr);
        $theme_count = array();
        $theme_list = array();
        foreach ($theme_count_arr as $row) {
            $theme_count[$row['theme_id']] = $row['homestay_count'];
        }
        foreach ($raw_theme as $theme) {
            $theme_list[] = array(
                'id' => $theme['id'],
                'type' => $theme['type'],
                'themeId' => $dest_id . '00' . $theme['id'],
                'themeName' => $theme['name'],
                'themePic' => $theme['img_url'],
                'themeSubTitle' => $theme['sub_title'],
                'homestayNum' => array_key_exists($theme['id'], $theme_count) ? $theme_count[$theme['id']] : 0,
                'delta' => $theme['delta'],
            );
        }
        return $theme_list;
    }

    public function get_theme_list_by_id($theme_id, $dest_id, $multi_lang = 12)
    {
        $lang = $multi_lang == 10 ? 'zh-tw' : 'zh-cn';
        $theme_dao = new Dao_Theme_ThemeInfo();
        $raw_theme = $theme_dao->get_theme_by_id($theme_id);
        $theme_id_arr = array($raw_theme['id']);
        $theme_count_arr = $theme_dao->count_theme_list($theme_id_arr);
        $theme_count = array();
        foreach ($theme_count_arr as $row) {
            $theme_count[$row['theme_id']] = $row['homestay_count'];
        }
        $theme = $raw_theme;
        $theme_list = array(
            'themeId' => $dest_id . '00' . $theme['id'],
            'themeName' => Util_Common::zzk_translate($theme['name'], $lang),
            'themePic' => $theme['img_url'],
            'themeSubTitle' => $theme['sub_title'],
            'homestayNum' => array_key_exists($theme['id'], $theme_count) ? $theme_count[$theme['id']] : 0,
        );
        return $theme_list;
    }

    public function get_homestay_by_theme_id($theme_id)
    {
        $theme_dao = new Dao_Theme_ThemeInfo();
        $raw_theme_homestay_arr = $theme_dao->get_homestay_by_theme_id($theme_id);
        $homestay_list = array();
        foreach ($raw_theme_homestay_arr as $row) {
            $homestay_list[] = array(
                'homeId' => $row['homestay_uid'],
                'content' => $row['content'],
            );
        }
        return $homestay_list;
    }


    public function get_data_by_lang($lang_id){
        $theme_dao=new Dao_Theme_ThemeInfo();
        return $theme_dao->get_data_by_lang($lang_id);
    }

    public function get_total_global_data()
    {
        $theme_dao = new Dao_Theme_ThemeInfo();
        return $theme_dao->get_total_global_data();
    }

    public function insert_or_update_global_index_data($data)
    {
        $dao_theme = new Dao_Theme_ThemeInfo();
        if (array_key_exists('id', $data)) {
            $id = $data['id'];
            unset($data['id']);
            return $dao_theme->update_global_index_data($id, $data);
        } else
            return $dao_theme->insert_global_index_data($data);
    }






}
