<?php

require_once ('Zend/File/Transfer.php');
require_once (APP.'base/cllib/Util.php');

class FileUploader {

    private $controller;
    private $session;
    private $logger;
    private $upload;

    function __construct($controller,$session,$logger) {
        $this->logger = $logger;
        $this->session = $session;
        $this->controller = $controller;
        $this->upload = new Zend_File_Transfer();
    }

    public function getFilePath(){
    }

}
