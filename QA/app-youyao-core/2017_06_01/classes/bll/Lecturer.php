<?php

class Bll_Lecturer
{
    public static function is_lecturer($homestay_uid)
    {
        $key = 't_teacher_share';
        $mc = APF_Cache_Factory::get_instance()->get_memcache();
        $lecturer_arr = $mc->get($key);
        if (!$lecturer_arr) {
            $lecturer_dao = new Dao_Lecturer();
            $result = $lecturer_dao->all();
            $lecturer_arr = array_column($result, 'user_id');
            $time = Util_MemCacheTime::get_one_hour();
            $mc->set($key, $lecturer_arr, 0, $time);
        }

        if (in_array($homestay_uid, $lecturer_arr)) {
            return true;
        } else {
            return false;
        }
    }
}
