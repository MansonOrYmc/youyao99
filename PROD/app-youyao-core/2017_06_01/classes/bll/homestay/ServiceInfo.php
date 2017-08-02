<?php

/**
 * Created by PhpStorm.
 * User: chanlevel
 * Date: 16/3/22
 * Time: 下午3:03
 */
class Bll_Homestay_ServiceInfo
{

    public static function service_by_homestayInfo ($homestay_info,$multi_price=12,$area=null){
        if(empty($homestay_info))return null;
        $homestay_uid=$homestay_info->id;
        $homestay_bll = new Bll_Homestay_StayInfo();

        $multi_price = empty($multi_price) ? 12 : intval($multi_price);
        if (empty($area)) {
            $bll_area = new Bll_Area_Area();
            $area = $bll_area->get_dest_config_by_destid($multi_price);
        }

        $all_service=json_decode($homestay_info->all_service_list_s,true);
        $list=array();
        foreach ($all_service as $service_name => $type) {
            foreach ($type as $k => $row) {

               // $multi_price = empty($param_arr['multiprice']) ? 12 : intval($param_arr['multiprice']);
                if ($multi_price == 12) {
                    $price = Util_Common::zzk_tw_price_convert($row['price'], $homestay_info->dest_id);
                } else {
                    $price = Util_Common::zzk_price_convert($row['price'], $homestay_info->dest_id, $multi_price);
                }
//                $service_img_arr = array_map(function ($v) {
//                    return Util_Image::img_url_generate($v, 1);
//                }, $row['images']);
//                $images = array_values($service_img_arr);
                $images=$row['images'];
                $data = array(
                    'service_id' => $row['id'],
                    'service_name' => $row['service_name'],
                    'type' => $row['free'],
                    'price' => $price,
                    'content' => $row['content'],
                    'currency_sym' => $area['currency_code'],
                    'name' => $row['title'],
                    'id' => $row['id'],
                    'images' => empty($images) ? null : $images,
                    'offlinePayment' => $row['have_rebate'] ? 0 : 1,

                );
                if($service_name =='other') {
                    $data['name'] = $row['service_name'];
                } 
                $list[$service_name][] = $data;
            }
        }
        $service['baoche']=$list['baoche'];
        $service['other_service']=$list['other'];
        $service['pickup_service']=$list['jiesong'];
        $service['catering_service'] = $list['zaocan'];
        $service['outdoor_service'] = $list['huwai'];
        $service['ticket_service'] = $list['daiding'];



        return $service;
    }


}
