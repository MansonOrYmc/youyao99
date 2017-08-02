<?php

class Bll_Images_Info
{

    private $imagesdao;

    public function __construct()
    {
        $this->imagesdao = new Dao_Images_InfoMemcache();
    }

    public function get_images_byfid($fids)
    {
        if (empty($fids)) {
            return;
        }

        if (!is_array($fids)) {
            $fids = array($fids);
        }

        foreach ($fids as $v) {
            if (!$v) {
                continue;
            }

            if (Util_Image::img_version($v)) {
                $pic[] = $v;
            } else {
                $oldpic[] = $v;
            }
        }

        $a = $this->imagesdao->get_multi_file_managed($oldpic);
        $b = $this->imagesdao->get_multi_t_img_managed($pic);
        $all = array_merge($a ? $a : array(), $b ? $b : array());

        $data = array();
        $result = array();
        foreach ($all as $row) {
            $data[$row['fid']] = $row['uri'];
        }

        foreach ($fids as $id) { // 保持与传进来的顺序不变
            $result[$id] = $data[$id];
        }

        return $result;
    }

    public function update_images($homestay_uid, $img_arr)
    {
        $this->imagesdao->update_homestay_images($homestay_uid, $img_arr);
    }

    public function update_room_images($room_id,$img_arr){
        $this->imagesdao->update_room_images($room_id, $img_arr);
    }
}
