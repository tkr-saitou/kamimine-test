#!/bin/sh

start_msg() {
        \echo "############################################################"
        \echo "                                         `date '+%Y/%m/%d %H:%M:%S'`"
        \echo "  ジョブ名：${W_JOB}"
        \echo
        \echo "############################################################"
        \echo
}
end_msg() {
        \echo
        if [ ${W_RETURN} -eq 0 ]; then
                \echo "  ＜＜ 処理が正常終了しました ＞＞"
        elif [ ${W_RETURN} -lt 5 ];then
                \echo "  ＜＜ 処理が警告終了しました  ステータス：${W_RETURN} ＞＞"
        else
                \echo "  ＜＜ 処理が異常終了しました  ステータス：${W_RETURN} ＞＞"
        fi
        \echo "                                         `date '+%Y/%m/%d %H:%M:%S'`"
        \echo
        exit ${W_RETURN}
}
exec_msg() {
        \echo "## `date '+%H:%M:%S'`: ${W_STEP} ##"
}
exec_err_msg() {
        \echo "  ******* ${W_STEP} エラー *******"
}
put_msg(){
        \echo "  $1"

        if [ $# -gt 1 ]; then
                w_loop=0
                while [ ${w_loop} -lt $2 ]; do
                        \echo
                        w_loop=`expr ${w_loop} + 1`
                done
        fi
}
