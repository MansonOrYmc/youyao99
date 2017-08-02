<?php
apf_require_class("APF_DecoratorPage");
abstract class FrontPage extends APF_DecoratorPage {

    protected function show_dw() {
        return false;
    }
    
    protected function show_rightbar() {
        return false;
    }

    public static function use_javascripts() {
        return array(
         array("APF.js", PHP_INT_MAX, true)
        );
    }

    public function get_decorator() {
        return "decorator/FrontMain";
    }
	public function get_ajax_url(){
		$host = $_SERVER['HTTP_HOST'];
		if(preg_match('/^user/', $host)){
			return Url_CommonUrl::build_basic_user();
		}elseif(preg_match('/^www/', $host)){
			return Url_BasicUrl::build_base_url('www');
		}else{
			return Url_BasicUrl::build_base_url();
		}
	}
	
//    public static function use_boundable_javascripts() {
//        return array(array("Front.js", PHP_INT_MAX));
//    }

    public static function use_boundable_styles() {
        return array(array("Front.css", PHP_INT_MAX));
    }

    public function get_head_sections() {
        return array_merge(
            parent::get_head_sections()
        );
    }

    public function get_mainnav(){
        return '';//'Global_MainNav';
    }

    public function get_header() {
        return '';
    }

    public function get_footer() {
        return '';
    }

    public function get_ga_code() {
        $city_set = APF::get_instance()->get_request()->load_city_set();
        return $city_set['gacode'];
    }

    public function get_city_set() {
        $city_set = APF::get_instance()->get_request()->load_city_set();
        return $city_set;
    }

    public function get_actived_tab() {
        return null;
    }

    public function is_metro_tab(){
        return false;
    }

    public function is_special_header(){
        return APF::get_instance()->get_request()->is_special_header();
    }

    public function get_meta_keywords() {
        return "";
    }

    public function get_meta_description() {
        return "";
    }

    public function get_meta_other () {
        return "";
    }

    public function get_page_name(){
        $page_name = APF::get_instance()->get_request()->get_page_name();
        return $page_name;
    }

    public function get_class(){
        return get_class($this);
    }

    public function get_google_statistic() {
        return "";
    }

    public function get_config($key=NULL, $name, $file='common'){
        $config = APF::get_instance()->get_config($name, $file);
        return is_null($key)?$config:$config[$key];
    }

    /**
     * 判断是否是home页
     * @return string
     */
    public function isHomePage() {
        return false;
    }

    /**
     * 获得soj发送的js的url
     */
    public function getSojJsUrl(){
        $sojDomain = APF::get_instance()->get_config("sojDomain");
        $sojDomain =  $sojDomain ? $sojDomain : "s.anjuke.com";
        return "http://$sojDomain/bb.js";
    }
}