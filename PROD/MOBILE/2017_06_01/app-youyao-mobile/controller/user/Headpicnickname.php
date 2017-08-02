<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/12/15
 * Time: 上午11:33
 */
apf_require_class("APF_Controller");

class User_HeadpicnicknameController extends APF_Controller {

    public function handle_request() {

        header("Content-type: application/json; charset=utf-8");

        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();
        $uids = $params['uid'];
        $uids =  explode(",",$uids);
        $r = self::handle_headPic($uids);

        if ($params['beauty'] == 'true') {
            $jsonData = array(
                'status' => 200,
                'data' => $r
            );
            echo(json_encode(Util_Beauty::wanna($jsonData)));
        } else {
            echo(json_encode($r));
        }
    }

    private function handle_headPic($uids) {
        $bll_userInfo = new Bll_User_UserInfo();
        $userInfoDao = new Dao_User_UserInfoMemcache();
        if(is_array($uids)&&count($uids)>0){
            foreach($uids as $uid){
                $userInfo = $bll_userInfo->get_user_head_pic_by_uid($uid);
                $nickname = $userInfoDao->get_user_nickname_by_uid($uid);
                $username = $userInfoDao->get_username_by_uid($uid);
                if(!$nickname){
                    $nickname = '';
                }
                if(!$userInfo){
                    $userInfo = Util_Avatar::dispatch_avatar($uid);
                }
                $returnJSON[] = array('uid'=>$uid,'headPic'=>$userInfo,'nickname'=>$nickname,'username'=>$username);
            }
            return $returnJSON;
        }
    }


}

?>
