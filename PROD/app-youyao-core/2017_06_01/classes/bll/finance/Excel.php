<?php
class Bll_Finance_Excel {

    private $type;
    private $data;
    private $objPHPExcel;
        
    public function __construct($type, $data) {
        $this->type = $type;
        $this->data = $data;
    }

    // 创建excel
    public function create() {
        $this->objPHPExcel = new PHPExcel;
        $this->excel_header();
        $this->excel_title();
        $this->build_data();
		return $this;
    }

    public function excel_header() {
        // Set document properties
        $this->objPHPExcel->getProperties()->setCreator("Finance Zizaike")
            ->setLastModifiedBy("Finance Zizaike")
            ->setTitle("Zizaike XLSX Document")
            ->setSubject("Zizaike XLSX Document")
            ->setDescription("Zizaike XLSX document for Office 2007 XLSX, generated using PHP classes.");
//            ->setKeywords("office 2007 openxml php")
//            ->setCategory("Test result file");
        $this->objPHPExcel->setActiveSheetIndex(0);
        $this->objPHPExcel->getActiveSheet()->setTitle('Sheet1');
    }

    // 生成excel tilte
    public function excel_title() { 
        $title = $this->excel_title_config($this->type);
        $sheet = $this->objPHPExcel->getActiveSheet();
        foreach($title as $k=>$v) {
            $sheet->setCellValueByColumnAndRow($k, 1, $v);
        }
    }

    // 生成数据body
    public function build_data() {
        $sheet = $this->objPHPExcel->getActiveSheet();
        $line = 2;
        foreach($this->data as $r) {
            $column = 0;
            foreach($r as $data) {
                $sheet->setCellValueByColumnAndRow($column, $line, $data);
                $column++;
            }
            $line++;
        }
    }

    // 输出头
    public function output_header() {

        $today = date("Ymdgi");
        $excel_name = Bll_User_Wallet::bank_name($this->type) . $today . '.xls';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $excel_name);
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
    }

    public function output() {
        $this->output_header();
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        return true;
    }

    public function excel_title_config($type) {
        $title = array(
            "TW_BANK" => array( // 台新银行
                "用戶自訂序號",
                "付款日期",
                "金額(含小數2位TWD)",
                "付款帳號",
                "付款戶名",
                "收款帳號",
                "收款戶名",
                "付款總行代碼",
                "付款分行代碼",
                "收款總行代碼",
                "收款分行代碼",
                "附言",
                "收款人識別碼",
                "收款人代碼識別",
                "付款人識別碼",
                "付款人代碼識別",
                "手續費負擔別",
                "對帳單key值",
                "付款聯絡人",
                "付款聯絡電話",
                "付款傳真號碼",
                "收款聯絡人",
                "收款聯絡電話",
                "收款傳真號碼",
                "收款通知E-mail",
                "民宿名称",
                "提现号"
            ),
            "CN_BANK" => array( // 大陆银行
                "序号",
                "日期",
                "金额",
                "银行",
                "分行",
                "账号",
                "户名",
                "邮箱",
                "民宿名字",
                "提现号",
            ),
            "JAPAN_BANK" => array( // 日本银行
                "自在客番号",
                "予約成約日",
                "チェックイン日",
                "チェックアウト日",
                "代表者",
                "登记与否",
                "宿名",
                "銀行名",
                "銀行支店名",
                "口座番号",
                "口座名義",
                "支払期日",
                "コミッション%",
                "支払金額",
                "振込手数料区分",
                "ロデック番号",
                "振込手数料",
                "実振込日",
                "自在客備考",
                "ロデック備考",
                "データー記入者",
            ),
            "ALIPAY" => array( // 支付宝
                "序号",
                "金额",
                "支付宝账号",
                "邮箱",
                "民宿名字",
                "提现号",
            ),
            "PAYPAL" => array( // paypal
                "序号",
                "金额",
                "支付宝账号",
                "邮箱",
                "民宿名字",
                "提现号",
            ),
        );

        return $title[$type];
    }
}
