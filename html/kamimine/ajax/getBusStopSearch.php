<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busstop.php"); 

// ### Class Definition -----------------------------------------------------------------
class getBusStopSearch extends BaseApi {

    protected function main() {
        $initial = $_POST['initial'];
        $data['initial'] = $initial;
        $data['busstop'] = $this->db->invoke('t_sbt_busstop','getKanaBusstop',array($initial));
        
        return $data;

    }

}
// ### Run Process ---------------------------------------------------------------------
$class = new getBusStopSearch();
$class->run();
