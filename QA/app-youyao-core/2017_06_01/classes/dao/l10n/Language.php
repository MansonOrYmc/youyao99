<?php
apf_require_class("APF_DB_Factory");

class Dao_L10n_Language {

	private $slave_pdo;

	public function __construct() {
		$this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		$this->one_slave = APF_DB_Factory::get_instance()->get_pdo("slave");
	}

	public function get_text($dest_id,$key) {
		$sql = 'SELECT l_desc FROM m_dest_language '
			.' WHERE l_key = :key AND dest_id IN (:dest_id,:default_dest_id) '
			.' ORDER BY dest_id DESC';
		$stmt = $this->slave_pdo->prepare($sql);
		$stmt->execute(array(
			'key'=>$key,
			'dest_id'=>$dest_id,
			'default_dest_id'=>Const_Default_Dest_ID
		));
		return $stmt->fetchColumn();
	}

	public function get_translate_text($source, $language, $context = '', $textgroup = 'default') {
		$sql = <<<'SQL'
SELECT s.lid, t.translation, s.version
FROM drupal_locales_source s
LEFT JOIN drupal_locales_target t ON s.lid = t.lid
AND t.language = :language
WHERE s.source = :source
SQL;
		$stmt = $this->one_slave->prepare($sql);
		$stmt->execute(array(
			'source' => $source,
			'language' => $language,
		));
		return $stmt->fetch();
	}
}