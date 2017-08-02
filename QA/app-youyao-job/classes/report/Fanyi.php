<?php
class Report_Fanyi
{

    public function run(){
        $myfile = fopen("jp.txt", "r") or die("Unable to open file!");
        while(!feof($myfile)) {
            $str  =  fgets($myfile);
            $row  = explode('	',$str);
            $info = array('l_key'=>$row[0],'dest_id'=>11,'l_desc'=>$row[1]);
            $this->set_quxian_record($info);
        }
        fclose($myfile);
    }

    public function set_quxian_record($info){
        $pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $sql = "insert into m_dest_language(l_key,dest_id,l_desc) values(?,?,?)";
        $stmt = $pdo->prepare($sql);  echo $info['l_desc'];
        $stmt->execute(array($info['l_key'], $info['dest_id'], $info['l_desc']));
        return $pdo->lastInsertId();
    }

}