/* Copyright (C) 2014 TCI All Rights Reserved */

#include "stdio.h"
#include "my_global.h"
#include "mysql.h"
#include <string.h>
#include "variables.h"

/***** 型定義 *****/
struct DiaRec {
	char bin_no[12];
	char busstop_id[12];
	double lat;
	double lng;
	char first_last_flg[3];
	int stop_seq;
	char dia_time[20];
};

struct Probe {
	double lat;
	double lng;
	char gpstime[22];
	char busstop_id[12];
};

/***** グローバル変数 *****/
MYSQL *conn;
MYSQL_RES *res1,*res2,*res3;
MYSQL_ROW row1,row2,row3;
Probe *probe;
DiaRec *gDia;

/***** プロトタイプ宣言 *****/
time_t toTime(char *str);
double calcDistance(double lat1, double lng1, double lat2, double lng2);

/**
 * クエリ発行(エラーチェック付)
 */
MYSQL_RES *
query_with_check(char* sql, int isNumCheck, const char* numCheckMsg, int errorCd) 
{
    MYSQL_RES *res;

    mysql_query(conn, sql);
    res = mysql_store_result(conn);
	if(res){
        if (isNumCheck) {
		    if(mysql_num_rows(res) == 0){
			    printf("%s\n", numCheckMsg);
		        printf("Query Error: %s\n", sql);
			    exit(errorCd);
		    }
        }
        return res;
	} else {
		printf("Query Error: %s\n", sql);
		exit(1);
	}
}

/*
argv[1]:device_id
argv[2]:buscategory_cd
argv[3]:course_id
argv[4]:bin_no
argv[5]:now_stop_seq
argv[6]:now_busstop_id
*/
int 
main(int argc, char *argv[])
{
	char buscategory_cd[12];
	char course_id[12];
	char bin_no[12];
	char device_id[38];
	char now_busstop_id[12];
	int now_stop_seq;
	int pCnt, dCnt;
	int i, j;
    int retcd = 0;

	strcpy(device_id,argv[1]);
	strcpy(buscategory_cd, argv[2]);
	strcpy(course_id, argv[3]);
	strcpy(bin_no, argv[4]);
	now_stop_seq = atoi(argv[5]);
	strcpy(now_busstop_id, argv[6]);

	int bsflg = 0;

	int recnum = 10000;
	gDia = (DiaRec*)malloc(recnum * sizeof(DiaRec));
	if(gDia == NULL) {
		printf("メモリ確保エラー");
		exit(1); //メモリ確保エラー
	}

	int pmax = 20;
	probe = (Probe*)malloc(pmax * sizeof(Probe));
	if (probe == NULL) {
		printf("メモリ確保エラー");
		exit(1); //メモリ確保エラー
	}

#ifdef DEBUG
	// ログファイル
	FILE *fp;
	char *logFile = (char*)"/tmp/log/PassingBusStop.log";
	if ((fp = fopen(logFile, "a")) == NULL) {
		printf("ファイルオープンエラー");
		exit(1);
	}
    fprintf(fp, "********** PassingBusStop START **********\n");
#endif

	//Initiarize connect DB
	conn=mysql_init(NULL);
	if(!conn){
		printf("DB初期化エラー");
		exit(1); //DB初期化エラー
	}

	//DB接続
	conn=mysql_real_connect(conn,SERVER,USER,PASSWORD,DATABASE,0,NULL,0);
	if(!conn){
		printf("DB接続エラー");
		exit(1); //DB接続エラー
	}

	// 直近到着バス停の

	// 直近15レコード抽出
    // サーバの時間が狂っていて件数が取れすぎるとセグメントエラーになるためlimitを入れておく
	char query1[2000];
	sprintf(
        query1,
        "SELECT lat, lng, gps_time, busstop_id FROM t_sbt_busprobe WHERE gps_time >= (NOW() - INTERVAL 15 SECOND) and device_id = '%s' limit 20;",
        device_id
    );
    res1 = query_with_check(query1, 1, "Warning: 直近15秒のプローブデータが抽出できませんでした。通信状況等によりプローブデータの受信順序が変わった事が原因であると考えられます。", 2);
#ifdef DEBUG
    fprintf(fp, "直近15レコードのプローブデータ抽出(件数：%d件)\n", mysql_num_rows(res1));
    fprintf(fp, "SQL: %s\n", query1);
#endif

	pCnt = 0;
	while ((row1 = mysql_fetch_row(res1)) != NULL) {
		probe[pCnt].lat = atof(row1[0]);
		probe[pCnt].lng = atof(row1[1]);
		strcpy(probe[pCnt].gpstime, row1[2]);
		strcpy(probe[pCnt].busstop_id, row1[3]);
		pCnt++;
	}

	// 直近バス停最接近時刻を取得
    // 最後に停車または通過したバス停の情報を取得（=recentMin）
	struct Probe recentMin;
	char query2[2000];
	sprintf(
        query2, 
        "SELECT lat, lng, gps_time, busstop_id FROM t_sbt_busprobe WHERE device_id = '%s' AND buscategory_cd = '%s' AND course_id = '%s' AND bin_no = '%s' AND busstop_id <> 0 ORDER BY gps_time DESC limit 1;",
        device_id, 
        buscategory_cd, 
        course_id, 
        bin_no
    );
	mysql_query(conn, query2);
	if (!(res2 = mysql_store_result(conn))) {
		printf("クエリエラー");
		exit(1);
	}
#ifdef DEBUG
    fprintf(fp, "最後に停車または通過したバス停の情報を取得(件数：%d件)\n", mysql_num_rows(res2));
    fprintf(fp, "SQL: %s\n", query2);
#endif

	int numrows2 = mysql_num_rows(res2);
	if (numrows2 == 0) {
		recentMin.lat = 0;
		recentMin.lng = 0;
		strcpy(recentMin.gpstime, "2000-01-01 00:00:00");
		strcpy(recentMin.busstop_id, "0");
	} else {
		row2 = mysql_fetch_row(res2);
		recentMin.lat = atof(row2[0]);
		recentMin.lng = atof(row2[1]);
		strcpy(recentMin.gpstime, row2[2]);
		strcpy(recentMin.busstop_id, row2[3]);
	}

	// 対象便のダイヤ、バス停情報抽出
	char query3[2000];
	sprintf(
        query3,
        "SELECT VDIA.bin_no, VDIA.busstop_id, IFNULL(BS.lat, '0'), IFNULL(BS.lng, '0'), VDIA.first_last_flg, VDIA.stop_seq, CONCAT_WS(' ', CURDATE(), VDIA.dia_time) FROM v_sbt_busdia VDIA JOIN t_sbt_busstop BS USING(busstop_id) LEFT JOIN t_sbt_calendar CA ON CA.buscompany_id = VDIA.buscompany_id AND (CA.ybkbn = VDIA.ybkbn OR VDIA.ybkbn IS NULL) WHERE VDIA.buscompany_id = '%s' AND VDIA.bin_no = '%s' AND CA.srvdate = CURDATE() AND VDIA.dia_time < time(NOW() + INTERVAL 10 MINUTE) ORDER BY VDIA.stop_seq;",
        BUSCOMPANY_ID,
        bin_no
    );
    res3 = query_with_check(query3, 1, "Warning: 直近10分以内にダイヤ情報が存在しません", 2);
#ifdef DEBUG
    fprintf(fp, "対象便のダイヤ、バス停情報抽出(件数：%d件)\n", mysql_num_rows(res3));
    fprintf(fp, "SQL: %s\n", query3);
#endif

	dCnt = 0;
	while((row3 = mysql_fetch_row(res3)) != NULL){
		strcpy(gDia[dCnt].bin_no, row3[0]);
		strcpy(gDia[dCnt].busstop_id, row3[1]);
		gDia[dCnt].lat = atof(row3[2]);
		gDia[dCnt].lng = atof(row3[3]);
		strcpy(gDia[dCnt].first_last_flg, row3[4]);
		gDia[dCnt].stop_seq = atoi(row3[5]);
		strcpy(gDia[dCnt].dia_time, row3[6]);
		dCnt++;
	}

	int minPIdx = 0;
	int minDIdx = 0;
	double mindist = 99999999;
	double oldMinD = 0;
	time_t diaTime = 0;
	int delayTime = 0;
	int flg = dCnt;
	for (i = 0; i < dCnt; i++) { // ダイヤの順路No順にバス停を判定。
		double lat1 = gDia[i].lat;
		double lng1 = gDia[i].lng;
		int stop_seq = gDia[i].stop_seq;
		if (stop_seq < now_stop_seq) { // 通過済確定バス停
#ifdef DEBUG
            fprintf(fp, "通過済確定バス停(stop_seq: %d, now_stop_seq: %d)\n", stop_seq, now_stop_seq);
#endif
			continue;
		} else if (stop_seq == now_stop_seq) { // 直近到着判定バス停
#ifdef DEBUG
            fprintf(fp, "直近到着判定バス停(stop_seq: %d)\n", stop_seq);
#endif
			// 遅れ時間取得
			time_t dtime = toTime(gDia[i].dia_time);
			time_t rtime = toTime(recentMin.gpstime);
			delayTime = difftime(rtime, dtime);
			if (delayTime < 0) delayTime = 0;
			// 前回判定時の距離算出
			oldMinD = calcDistance(lat1, lng1, recentMin.lat, recentMin.lng);
		} else { // 未到着バス停
#ifdef DEBUG
            fprintf(fp, "未到着バス停(stop_seq: %d)\n", stop_seq);
            fprintf(fp, "終了判定flg: %d\n", flg);
#endif
			//上峰町対応 巡回バス
			// 6	0000012801
			// 7	0000012901
			// 8	0000010301
			// 9	0000013101
			// 10	0000012801
			// 11	0000012901
			if (strcmp(course_id, "05") == 0) {
				// 同じバス停が複数回出現する場合、現在そのバス停にいるときは以降の同じバス停は無視
				if (strcmp(now_busstop_id, gDia[i].busstop_id) == 0) {
					continue;
				}
				// 巡回バスでバス停を通過するだけなので無視(ご検知を予防。ただし、補正機能が遅延するデメリットあり)
				if (stop_seq == 9) {
					if (now_stop_seq != 8) {
						continue;
					}
				} else if (stop_seq == 10) {
					if (now_stop_seq == 7) {
						continue;
					}
				}
			}

			// いずれかのバス停の30m以内に入ったら次のバス停まで判定して終了。
			if (flg < 0) break;
			// １つ前のバス停とのダイヤ時刻間隔から短縮可能時間を考慮、遅れ時間を修正。
			if (i > 0) {
				time_t prevDia = toTime(gDia[i - 1].dia_time);
				time_t nowDia = toTime(gDia[i].dia_time);
				int diaDiff = difftime(nowDia, prevDia);
				int reduceTime = 0;
				if (delayTime < 5 * 60) {
					reduceTime = (int)(diaDiff / 4);
				} else if (delayTime < 10 * 60) {
					reduceTime = (int)(diaDiff / 3);
				} else {
					reduceTime = (int)(diaDiff / 2);
				}
				delayTime = delayTime - reduceTime;
				if (delayTime < 0) delayTime = 0;
			}

		}

		diaTime = toTime(gDia[i].dia_time);

        // 各プローブごとにバス停との距離を算出、30m以内で最接近するプローブを取得
		for(j = 0; j < pCnt ; j++){
			// ダイヤ時刻+遅れ時間とプローブ時刻を比較
			time_t gpsTime = toTime(probe[j].gpstime);
#ifdef DEBUG
            fprintf(fp, "ダイヤ時刻+遅れ時間とプローブ時刻を比較\n");
            fprintf(fp, "ダイヤ時刻: %s\n", gDia[i].dia_time);
            fprintf(fp, "遅れ時間: %s\n", recentMin.gpstime);
            fprintf(fp, "gpsTime: %s\n", probe[j].gpstime);
            fprintf(fp, "時間差: %d\n", difftime(diaTime + delayTime - 90, gpsTime));
#endif
//			if (difftime(diaTime + delayTime - 90, gpsTime) > 0) continue;

			double lat2 = probe[j].lat;
			double lng2 = probe[j].lng;

			if(lat2 == 0 || lng2 == 0) continue;
			double D = calcDistance(lat1, lng1, lat2, lng2);
#ifdef DEBUG
            fprintf(fp, "距離D: %lf\n", D);
            fprintf(fp, "緯度経度: %lf, %lf, %lf, %lf\n", lat1, lng1, lat2, lng2);
#endif
			if(stop_seq == now_stop_seq && D < oldMinD && D < 50 && D < mindist
				|| stop_seq > now_stop_seq && D < 50 && D < mindist){
				mindist = D;
				minDIdx = i;
				minPIdx = j;
				flg = 1;
				bsflg = 1;
			}
			
		}
		
		flg = flg - 1;
#ifdef DEBUG
            fprintf(fp, "終了判定flg: %d\n", flg);
#endif

	}

    // 以下のロジックは、プローブデータ(busprobe)を停車バス停ごとに1件だけバス停IDが入っている状態とするために組まれている。
    // プローブデータは、バスの遅れ判定などにも参照する仕様のため、バス停ごとの1件だけになっている必要がある。そういう仕様。
    // gps_time               busstop_id
    // ----------------------+-------------
    // 2015-11-24 13:22:40    0            <- 0000010101バス停にいたが、後で0クリアされている(query4でID更新 -> query5で0クリア)
    // 2015-11-24 13:22:41    0            <- 同上
    // 2015-11-24 13:22:42    0000010101   <- 0000010101バス停を13:22:42に発車した(query4でID更新)
    // 2015-11-24 13:22:43    0            <- 0000010101の次のバス停に向かっている途中（bsflg != 1でループをスルーする）
    //       :
    // 2015-11-24 13:33:10    0000020801   <- 終点である0000020801バス停に13:33:10に到着した(query4でID更新)
    // 2015-11-24 13:33:11    0            <- 終点では到着以降の時間ではバス停IDは0クリアされる（query4でID更新 -> query7で0クリア）
    // 2015-11-24 13:33:12    0            <- 同上
    //       :

    // 最接近したバス停を取得できた場合
	if (bsflg == 1) {
        // 最接近したバス停で、busprobeを更新する
		char query4[2000];
		sprintf(
            query4,
            "UPDATE t_sbt_busprobe SET busstop_id = '%s' WHERE device_id = '%s' AND gps_time = '%s';",
            gDia[minDIdx].busstop_id,
            device_id,
            probe[minPIdx].gpstime
        );
		if (mysql_query(conn,query4)) {
#ifdef DEBUG
            fprintf(fp, "クエリエラー\n");
            fprintf(fp, "SQL: %s\n", query4);
#endif
            printf("クエリエラー");
		    exit(1);
	    }

#ifdef DEBUG
        fprintf(fp, "最接近したバス停で、busprobeを更新する\n");
        fprintf(fp, "SQL: %s\n", query4);
#endif

        // 最接近したバス停＝現在のバス停だった場合（つまり、既にプローブデータがあるバス停のプローブが再度UPされた場合）
        // -> 前回（=recentMin）のプローブデータのバス停IDをクリアする
        // ※「現在のバス停」はquery6の処理で更新されている。
		if (gDia[minDIdx].stop_seq == now_stop_seq) {
#ifdef DEBUG
        fprintf(fp, "最接近したバス停＝現在のバス停だった場合\n");
#endif
			char query5[2000];
			sprintf(
                query5,
                "UPDATE t_sbt_busprobe SET busstop_id = 0 WHERE device_id = '%s' AND gps_time = '%s' AND busstop_id = '%s';",
                device_id,
                recentMin.gpstime,
                recentMin.busstop_id
            );
			if (mysql_query(conn,query5)) {
#ifdef DEBUG
                fprintf(fp, "クエリエラー\n");
                fprintf(fp, "SQL: %s\n", query5);
#endif
                printf("クエリエラー");
		        exit(1);
	        }

#ifdef DEBUG
        fprintf(fp, "前回（=recentMin）のプローブデータのバス停IDをクリアする\n");
        fprintf(fp, "SQL: %s\n", query5);
#endif
		}
        // 最接近したバス停 != 現在のバス停(バス停に初めて到着した時)
        // 運行データ分析用テーブルへのinsert
        else {
#ifdef DEBUG
            fprintf(fp, "最接近したバス停 != 現在のバス停だった場合\n");
#endif
			char query5[2000];
			sprintf(
                query5,
                "INSERT INTO t_sbt_busdia_actual VALUES(CURDATE(), '%s', '%s', %d, '%s', '%s', '%s', NULL, 'ADMIN', NOW(), NULL, 'ADMIN', NOW(), NULL);",
                BUSCOMPANY_ID,
                gDia[minDIdx].bin_no,
				gDia[minDIdx].stop_seq,
                gDia[minDIdx].busstop_id,
                gDia[minDIdx].dia_time,
                probe[minPIdx].gpstime // DATETIME型をTIME型に入れると自動的にTIME型を抽出するため未変換でinsert
            );
			if (mysql_query(conn,query5)) {
#ifdef DEBUG
                fprintf(fp, "クエリエラー\n");
                fprintf(fp, "SQL: %s\n", query5);
#endif
                printf("クエリエラー");
		        exit(1);
	        } else {
                // 運行実績を記録したことをPHP側に通知
                printf("1");
            }
        }

        // 現在データ（t_sbt_bus_current_route）を、最接近したバス停で更新する。
		char query6[2000];
		sprintf(
            query6,
            "UPDATE t_sbt_bus_current_route SET busstop_id = '%s', stop_seq = %d, reg_time = NOW() WHERE device_id = '%s';",
            gDia[minDIdx].busstop_id,
            gDia[minDIdx].stop_seq,
            device_id
        );
		if (mysql_query(conn,query6)) {
#ifdef DEBUG
            fprintf(fp, "クエリエラー\n");
            fprintf(fp, "SQL: %s\n", query6);
#endif
            printf("クエリエラー");
		    exit(1);
	    }

#ifdef DEBUG
        fprintf(fp, "現在データ（t_sbt_bus_current_route）を、最接近したバス停で更新する\n");
        fprintf(fp, "SQL: %s\n", query6);
#endif
        // 終点バス停に到着した場合	
		if(strcmp(gDia[minDIdx].first_last_flg, "L") == 0){
            // 終点バス停は、到着した時間を残す。（終点到着後、そこにずっと停車していても、最初に到着した時間を残す）
#ifdef DEBUG
        fprintf(fp, "終点バス停に到着した場合\n");
#endif
			char query7[2000];
			sprintf(
                query7,
                "UPDATE t_sbt_busprobe SET buscategory_cd = 0, course_id = 0, bin_no = 0, busstop_id = 0 WHERE device_id = '%s' and gps_time > '%s';",
                device_id,
                probe[minPIdx].gpstime
            );
			if (mysql_query(conn,query7)) {
#ifdef DEBUG
                fprintf(fp, "クエリエラー\n");
                fprintf(fp, "SQL: %s\n", query7);
#endif
                printf("クエリエラー");
		        exit(1);
	        }

#ifdef DEBUG
        fprintf(fp, "終点バス停は、到着した時間を残す\n");
        fprintf(fp, "SQL: %s\n", query7);
#endif

            // 終点に着いたら現在データをクリア	
			char query8[2000];
			sprintf(
                query8,
                "UPDATE t_sbt_bus_current_route SET bin_no = 0, busstop_id = 0, stop_seq = 0, reg_time = NOW() WHERE device_id = '%s';",
                device_id
            );
			if (mysql_query(conn,query8)) {
#ifdef DEBUG
                fprintf(fp, "クエリエラー\n");
                fprintf(fp, "SQL: %s\n", query8);
#endif
                printf("クエリエラー");
		        exit(1);
	        }

#ifdef DEBUG
        fprintf(fp, "終点に着いたら現在データをクリア\n");
        fprintf(fp, "SQL: %s\n", query8);
#endif
        retcd = 2;
		}
	}

	mysql_close(conn);

#ifdef DEBUG
    fprintf(fp, "********** PassingBusStop END **********\n");
    fclose(fp);    
#endif

    return retcd;
}

time_t toTime(char *str)
{
	int YY,MM,DD;
	int hh,mm,ss;
	char cYY[3],cMM[3],cDD[3];
	char chh[3],cmm[3],css[3];
	memcpy(cYY, &str[0], 4);
	cYY[2]='\0';
	YY = atoi(cYY);
	memcpy(cMM, &str[5], 2);
	cMM[2]='\0';
	MM = atoi(cMM);
	memcpy(cDD, &str[8], 2);
	cDD[2]='\0';
	DD = atoi(cDD);
	memcpy(chh, &str[11], 2);
	chh[2]='\0';
	hh = atoi(chh);
	memcpy(cmm, &str[14], 2);
	cmm[2]='\0';
	mm = atoi(cmm);
	memcpy(css, &str[17], 2);
	css[2]='\0';
	ss = atoi(css);

	if( YY < 100 ) YY += 2000;
	struct tm tm;
	tm.tm_year      = YY-1900;
	tm.tm_mon       = MM-1;
	tm.tm_mday      = DD;
	tm.tm_hour      = hh;
	tm.tm_min       = mm;
	tm.tm_sec       = ss;
	tm.tm_isdst     = 0;
	time_t t = mktime(&tm);
	return t;
}

double calcDistance(double lat1, double lng1, double lat2, double lng2) {
	double rad_a = lat1*PI/180;
	double rad_b = lng1*PI/180;
	double rad_c = lat2*PI/180;
	double rad_d = lng2*PI/180;
	double lr,sr,e,dy,dx,u_y,W,M,N,D;

	lr = 6378137.0;
	sr = 6356752.314245;
	e = sqrt(((lr*lr)-(sr*sr))/(lr*lr));
	dx = rad_d-rad_b;
	dy = rad_c-rad_a;
	u_y =(rad_a+rad_c)/2;
	W = sqrt(1-(e*e*sin(u_y)*sin(u_y)));
	if(W != 0){
		M = (lr*(1-(e*e)))/(pow(W,3));
		N = lr/W;
	} else {
		M = 0;
		N = 0;
	}
	D = sqrt(pow(dy*M,2)+pow(dx*N*cos(u_y),2));//2点間距離D
	return D;
}
