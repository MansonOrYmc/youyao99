<?php
apf_require_class("APF_Controller");

class Transaction_BaseInfoController extends APF_Controller
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

        $yyid = "388C4087523C11E79E6C68F728954D54";
        if(isset($params['yyid']) && strlen($params['yyid']) == 32){
          $yyid = $params['yyid'];
        }

        $transaction = array();
        $transaction = self::get_transaction_data($yyid);
       

        echo json_encode(Util_Beauty::wanna(array(
            'code' => 1,
            'codeMsg' => 'normal_request',
            //"params" => $params,
            "transaction" => $transaction,
        )));

        return false;
    }

    private function get_transaction_data($yyid)
    {
        if(empty($yyid)){
           return array();
        }

        $sql = "select id, yyid, h_yyid, d_yyid, datei, tid, name, specs, t_num, buyer_hospital, trans_date, hospital_id, product_id, batch_number, version, created, update_date from t_transaction where yyid = ? and status = 1 limit 1; ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array("$yyid"));
        $transaction = $stmt->fetch();
        if(isset($transaction['created'])){
          $transaction['created'] = date('Y-m-d H:i:s', $transaction['created']);
        }

        return $transaction;
    }
}
