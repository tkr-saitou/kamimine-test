<?php

require_once (APP.'base/models/s_user_auto.php');
require_once ('Zend/Registry.php');

class s_user extends s_user_auto {

    /**
     * ユーザマスタ全件取得
     * 取得後にRegistryに登録
     */
    private function getAllUsers() {
        if(!Zend_Registry::isRegistered('tciUsers')) {
            $result = $this->fetchAll(
                'SELECT * FROM s_user ORDER BY user_id ',
                array()
                );
            Zend_Registry::set('tciUsers', $result);
            return $result;
        } else {
            return Zend_Registry::get('tciUsers');
        }
    }

    /**
     * ユーザ取得 
     */
    public function getUser($user_id) {
        $result = array();
        $list = $this->getAllUsers();
        foreach ($list as $row) {
            if($row['user_id'] == $user_Id) {
                return $row;
            }
        } 
        return null;
    }

    /**
     * ユーザー一覧取得
     */
    public function getUserList($site_id, $user_name=null, $org_cd=null) {
        // SQL文生成
        $sql1 = 'select user.*, org.head_branch_office_name ';
        $sql1.= ' from (s_user user inner join t_site_user site ';
        $sql1.= '   on user.user_id = site.user_id) ';
        $sql1.= '    left outer join t_org org ';
        $sql1.= '      on user.org_cd = org.org_cd ';
        $sql1.= '        where 1=1 ';
        $sql1.= '          and site.site_id = :site_id ';
        $sql2='order by user.user_id asc ';
        $param = array("site_id" => $site_id);
        if(!empty($org_cd)) {
            $sql1 .= ' and org.org_cd = :org_cd ';
            $param = array_merge($param, array("org_cd" => $org_cd));
        }
        if(!empty($user_name)) {
            $sql1 .= ' and user.user_name like :user_name ';
            $str   = '%'.$user_name.'%';
            $param = array_merge($param, array("user_name" => $str));
        }

        $this->logger->writeDebug($sql1.$sql2);

        // DB検索
        $result = $this->fetchAll($sql1.$sql2, $param);
        return $result;
    }

    /**
     * ユーザー一覧取得
     */
    public function getUserListById($site_id, $user_id) {
        // SQL文生成
        $sql1 = 'select user.*, org.head_branch_office_name ';
        $sql1.= ' from (s_user user inner join t_site_user site ';
        $sql1.= '   on user.user_id = site.user_id) ';
        $sql1.= '    left outer join t_org org ';
        $sql1.= '      on user.org_cd = org.org_cd ';
        $sql1.= '        where 1=1 ';
        $sql1.= '          and site.site_id = :site_id ';
        $sql1.= '            and user.user_id = :user_id ';
        $param = array("site_id" => $site_id);
        $param = array_merge($param, array("user_id" => $user_id));

        $this->logger->writeDebug($sql1);

        // DB検索
        $result = $this->fetchRow($sql1, $param);
        return $result;
    }

    public function updateUserList($data) {
        $sql = 'UPDATE s_user SET '
        .'user_id = :user_id '
        .',user_name = :user_name '
        .',login_id = :login_id '
        .',user_mail_address = :user_mail_address '
        .',org_cd = :org_cd '
        .',enter_date = :enter_date '
        .',retire_date = :retire_date '
        .',upd_user_id = :upd_user_id '
        .',upd_time = :upd_time '
        .',upd_transaction_id = :upd_transaction_id '
        .'WHERE 1=1 '
        .'AND user_id = :user_id ';

        $param = array(
        ':user_id'=> $data['user_id']
        ,':user_name'=> $data['user_name']
        ,':login_id'=> $data['login_id']
        ,':user_mail_address'=> $data['user_mail_address']
        ,':org_cd'=> Util::emptyToNull($data['org_cd'])
        ,':enter_date'=> Util::emptyToNull($data['enter_date'])
        ,':retire_date'=> Util::emptyToNull($data['retire_date'])
        ,':upd_user_id'=> $this->getUserId()
        ,':upd_time'=> $this->getDatetime()
        ,':upd_transaction_id'=> $this->getTransactionId()
        );
        $result = $this->query($sql,$param);
        return $result;
    }

    public function updateUserPasswd($data) {
        $sql = 'UPDATE s_user SET '
        .'passwd = :passwd '
        .',upd_user_id = :upd_user_id '
        .',upd_time = :upd_time '
        .',upd_transaction_id = :upd_transaction_id '
        .'WHERE 1=1 '
        .'AND user_id = :user_id ';

        $param = array(
        ':user_id'=> $data['user_id']
        ,':passwd'=> Util::emptyToNull($data['passwd'])
        ,':upd_user_id'=> $this->getUserId()
        ,':upd_time'=> $this->getDatetime()
        ,':upd_transaction_id'=> $this->getTransactionId()
        );
        $result = $this->query($sql,$param);
        return $result;
    }

    /**
     * 旧ユーザテーブルからの取得(t_user)
     * BaseControllerでのユーザ名取得用。それ以外では使わないこと。
     */
    public function selectOldUserTable($user_id) {
        $param = array();
        $sql = <<<SQL
            SELECT * FROM t_user WHERE 1=1 
             AND user_id = :user_id 
SQL;
        $param = array(
            ':user_id' => $user_id
        );
        return $this->fetchRow($sql, $param);
    }

}
