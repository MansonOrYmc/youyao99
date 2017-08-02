<?php
class Bll_Room_RoomImage {
    private $roomdao;
    private $utilimage;

    public function __construct() {
        $this->imagedao    = new Dao_Room_Image();
        $this->utilimage = new Util_Image();
    }

    public function get_room_image($rid){
        $list = $this->imagedao->get_field_data_field_image($rid);
        
        $oldpic = array();
        $pic = array();
        foreach($list as $row) {
            if($row['field_image_version'] || $this->utilimage->img_version($row['field_image_fid'])) {
                $pic[] = $row['field_image_fid'];
            } else {
                $oldpic[] = $row['field_image_fid'];
            }
        }

        $result = (array) array_values($this->imagedao->get_multi_file_managed($oldpic)) + (array) array_values($this->imagedao->get_multi_t_img_managed($pic));

        return $result;
     }

}
