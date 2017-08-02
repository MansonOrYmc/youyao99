<?php
apf_require_class("APF_DB_Factory");

class Dao_Images_Info
{
    private $pdo;
    private $slave_pdo;
    private $one_pdo;
    private $one_slave_pdo;

    public function __construct()
    {
        $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
        $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
        $this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
        $this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
    }

    public function get_multi_file_managed($fids)
    {
        if (empty($fids)) {
            return;
        }

        $condition = implode(', ', $fids);
        $sql = 'SELECT * FROM drupal_file_managed WHERE fid in (' . $condition . ') ';
        $stmt = $this->one_slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function get_multi_t_img_managed($fids)
    {
        if (empty($fids)) {
            return;
        }

        $condition = implode(', ', $fids);
        $sql = 'SELECT * FROM t_img_managed WHERE fid in (' . $condition . ') ';
        $stmt = $this->slave_pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update_homestay_images($uid, $pic_arr)
    {
        if ($this->one_pdo->beginTransaction()) {
            $sql = <<<SQL
DELETE FROM one_db.drupal_field_data_field_image
WHERE entity_id = :uid
AND entity_type = 'user'
AND bundle = 'user'
SQL;
            $delete_stmt = $this->one_pdo->prepare($sql);
            $sql = <<<SQL
INSERT INTO one_db.drupal_field_data_field_image
(entity_id,revision_id,entity_type,bundle,delta,language,field_image_fid,field_image_version)values
(:uid,:uid,'user','user',:delta,'und',:fid,1)
SQL;
            $insert_stmt = $this->one_pdo->prepare($sql);
            $sql = <<<SQL
INSERT INTO one_db.drupal_field_revision_field_image
(entity_id,revision_id,entity_type,bundle,delta,language,field_image_fid,field_image_version)values
(:uid,:uid,'user','user',:delta,'und',:fid,1)
ON DUPLICATE KEY UPDATE field_image_fid = values(field_image_fid),field_image_version =values(field_image_version), delta=values(delta)
SQL;
            $revision_stmt = $this->one_pdo->prepare($sql);
            try {
                $delete_stmt->execute(array('uid' => $uid));
                foreach ($pic_arr as $k => $v) {
                    $insert_stmt->execute(array('uid' => $uid, 'fid' => $v, 'delta' => $k));
                    $revision_stmt->execute(array('uid' => $uid, 'fid' => $v, 'delta' => $k));
                }
                return $this->one_pdo->commit();
            } catch (Exception $e) {
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($e->getMessage(), true));
                $this->one_pdo->rollBack();
                return false;
            }
        } else {
            return false;
        }
    }

    public function update_room_images($nid, $pic_arr)
    {
        if ($this->one_pdo->beginTransaction()) {
            $sql = <<<SQL
DELETE FROM one_db.drupal_field_data_field_image
WHERE entity_id = :uid
AND entity_type = 'node'
AND bundle = 'article'
SQL;
            $delete_stmt = $this->one_pdo->prepare($sql);
            $sql = <<<SQL
INSERT INTO one_db.drupal_field_data_field_image
(entity_id,revision_id,entity_type,bundle,delta,language,field_image_fid,field_image_version)values
(:uid,:uid,'node','article',:delta,'und',:fid,1)
SQL;
            $insert_stmt = $this->one_pdo->prepare($sql);
            $sql = <<<SQL
INSERT INTO one_db.drupal_field_revision_field_image
(entity_id,revision_id,entity_type,bundle,delta,language,field_image_fid,field_image_version)values
(:uid,:uid,'node','article',:delta,'und',:fid,1)
ON DUPLICATE KEY UPDATE field_image_fid = values(field_image_fid),field_image_version =values(field_image_version), delta=values(delta)
SQL;
            $revision_stmt = $this->one_pdo->prepare($sql);
            try {
                $delete_stmt->execute(array('uid' => $nid));
                foreach ($pic_arr as $k => $v) {
                    $insert_stmt->execute(array('uid' => $nid, 'fid' => $v, 'delta' => $k));
                    $revision_stmt->execute(array('uid' => $nid, 'fid' => $v, 'delta' => $k));
                }
                return $this->one_pdo->commit();
            } catch (Exception $e) {
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($e->getMessage(), true));
                $this->one_pdo->rollBack();
                return false;
            }
        } else {
            return false;
        }

    }

}
