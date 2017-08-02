<?php
class PageHelper {

    public static function get_pure_static_url($uri){
        $apf = APF::get_instance();
        $host = @$apf->get_config("cdn_pure_static_host", "resource");
        $path = @$apf->get_config("cdn_pure_static_path", "resource");
        $schema = $apf->get_request()->is_secure() ? "https" : "http";
        return $host ? "$schema://$host$path$uri" : "$path$uri";
    }

    public static function static_url($uri) {
        $apf = APF::get_instance();
        $host = @$apf->get_config("cdn_host", "resource");
        $path = @$apf->get_config("cdn_path", "resource");
        $version = $apf->get_config('version', 'resource');
        $uri .= '?v='.$version;
        $schema = $apf->get_request()->is_secure() ? "https" : "http";
        return $host ? "$schema://$host$path$uri" : "$path$uri";
    }

    public static function pure_static_url($uri) {
        $apf = APF::get_instance();
        $host = @$apf->get_config("cdn_pure_static_host", "resource");
        $path = @$apf->get_config("cdn_pure_static_path", "resource");
        $version = @$apf->get_config("resource_version", "resource");
        if(@ereg('[0-9]{8}',$version)){
            $version="/".$version;
        }else{
            $version='';
        }
        $request = $apf->get_request();
        if ($request) {
            $schema = $request->is_secure() ? "https" : "http";
        } else {
            $schema = "http";
        }
        return $host ? "$schema://$host$path$version$uri" : "$path$version$uri";
    }
}