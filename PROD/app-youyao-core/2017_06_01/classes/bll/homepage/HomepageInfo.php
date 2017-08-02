<?php
require_once(CORE_PATH . 'classes/Solr/Service.php');

class Bll_Homepage_HomepageInfo {

	private $homepagedao;
    private $pagecache;
	
	public function __construct() {
		$this->homepagedao = new Dao_Homepage_HomepageMemcache();
        $this->pagecache = array();
	}
	
	public function zzk_homepage_big_pic($preview = 0) {
		return $this->homepagedao->homepage_cache($preview);
	}

	public function get_dest_list() {
		return $this->homepagedao->get_dest_list();
	}

	public function get_loc_list() {
		return $this->homepagedao->get_loc_list();
	}

    public function get_home_stay_images($id, $limit = 100) {
        return $this->homepagedao->get_home_stay_images($id, $limit);
    }
    public function homepage_data_cache()
    {
        return $this->homepagedao->homepage_data_cache();
    }
	/*
	 *首单优惠
	 */
	public function is_homestay_firstorder($uid){
		return $this->homepagedao->is_homestay_firstorder($uid);
	}

    public function homepage_type_enum($e){
        $type = array(
            'bigBanner' => 5,
            'hotArea' => 6,
            'boutiqueHome' => 7,
            'tipsLegend' => 8,
            'discountHome' => 9,
            'bottomBanner' => 10,
        );
        return $type[$e];
    }

    public function homepage_cache_all($preview) {
        $data = $this->homepagedao->homepage_cache_all($preview);
        $result = $data;
        $editing = array();
        if($preview == 1) {
            $result = array();
            foreach($data as $r) {
                if($r['status'] == '2') {
                    $editing[] = $r['type'];
                }
            }
            foreach($data as $r) {
                if(in_array($r['type'], $editing) && $r['status'] == 1) continue;
                $result[] = $r;
            }
        }
        return $result;
    }

    public function homepage_cache_type($type = null) {
        $preview = 0;
        if($_REQUEST['preview']) {
            $apf = APF::get_instance();
            $request = $apf->get_request();
            $client_ip = $request->get_client_ip();
            $allow_patterns = @$apf->get_config("debug_allow_patterns");
            if (is_array($allow_patterns)) {
                foreach ($allow_patterns as $pattern) {
                    if (preg_match($pattern, $client_ip)) {
                        $preview = 1;
                    }
                }
            }
        }

        if($this->pagecache) {
            return $this->pagecache[$type] ? $this->pagecache[$type] : array();
        }
        $all_data = $this->homepage_cache_all($preview);
        $result[$type] = array();
        foreach($all_data as $r) {
            $data = json_decode($r['htmlcontent'], true);
            if(is_array($data)) {
                $result[$r['type']][] = $data;
            }else{
                $result[$r['type']][] = $r['htmlcontent'];
            }
        }
        $this->pagecache = $result;

        return $result[$type];
    }

    public function carousel_banner() {
        $cache = $this->homepage_cache_type(5);
        $data = $cache;
        foreach($data as $r) {
            $row = array(
                'link' => $r['link'],
                'img' => $r['img'],
                'name' => Trans::t($r['name_key']),
            );
            $result[] = $row;
        }
        return $result;
    }

    public function hot_area() {
        $cache = $this->homepage_cache_type(6);
        $data = $cache;
        foreach($data as $r) {
            if($r['type'] == 'custom') {
                $subtitle = Trans::t($r['subtitle']);
            }else{
                $subtitle = Trans::t("total_%a_bnb", null, array('%a'=>$r['num']));
            }

            $row = array(
                'name' => Trans::t($r['name_key']),
                'subtitle' => $subtitle,
                'img' => $r['img'],
                'link' => $r['link'],
            );
            $result[] = $row;
        }
        return $result;
    }

    public function boutique_homestay() {
        $cache = $this->homepage_cache_type(7);
        $uids = array_column($cache, "uid");

        //从solr搜索，防止下架的情况
        $data = $this->homestay_by_solr($uids);

        return $data;
    }

    public function tips_legend() {
        $cache = $this->homepage_cache_type(8);
        return $cache;
    }

    public function discount_homestay() {
        $cache = $this->homepage_cache_type(9);
        $uids = array_column($cache, "uid");

        //从solr搜索，防止下架的情况
        $data = $this->homestay_by_solr($uids);

        return $data;
    }

    public function bottom_banner() {
        $cache = $this->homepage_cache_type(10);
        return $cache;
    }

    public function homestay_by_solr($uids) {
        if(empty($uids)) return array();
        $solr_host = APF::get_instance()->get_config('solr_host');
        $solr_port = APF::get_instance()->get_config('solr_port');
        $solr = new Apache_Solr_Service($solr_host, $solr_port, '/search/user/');
        $query = "";
        foreach($uids as $k => $r) {
            if($query) $query .= " OR ";
            $query .= "id:$r";
        }
        $columns = array(
            'id',
            'username',
            'int_room_price',
            'hs_comments_num_i',
            'hs_rating_avg_i',
            'default_image_s',
        );
        $params = array(
            'fq' => $query,
            'fl' => implode(",", $columns),
            'wt' => 'json',
        );
        $solr_response = $solr->search("*:*", 0, 50, $params);
        $solr_result = json_decode($solr_response->getRawResponse(), true);
        $list = $solr_result['response']['docs'];
        foreach($list as $r) {
            $r['default_image_s'] = str_replace("http://taiwan.youyao.com/sites/default/files/styles/galleryformatter_slide/", "", $r['default_image_s']);
            $row = array(
                'name' => $r['username'],
                'star' => $r['hs_rating_avg_i'],
                'price' => Price::tostr(Price::c($r['int_room_price']), false, true),
                'review' => ($r['hs_comments_num_i'] > 0 ) ? Trans::t('%q_comments', null, array('%q' => $r['hs_comments_num_i'])) : Trans::t('No_comments') ,
                'img' => Util_Image::get_imgsrc_by_name($r['default_image_s'], "roompic.jpg"),
                'link' => "/h/". $r['id'],
            );
            $key = array_search($r['id'], $uids); //排序用
            $result[$key] = $row;
        }
        ksort($result);

        return $result;
    }

    public function add_new_item_by_type($data, $type) {
        $this->modify_front_page_status(0, array('status' => 2, 'type' => $type));
        foreach($data as $k=>$r) {
            if($type == 5) {
                $data = array(
                    'img' => $r['img'],
                    'link' => $r['link'],
                    'name_key' => $r['name_key'],
                );
            }
            elseif($type == 6) {
                $data = array(
                    "hot_id"  => $r['hot_id'],
                    "area_id" => $r['area_id'],
                    "num"  => $r['num'],
                    "img"  => $r['img'],
                    "link" => $r['link'],
                    "type" => $r['type'],
                    "subtitle" => $r['subtitle'],
                    "name_key" => $r['name_key'],
                );
            }
            elseif($type == 7 || $type == 9) {
                $data = array(
                    'uid' => $r['uid'],
                );
            }
            elseif($type == 8) {
                $data = array(
                    'img'  => $r['img'],
                    'link' => $r['link'],
                    'description' => $r['description'],
                );
            }
            elseif($type == 10) {
                $data = array(
                    'img' => $r['img'],
                    'link' => $r['link'],
                );
            }

            if($data) {
                $this->add_new_item($type, json_encode($data), $k);
            }
        }
    }

    // 新增
    public function add_new_item($type, $htmlcontent, $weight, $status = 2, $dest_id = 10) {
        $user = Util_Signin::get_user();
        $admin_uid = $user->uid;
        return $this->homepagedao->add_new_item($type, $htmlcontent, $weight, $status, $dest_id, $admin_uid);
    }

    // 回滚
    public function rollback(){
        $all_data = $this->homepage_cache_all(1);
        $clear_type = array();
        foreach($all_data as $r) {
            if($r['status'] == 2) {
                $clear_type = $r['type'];
            }
        }
        $clear_type = array_unique($clear_type);
        foreach($clear_type as $r) {
            $this->modify_front_page_status(0, array('status' => 2, 'type'=>$r));
        }
    }

    // 发布
    public function release(){
        $all_data = $this->homepage_cache_all(1);
        $clear_type = array();
        foreach($all_data as $r) {
            if($r['status'] == 2) {
                $clear_type[] = $r['type'];
            }
        }
        $clear_type = array_unique($clear_type);
        foreach($clear_type as $r) {
            $this->modify_front_page_status(0, array('status' => 1, 'type'=>$r));
            $this->modify_front_page_status(1, array('status' => 2, 'type'=>$r));
        }
        $this->homepagedao->clear_page_cache();
    }

    // 只更改状态，不改其他信息
    public function modify_front_page_status($status, $condition) {
        return $this->homepagedao->modify_front_page_status($status, $condition);
    }


}
