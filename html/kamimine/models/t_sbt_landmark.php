<?php

require_once ('../base/base/BaseModel.php');

class t_sbt_landmark extends BaseModel {

    /**
     * 主要施設一覧を取得
     */
    public function getLandmarkList($buscompany_id, $buscategory_cd, $course_id) {
        $sql = <<< SQL
            SELECT  DISTINCT
                    LA.landmark_id, 
                    LAL.landmark_name, 
                    LA.busstop_id, 
                    LA.lat, 
                    LA.lng 
            FROM    t_sbt_landmark LA
                    LEFT JOIN t_sbt_landmark_lang LAL
                        ON LAL.landmark_id = LA.landmark_id
                        AND LAL.lang_cd = :lang_cd
                    LEFT JOIN v_sbt_busdia VDIA
                        ON VDIA.buscompany_id = :buscompany_id
                        AND VDIA.busstop_id = LA.busstop_id
            WHERE   LA.buscompany_id = :buscompany_id
SQL;
        $param = array(":buscompany_id" => $buscompany_id, ":lang_cd" => 'ja');
        if ($buscategory_cd != 0) {
            $sql .= " AND VDIA.buscategory_cd = :buscategory_cd";
            $param[":buscategory_cd"] = $buscategory_cd;
        }
        if ($course_id != 0) {
            $sql .= " AND VDIA.course_id = :course_id";
            $param[":course_id"] = $course_id;
        }
        $sql .= " ORDER BY LA.landmark_id, LA.busstop_id";

        return $this->fetchAll($sql, $param);
    }

}
