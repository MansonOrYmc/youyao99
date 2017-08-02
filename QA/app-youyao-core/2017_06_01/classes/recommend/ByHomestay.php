<?php
class Recommend_ByHomestay {

    private $uid;

    public function __construct($uid) {
      $this->uid = $uid;
    }

    public function get_other_room_recommend_list($order_id, $nid=null, $check_in=null, $check_out=null, $mutiprice=12) {
        $result = array();
        $room_info_bll = new Bll_Room_RoomInfo();
        $room_static_bll = new Bll_Room_Static();
        $so_nicerecommend_bll = new So_NiceRecommend();
        #$room_list = $room_info_bll->get_roominfo_by_uids_withoutspeed($this->uid, 1);
        $room_list_str = $so_nicerecommend_bll->get_recommend_rooms($order_id);
        if(!$room_list_str) {
            return $result;
        }
        $room_list = explode(",", $room_list_str);
        foreach($room_list as $row) {
            $row = (int)$row;
            if($row == $nid) continue;
            $name = $room_static_bll->get_room_title_by_nid($row);
            $lowprice = $room_static_bll->get_lowest_room_price($checkin,$checkout,$row,false,$mutiprice);
            $pic = reset($room_info_bll->get_image_uri_by_nid($row, 1));
            $result[] = array(
                'id' => $row,
                'title' => $name,
                'low_price' => $lowprice,
                'first_pic' => $pic,
            );
        }
        return $result;
    }
}
