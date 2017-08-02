<?php
apf_require_class("APF_Controller");

class Token_ValidateController extends APF_Controller {

    public function handle_request() {
        $apf = APF::get_instance();
        $req = $apf->get_request();
        $params = $req->get_parameters();

        if(!$params['user_token']) {
            Util_Json::render(458,null,"error token","登录状态过期，请重新登录");
            return;
        }

        $sign_dao = new Dao_User_Sign();
        $auth_info = $sign_dao->get_record_by_sid($params['user_token']);
        if($auth_info['status'] != 1) {
            Util_Json::render(459,null,"error token","用户被禁用");
            return;
        }

        $userinfo_bll = new Bll_User_UserInfo();
        $auth_info['nickname'] = $userinfo_bll->get_user_nickname_by_uid((int)$auth_info['uid']);
        $auth_info['role_id'] = $userinfo_bll->get_user_role_id((int)$auth_info['uid']);
        Util_Json::render(200, null, "correct token", "验证通过", true, $auth_info);
    }
}
