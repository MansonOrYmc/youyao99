<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 16/2/3
 * Time: 下午2:51
 */

class So_NiceClean{

    public static function clean_Array($array,$keys){
        if(!is_array($array)) return $array;
        foreach($array as $key=>$value)
        {
            if(!in_array($key,$keys) and (!is_numeric($key))){
                unset($array[$key]);
            }else{
                $array[$key] = self::clean_Array($value,$keys);
            }
        }
        return $array;
    }
}