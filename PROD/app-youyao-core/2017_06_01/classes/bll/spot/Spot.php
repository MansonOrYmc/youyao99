<?php
class Bll_Spot_Spot {
    private $dao;
    public function __construct() {
       $this->dao    = new Dao_Homestay_SpotMemcache();
    }
   
    public function get_spot_byid($destid=0,$locid=0){
   	return  $this->dao->get_spot_byid($destid,$locid);
    }
   
    public function get_spot_bypid($id){
   	return  $this->dao->get_spot_bypid($id);
    }

    public function get_t_loc_poi($locid = '', $id = null, $dest_id = 10) {
       return $this->dao->get_t_loc_poi($locid, $id, $dest_id);
    }

    public function get_t_room_model_byid($id) {
       return $this->dao->get_t_room_model_byid($id);
    }

    public function get_t_room_price() {
       return $this->dao->get_t_room_price();
    }

    public function get_t_room_model() {
       return $this->dao->get_t_room_model();
    }

    public function t_loc_poi_all($status=1) {
        return $this->dao->t_loc_poi_all($status);
    }

    public function t_loc_type_all($status=1) {
        return $this->dao->t_loc_type_all($status);
    }

    public function zzk_homestay_service_types() {

        return array(
            //array('id' => 0, 'name' => Trans::t('any')),
            //array('id' => 10, 'name' => '有特色'),
            array('id' => '3', 'name' => Trans::t('baoche_server_check'), "key" => "baoche"),
            array('id' => '2', 'name' => Trans::t('homestayjiesong'), "key" => "jiesong"),
            array('id' => '1', 'name' => Trans::t('food_service'), "key" => "food"),
            array('id' => '5', 'name' => Trans::t('daiding'), "key" => "booking"),
            array('id' => '8', 'name' => Trans::t('outdoor_service'), "key" => "outdoors"),
            array('id' => '4', 'name' => Trans::t('other_service'), "key" => "otherService"),
            array('id' => '7', 'name' => Trans::t('mandarin_service'), "key" => "translation"),
            //array('id' => 6, 'name' => '青年旅社'),
        );
    }

}
