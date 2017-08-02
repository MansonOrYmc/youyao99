<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 16/3/17
 * Time: 上午10:37
 */
class Teacher_Share{
    public static function is_tearher($uid){
        $array = array('12085','10934','130393','554','271','5061','70983','643');
        if(in_array($uid,$array)){
            return true;
        }else{
            return false;
        }
    }
}