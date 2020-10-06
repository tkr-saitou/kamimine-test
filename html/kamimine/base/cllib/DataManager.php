<?php

require_once 'Zend/Config/Ini.php';
require_once 'Zend/Db.php';

class DataManager {
    // DbManager¿¿

    private $db;
    private $logger;

    function __construct($logger=null) {
        $this->logger = $logger;
    }

    public function getConnection() {
        if(empty($this->db)){
            $this->db = $this->getNewConnection();
        }
        return $this->db;
    }

    public function getNewConnection() {
        try {
            // read ini file
            $config = new Zend_Config_Ini(SHARE_DIR.'application.ini', array('database'));
            // connect DB
            $db = Zend_Db::factory($config);
            $db->query('SET CHARACTER SET utf8');
            $db->beginTransaction();
        } catch (Zend_Exception $e) {
            die($e->getMessage());
        }
        return $db;
    }
}
