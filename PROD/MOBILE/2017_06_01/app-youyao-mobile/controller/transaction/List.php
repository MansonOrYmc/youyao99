<?php
apf_require_class("APF_Controller");

class Transaction_ListController extends APF_Controller
{
    private $pdo;

    public function __construct() {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("master");
    }

    public function handle_request()
    {

        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:SIG,DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");

        header("Content-type: application/json; charset=utf-8");

        $req = APF::get_instance()->get_request();
        $params = $req->get_parameters();
        
        $default_yyid = 'FS1001';
        if ( isset($params['yyid'])) {
                   $yyid = strtoupper($params['yyid']);
        } else {
                   $yyid = $default_yyid;
        }

        $page_num = 0;
        $page_size = 10;

        if (isset($params['page']) && is_numeric($params['page'])) {
           $page_num = intval($params['page']);
        }
        $page_num = $page_num <= 0 ? 1 : $page_num;
        $page_start = ($page_num - 1) * $page_size;
        $total = 0;

        $security = Util_Security::Security($params);

        if (!$security) {
            echo json_encode(Util_Beauty::wanna(array(
                'code' => 0,
                'codeMsg' => 'Illegal_request',
                'status' => 'fail',
                'msg' => "request forbidden",
                "userMsg" => 'Illegal_request',
            )));

            return false;
        }

        $transaction_list = array();


        $transaction_list = self::get_transaction_list($yyid, $page_size, $page_start);
        $total = self::get_transaction_count($yyid);


        echo json_encode(Util_Beauty::wanna(array(
            'code' => 1,
            'codeMsg' => 'normal_request',
            //"params" => $params,
            "page_num" => $page_num,
            "page_size" => $page_size,
            "total" => $total,
            "transaction_list" => $transaction_list,
        )));

        return false;
    }

    private function get_transaction_list($pid, $limit, $offset)
    {

        if(empty($pid) || !is_numeric($limit) || !is_numeric($offset)){
            return array();
        }

        $sql = "select yyid, h_yyid, d_yyid, datei, tid, name, specs, t_num, buyer_hospital, trans_date, hospital_id, product_id, batch_number from t_transaction where product_id = :product_id and status = 1 order by id desc LIMIT :limit OFFSET :offset ;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':product_id', $pid, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $jobs = $stmt->fetchAll();
        $job = array();
        foreach($jobs as $k=>$j){
          $job[$k] = $j;
        }

        return $job;
    }

    private function get_transaction_count($pid)
    {
        if(empty($pid)) {
            return array();
        }

        $c = 0;
        $get_count_sql = "select count(*) c from t_transaction where product_id = :product_id and status=1;";
        $stmt = $this->pdo->prepare($get_count_sql);
        $stmt->bindParam(':product_id', $pid, PDO::PARAM_STR);
        $stmt->execute();
        $c = $stmt->fetchColumn();
        return $c;
    }
}
