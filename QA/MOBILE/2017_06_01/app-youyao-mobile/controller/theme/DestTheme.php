<?php
/**
 * Created by PhpStorm.
 * User: chanlevel
 * Date: 16/7/4
 * Time: 上午11:16
 */
apf_require_class("APF_Controller");

class Theme_DestThemeController extends APF_Controller
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


    const MULTI_LANG_CN = 12;
    const MULTI_LANG_TW = 10;

    public function handle_request()
    {
        // TODO: Implement handle_request() method.
        $params = Apf::get_instance()->get_request()->get_parameters();

        $dest_id = $params['dest_id'];
        $multilang = $params['multilang'];
        if (empty($dest_id)) {
            Util_Json::render(400, null, "dest_id needed", "dest_id needed");
            return false;
        }

        $data = $this->get_theme_by_dest_id($dest_id, $multilang);
        $data[] = $this->theme_more($dest_id);

        Util_Json::render(200, $data);
    }

    private function get_theme_by_dest_id($dest_id, $multilang)
    {

        $theme_bll = new Bll_Theme_ThemeInfo();
        $results = $theme_bll->get_theme_list_by_dest_id($dest_id, $multilang);

        foreach ($results as $k) {
            $t=Theme_MultiThemeController::get_theme_object($k['themeId'], $k['themeName'], $k['themePic'], $k['homestayNum'], $k['themeSubTitle'], $k['type'], $k['id']);
            $list[$k['delta']] = $t;
        }

        return array_slice( array_values($list),0,6);
    }

    private function theme_more($dest_id)
    {
        $data = Theme_GlobalController::get_destination($dest_id, "http://7xkg3j.com1.z0.glb.clouddn.com/search_theme_bg.png");
        $data['title'] = " ";
        $data['type']='theme';
        return $data;

    }


}