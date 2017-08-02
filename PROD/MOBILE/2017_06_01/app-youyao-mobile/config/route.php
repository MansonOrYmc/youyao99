<?php
$config['regex_function'] = 'ereg';
$config['404'] = 'Error_Http404';

//for robots
$config['mappings']['Robots_Robots'] = array(
    '^/robots.txt',
);

//for robots
$config['mappings']['Robots_MP'] = array(
    '^/MP_verify_D4Q9Fc6rr3FNqU8t.txt',
);

// test api
$config['mappings']['Test_ApiTest'] = array(
    '^/test/(.*)',
);

// hosptail yyid: A11EDA95534311E79E6C68F728954D54
$config['mappings']['Hospital_BaseInfo'] = array(
    '^/hospital/view/(.*)',
);

// location yyid: 6F86727E527411E79E6C68F728954D54 / 朝阳 / 422,1,2
$config['mappings']['Hospital_List'] = array(
    '^/hospital/list/(.*)/(\d+)',
);

$config['mappings']['Hospital_Edit'] = array(
    '^/user/hospital/edit/',
);

// doctor yyid: A11EDA95534311E79E6C68F728954D54
$config['mappings']['Doctor_BaseInfo'] = array(
    '^/doctor/view/(.*)',
);

// location yyid: 6F86727E527411E79E6C68F728954D54 / 朝阳 / 422,1,2
$config['mappings']['Doctor_List'] = array(
    '^/doctor/list/(.*)/(\d+)',
);

// drug yyid: A11EDA95534311E79E6C68F728954D54
$config['mappings']['Drug_BaseInfo'] = array(
    '^/drug/view/(.*)',
);

// location yyid: 6F86727E527411E79E6C68F728954D54 / 朝阳 / 422,1,2
$config['mappings']['Drug_List'] = array(
    '^/drug/list/(.*)/(\d+)',
);

$config['mappings']['Drug_Edit'] = array(
    '^/drug/edit/',
);

// department yyid: A11EDA95534311E79E6C68F728954D54
$config['mappings']['Department_BaseInfo'] = array(
    '^/department/view/(.*)',
);

// location yyid: 6F86727E527411E79E6C68F728954D54 / 朝阳 / 422,1,2
$config['mappings']['Department_List'] = array(
    '^/department/list/(.*)/(\d+)',
);

// 
$config['mappings']['Transaction_BaseInfo'] = array(
    '^/transaction/view/(.*)',
);

// 
$config['mappings']['Transaction_List'] = array(
    '^/transaction/list/(.*)/(\d+)',
);

// 
$config['mappings']['Location_BaseInfo'] = array(
    '^/location/view/(.*)',
);

$config['mappings']['Transaction_Edit'] = array(
    '^/transaction/edit/',
);

// location 
$config['mappings']['Location_List'] = array(
    '^/location/list/(.*)/(\d+)',
);

// upload files
$config['mappings']['Wechat_Uploadsingleformfile'] = array(
    '^/wechat/uploadsingleformfile',
);

// User Center
$config['mappings']['User_UserInfo'] = array(
    '^/userInfo',
);
$config['mappings']['User_ProfileEdit'] = array(
    '^/user/profile/edit',
);
$config['mappings']['User_ServeScope'] = array(
    '^/user/serve/scope/',
);
$config['mappings']['User_ServeList'] = array(
    '^/user/serve/list/',
);
$config['mappings']['User_ServeView'] = array(
    '^/user/serve/view/',
);
$config['mappings']['User_ProfileVerify'] = array(
    '^/user/profile/verify',
);
$config['mappings']['User_Profile'] = array(
    '^/user/profile',
);
$config['mappings']['User_HospitalActive'] = array(
    '^/user/hospitalactive',
);
$config['mappings']['User_Hospital'] = array(
    '^/user/hospital',
);
$config['mappings']['User_Collect'] = array(
    '^/usercollect',
); //done
$config['mappings']['User_AdminUserInfo'] = array(
    '^/adminUserInfo',
);
$config['mappings']['User_Headpicnickname'] = array(
    '^/user/userheadpicnickname',
); //done

$config['mappings']['User_Register'] = array(
    '^/user/register([\w\/]*)',
);
$config['mappings']['User_PhoneLogin'] = array(
    '^/user/phone/login([\w\/]*)',
);
$config['mappings']['User_Findphone'] = array(
    '^/user/findphone([\w\/]*)',
);
$config['mappings']['User_Captcha'] = array(
    '^/user/captcha/',
);

