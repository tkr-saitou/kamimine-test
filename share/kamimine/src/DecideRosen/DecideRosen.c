/* Copyright (C) 2014 TCI All Rights Reserved */

#include "stdio.h"
#include "my_global.h"
#include "mysql.h"
#include <string.h>
#include "variables.h"

#define RECNUM 10000
#define MINIMUMSTAYTIME 5

/***** 型定義 *****/
struct DiaRec {
	char buscategory_cd[12];
	char course_id[12];
	char bin_no[12];
    char busstop_id[12];
    char first_last_flg[3];
    char dia_time[10];
    double lat;
    double lng;
};

/***** グローバル変数 *****/
MYSQL *conn;

/**
 * クエリ発行(エラーチェック付)
 */
MYSQL_RES *
query_with_check(char* sql, int isNumCheck, const char* numCheckMsg) 
{
    MYSQL_RES *res;

    mysql_query(conn, sql);
    res = mysql_store_result(conn);
	if(res){
        if (isNumCheck) {
		    if(mysql_num_rows(res) == 0){
			    printf("%s\n", numCheckMsg);
		        printf("Query Error: %s\n", sql);
			    exit(1);
		    }
        }
        return res;
	} else {
		printf("Query Error: %s\n", sql);
		exit(1);
	}
}

/**
 * メイン関数
 */
int
main(int argc, char *argv[])
{
    MYSQL_RES *res,*res2,*res3;
    MYSQL_ROW row,row2,row3;
	char sql[2000];

    DiaRec *gDia;
	char device_id[38];
	int stop_seq;
    char shift_cd[12];

    /***** 引数取得 *****/
	strcpy(device_id, argv[1]);
	stop_seq = atoi(argv[2]);
    strcpy(shift_cd, argv[3]);

    /***** メモリ確保 *****/
    gDia = (DiaRec*)malloc(RECNUM * sizeof(DiaRec));
	if(gDia == NULL) { printf("メモリ確保エラー"); exit(1); }

#ifdef DEBUG
	// ログファイル
	FILE *fp;
	char *logFile = (char*)"/tmp/log/DecideRosen.log";
	if ((fp = fopen(logFile, "a")) == NULL) {
		printf("ファイルオープンエラー");
		exit(1);
	}
    fprintf(fp, "********** DecideRosen START **********\n");
#endif

	/***** DB接続初期化 *****/
	conn = mysql_init(NULL);
	if(!conn){ printf("DB初期化エラー"); exit(1); }

	/***** DB接続 *****/
	conn = mysql_real_connect(conn, SERVER, USER, PASSWORD, DATABASE, 0, NULL, 0);
	if(!conn){ printf("DB接続エラー"); exit(1); }

	/***** 直近60以内のプローブデータ抽出 *****/
	sprintf(
        sql,
        "SELECT device_id, lat, lng, velocity, gps_time, busstop_id FROM t_sbt_busprobe WHERE gps_time>= (NOW() - INTERVAL 60 SECOND) AND device_id = '%s';",
        device_id
    );
    res = query_with_check(sql, 1, "プローブデータ抽出エラー");
#ifdef DEBUG
        fprintf(fp, "直近60以内のプローブデータ抽出(件数：%d件)\n", mysql_num_rows(res));
        fprintf(fp, "SQL: %s\n", sql);
#endif

	/***** 直近±120秒以内のダイヤ情報抽出 *****/
	sprintf(
        sql,
        "SELECT VDIA.buscategory_cd, VDIA.course_id, VDIA.bin_no, VDIA.busstop_id, VDIA.first_last_flg, VDIA.dia_time, IFNULL(BS.lat, '0'), IFNULL(BS.lng, '0') FROM v_sbt_busdia VDIA JOIN t_sbt_busstop BS USING(busstop_id) LEFT JOIN t_sbt_calendar CA ON CA.buscompany_id = VDIA.buscompany_id AND (CA.ybkbn = VDIA.ybkbn OR VDIA.ybkbn IS NULL) WHERE VDIA.buscompany_id = '%s' AND CA.srvdate = CURDATE() AND VDIA.first_last_flg = 'F' AND VDIA.dia_time BETWEEN time(NOW() - INTERVAL 120 SECOND) AND time(NOW() + INTERVAL 120 SECOND) AND VDIA.bin_no IN (SELECT bin_no FROM t_sbt_shift_pattern_list WHERE buscompany_id = '%s' AND shift_pattern_cd = '%s' AND ybkbn = (SELECT ybkbn FROM t_sbt_calendar WHERE buscompany_id = '%s' AND srvdate = CURDATE())) ORDER BY VDIA.dia_time;",
        BUSCOMPANY_ID,
        BUSCOMPANY_ID,
        shift_cd,
        BUSCOMPANY_ID
    );
    res3 = query_with_check(sql, 0, "ダイヤ情報抽出エラー");
#ifdef DEBUG
        fprintf(fp, "直近±120秒以内のダイヤ情報抽出(件数：%d件)\n", mysql_num_rows(res3));
        fprintf(fp, "SQL: %s\n", sql);
#endif
    
	int i = 0;
	while((row3 = mysql_fetch_row(res3)) != NULL){
		strcpy(gDia[i].buscategory_cd, row3[0]);
		strcpy(gDia[i].course_id, row3[1]);
		strcpy(gDia[i].bin_no, row3[2]);
		strcpy(gDia[i].busstop_id, row3[3]);
		strcpy(gDia[i].first_last_flg, row3[4]);
		strcpy(gDia[i].dia_time , row3[5]);
		gDia[i].lat = atof(row3[6]);
		gDia[i].lng = atof(row3[7]);
		i++;
	}

	double mindist = 99999999;
	char minbuscategory_cd[12];
	char mincourse_id[12];
	char minbin_no[12];
	char minbusstop_id[12];
	double minlat = 0;
	double minlng = 0;
	int minvel = 0;
	char mingpstime[20];
	char minfirst_last_flg[3];
    char mindia_time[10];
	int points = 0;
	int inflg = 0;
	while((row = mysql_fetch_row(res)) != NULL){ //プローブデータ1件fetch

		double a = atof(row[1]);
		double b = atof(row[2]);
		if(a == 0 || b == 0) continue;
        inflg = 0;

		for(int j = 0; j < i ; j++){
			double c = gDia[j].lat;
			double d = gDia[j].lng;

			//inflg = 0;
			if(c == 0 || d == 0) continue;

			double rad_a = a*PI/180;
			double rad_b = b*PI/180;
			double rad_c = c*PI/180;
			double rad_d = d*PI/180;
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
#ifdef DEBUG
        fprintf(fp, "2点間距離D: %lf\n", D);
#endif
			if(D < 50) {
				inflg = 1;
				if(mindist > D){
					mindist = D;
					strcpy(minbuscategory_cd, gDia[j].buscategory_cd);
					strcpy(mincourse_id, gDia[j].course_id);
					strcpy(minbin_no, gDia[j].bin_no);
					strcpy(minbusstop_id, gDia[j].busstop_id);
					strcpy(minfirst_last_flg, gDia[j].first_last_flg);
					strcpy(mindia_time, gDia[j].dia_time);
					strcpy(mingpstime,row[4]);
				}
			}
			
		}
		if(inflg > 0) points++;
	}
#ifdef DEBUG
        fprintf(fp, "2点間距離が30m以内のプローブの数: %d\n", points);
#endif

	int returnflg = 0;
	if(points > MINIMUMSTAYTIME){
#ifdef DEBUG
        fprintf(fp, "路線判定成功\n");
#endif

		sprintf(
            sql,
            "UPDATE t_sbt_busprobe SET buscategory_cd = '%s', course_id = '%s', bin_no = '%s', busstop_id = '%s' WHERE device_id = '%s' AND gps_time >= '%s' AND buscategory_cd = 0 AND (course_id = 0 OR course_id IS NULL);",
            minbuscategory_cd,
            mincourse_id,
            minbin_no,
            minbusstop_id,
            device_id,
            mingpstime
        );
		mysql_query(conn, sql);
#ifdef DEBUG
        fprintf(fp, "t_sbt_busprobeをUPDATE\n");
        fprintf(fp, "SQL: %s\n", sql);
#endif

		sprintf(
            sql,
            "UPDATE t_sbt_bus_current_route SET bin_no = '%s', busstop_id = '%s', stop_seq = 1, reg_time = NOW() WHERE device_id = '%s';",
            minbin_no,
            minbusstop_id,
            device_id
        );
		mysql_query(conn, sql);		
#ifdef DEBUG
        fprintf(fp, "t_sbt_bus_current_routeをUPDATE\n");
        fprintf(fp, "SQL: %s\n", sql);
#endif

		sprintf(
            sql,
            "INSERT INTO t_sbt_busdia_actual VALUES(CURDATE(), '%s', '%s', 1, '%s', '%s', '%s', NULL, 'ADMIN', NOW(), NULL, 'ADMIN', NOW(), NULL);",
            BUSCOMPANY_ID,
            minbin_no,
            minbusstop_id,
            mindia_time,
            mingpstime // DATETIME型をTIME型に入れると自動的にTIME型を抽出するため未変換でinsert
        );
		if (mysql_query(conn, sql)) {
#ifdef DEBUG
            fprintf(fp, "クエリエラー\n");
            fprintf(fp, "SQL: %s\n", sql);
#endif
            printf("クエリエラー");
		    exit(1);
	    }


//	} else if(points <= MINIMUMSTAYTIME && stop_seq == 1){
    } else {
#ifdef DEBUG
        fprintf(fp, "points <= MINIMUMSTAYTIME && stop_seq == 1\n");
#endif
		returnflg = 2;
	}
    
	mysql_close(conn);

#ifdef DEBUG
    fprintf(fp, "********** DecideRosen END **********\n");
    fclose(fp);    
#endif

    return returnflg;

}
