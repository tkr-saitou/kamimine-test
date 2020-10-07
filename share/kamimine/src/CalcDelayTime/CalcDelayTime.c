/* Copyright (C) 2014 TCI All Rights Reserved */

#include "stdio.h"
#include "my_global.h"
#include "mysql.h"
#include <string.h>
#include <stdlib.h>
#include "variables.h"

#define RECNUM 10000
#define RESULTSNUM 100

/***** 型定義 *****/
struct DiaRec{
	char buscategory_cd[12];
	char course_id[12];
	char bin_no[12];
	int delay;
	int stop_seq_from;
	int stop_seq_to;
	char dia_time_from[10];
	char dia_time_to[10];
	int fromtime;
};

/***** グローバル変数 *****/
MYSQL *conn;

/***** プロトタイプ宣言 *****/
time_t toTime1(char *YYMMDDhhmmss);
time_t toTime2(char *YY,char *MM,char *DD,char *hh,char *mm,char *ss);
int comp( const void *c1, const void *c2 );

/**
 * エラー処理
 */
void exitSystemErr(const char* errorMsg, int errorCd) {
    printf("%d\n", errorCd);
    printf("%s\n", errorMsg);
    exit(0);
}

/**
 * クエリ発行(エラーチェック付)
 * errorCd=0: システムエラー
 * errorCd=1: 業務エラー
 */
MYSQL_RES *
query_with_check(char* sql, int isNumCheck, const char* numCheckMsg, int errorCd) 
{
    MYSQL_RES *res;

    mysql_query(conn, sql);
    res = mysql_store_result(conn);
	if(res) {
        if (isNumCheck > 0) {
		    if(mysql_num_rows(res) == 0){
                printf("%d\n", errorCd);
			    printf("%s\n", numCheckMsg);
		        printf("Query Error: %s\n", sql);
			    exit(0);
		    }
        }
        return res;
	} else { // クエリエラー
        printf("%d\n", 2);
        printf("クエリエラー\n");
		printf("Query Error: %s\n", sql);
		exit(0);
	}
}

/**
 * メイン関数
 */
int 
main(int argc, char *argv[])
{
    MYSQL_RES *res;
    MYSQL_ROW row, row2, row3, row4, row5;
	char sql[2000];
    DiaRec *gDia;
    DiaRec *results;

    int argflg;             // 検索方式番号
	char bscd_from[12];     // 乗車バス停(末尾の連番無し)
	char bsname_from[100];  // 乗車バス停名
	char bscd_to[12];       // 降車バス停(末尾の連番無し)
	char bsname_to[100];    // 降車バス停名
	char buscategory_cd[12];// 分類
	char course_id[12];     // コース
	int ybkbn;              // 曜日区分

    /***** 引数取得 *****/
	if(argc != 6) exitSystemErr("引数不足エラー", 2);
	argflg = atoi(argv[1]);
	strcpy(bscd_from, argv[2]);
    strcpy(bscd_to, argv[3]);
	strcpy(buscategory_cd, argv[4]);
	strcpy(course_id, argv[5]);

    /***** メモリ確保 *****/
	gDia = (DiaRec*)malloc(RECNUM * sizeof(DiaRec));
    if(gDia == NULL) exitSystemErr("メモリ確保エラー", 2);
	results = (DiaRec*)malloc(RESULTSNUM * sizeof(DiaRec));
	if(results == NULL) exitSystemErr("メモリ確保エラー", 2); 

#ifdef DEBUG
	// ログファイル
	FILE *fp;
	char *logFile = (char*)"/tmp/log/CalcDelayTime.log";
	if ((fp = fopen(logFile, "a")) == NULL) {
		printf("ファイルオープンエラー");
		exit(1);
	}
    fprintf(fp, "********** CalcDelayTime START **********\n");
#endif

	/***** DB接続初期化 *****/
	conn = mysql_init(NULL);
	if(!conn) exitSystemErr("初期化エラー", 2);
    // 文字コードをUTF-8に設定(日本語のバス停名を取得するため)
    mysql_options(conn, MYSQL_SET_CHARSET_NAME, "utf8");

	/***** DB接続 *****/
	conn = mysql_real_connect(conn, SERVER, USER, PASSWORD, DATABASE, 0, NULL, 0);
	if(!conn) exitSystemErr("接続エラー", 2); 

    /***** バス停名取得 *****/
    if (argflg != 3) { // getBusLocationからの呼び出しでは行わない
        // 出発バス停取得(検索結果表示用)
 	    sprintf(
            sql,
            "SELECT busstop_name FROM t_sbt_busstop_lang WHERE SUBSTRING(busstop_id, 1, CHAR_LENGTH(busstop_id) - 2) = '%s' AND lang_cd = 'ja';",
            bscd_from
        );
        res = query_with_check(sql, 1, "出発バス停取得エラー", 2);
	    row = mysql_fetch_row(res);
	    strcpy(bsname_from, row[0]);

        // 到着バス停取得(検索結果表示用)
 	    sprintf(
            sql,
            "SELECT busstop_name FROM t_sbt_busstop_lang WHERE SUBSTRING(busstop_id, 1, CHAR_LENGTH(busstop_id) - 2) = '%s' AND lang_cd = 'ja';",
            bscd_to
        );
        res = query_with_check(sql, 1, "到着バス停取得エラー", 2);
	    row = mysql_fetch_row(res);
	    strcpy(bsname_to, row[0]);
    } else {
        strcpy(bsname_from, "");
        strcpy(bsname_to, "");
    }

	/***** 本日の曜日区分を抽出 *****/
	sprintf(
        sql,
        "SELECT ybkbn FROM t_sbt_calendar WHERE buscompany_id = '%s' AND srvdate = CURDATE();",
        BUSCOMPANY_ID
    );
    res = query_with_check(sql, 1, "曜日区分取得エラー", 2);
	row = mysql_fetch_row(res);
	ybkbn = atoi(row[0]);

    /***** ダイヤ情報から対象系統を絞り込む *****/
	switch (argflg) {
    // 分類と出発バス停のみ指定
    // 現時点では未使用(2015/12/01 上越版)
	case 0:
		sprintf(
            sql,
            "SELECT buscategory_cd, course_id, bin_no, stop_seq, dia_time FROM v_sbt_busdia WHERE buscompany_id = '%s' AND (ybkbn = %d OR ybkbn IS NULL) AND buscategory_cd = '%s' AND SUBSTRING(busstop_id, 1, CHAR_LENGTH(busstop_id) - 2) = '%s' AND first_last_flg != 'L' ORDER BY buscategory_cd, course_id, bin_no;",
            BUSCOMPANY_ID,
            ybkbn,
            buscategory_cd,
            bscd_from
        );
        res = query_with_check(sql, 1, "対象系統なしエラー", 2);
		break;

    // 出発バス停、到着バス停のみ指定
    // 画面からの検索に使用
	case 1:
		sprintf(
            sql,
            "SELECT A.buscategory_cd, A.course_id, A.bin_no, A.stop_seq, B.stop_seq, A.dia_time, B.dia_time FROM v_sbt_busdia A, v_sbt_busdia B WHERE A.buscompany_id = '%s' AND (A.ybkbn = %d OR A.ybkbn IS NULL) AND SUBSTRING(A.busstop_id, 1, CHAR_LENGTH(A.busstop_id) - 2) = '%s' AND SUBSTRING(B.busstop_id, 1, CHAR_LENGTH(B.busstop_id) - 2) = '%s' AND (A.ybkbn = B.ybkbn OR B.ybkbn IS NULL) AND A.buscategory_cd = B.buscategory_cd AND A.course_id = B.course_id AND A.bin_no = B.bin_no AND A.stop_seq < B.stop_seq ORDER BY A.buscategory_cd, A.course_id, A.bin_no;",
            BUSCOMPANY_ID,
            ybkbn,
            bscd_from,
            bscd_to
        );
        res = query_with_check(sql, 2, "出発バス停->到着バス停の順に経由する路線がありません。路線を確認の上、バス停を指定して下さい。", 0);
		break;

    // 路線、出発バス停、到着バス停指定
    // 画面からの検索に使用
	case 2:
		sprintf(
            sql,
            "SELECT A.buscategory_cd, A.course_id, A.bin_no, A.stop_seq, B.stop_seq, A.dia_time, B.dia_time FROM v_sbt_busdia A, v_sbt_busdia B WHERE A.buscompany_id = '%s' AND (A.ybkbn = %d OR A.ybkbn IS NULL) AND SUBSTRING(A.busstop_id, 1, CHAR_LENGTH(A.busstop_id) - 2) = '%s' AND SUBSTRING(B.busstop_id, 1, CHAR_LENGTH(B.busstop_id) - 2) = '%s' AND A.buscategory_cd = '%s' AND A.course_id = '%s' AND (A.ybkbn = B.ybkbn OR B.ybkbn IS NULL) AND A.buscategory_cd = B.buscategory_cd AND A.course_id = B.course_id AND A.bin_no = B.bin_no and A.stop_seq < B.stop_seq ORDER BY A.buscategory_cd, A.course_id, A.bin_no;",
            BUSCOMPANY_ID,
            ybkbn,
            bscd_from,
            bscd_to,
            buscategory_cd,
            course_id
        );
        res = query_with_check(sql, 2, "指定路線は出発バス停->到着バス停を経由しません。路線を確認の上、バス停を指定して下さい。", 0);
		break;

    // 分類、路線、出発バス停指定
    // バスの現在地取得時の遅れ時間を取得する際に使用
	case 3:
		sprintf(
            sql,
//            "SELECT buscategory_cd, course_id, bin_no, stop_seq, dia_time FROM v_sbt_busdia WHERE buscompany_id = '%s' AND ybkbn = %d AND SUBSTRING(busstop_id, 1, CHAR_LENGTH(busstop_id) - 2) = '%s' AND buscategory_cd = '%s' AND course_id = '%s' and first_last_flg != 'L' ORDER BY buscategory_cd, course_id, bin_no;",
            "SELECT buscategory_cd, course_id, bin_no, stop_seq, dia_time FROM v_sbt_busdia WHERE buscompany_id = '%s' AND (ybkbn = %d OR ybkbn IS NULL) AND SUBSTRING(busstop_id, 1, CHAR_LENGTH(busstop_id) - 2) = '%s' AND buscategory_cd = '%s' AND course_id = '%s' ORDER BY buscategory_cd, course_id, bin_no;",
            BUSCOMPANY_ID,
            ybkbn,
            bscd_from,
            buscategory_cd,
            course_id
        );
        res = query_with_check(sql, 2, "指定路線上に出発バス停がありません。条件を指定しなおしてください。", 1);
		break;
	}

#ifdef DEBUG
        fprintf(fp, "ダイヤ情報から対象系統を絞り込む(件数: %d, flg: %d)\n", mysql_num_rows(res), argflg);
        fprintf(fp, "SQL: %s\n", sql);
#endif

    /***** クエリ結果をgDiaに格納 *****/
	int i = 0;
	while((row2 = mysql_fetch_row(res)) != NULL) {
		strcpy(gDia[i].buscategory_cd, row2[0]);
		strcpy(gDia[i].course_id, row2[1]);
		strcpy(gDia[i].bin_no, row2[2]);
		gDia[i].delay = 0;
		gDia[i].stop_seq_from = atoi(row2[3]);
        if (argflg == 0 || argflg == 3) {
    		gDia[i].stop_seq_to = 999;
	    	strcpy(gDia[i].dia_time_from , row2[4]);
		    strcpy(gDia[i].dia_time_to , "99:99:99");
        } else if (argflg == 1 || argflg == 2) {
    		gDia[i].stop_seq_to = atoi(row2[4]);
	    	strcpy(gDia[i].dia_time_from , row2[5]);
		    strcpy(gDia[i].dia_time_to , row2[6]);
        }
		i++;
	}

	//i:抽出された路線数
//	int delay = 0;
	strcpy(buscategory_cd, "");	// ループ中のエリアCD
    strcpy(course_id, ""); // ループ中の路線CD
	int isFound = 0; // ループ中のエリア・路線において検索結果となる便を見つけたか否かのフラグ
	int rIdx = 0; // 検索結果配列のindex

	for(int j = 0 ; j < i ; j++){
		// エリア･路線の確認
		if (strcmp(buscategory_cd, gDia[j].buscategory_cd) != 0 || strcmp(course_id, gDia[j].course_id) != 0) {
#ifdef DEBUG
        fprintf(fp, "エリア・路線に変更あり\n");
#endif
			// エリア・路線に変更があれば、新たなエリア・路線をセットしフラグをリセット
			strcpy(buscategory_cd, gDia[j].buscategory_cd);
			strcpy(course_id, gDia[j].course_id);
			isFound = 0;
		}
        // *********************************************************** //
        // 下記のコメントアウトを外すと、各便の検索結果が1件のみになる
        // *********************************************************** //
        /*
        else {
			// 分類・路線に変更がなく、検索結果となる便を既に見つけているならその後の便は判定しない
			if (isFound == 1) continue;
		}
        */

		//運行中バス抽出
		sprintf(
            sql,
            "SELECT device_id, stop_seq FROM t_sbt_bus_current_route WHERE buscompany_id = '%s' AND bin_no = '%s' AND reg_time > CURDATE() ORDER BY stop_seq DESC;",
            BUSCOMPANY_ID,
            gDia[j].bin_no
        );
        res = query_with_check(sql, 0, NULL, 2);
#ifdef DEBUG
        fprintf(fp, "運行中バス抽出\n");
        fprintf(fp, "SQL: %s\n", sql);
#endif

		// 現在のエリア・路線・便で運行中のバスが存在すれば
		if (mysql_num_rows(res) > 0) {
#ifdef DEBUG
        fprintf(fp, "運行中のバスが存在(bin_no: %s)\n", gDia[j].bin_no);
#endif
			row3 = mysql_fetch_row(res);
			int startingcd = atoi(row3[1]);
			// startingcdで状況を判定
// 始発バス停～2番目のバス停の遅れ時間を取得できるようにするため、コメントアウト
/*
			if (startingcd == 1) { // 始発バス停にいるのでダイヤどおり
#ifdef DEBUG
        fprintf(fp, "始発バス停にいる\n");
#endif
				// 何もしない
			} else
*/
            if (startingcd >= gDia[j].stop_seq_from) { // 出発バス停を既に過ぎている
#ifdef DEBUG
        fprintf(fp, "出発バス停を既に過ぎている\n");
#endif
				// 次の便へ
				continue;
			} else { // 出発バス停より手前
#ifdef DEBUG
        fprintf(fp, "出発バス停より手前\n");
        fprintf(fp, "遅れ判定中...\n");
#endif
				// 遅れ判定
				//直近バス停到着時刻抽出
				sprintf(
                    sql,
                    "SELECT device_id, busstop_id, gps_time FROM t_sbt_busprobe WHERE device_id = '%s' AND busstop_id != 0 AND gps_time = (SELECT MAX(gps_time) FROM t_sbt_busprobe WHERE device_id = '%s' AND busstop_id != 0);",
                    row3[0], 
                    row3[0]
                );
                res = query_with_check(sql, 1, "直近バス停到着なしエラー", 2);
				row4 = mysql_fetch_row(res);
#ifdef DEBUG
        fprintf(fp, "直近バス停到着時刻抽出(件数：%d)\n", mysql_num_rows(res));
        fprintf(fp, "SQL: %s\n", sql);
#endif

				//直近バス停ダイヤ時刻抽出
				sprintf(
                    sql,
                    "SELECT dia_time FROM t_sbt_busdia WHERE buscompany_id = '%s' AND bin_no = '%s' AND stop_seq >= %d;",
                    BUSCOMPANY_ID,
                    gDia[j].bin_no,
                    startingcd
                );
                res = query_with_check(sql, 1, "直近バス停ダイヤ時刻抽出エラー", 2);
				row5 = mysql_fetch_row(res);
#ifdef DEBUG
        fprintf(fp, "直近バス停ダイヤ時刻抽出(件数：%d)\n", mysql_num_rows(res));
        fprintf(fp, "SQL: %s\n", sql);
#endif

				char YY[3];
				char MM[3];
				char DD[3];
				char hh[3];
				char mm[3];
				char ss[3];
				strcpy(YY,"15");
				strcpy(MM,"01");
				strcpy(DD,"01");
				strncpy(hh,row4[2]+11,2);
				strncpy(mm,row4[2]+14,2);
				strncpy(ss,row4[2]+17,2);
				time_t last_bustime = toTime2(YY,MM,DD,hh,mm,ss);

				strncpy(hh,row5[0],2);
				strncpy(mm,row5[0]+3,2);
				strncpy(ss,row5[0]+6,2);
				time_t last_diatime = toTime2(YY,MM,DD,hh,mm,ss);

				int delay = (int)(last_bustime - last_diatime);
				if (delay < 0) delay = 0;
				gDia[j].delay = (int)(delay / 60);

				//乗車バス停時刻、降車バス停時刻(降車バス停の指定があれば)に遅れ時間を加算
				struct tm *dia_time_from_tm,*dia_time_to_tm;

				strncpy(hh,gDia[j].dia_time_from,2);
				strncpy(mm,gDia[j].dia_time_from+3,2);
				strncpy(ss,gDia[j].dia_time_from+6,2);
				time_t dia_time_from = toTime2(YY,MM,DD,hh,mm,ss);

				dia_time_from = dia_time_from + delay;
				dia_time_from_tm = localtime(&dia_time_from);
				sprintf(gDia[j].dia_time_from,"%02d:%02d:%02d\0",dia_time_from_tm->tm_hour,dia_time_from_tm->tm_min,dia_time_from_tm->tm_sec);
				
				if (gDia[j].stop_seq_to != 999) {
					strncpy(hh,gDia[j].dia_time_to,2);
					strncpy(mm,gDia[j].dia_time_to+3,2);
					strncpy(ss,gDia[j].dia_time_to+6,2);
					time_t dia_time_to = toTime2(YY,MM,DD,hh,mm,ss);

					dia_time_to = dia_time_to + delay;
					dia_time_to_tm = localtime(&dia_time_to);
					sprintf(gDia[j].dia_time_to  ,"%02d:%02d:%02d\0",dia_time_to_tm->tm_hour,dia_time_to_tm->tm_min,dia_time_to_tm->tm_sec);
				}

				// 現在のダイヤ情報を結果配列に追加、発見フラグを立てて次の便へ
				results[rIdx] = gDia[j];
				rIdx++;
				isFound = 1;
				continue;
			}
		}

#ifdef DEBUG
        fprintf(fp, "運行中のバスが存在しない、または始発バス停にいる\n");
#endif

		// 運行中のバスが存在しない、または始発バス停にいる場合は出発バス停のダイヤ時刻を判定
		int flg = 0;
		// 現在時取得
		time_t now;
		struct tm *now_tm;
		now = time(NULL);
		now_tm = localtime(&now);
		int now_hour,now_min;
		now_hour = now_tm->tm_hour;
		now_min = now_tm->tm_min;
		// 出発バス停のダイヤ時刻
		char dia_hour[3], dia_min[3];
		strncpy(dia_hour, gDia[j].dia_time_from, 2);
		strncpy(dia_min, gDia[j].dia_time_from + 3, 2);
		// 終端文字挿入
		dia_hour[2] = '\n';
		dia_min[2] = '\n';
		// 時、分を比較
		if (atoi(dia_hour) > now_hour) {
			flg = 1;
		} else if (atoi(dia_hour) == now_hour) {
			if (atoi(dia_min) > now_min) {
				flg = 1;
			}
		}
		// ダイヤ時刻が未来なら結果配列に追加、発見フラグを立てる
		if (flg == 1) {
			results[rIdx] = gDia[j];
			rIdx++;
			isFound = 1;
		}
	}

	//jsonを生成
	if(i == 0) {
        printf("%d\n", 1);
		printf("検索条件に適合する路線はありません。");
		exit(0); // 路線なしエラー(起こりえないケース)
	}

	if (rIdx == 0) {
        printf("%d\n", 0);
		printf("検索結果は０件です。");
		exit(0); // 検索した時刻にダイヤ・走行バスが存在しない
	}

	qsort( results, rIdx, sizeof(DiaRec), comp );//出力対象構造体を時刻でソート

	printf("{\"n\":%d,", rIdx);
	printf("\"results\":[");
	int m = 0;
	printf("{\"areacd\":\"%s\",\"syscd\":\"%s\",\"diano\":\"%s\",\"delay\":%d,\"from\":\"%s\",\"to\":\"%s\",\"bscd_from\":\"%s\",\"bscd_to\":\"%s\",\"bsname_from\":\"%s\",\"bsname_to\":\"%s\",\"stop_seq_from\":\"%d\",\"stop_seq_to\":\"%d\"}",results[m].buscategory_cd,results[m].course_id,results[m].bin_no,results[m].delay,results[m].dia_time_from,results[m].dia_time_to,bscd_from,bscd_to,bsname_from,bsname_to,results[m].stop_seq_from, results[m].stop_seq_to);
	for(m = 1; m < rIdx; m++){
		printf(",{\"areacd\":\"%s\",\"syscd\":\"%s\",\"diano\":\"%s\",\"delay\":%d,\"from\":\"%s\",\"to\":\"%s\",\"bscd_from\":\"%s\",\"bscd_to\":\"%s\",\"bsname_from\":\"%s\",\"bsname_to\":\"%s\",\"stop_seq_from\":\"%d\",\"stop_seq_to\":\"%d\"}",results[m].buscategory_cd,results[m].course_id,results[m].bin_no,results[m].delay,results[m].dia_time_from,results[m].dia_time_to,bscd_from,bscd_to,bsname_from,bsname_to,results[m].stop_seq_from, results[m].stop_seq_to);
	}
	printf("]");
	printf("}\n");

	mysql_close(conn);

#ifdef DEBUG
    fprintf(fp, "********** CalcDelayTime END **********\n");
    fclose(fp);    
#endif

    return rIdx;
}


time_t toTime1(char *YYMMDDhhmmss)
{
	int YY,MM,DD;
	int hh,mm,ss;
	char cYY[3],cMM[3],cDD[3];
	char chh[3],cmm[3],css[3];
	memcpy(cYY, &YYMMDDhhmmss[0], 2);
	cYY[2]='\0';
	YY = atoi(cYY);
	memcpy(cMM, &YYMMDDhhmmss[2], 2);
	cMM[2]='\0';
	MM = atoi(cMM);
	memcpy(cDD, &YYMMDDhhmmss[4], 2);
	cDD[2]='\0';
	DD = atoi(cDD);
	memcpy(chh, &YYMMDDhhmmss[6], 2);
	chh[2]='\0';
	hh = atoi(chh);
	memcpy(cmm, &YYMMDDhhmmss[8], 2);
	cmm[2]='\0';
	mm = atoi(cmm);
	memcpy(css, &YYMMDDhhmmss[10], 2);
	css[2]='\0';
	ss = atoi(css);

	if( YY < 100 ) YY += 2000;
	struct tm tm;
	tm.tm_year	= YY-1900;
	tm.tm_mon	= MM-1;
	tm.tm_mday	= DD;
	tm.tm_hour	= hh;
	tm.tm_min	= mm;
	tm.tm_sec	= ss;
	tm.tm_isdst	= 0;
	time_t t = mktime(&tm);
	return t;
}


time_t toTime2(char *YY,char *MM,char *DD,char *hh,char *mm,char *ss)
{
	int iYY,iMM,iDD;
	int ihh,imm,iss;
	iYY = atoi(YY);
	iMM = atoi(MM);
	iDD = atoi(DD);
	ihh = atoi(hh);
	imm = atoi(mm);
	iss = atoi(ss);
	
	if( iYY < 100 ) iYY += 2000;
	struct tm tm;
	tm.tm_year	= iYY-1900;
	tm.tm_mon	= iMM-1;
	tm.tm_mday	= iDD;
	tm.tm_hour	= ihh;
	tm.tm_min	= imm;
	tm.tm_sec	= iss;
	tm.tm_isdst	= 0;
	time_t t = mktime(&tm);
	return t;
	
}

int comp( const void *c1, const void *c2 )
{
	DiaRec value1 = *(DiaRec *)c1;
	DiaRec value2 = *(DiaRec *)c2;

	time_t t1, t2;
	char tmp1[10];
	char tmp2[10];
        char YY[3];
        char MM[3];
        char DD[3];
        char hh[3];
        char mm[3];
        char ss[3];

	strcpy(tmp1, value1.dia_time_from);
	strcpy(tmp2, value2.dia_time_from);
        strcpy(YY, "15");
        strcpy(MM, "01");
        strcpy(DD, "01");
        strncpy(hh, tmp1, 2);
        strncpy(mm, tmp1 + 3, 2);
        strncpy(ss, tmp1 + 6, 2);
	t1 = toTime2(YY, MM, DD, hh, mm, ss);

        strncpy(hh, tmp2, 2);
        strncpy(mm, tmp2 + 3, 2);
        strncpy(ss, tmp2 + 6, 2);
	t2 = toTime2(YY, MM, DD, hh, mm, ss);

	if(t2 < t1){
		return 1;
	} else if(t2 > t1){
		return -1;
	} else {
		return 0;
	}
}

