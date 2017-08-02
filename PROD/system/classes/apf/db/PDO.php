<?php

apf_require_class("APF_DB_PDOStatement");
apf_require_class("APF_Exception_SqlException");
class APF_DB_PDO extends PDO {

    private static $pdo_statement_class;

    private $i = 0;
    public function __construct($dsn, $username="", $password="", $driver_options=array()) {

        if(!self::$pdo_statement_class) {
            self::$pdo_statement_class = APF::get_instance()->get_config('pdo_statement_class','database');
            if(!self::$pdo_statement_class) {
                self::$pdo_statement_class='APF_DB_PDOStatement';
            }
            apf_require_class(self::$pdo_statement_class);
        }



        parent::__construct($dsn, $username, $password, $driver_options);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array(self::$pdo_statement_class, array($this)));
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }

    public function exec($statement) {

        if (APF::get_instance()->is_debug_enabled()) {
            APF::get_instance()->debug(__CLASS__ .'['. $this->name .']'. "->exec: $statement");
        }

        $logger = APF::get_instance()->get_logger();
        $logger->debug(__CLASS__, '['. $this->name .']->exec: ', $statement);
        $stmt = parent::exec($statement);

        if ($stmt instanceof PDOStatement) {
            $stmt->setFetchMode($this->default_fetch_mode);
        } else {
            $error_info = parent::errorInfo();
            if (parent::errorCode() !== '00000') {
                throw new APF_Exception_SqlException(
                    $this->get_name().' | '.$this->config['dsn'].' | '.$statement.' | '.join(' | ',$error_info),
                    parent::errorCode()
                );
            }
        }

        return $stmt;
    }

    public function prepare($statement, $driver_options=array()) {
        //add by jackie for record SQL
        APF::get_instance()->pf_benchmark("sql",array($this->i=>$statement));
        $stmt = parent::prepare($statement, $driver_options);
        if ($stmt instanceof PDOStatement) {
            $stmt->setFetchMode($this->default_fetch_mode);
        }
        //add by hexin for record SQL execute time
        $stmt->set_i($this->i);
        $this->i++;
        $stmt->_sql = $statement;
        return $stmt;
    }

    public function query($statement, $pdo=NULL, $object=NULL) {
        if (APF::get_instance()->is_debug_enabled()) {
            APF::get_instance()->debug(__CLASS__ .'['. $this->config['dsn'] .'|'.$this->name.']'. "->query: $statement");
        }
        $logger = APF::get_instance()->get_logger();
        $logger->debug(__CLASS__, '['. $this->name .']->query: ', $statement);
        if($pdo != NULL && $object != NULL){
            $stmt = parent::query($statement, $pdo, $object);
        }else{
            $stmt = parent::query($statement);
        }
            if ($stmt instanceof PDOStatement) {
            $stmt->setFetchMode($this->default_fetch_mode);
        }
        return $stmt;
    }

    public function set_name($name) {
        $this->name = $name;
        $this->config = APF::get_instance()->get_config($name, "database");
    }

    public function get_name() {
        return $this->name;
    }

    private $name;

    public $config;

    public function set_default_fetch_mode($mode) {
        $this->default_fetch_mode = $mode;
    }

    private $default_fetch_mode = PDO::FETCH_BOTH;
}
