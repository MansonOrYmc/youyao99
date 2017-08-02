<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 15/11/11
 * Time: 下午5:35
 * 添加根据namecode获取dest_id方法！！！
 */
class So_Hot{
    //热点类
    //所有区县框内容从这里走！！
    //POI 以商圈形式查
    //LOC 以区县形式查
    public  $type = array(
        'SCENIC_SPOTS' => 'POI',
        'BUSINES_CIRCLE' => 'POI',
        'SPORTVAN' => 'POI',
        'REGION' => 'LOC',
        'CITY' => 'LOC',
        'STATE' => 'LOC',
    );

    public $conf = array(
        '阿里山' => array('name_code'=>'alishan','locid'=>27736,'type_name'=>'阿里山'),
        '垦丁'  => array('name_code'=>'kending','locid'=>60506,'type_name'=>'垦丁'),
        '马祖'  => array('name_code'=>'mazhu','locid'=>60507,'type_name'=>'马祖'),
        '日月潭' => array('name_code'=>'riyuetan','locid'=>60512,'type_name'=>'日月潭'),
        '九份'  => array('name_code'=>'jiufen','locid'=>60511,'type_name'=>'九份'),
        '清境'  => array('name_code'=>'qingjing','locid'=>60513,'type_name'=>'清境'),
        '绿岛'   => array('name_code'=>'lvdao','locid'=>60517,'type_name'=>'绿岛'),
        '小琉球' => array('name_code'=>'xiaoliuqiu','locid'=>60518,'type_name'=>'小琉球'),
        '兰屿'  => array('name_code'=>'lvdao','locid'=>60519,'type_name'=>'兰屿'),
    );

    public function get_hot_list($dest_id){
        //需要为移动端做特殊处理
        $dao = new Dao_Search_Dest();
        $s_city = $dao->get_hot($dest_id);
        foreach($s_city as $obj){
            $result = array();
            $result['type_name']=$obj->recommendName;
            $result['name_code']=$obj->engName;
            $result['dest_id']=$obj->destId;
            if($this->type[$obj->recommendType]=='POI')
            {
                $result['type_name']=$this->conf[$obj->recommendName]['type_name'];
                $result['name_code']=$this->conf[$obj->recommendName]['name_code'];
                $result['locid']=$this->conf[$obj->recommendName]['locid'];
            }
            else{
                $result['locid']=$obj->relationId;
            }
            $results[] = $result;
        }
        return $results;
    }
    public function get_hot_list_4_web($dest_id){
        $dao = new Dao_Search_Dest();
        $s_city = $dao->get_hot($dest_id);
        foreach($s_city as $obj){
            $result = array();
            $result['type_name']=$obj->recommendName;
            $result['name_code']=$obj->engName;
            $result['dest_id']=$obj->destId;
            if($this->type[$obj->recommendType]=='POI')
            {
                $result['sight_id']=$obj->relationId;
            }elseif($this->type[$obj->recommendType]=='LOC')
            {
                $result['locid']=$obj->relationId;
            }
            $results[] = $result;

        }
        return $results;
    }

    public function get_destid_by_namecode($namecode){
        //如果实在需要destid，但是只有namecode的情况下，选择调用
        $dao = new Dao_Search_Dest();
        $result = array();
        $s_city = $dao->get_hot('all');
        foreach($s_city as $obj){
            if($namecode ==  $obj->engName) return $obj->destId;
        }
        return 10;
    }
    public function get_dest_by_namecode($namecode){
        //如果实在需要dest，但是只有namecode的情况下，选择调用
        $dao = new Dao_Search_Dest();
        $result = array();
        $s_city = $dao->get_hot('all');
        foreach($s_city as $obj){
            if($namecode ==$obj->engName && $this->type[$obj->recommendType]=='LOC')
            {
                return array('locid'=>$obj->relationId,'name_code'=>$obj->engName,'type_name'=>$obj->recommendName);
            }elseif($namecode ==$obj->engName){
                return array('locid'=>$this->conf[$obj->recommendName]['locid'],'name_code'=>$obj->engName,'type_name'=>$obj->recommendName);
            }
        }
        return 10;
    }
}
