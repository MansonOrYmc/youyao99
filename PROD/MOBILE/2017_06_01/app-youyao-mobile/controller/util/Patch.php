<?php
/**
 * Created by PhpStorm.
 * User: chanlevel
 * Date: 16/4/12
 * Time: 下午2:23
 */
apf_require_class("APF_Controller");

class Util_PatchController extends APF_Controller
{

    /**
     * 子类通过实现本方法添加业务逻辑
     * @return mixed string|array 直接返回字符串表示页面类名称；返回数组包含
     * 两个成员，第一个是页面类名称，第二个为页面类使用的变量。
     * @example 返回'Hello_Apf_Demo'，APF会加载Hello_Apf_DemoPage类。
     * @example 返回array('Hello_Apf_Demo', array('foo' => 'bar'))，APF会加载
     * Hello_Apf_Demo类，而且在对应的phtml文件中可以直接使用变量$foo，其值为'bar'。
     *
     * 注意，返回字符串是为了兼容旧有代码，不推荐使用。
     */
    public function handle_request()
    {
        // TODO: Implement handle_request() method.
        header("Content-type: application/json");

        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();

        $os = $params['os'];
        $version = $params['version'];

        if(empty($os)||empty($version)){
            Util_Json::render(400,null);
            return;
        }

        $patchVersion = $version . '_' . '1';
        $data = array(
            'need' => 1,
            'patchVersion' => $patchVersion,
            'url' => "",
            'version' => $version
        );


        
        if ($data['need'])
            Util_Json::render(200, $data);
        else Util_Json::render(400, null);


    }
}