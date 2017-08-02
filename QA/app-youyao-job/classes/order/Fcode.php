<?php
class Order_Fcode
{
	private $pdo;
    public function run()
    {
    	echo "start\n";
    	$this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    	$iv_lists= $this->get_f_list();   
    	foreach ($iv_lists as $key=>$value){
                $url='http://taiwan.zizaike.com/fanti.php?w='.urlencode($value['l_desc']);
    			$this->update_order_succ($value['id'],$this->curl_get($url));
    	}

    }
    
    public function get_f_list() {
        $sql = "select * from m_dest_language where dest_id=10 and id not in (342,345,348,351,353,23,62,29,11,14,17,26,113,116,131,837,134,835,92,95,98,101,104,107)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    

    
    public function update_order_succ($id,$tw){
        $sql = "update m_dest_language set l_desc='".trim($tw)."' where id=".$id;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        echo $sql."\n";
    }

    public function curl_get($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
    
}