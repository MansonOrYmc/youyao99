<?php
apf_require_class('APF_DB_Factory');

class Dao_Db {
	protected static $pdo;
	protected static $slave_pdo;
	protected static $one_pdo;
	protected static $one_slave_pdo;

	protected static function load_lky_db() {
		if (!self::$pdo) {
			self::$pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
		}
		return self::$pdo;
	}

	protected static function load_lky_slave_db() {
		if (!self::$slave_pdo) {
			self::$slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
		}
		return self::$slave_pdo;
	}

	protected static function load_one_db() {
		if (!self::$one_pdo) {
			self::$one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
		}
		return self::$one_pdo;
	}

	protected static function load_one_slave_db() {
		if (!self::$one_slave_pdo) {
			self::$one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
		}
		return self::$one_slave_pdo;
	}
}