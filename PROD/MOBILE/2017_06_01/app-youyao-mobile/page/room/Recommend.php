<?php
apf_require_page("Front");

class Room_RecommendPage extends FrontPage {

    public static function use_boundable_javascripts() {
        $path = apf_classname_to_path(__CLASS__);
        return array_merge(
            parent::use_boundable_javascripts(),
            array($path."Recommend.js")
        );
    }

    public static function use_boundable_styles() {
        $path = apf_classname_to_path(__CLASS__);
        return array_merge(array($path."Recommend.css"));
    }

    public function get_title () {
        return "自在客";
    }

    public function get_view(){
        $request = APF::get_instance()->get_request();
        $attributes = $request->get_attributes();
        foreach ($attributes as $key=>$attr) {
             $this->assign_data($key, $attr);
        }
        return "Recommend";
    }
}