<?php
apf_require_class("APF_DB_Factory");

class Dao_Field_Info
{

    private $pdo;
    private $one_pdo;
    private $slave_pdo;
    private $one_slave_pdo;

    public function __construct()
    {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
    }

    public function get_field($id, $bundle, $type, $table_name)
    {
        $sql = "select * from drupal_field_data_field_{$table_name} where entity_id = ? and bundle = ? and entity_type = ?";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute(array($id, $bundle, $type));
        return $stmt->fetchAll();
    }

    public function get_user_field_by_uids($tables, $uid)
    {

        if (empty($uid) || empty($tables)) {
            return;
        }

        $result = array();
        foreach ($tables as $table) {
            foreach (self::get_field_data($table, $uid, 'user') as $row) {
                if (!empty($row)) {
                    $id = $row['entity_id'];unset($row['entity_id']); // 下面赋值的时候id太多了，看着丑，就unset掉了
                    if (is_numeric(key($data[$id][key($table)]))) {
                        $data[$id][key($table)][] = $row; // 像image这样的数据是数组
                    } elseif ($data[$id][key($table)]) {
                        $data[$id][key($table)] = array($data[$id][key($table)], $row);
                    } else {
                        $data[$id][key($table)] = $row;
                    }
                }
            }
        }

        return $data;
    }

    public function get_node_field_by_nids($tables, $nid)
    {

        if (empty($nid) || empty($tables)) {
            return;
        }

        $result = array();
        foreach ($tables as $table) {
            foreach (self::get_field_data($table, $nid) as $row) {
                if (!empty($row)) {
                    $id = $row['entity_id'];
                    unset($row['entity_id']); // 下面赋值的时候id太多了，看着丑，就unset掉了
                    if (is_numeric(key($data[$id][key($table)]))) {
                        $data[$id][key($table)][] = $row; // 像image这样的数据是数组
                    } elseif ($data[$id][key($table)]) {
                        $data[$id][key($table)] = array($data[$id][key($table)], $row);
                    } else {
                        $data[$id][key($table)] = $row;
                    }
                }
            }
        }

        return $data;
    }

    public function get_field_data($table, $id, $type = 'node')
    {
        if (empty($table) || empty($id)) {
            return;
        }

        foreach ($table as $k => $v) {
            $tableName = "drupal_" . $k;
            foreach ($v as $value) {
                $valueArr[] = $value;
            }
        }
        $valueStr = implode(",", $valueArr);
        $idStr = implode(",", $id);

        $sql = "select entity_id,$valueStr from $tableName where entity_id in ($idStr) and entity_type = '$type' ";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();

    }

    public function get_field_config($field_names)
    {
        if (empty($field_names)) {
            return;
        }

        $fieldStr = implode(",", $field_names);
        $sql = "select * from drupal_field_config where field_name in ($fieldStr) ";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function get_field_config_instance($type, $bundle)
    {

        if (empty($type)) {
            return;
        }

        if ($bundle) {
            $bundle = " and bundle = '$bundle' ";
        }

        $sql = "select * from drupal_field_config_instance where entity_type = '$type' $bundle";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function get_taxonomy_term_data($tid, $vid)
    {
        if ($tid) {
            $tid = "tid = '$tid'";
        }

        if ($vid) {
            $vid = "vid = '$vid'";
        }

        if ($tid && $vid) {
            $condition = "where $tid and $vid ";
        } elseif ($tid || $vid) {
            $condition = "where $tid $vid ";
        }
        $sql = "select * from drupal_taxonomy_term_data $condition ";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function get_field_data_by_table($tableName, $entity_type, $bundle, $entity_id)
    {
        $sql = "select * from drupal_$tableName where entity_id = '$entity_id' and bundle = '$bundle' and entity_type = '$entity_type' ";
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    /*
     **     user 中 entity_id = revision_id
     **   node(房间) 里一般revision_id 比entity_id 大1
     **   相对node这个是drupal_node和drupal_node_revision的主键索引，可能是之前直接数据库insert导致的，
     **   对应为drupal_node或者drupal_node_revision 的 nid和vid
     */
    public function insert_update_field_table($tableName, $entity_type, $bundle, $value, $entity_id, $revision_id)
    {

        if (empty($revision_id)) {
            $revision_id = $entity_id;
        }

        $duplicate = "";
        if (count($value) != 1) {
            return;
        }
        // 先只考虑只写入各种field表的主要字段的情况
        $insertValues = array();
        foreach ($value as $column => $val) { // 只循环一次 column就是要insert的字段名

            if (is_array($val)) {
                $k = 0;
                foreach ($val as $row) {
                    if ($tableName == "field_data_field_image" || $tableName == "field_data_field_jiaotongtu") { //图片需要多加一个字段
                        $questionMarks[] = '(' . Util_Common::placeholders('?', 9) . ')'; // 图片生成9个? 作为pdo insert用
                        $insertValues = array_merge( // 生成数据 保证key都是递增数字
                            $insertValues,
                            array(
                                $entity_id,
                                $revision_id,
                                $entity_type,
                                $bundle,
                                0,
                                $k, // delta
                                'und',
                                $row,
                                Util_Image::img_version($row),
                            )
                        );
                    } else {
                        $questionMarks[] = '(' . Util_Common::placeholders('?', 8) . ')'; // 生成8个? 作为pdo insert用
                        $insertValues = array_merge( // 生成数据 保证key都是递增数字
                            $insertValues,
                            array(
                                $entity_id,
                                $revision_id,
                                $entity_type,
                                $bundle,
                                0,
                                $k, // delta
                                'und',
                                $row,
                            )
                        );
                    }
                    $k++;
                }
            } else {
                $questionMarks[] = '(' . Util_Common::placeholders('?', 8) . ')';
                $insertValues = array(
                    $entity_id,
                    $revision_id,
                    $entity_type,
                    $bundle,
                    0,
                    0,
                    'und',
                    $val,
                );
            }
        }

        $duplicate .= "$column=values($column), delta=values(delta)";

/*
$keyStr = implode(", ", $keyArr);
$valStr = implode(", ", $valArr);
 */
//        $history = $this->get_field_data_by_table($tableName, $entity_type, $bundle, $uid);

        // 两个特殊处理
        if ($tableName == "field_data_field_image") {
            $column = $column . ", field_image_version";
        }

        if ($tableName == "field_data_field_jiaotongtu") {
            $column = $column . ", field_jiaotongtu_version";
        }

        // 此类型的表作为字段使用 而且drupal的处理方法就是删除、所以失效的还是删除吧。
        $delete = "delete from drupal_$tableName where entity_id = $entity_id and entity_type = '$entity_type' and bundle = '$bundle' ";
        $insert_base = "insert into drupal_$tableName (entity_id, revision_id, entity_type, bundle, deleted, delta, language, $column) values " . implode(",", $questionMarks);

        // 操作revision表只是为了drupal兼容 所以不删除多余数据也无所谓。
        $revisionName = str_replace('data', 'revision', $tableName);
        $insert_revision = "insert into drupal_$revisionName (entity_id, revision_id, entity_type, bundle, deleted, delta, language, $column) values " . implode(",", $questionMarks) . " ON DUPLICATE KEY UPDATE $duplicate";

        $this->one_pdo->beginTransaction();
        $deleteStmt = $this->one_pdo->prepare($delete);
        $baseStmt = $this->one_pdo->prepare($insert_base);
        $revisionStmt = $this->one_pdo->prepare($insert_revision);
        try {
            $deleteStmt->execute();
            $baseStmt->execute($insertValues);
            $revisionStmt->execute($insertValues);

        } catch (Exception $e) {
            Util_Debug::zzk_debug("insert_update_field_table:", print_r($e->getMessage(), true));
        }

        $this->one_pdo->commit();

    }

    // 此方法更新多个entity_id 但每个字段表只更新一条数据， 不适合drupal_field_data_field_images表
    public function multi_insert_update_field_table($tableName, $entity_type, $bundle, $params, $primary_id)
    {

        $value = reset($params);
        $key = key($params);
        if (count($value) != 1) {
            return;
        }
        //只更新field表的一个字段
        $insertValues = array();
        foreach ($primary_id as $k => $v) {
            $entity_ids[] = $v['entity_id'];
            $valueStr .= $valueStr ? ", " : "";
            $valueStr .= "('" . $v['entity_id'] . "', '" . $v['revision_id'] . "', '$entity_type', '$bundle', 0, 0, 'und', '" . reset($params) . "')";

            $questionMarks[] = '(' . Util_Common::placeholders('?', 8) . ')';
            $insertValues = array_merge(
                $insertValues,
                array(
                    $v['entity_id'],
                    $v['revision_id'],
                    $entity_type,
                    $bundle,
                    0,
                    0,
                    'und',
                    reset($params),
                )
            );

        }
        $duplicate = "$key=values($key)";

//        $entityIdStr = implode(", ", $entity_ids);
        //        $delete = "delete from drupal_$tableName where entity_id in ($entityIdStr) and entity_type = '$entity_type' and bundle = '$bundle' ";
        // 与上面insert_update_field_table不同，这里不处理多值得情况， 所以不删除也可以
        $insert_base = "insert into drupal_$tableName (entity_id, revision_id, entity_type, bundle, deleted, delta, language, $key) values " . implode(",", $questionMarks) . " ON DUPLICATE KEY UPDATE $duplicate";
        $revisionName = str_replace('data', 'revision', $tableName);
        $insert_revision = "insert into drupal_$revisionName (entity_id, revision_id, entity_type, bundle, deleted, delta, language, $key) values " . implode(",", $questionMarks) . " ON DUPLICATE KEY UPDATE $duplicate";

//        print_r($insert_base);
        //        print "\n";
        //        print_r($insert_revision);
        //        print "\n";
        //        print_r($insertValues);
        //        print "\n";

        $this->one_pdo->beginTransaction();
        $baseStmt = $this->one_pdo->prepare($insert_base);
        $revisionStmt = $this->one_pdo->prepare($insert_revision);

        try {
            $baseStmt->execute($insertValues);
            $revisionStmt->execute($insertValues);
        } catch (Exception $e) {
            Util_Debug::zzk_debug("multi_insert_update_field_table:", print_r($e->getMessage(), true));
        }
        $this->one_pdo->commit();

    }

}
