<?php
/*
 * 写这类,是因为我不想再写dao了
 *
 * */
class DB {
    /**
     * @param $select 你就说你要什么字段,然后变成一个字段数组传进来,ok? array('field')
     * @param $from 你就告诉我,你的数据库和表名字,就ok,当然,这也是一个数组  array('database'=>'LKYou','table'=>'t_tablename')
     * @param $where 还是数组,当然,你可以没有!
     * @param $order 字段和排序   array('field1'=>'desc','field2'=>'asc',)
     * @param $limit 传个数字过来吧
     */

    public static $pdo;
    private static function get_pdo(){
        if(!self::$pdo){
            self::$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        }
        return self::$pdo;
    }
    private static function get_select($select){
        $select_clause = false;
        if(is_array($select) and !empty($select)){
            $select_clause = "select ";
            $select_clause .= implode(" , ",$select);
        }
        return $select_clause;
    }
    private static function get_from($from){
        $from_clause = false;
        if(is_array($from) and !empty($from)){
            $from_clause = " from `".$from['database']."`.`".$from['table']."` ";
        }
        return $from_clause;
    }
    private static function get_where($where){
        $where_clause = '';
        if(is_array($where) and !empty($where)){
            $where_clause = "where ";
            $where_clause .= implode(" and ",$where);
        }
        return $where_clause;
    }
    private static function get_order($order){
        $order_cluase = '';
        if(is_array($order) and !empty($order)){
            $tmparr = array();
            $order_cluase = "order by ";
            foreach($order as $field=>$asc){
                $tmparr[] = "$field $asc";
            }
            $order_cluase.=implode(" , ",$tmparr);
        }
        return $order_cluase;
    }
    public static function getInfo($select, $from, $where=null, $order=null, $limit=null) {
        $pdo = self::get_pdo();
        $sql = "";
        $sql.= self::get_select($select).self::get_from($from).self::get_where($where).self::get_order($order);
        if($limit) $sql.= " limit $limit";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param $sql 干脆只执行一条sql好了
     */
    public static function execSql($sql,$lastId=null){
        $pdo = self::get_pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if($lastId){
            $lastId = $pdo->lastInsertId();
            return $lastId;
        }
        return $stmt->fetchAll();
    }
}