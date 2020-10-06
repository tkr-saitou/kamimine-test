/* ---------------------------------------------------------------------------------
 * グローバル変数 
 * -------------------------------------------------------------------------------*/
var isAutoLoading = true;
var updateTimer;
var listLength = 0; // 検索結果件数保持用

// 定数はgetVariables.phpでPHP側から取得する
var DEMO;
var current_pos;
var defaultLat;
var defaultLng ;
var zoom;
var buscategory_cd;
/* ---------------------------------------------------------------------------------
 * 初期処理
 * -------------------------------------------------------------------------------*/
$(function() {
    AppInitialize();
});

/**
 * variables.phpからJSで必要な情報を取得後、初期化処理
 */
function AppInitialize() {
    var opts = {
        signage_enable: 1,
	};
    tci.runApi("./ajax/getVariables.php", opts, function(response) {
        if (response.status == 0) { // 正常終了
            var variables = response.variables;
            DEMO = variables["demo"];
            current_pos = variables["current_pos"];
            defaultLat = variables["defaultLat"];
            defaultLng = variables["defaultLng"];
            zoom = variables["zoom"];
            buscategory_cd = variables["buscategory_cd"];
            // 初期化
            initialize();
        } else { // その他の予期せぬエラー
            tci.systemErr();
        }
    });
}

function initialize() {
    var init_coordinates = { "lat" : defaultLat, "lng" : defaultLng };

    // お知らせテロップ表示
	//setNewsTelop();

    // イベントリスナーを登録する
    registerListener(init_coordinates);

    // MAP初期化
    drawmap.mapInitialize(init_coordinates["lat"], init_coordinates["lng"], false);

    // MAPにバスアイコンを初期表示
    //drawmap.drawBusMarkerIcon(buscategory_cd, 0);
}

/**
 * 現在時刻の最新化
 */
function changeUpdTime() {
	var updtime = new Date();
	var hour = ("0" + updtime.getHours()).slice(-2);
	var minutes = ("0" + updtime.getMinutes()).slice(-2);
	$('#current_time').html(hour + ":" + minutes);
}

/**
 * 運行時間内であるかを判定
 */
function checkServiceTime() {
    tci.runApi("./ajax/checkServiceTime.php", null, function(response) {
		if (response.status == 0) { // 運行中
			$('#situation').html('現在は運行時間中です。');
		} else if (response.status == 1) { // 運行時間外
			$('#situation').html('<red>現在は運行時間外です。</red>');
		} else { // その他予期せぬエラー
            tci.systemErr();
		}
	});
}

/**
 * ニューステロップの表示
 */
/*
function setNewsTelop() {
    tci.runApi("./ajax/getNewsTelop.php", null, function(response) {
		if (response.status == 0) {
			$('#telop').html(response.telop);
		} else { // その他予期せぬエラー
            tci.systemErr();
		}
	});
}
*/

/* ---------------------------------------------------------------------------------
 * イベント登録
 * -------------------------------------------------------------------------------*/
/**
 * イベントリスナーの登録
 */
function registerListener(init_coordinates) {
	// 自動更新有効
	runAutoLoading(isAutoLoading);
}

/* ---------------------------------------------------------------------------------
 * 自動更新関連
 * -------------------------------------------------------------------------------*/
// バスロケ自動更新の開始・停止
function runAutoLoading(flg) {
    updateBusLocation();
    if (flg) { // 開始
        updateTimer = setInterval('updateBusLocation()', 30 * 1000);
    } else { // 停止
        clearInterval(updateTimer);
    }
}

// バス位置情報更新
function updateBusLocation() {

    doSearch(0, 0);

    // 更新時刻更新
    changeUpdTime();

    // 運行時間チェック
	checkServiceTime();
}

/* ---------------------------------------------------------------------------------
 * 検索ボタン押下関連処理
 * -------------------------------------------------------------------------------*/

/**
 * 現在位置取得成功処理
 */
function searchLocation(pos) {
    if(DEMO) {
        doSearch(defaultLat, defaultLng); 
    } else {
        doSearch(pos.coords.latitude, pos.coords.longitude);
    }
}

/**
 * 現在位置取得エラー処理
 */
function searchNoLocation(error) {
    console.log("現在地取得失敗");
	doSearch(0, 0);
}

/**
 * 選択された路線・バス停等より検索を実行、時刻表およびMAPを更新する
 */
function doSearch(lat,lng) {
    // 検索条件設定
    var course_id = $('#course_id').val();
    var opts = {
		buscategory_cd: buscategory_cd,
		course_id: course_id, 
		fromBsCd: $('#fromBS').val(),
		fromLmCd: $('#fromLM').val(),
		toBsCd: $('#toBS').val(),
		toLmCd: $('#toLM').val(),
        currentLat: lat,
        currentLng: lng,
        signage_enable: 1,
	};
	// 表示中の検索結果を消去
	$('#timetable').empty();
    // 検索実行
    tci.runApi("./ajax/searchBus.php", opts, function(obj) {
        // バス停のクリア
        busstopList.forEach(function(marker, idx) {
	    	marker.setMap(null);
    	});
	    busstopList = [];
        // ポリラインのクリア
        routeList.forEach(function(marker, idx) {
	    	marker.setMap(null);
	    });
	    routeList = [];
		if (obj.status == 0) { // 正常終了
			var response = obj.ret;
            listLength = response.n; //response.n: 検索結果件数
			if (listLength > 0) {
                var displaySet = {};
				$.each(response.results, function(i, result) {
                    // 検索結果の生成
					var timetable_html = getTimeTableHtml(i, result, opts);
                    $('#timetable').append(timetable_html);
                    // 系統名の色変更
                    var color = result.route_color;
                    if (color != "") {
                        $("#route" + i).css({
                            'color': color,
                            'border-color': color
                        });
                        drawmap.setRouteColor(result.syscd, result.route_color);
                    }
                    // Map表示用のListを作成
                    if (!(result.areacd in displaySet)) displaySet[result.areacd] = {}
                    if (!(result.syscd in displaySet[result.areacd])) {
                        displaySet[result.areacd][result.syscd] = [];
                    }
                    displaySet[result.areacd][result.syscd].push(
                        {"fromBsCd":result.bscd_from, "toBsCd":result.bscd_to}
                    );
                    list_id = "#result_" + i;
				});

                // 5件ずつ表示する矢印を作成
                if(listLength > 5) {
                    /*
                    $('#timetable').append('<div class="more" onclick=""><a href="#"></a></div>');
                    */
                    // リストの5つめ以降非表示
                    for (var i = 5; i <= listLength; i++) {
                        $('#timetable #result_' + i).hide();
                    }
                }

                // ポリライン、バス停一覧描画後にバス停アイコンの色を変えるための処理
                for (var areaCd in displaySet) {
                    for (var sysCd in displaySet[areaCd]) {
                        if (course_id == 0) { // 路線未選択時
//                            drawmap.drawBusStopMarkers(areaCd, sysCd, displaySet[areaCd][sysCd], false);
                            drawmap.drawBusStopMarkers(areaCd, sysCd, 1, false);
                            drawmap.drawCoursePolyline(areaCd, sysCd, false);
                        } else {
//                            drawmap.drawBusStopMarkers(areaCd, sysCd, displaySet[areaCd][sysCd], true);
                            drawmap.drawBusStopMarkers(areaCd, sysCd, 1, true);
                            drawmap.drawCoursePolyline(areaCd, sysCd, true);
                        }
                    }
                }

                // バスアイコン再描画
                var courseList = obj.course_list;
    		    drawmap.drawMultipleBusMarkerIcon(buscategory_cd, courseList);
			}
		} else if (obj.status == 1) { // エラーメッセージ表示
            // バスアイコンのクリア
        	busList.forEach(function(marker, idx) {
		        marker.setMap(null);
	        });
            tci.showErrModal(obj.errMsg);
		} else { // 予期せぬエラー
            tci.systemErr();
        } 
        tci.showIndicator(false);
	});
}

/**
 * 検索結果のHTMLを生成する
 */
function getTimeTableHtml(i, result, opts) {
	var id = "result_" + i;
    var fromBS = result.bsname_from;
//  var fromTime = result.from.substr(0, 5); // 遅れ時間を加味した時刻
    var fromTime = result.from_dia_time.substr(0, 5);
	var toBS = result.bsname_to;
//	var toTime = result.to.substr(0, 5); // 遅れ時間を加味した時刻
	var toTime = result.to_dia_time.substr(0, 5);
    var binDetail = result.busbin_detail_name;

    // 検索結果HTML描画
	var html = '<div class="bus-info clearfix" id="' + id + '">';

    // 系統情報
    html += '<div class="route" id="route' + i + '"><span class="route-text">' + result.route_name + '</span><span class="bin-text">' + binDetail + '</span></div>';
    // 時刻表
    html += '<div class="depart">'
            + '<div class="title">出発</div>'
            + '<div class="bus-stop"><span>' + fromBS + '</span></div>'
            + '<div class="time"><span>' + fromTime + '</span>発</div>'
        + '</div>'
        + '<div class="arrival">'
            + '<div class="title">到着</div>'
            + '<div class="bus-stop"><span>' + toBS + '</span></div>'
            + '<div class="time"><span>' + toTime + '</span>着</div>'
        + '</div>';
    // 運行情報
    if (result.except_delay_flg != 1) {
        if (result.flg == 0) {
            html += '<div class="bus-info-text">運行予定</div>';
        } else if(result.flg == 1) {
            html += '<div class="bus-info-text">' + result.counter + '個前のバス停を通過しました';
            if (result.delay == 0) {
                html += '(通常運行)';
            } else {
                html += '(約' + result.delay + '分遅れ)';
            }
            html += '</div>';
        } else {
            html += '<div class="bus-info-text">バスは既にこのバス停を発車しました。</div>';
        }
    }

    html += '</div>';
    return html;
}

