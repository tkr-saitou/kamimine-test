#!/bin/sh

DATE="`date -d '1 days ago' '+%y%m%d'`"

DB_USER="subtour"
DB_NAME="subtour_db"

W_D_HOME=/home/kcsmgr
W_D_BIN=${W_D_HOME}/bin
W_D_LOG=${W_D_HOME}/log
W_JOB="daily_batch"
W_F_LOG=${W_D_LOG}/${W_JOB}_`date '+%Y%m%d%H%M%S'`.log

#共通関数読み込み
. ${W_D_BIN}/commontool.sh
W_RETURN=0

# ログ初期メッセージ出力
touch ${W_F_LOG}
if [ $? -ne 0  ];then
        logger -p local3.info "${W_JOB}：ログファイル作成に失敗しました。"
        exit 9
fi

start_msg >>${W_F_LOG}

#============================================
# t_sbt_busprobeからt_sbt_buprobe_historyへinsert
#============================================
W_STEP=insert
exec_msg >>${W_F_LOG}

/usr/bin/mysql -u${DB_USER} ${DB_NAME} < ${W_D_BIN}/insert_probe_history.sql

if [ $? -ne 0  ];then
        put_msg "***** バスプローブデータストックエラー *****" 1 >>${W_F_LOG}
        exec_err_msg >>${W_F_LOG}
        W_RETURN=9
        end_msg >>${W_F_LOG}
fi

exec_msg >>${W_F_LOG}

#============================================
# t_sbt_busprobe truncate
#============================================
W_STEP=truncate
exec_msg >>${W_F_LOG}

/usr/bin/mysql -u${DB_USER} ${DB_NAME} < ${W_D_BIN}/truncate_probe.sql

if [ $? -ne 0  ];then
        put_msg "***** バスプローブデータtruncateエラー *****" 1 >>${W_F_LOG}
        exec_err_msg >>${W_F_LOG}
        W_RETURN=9
        end_msg >>${W_F_LOG}
fi

exec_msg >>${W_F_LOG}


#============================================
# t_bus_current_route truncate
#============================================
W_STEP=truncate2
exec_msg >>${W_F_LOG}

/usr/bin/mysql -u${DB_USER} ${DB_NAME} < ${W_D_BIN}/truncate_current_route.sql

if [ $? -ne 0  ];then
        put_msg "***** バスステータスデータtruncateエラー *****" 1 >>${W_F_LOG}
        exec_err_msg >>${W_F_LOG}
        W_RETURN=9
        end_msg >>${W_F_LOG}
fi

exec_msg >>${W_F_LOG}


# 終了メッセージ
end_msg >>${W_F_LOG}

