<?php
class ZzkRequest extends APF_Request {
    public function __construct() {
        parent::__construct();
        $this->timestamp = time();
        $this->userid    = 0;
        require_once(CORE_PATH . "classes/includes/Session.php");
        session_initialize();
    }
    
    private function reserved_ip($ip) {
        $reserved_ips = array( // not an exhaustive list
            '167772160'  => 184549375,  /*    10.0.0.0 -  10.255.255.255 */
            '3232235520' => 3232301055, /* 192.168.0.0 - 192.168.255.255 */
            '2130706432' => 2147483647, /*   127.0.0.0 - 127.255.255.255 */
            '2851995648' => 2852061183, /* 169.254.0.0 - 169.254.255.255 */
            '2886729728' => 2887778303, /*  172.16.0.0 -  172.31.255.255 */
            '3758096384' => 4026531839, /*   224.0.0.0 - 239.255.255.255 */
        );
    
        $ip_long = sprintf('%u', ip2long($ip));
    
        foreach ($reserved_ips as $ip_start => $ip_end) {
            if (($ip_long >= $ip_start) && ($ip_long <= $ip_end)) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    public function get_remote_ip() {
        $ret = null;
        if($_SERVER['HTTP_X_FORWARDED_FOR']) {
            foreach(explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']) as $ip) {
                if($ret) continue;
                if(self::reserved_ip($ip)) continue;
                $ret = $ip;
            }
        }
        return $ret ? $ret : $_SERVER['REMOTE_ADDR'];
    }
    
    public function get_userobject() {
        $userinfo = new Util_Signin();
        return $userinfo->get_user();
    }

    public function get_userid() {
        return $this->userid;
    }

    public function set_userid($value) {
        $this->userid = $value;
    }

    public function get_username() {
        return $this->username;
    }

    public function set_username($value) {
        $this->username = $value;
    }

    public function get_mobile() {
        return $this->mobile;
    }

    public function set_mobile($value) {
        $this->mobile = $value;
    }

    public function get_usertype() {
        return $this->usertype;
    }

    public function set_m_usertype($value) {
        $this->mutype = $value;
    }

    public function get_m_usertype() {
        return $this->mutype;
    }

    public function set_usertype($value) {
        $this->usertype = $value;
    }

    public function load_city_set () {
        $city_set = APF::get_instance()->get_config("city_set","multicity");
        return @$city_set[$this->get_cityid()];
    }
    public function get_city_set ($cityid='') {
        $id            = $cityid ? $cityid : $this->get_cityid();
        $city_set     = APF::get_instance()->get_config("city_set","multicity");
        return @$city_set[$id];
    }

    public function get_timestamp () {
        return $this->timestamp;
    }

    public function set_timestamp ($t) {
        $this->timestamp = $t;
    }

    public function set_guid ($guid) {
        $this->guid = $guid;
    }

    public function get_guid () {
        return $this->guid;
    }

    public function set_singleid ($singleid) {
        $this->singleid = $singleid;
    }

    public function get_singleid () {
        return $this->singleid;
    }

    public function get_current_url () {
        return (@$_SERVER['HTTPS']?"https":"http")."://".$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
    }

    private $guid;
    private $userid;
    private $mem_userid;
    private $username;
    private $mem_username;
    private $login_from;
    private $usertype;
    private $mutype;
    private $timestamp;
    private $obtain_by;
    private $mobile;
    private $singleid;
    private $page_name;

    public function set_obtain_by($obtain_by) {
        $this->obtain_by = $obtain_by;
    }

    public function get_obtain_by() {
        return $this->obtain_by;
    }

    public function get_cityid() {
        return $this->cityid;
    }

    public function set_cityid($value) {
        $this->cityid = $value;
    }

    private $cityid;

    public function get_ucityid() {
        return $this->ucityid;
    }

    public function set_ucityid($value) {
        $this->ucityid = $value;
    }

    private $ucityid;

    // override
    public function load_parameters() {
        if ($this->parameters_match_number >= 0) {
            $matches = $this->get_router_matches();
            return array_merge(
                APF_Util_StringUtils::decode_seo_parameters($matches[$this->parameters_match_number]),
                $_GET,
                $_POST);
        } else {
            return parent::load_parameters();
        }
    }

    public function set_parameters_match_number($number) {
        $this->parameters_match_number = $number;
    }

    private $parameters_match_number = -1;

     public function get_mem_userid() {
        return $this->mem_userid;
    }

    public function set_mem_userid($value) {
        $this->mem_userid = $value;
    }

    public function get_mem_username() {
        return $this->mem_username;
    }

    public function set_mem_username($value) {
        $this->mem_username = $value;
    }

    public function get_login_from() {
        return $this->login_from;
    }

    public function set_login_from($value) {
        $this->login_from = $value;
    }

    public function set_page_name($name){
        $this->page_name = $name;
    }

    public function get_page_name(){
        return $this->page_name;
    }

    private $soj_commid;

    public function get_soj_commid() {
        return $this->soj_commid;
    }

    public function set_soj_commid($value) {
        $this->soj_commid = $value;
    }

    /**
     * 全局获取URL函数
     * @param string $path  除域名外的URL路径
     * @param string $domain 主域名
     * @param string $sub_domain 分城市域名
     */
    public function url($path, $domain = null, $sub_domain = null ){
        if ( $sub_domain === null ){
            $city_set = $this->load_city_set();
            $sub_domain = $city_set['pinyin'];
        }

        $sub_domain = $sub_domain . ".";

        if ( $domain === null ){
            $domain = APF::get_instance()->get_config('base_domain');
        }

        $is_secure = $this->is_secure() ? 'https://' : 'http://';

        return $is_secure . $sub_domain . $domain . $path;
    }

    /*经纪人 */
    public function set_broker_id($brokerid = ''){
        $brokerid = intval($brokerid);
        $this->brokerid = $brokerid ? $brokerid : 0;
    }

    public function get_broker_id(){
        return $this->brokerid;
    }

    public function set_broker_name($name = ''){
        $name = trim($name);
        $this->brokername = $name ? $name : '';
    }

    public function get_broker_name(){
        return $this->brokername;
    }

    public function set_broker_mobile($mobile = ''){
        $mobile = trim($mobile);
        $this->brokermobile = $mobile ? $mobile : '';
    }

    public function get_broker_mobile(){
        return $this->brokermobile;
    }


    public function set_broker_type($type = ''){
        $type = intval($type);
        $this->brokertype = $type ? $type : 0;
    }

    public function get_broker_type(){
        return $this->brokertype;
    }

    public function set_broker_cityid($cityid = ''){
        $cityid = intval($cityid);
        $this->brokercity = $cityid ? $cityid : 0;
    }

    public function get_broker_cityid(){
        return $this->brokercity;
    }

    public function set_broker_logintime($time = ''){
        $this->lastlogin = ('' != $time) ? $time : time();
    }

    public function get_broker_logintime(){
        return $this->lastlogin;
    }
    public function get_parameters(){
         return array_merge(
            APF_Util_StringUtils::decode_seo_parameters($_SERVER['REQUEST_URI']),
            $_GET,
            $_POST);
    }

    public function get_n_parameters(){
        if (!isset($this->n_parameters)) {
            $this->n_parameters = $this->parameters_loader->load_n_parameters();
        }
        return $this->n_parameters;
    }

    protected $n_parameters;

    public function load_n_parameters() {
        return array_merge(
            APF_Util_StringUtils::decode_parameters($_SERVER['REQUEST_URI']),
            $_GET,
            $_POST);
    }

    public function is_special_header() {
        $header_type = APF::get_instance()->get_config('is_special_header');
        if($header_type == 2){
            return true;
        }
        if($this->cityid == 11 && $header_type == 1){
            return true;
        }
        return false;
    }

    /**
     * 判断当前的城市是否开通了好盘业务
     * @return boolean
     */
    public function is_hp_city(){
        $hp_cities=array();
        $config=APF::get_instance()->get_config('haopan_rank_set','haopan_rank');
        if(isset($config)&&is_array($config)){
            $hp_cities=array_keys($config);
        }
        return in_array($this->cityid, $hp_cities);
    }

    /* 租房管家 */
    private $g_userid;
    private $g_cityid;
    private $g_truename;

    public function set_guanjia_userid($g_userid = ''){
        $g_userid = intval($g_userid);
        $this->g_userid = $g_userid ? $g_userid : 0;
    }
    public function get_guanjia_userid(){
        return $this->g_userid;
    }

    public function set_guanjia_cityid($g_cityid = ''){
        $g_cityid = intval($g_cityid);
        $this->g_cityid = $g_cityid ? $g_cityid : 0;
    }
    public function get_guanjia_cityid(){
        return $this->g_cityid;
    }

    public function set_guanjia_truename($g_truename = ''){
        $this->g_truename = $g_truename;
    }
    public function get_guanjia_truename(){
        return $this->g_truename;
    }

    public function add_n_parameter($key, $val) {
        $this->n_parameters[$key] = $val;
    }


    private $dest_id;
    private $dest_config;
    private $sub_domain;

    public function set_dest_id($dest_id) {
        $this->dest_id = $dest_id;
    }

    public function get_dest_id() {
        return $this->dest_id;
    }

    public function set_dest_config($dest_config) {
        $this->dest_config = $dest_config;
    }

    public function get_dest_config() {
        return $this->dest_config;
    }

    public function set_sub_domain($sub_domain) {
        $this->sub_domain = $sub_domain;
    }

    public function get_sub_domain() {
        return $this->sub_domain;
    }


}
?>
