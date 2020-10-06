<?php

// include_path to ZendFramework's Library
set_include_path('/usr/share/ZendFramework-1.12.3/library'
		 . PATH_SEPARATOR . get_include_path());

require_once 'Zend/Config/Ini.php';
require_once 'Zend/Db.php';

class DbManager {
	public static $db;
	public static function getConnection() {
		if (!is_null(self::$db)) return self::$db;  
		try { 
			// iniファイル
			$config = new Zend_Config_Ini(SHARE_DIR.'application.ini', array('database'));
			
			// DB接続
			self::$db = Zend_Db::factory($config);		
			self::$db->query('SET CHARACTER SET utf8');
		} catch (Zend_Exception $e) {
			die($e->getMessage());
		}
		return self::$db;
	}
}


