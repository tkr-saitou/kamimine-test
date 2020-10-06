<?php
require_once (APP.'base/base/BaseModel.php');
class  s_user_auto extends BaseModel {
/**COUNT**/
public function selectCount($user_id=null) {
$param = array();
$sql = 'select count(*) from s_user where 1=1 ';
if(!empty($user_id)) {
$sql .= ' and user_id = :user_id '; 
$param = array_merge($param, array('user_id' => $user_id)); 
}
return $this->fetchOne($sql, $param);
}

/*** SELECT PK***/
public function selectByPk($user_id) {    
$param = array();
$sql = 'SELECT * FROM s_user WHERE 1=1 '
.' AND user_id = :user_id '
;
$param = array(
':user_id' => $user_id
);
return $this->fetchRow($sql, $param);
}
    
/**INSERT**/
public function insert($data) {
$sql = 'INSERT INTO s_user VALUES( '
.':user_id '
.',:user_name '
.',:login_id '
.',:passwd '
.',:user_mail_address '
.',:org_cd '
.',:post_cd '
.',:role '
.',:disc_flg '
.',:subtype '
.',:enter_date '
.',:retire_date '
.',:reg_user_id '
.',:reg_time '
.',:reg_transaction_id '
.',:upd_user_id '
.',:upd_time '
.',:upd_transaction_id '
.')'
;

$param = array(
':user_id'=> $data['user_id']
,':user_name'=> $data['user_name']
,':login_id'=> $data['login_id']
,':passwd'=> $data['passwd']
,':user_mail_address'=> $data['user_mail_address']
,':org_cd'=> Util::emptyToNull($data['org_cd'])
,':post_cd'=> $data['post_cd']
,':role'=> Util::emptyToNull($data['role'])
,':disc_flg'=> Util::emptyToNull($data['disc_flg'])
,':subtype'=> Util::emptyToNull($data['subtype'])
,':enter_date'=> Util::emptyToNull($data['enter_date'])
,':retire_date'=> Util::emptyToNull($data['retire_date'])
,':reg_user_id'=> $this->getUserId()
,':reg_time'=> $this->getDatetime()
,':reg_transaction_id'  => $this->getTransactionId()
,':upd_user_id'=> $this->getUserId()
,':upd_time'=> $this->getDatetime()
,':upd_transaction_id'=> $this->getTransactionId()
);
$result = $this->query($sql,$param);
return $result;
}
    
/**UPDATE**/
public function update($data) {
$sql = 'UPDATE s_user SET '
.'user_id = :user_id '
.',user_name = :user_name '
.',login_id = :login_id '
.',passwd = :passwd '
.',user_mail_address = :user_mail_address '
.',org_cd = :org_cd '
.',post_cd = :post_cd '
.',role = :role '
.',disc_flg = :disc_flg '
.',subtype = :subtype '
.',enter_date = :enter_date '
.',retire_date = :retire_date '
.',upd_user_id = :upd_user_id '
.',upd_time = :upd_time '
.',upd_transaction_id = :upd_transaction_id '
.'WHERE 1=1 '
.'AND user_id = :user_id '
;

$param = array(
':user_id'=> $data['user_id']
,':user_name'=> $data['user_name']
,':login_id'=> $data['login_id']
,':passwd'=> $data['passwd']
,':user_mail_address'=> $data['user_mail_address']
,':org_cd'=> Util::emptyToNull($data['org_cd'])
,':post_cd'=> $data['post_cd']
,':role'=> Util::emptyToNull($data['role'])
,':disc_flg'=> Util::emptyToNull($data['disc_flg'])
,':subtype'=> Util::emptyToNull($data['subtype'])
,':enter_date'=> Util::emptyToNull($data['enter_date'])
,':retire_date'=> Util::emptyToNull($data['retire_date'])
,':upd_user_id'=> $this->getUserId()
,':upd_time'=> $this->getDatetime()
,':upd_transaction_id'=> $this->getTransactionId()
);
$result = $this->query($sql,$param);
return $result;
}
    
/**DELETE**/
public function delete($user_id) {    
$sql = 'DELETE FROM s_user WHERE 1=1 '
;
if(!empty($user_id)){
$sql .=' and user_id = :user_id ';
$param = array_marge($param, array('user_id' => $user_id));
}
$result = $this->query($sql,$param);
return $result;
}
    
}
