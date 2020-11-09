/* ---------------------------------------------------------------------------------
 * グローバル変数
 * -------------------------------------------------------------------------------*/
var isAutoLoading = false;
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
    tci.runApi("./ajax/getVariables.php", null, function(response) {
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

    // 路線（Course）ドロップダウン初期化
    setCourseDropdown(buscategory_cd);

    // 出発・到着バス停ドロップダウン初期化
    //	setBusStopSelect(0, 0);
	setBusStopSelect(buscategory_cd, 0);

	// 更新時刻更新
	changeUpdTime();

    // 運行時間チェック
	checkServiceTime();

    // お知らせテロップ表示
    setNewsTelop();

    // イベントリスナーを登録する
    registerListener(init_coordinates);

    // 右上のハンバーガーメニューの初期化
    initMenu();

    // MAP初期化
    drawmap.mapInitialize(init_coordinates["lat"], init_coordinates["lng"]);

    // MAPにバスアイコンを初期表示
    drawmap.drawBusMarkerIcon(buscategory_cd, 0);

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
function setNewsTelop() {
    tci.runApi("./ajax/getNewsTelop.php", null, function(response) {
		if (response.status == 0) {
			$('#telop').html(response.telop);
		} else { // その他予期せぬエラー
            tci.systemErr();
		}
	});
}

/**
 * 右上のハンバーガーメニューの初期化
 */
function initMenu() {
    var menu = $("div.js-menu");
    var search = $("div.js-search");
    menu.hide();
    search.hide();
    $("div.menu").on("click", {a: menu, b: search}, slide);
    $("div.search").on("click", {a: search, b: menu}, slide);
}
function slide(event) {
    if (event.data.a.css("display") === "none") {
        event.data.a.slideDown(250);
        event.data.b.slideUp(250);
    } else {
        event.data.a.slideUp(250);
    }
}

/* ---------------------------------------------------------------------------------
 * イベント登録
 * -------------------------------------------------------------------------------*/
/**
 * イベントリスナーの登録
 */
function registerListener(init_coordinates) {
    //console.log(init_coordinates);
/*
	// 選択した項目を表示する
	$('.custom_select .selector').change(function() {
		var id = $(this).attr('id');
		var selectedText = $('.custom_select .selector#'+id+' option:selected').text();
		var textElem = $('.custom_select_text#'+id).children('span');
		textElem.text(selectedText);
	});

	// エリアが変更されたとき
	$('.selector#area').change(function() {
		var areaCd = $(this).val();
		// デフォルト
		var center = init_coordinates;
		if (areaCd == "101") { // 号
			center = init_coordinates;
		} else if (areaCd == 102) { // バス
			center = LETTER_CENTER;
		}

		// 地図表示変更
		moveCenter(center["lat"], center["lng"], zoom);
		drawmap.drawBusMarkerIcon(areaCd, 0);
		drawmap.drawBusStopMarkers(areaCd, 0);
		drawmap.drawCoursePolyline(areaCd, 0);
		// 検索ボックス表示変更
		searchOptionsIsHide(areaCd);
		setCourseDropdown(areaCd);
		setBusStopSelect(areaCd, 0);
		setLandmarkSelect(areaCd, 0);
		// 更新時刻更新
		changeUpdTime();
	});
*/

	// 路線が選択されたとき
	$('#course_id').change(function() {
		var course_id = $(this).val();
		var center = init_coordinates;
		// var fromBS = document.getElementById("fromBS").value;
		// var toBS = document.getElementById("toBS").value;

        // 検索条件：路線ドロップダウン
        if ($(this).val() == 0) {
            $('#courseText').html('路線<span>をえらぶ</span>');
        } else {
            $('#courseText').html($('#course_id option:selected').text());
        }
		// 地図の中心点変更
		drawmap.moveCenter(center["lat"], center["lng"], zoom);
        // バスアイコン再描画
		drawmap.drawBusMarkerIcon(buscategory_cd, course_id);
        // バス停マーカーリスト再描画
		drawmap.drawBusStopMarkers(buscategory_cd, course_id, null, true, 0, 0);
        // 路線ポリライン再描画
		drawmap.drawCoursePolyline(buscategory_cd, course_id, true);
		// 検索条件：バス停ドロップダウン再設定
		setBusStopSelect(buscategory_cd, course_id, 0, 0);
		// 検索条件：ランドマークドロップダウン再設定
		//setLandmarkSelect(buscategory_cd, course_id);
		// 更新時刻更新
		changeUpdTime();
	});

	// 出発バス停が選択されたとき
	$('#fromBS').change(function() {
			console.log(this);
        if ($(this).val() == 0) {
            $('#fromBSText').html('出発<span>するバス停をえらぶ</span>');
        } else {
						$('#fromBSText').html($('#fromBS option:selected').text());
				}
		var fromBsCd = $(this).val();
		var toBS = document.getElementById("toBS").value;
		var courseId = document.getElementById("course_id").value;
		// console.log(courseId);
		setBusStopSelect(buscategory_cd, courseId, fromBsCd, toBS);
		drawmap.drawBusStopMarkers(buscategory_cd, courseId, 1, true, fromBsCd, toBS);
		// バス停の色変更
		drawmap.changeBusStopIcon(fromBsCd, buscategory_cd, 1, true);
		// drawmap.drawBusStopMarkers(buscategory_cd, courseId, 1, true, fromBsCd, toBS);
		// 検索条件：バス停ドロップダウン再設定
		// setBusStopSelect(buscategory_cd, courseId, fromBsCd, toBS);
		// drawmap.drawBusStopMarkers(buscategory_cd, courseId, null, true, fromBsCd, toBS);
	});

	// 到着バス停が選択されたとき
	$('#toBS').change(function() {
				// console.log(this);
        if ($(this).val() == 0) {
            $('#toBSText').html('到着<span>するバス停をえらぶ</span>');
        } else {
						$('#toBSText').html($('#toBS option:selected').text());
        }
		var toBsCd = $(this).val();
		var fromBS = document.getElementById("fromBS").value;
		var courseId = document.getElementById("course_id").value;
		// console.log(courseId);
		// drawmap.drawBusStopMarkers(buscategory_cd, courseId, 1, false, fromBS, toBsCd);
		setBusStopSelect(buscategory_cd, courseId, fromBS, toBsCd);
		drawmap.drawBusStopMarkers(buscategory_cd, courseId, 1, false, fromBS, toBsCd);
		// バス停の色変更
		drawmap.changeBusStopIcon(toBsCd, buscategory_cd, 2, true);
		// drawmap.drawBusStopMarkers(buscategory_cd, courseId, 1, false, fromBS, toBsCd);
		// 検索条件：バス停ドロップダウン再設定
		// setBusStopSelect(buscategory_cd, courseId, fromBS, toBsCd);
		// drawmap.drawBusStopMarkers(buscategory_cd, courseId, null, true, fromBS, toBsCd);
		console.log(course_id);

	});

    /*
	// 出発主要施設が選択されたとき
	$('.selector#fromLM').change(function() {
		var fromBsCd = $(this).val();
		var areaCd = $('.selector#area').val();
		// バス停の色変更？
		changeBusStopIcon(fromBsCd, areaCd, 1);
	});

	// 到着主要施設が選択されたとき
	$('.selector#toLM').change(function() {
		var toBsCd = $(this).val();
		var areaCd = $('.selector#area').val();
		// バス停の色変更？
		changeBusStopIcon(toBsCd, areaCd, 2);
	});
    */

    // 「出発バス停に設定する」リンクを選択時
	$("a[id^=set_fromBS]").live("click", function() {
		var id = $(this).attr("id").slice(11);
		var bscd = id.substring(0, id.length - 2);
		var bsname = $("#bsname_" + id).html();
		$("#fromBS").val(bscd);
        $('#fromBSText').html($('#fromBS option:selected').text());
		// $(".custom_select_text#fromBS").children("span").html(bsname);
		drawmap.changeBusStopIcon(bscd, buscategory_cd, 1, true);
		//$('.custom_select#fromLM').hide();
		//$('.custom_select#fromBS').show();
		tci.showInfoModal(bsname + 'が出発バス停に設定されました。');
	});

    // 「到着バス停に設定する」リンクを選択時
	$("[id^=set_toBS]").live("click", function() {
		var id = $(this).attr("id").slice(9);
		var bscd = id.substring(0, id.length - 2);
		var bsname = $("#bsname_" + id).html();
		$("#toBS").val(bscd);
        $('#toBSText').html($('#toBS option:selected').text());
		//$(".custom_select_text#toBS").children("span").html(bsname);
		drawmap.changeBusStopIcon(bscd, buscategory_cd, 2, true);
        //$('.custom_select#toLM').hide();
        //$('.custom_select#toBS').show();
		tci.showInfoModal(bsname + 'が到着バス停に設定されました。');
	});

    /*
	$("a[id^=set_fromLM]").live("click", function() {
		var bscd = $(this).attr("id").slice(11);
		var areaCd = $('.selector#area').val();
		var lmname = $("#lmname_" + bscd).html();
		$(".selector#fromLM").val(bscd);
		$(".custom_select_text#fromLM").children("span").html(lmname);
		changeBusStopIcon(bscd, areaCd, 1);
		$('.custom_select#fromBS').hide();
		$('.custom_select#fromLM').show();
		tci.showInfoModal(lmname + 'が出発バス停に設定されました。');
	});

	$("[id^=set_toLM]").live("click", function() {
		var bscd = $(this).attr("id").slice(9);
		var areaCd = $('.selector#area').val();
		var lmname = $("#lmname_" + bscd).html();
		$(".selector#toLM").val(bscd);
		$(".custom_select_text#toLM").children("span").html(lmname);
		changeBusStopIcon(bscd, areaCd, 2);
                $('.custom_select#toBS').hide();
                $('.custom_select#toLM').show();
		tci.showInfoModal(lmname + 'が到着バス停に設定されました。');
	});

	$("[id^=timetable]").live("click", function() {
		//時刻表へのリンク
	});
    */

    /**
     * 検索ボタン押下処理
	 * LmCdには主要施設に対応するBsCdが入っている
     */
	$('#searchBtn').click(function() {
		// 出発バス停(エリアも付随)は必須
		//if (opts.areaCd == 0 || (opts.fromBsCd == 0 && opts.fromLmCd == 0)) {
		if ($('#fromBS').val() == 0) {
			//tci.showErrModal("出発バス停または出発主要施設を選択してください。");
			tci.showErrModal("出発バス停を選択してください。");
			return;
		}
        tci.showIndicator(true);
        if ($('#fromBS').val() == current_pos) { // 現在地から検索
            navigator.geolocation.getCurrentPosition(searchLocation, searchNoLocation);
        } else { // バス停から検索
            doSearch(0, 0);
        }
		// 現在時刻更新
		changeUpdTime();
    });

    /*
	// バス停・主要施設検索切替
	$('#fromLMselector').live("click",function(){
		$('.custom_select#fromBS').hide();
		$('.custom_select .selector#fromBS').val(0);
		$(".custom_select_text#fromBS").children("span").html("");
		$('.custom_select#fromLM').show();
		showLandmark();
	});
	$('#fromBSselector').live("click",function(){
		$('.custom_select#fromLM').hide();
		$('.custom_select .selector#fromLM').val(0);
		$(".custom_select_text#fromLM").children("span").html("");
		$('.custom_select#fromBS').show();
		hideLandmark();
	});
	$('#toLMselector').live("click",function(){
		$('.custom_select#toBS').hide();
		$('.custom_select .selector#toBS').val(0);
		$(".custom_select_text#toBS").children("span").html("");
		$('.custom_select#toLM').show();
		showLandmark();
	});
	$('#toBSselector').live("click",function(){
		$('.custom_select#toLM').hide();
		$('.custom_select .selector#toLM').val(0);
		$(".custom_select_text#toLM").children("span").html("");
		$('.custom_select#toBS').show();
		hideLandmark();
	});
    */

    // 5件ずつ表示する矢印ボタン押下時
    var Num = 5;
    $('.more').live("click", function() {
        Num += 5; // クリックするごとに+5
        // Num+10個目以前を表示
        for (var i = 0; i < Num; i++) {
            $('#timetable #result_' + i).show();
        }
        if(listLength <= Num){
            $('.more').hide();
        }
    });

    // 自動更新ボタン
    $('.refresh').click(function() {
        if (!isAutoLoading) {
            $('.refresh').css("background-image", "url(./images/icon_refresh-toggled.png)");
            $('#status').html('更新中');
        } else {
            $('.refresh').css("background-image", "url(./images/icon_refresh.png)");
            $('#status').html('更新');
        }
        isAutoLoading = !isAutoLoading;
        runAutoLoading(isAutoLoading);
    });

    // ご意見送信ボタン
    $('#opinionBtn').click(function() {
        tci.showIndicator(true);
        var opts = {"opinion": $('#opinion').val()};
        tci.runApi("./ajax/insertOpinion.php", opts, function(response) {
		    if (response.status == 0) {
                tci.showInfoModal("送信しました。貴重なご意見ありがとうございました。");
                $('#opinion').val("");
    		} else { // 予期せぬエラー
                tci.systemErr();
            }
		});
        tci.showIndicator(false);
	});
}

/* ---------------------------------------------------------------------------------
 * 自動更新関連
 * -------------------------------------------------------------------------------*/
// バスロケ自動更新の開始・停止
function runAutoLoading(flg) {
    if (flg) { // 開始
        updateBusLocation();
        updateTimer = setInterval('updateBusLocation()', 30 * 1000);
    } else { // 停止
        clearInterval(updateTimer);
    }
}

// バス位置情報更新
function updateBusLocation() {
    //var areaCd = BUSCATEGORY_CD;
    drawmap.drawBusMarkerIcon(buscategory_cd, $('#course_id').val());
    // 更新時刻更新
    changeUpdTime();
}

/* ---------------------------------------------------------------------------------
 * 各種処理
 * -------------------------------------------------------------------------------*/

/*
// 主要施設の表示
function showLandmark() {
	var areacd = $('.selector#area').val();
	var routecd = $('.selector#route').val();
	drawmap.drawLandmarks(areacd, routecd);
}

// 主要施設の非表示
function hideLandmark() {
	if ($('.custom_select#fromLM').css('display') != 'block'
		&& $('.custom_select#toLM').css('display') != 'block') {
		landmarkList.forEach(function(marker, idx) {
			marker.setMap(null);
		});
		landmarkList = [];
	}
}

// エリアの選択肢を生成
function setAreaSelect() {
	// いったんoptionを全削除
	$('.custom_select .selector#area').children('option').remove();
	// 空要素を追加
	$('.custom_select .selector#area').append('<option value="0" selected="selected"></option>');
	$.ajax({
		type: "POST",
		url: "./ajax/getAreaList.php",
		dataType: "JSON",
		data: {"areaCd": 0},
		success: function(obj) {
			if (obj.status == 0) {
				$.each(obj.categoryList, function(i, category) {
					$('.custom_select .selector#area').append(
						'<option value="' + category.buscategory_cd + '">' + category.category_name + '</option>'
					);
				});
			}
		}
	});
}
*/

/**
 * 路線（Course）のドロップダウン生成
 */
function setCourseDropdown(buscategory_cd) {
	// いったんoptionを全削除、選択項目をリセット
	$('#course_id').children('option').remove();
	// 空要素を追加
	$('#course_id').append('<option value="0" selected="selected"></option>');
    // 路線一覧を取得
    var opts = {"areaCd": buscategory_cd};
    tci.runApi("./ajax/getCourseList.php", opts, function(response) {
		if (response.status == 0) {
			$.each(response.route, function(i, route) {
                var html = '<option value="' + route.course_id + '">' + route.course_name + '</option>'
				$('#course_id').append(html);
			});
		}
	});
}

/**
 * バス停ドロップダウン生成（出発・到着両方）
 */
function setBusStopSelect(buscategory_cd, course_id, fromBS, toBS) {
	if (fromBS == 0 && toBS == 0) {
		// console.log(fromBS);
		// console.log(toBS);
	// いったんoptionを全削除、選択項目をリセット
  $('#fromBSText').html('出発<span>するバス停をえらぶ</span>');
  $('#toBSText').html('到着<span>するバス停をえらぶ</span>');
	$('#fromBS').children('option').remove();
	$('#toBS').children('option').remove();
	// 空要素を追加
	$('#fromBS').append('<option value="0" selected="selected"></option>');
	$('#toBS').append('<option value="0" selected="selected"></option>');
	var opts = {"buscategory_cd": buscategory_cd, "course_id": course_id, "fromBS": fromBS, "toBS": toBS, "orientation": 0};
//	console.log(opts);
	tci.runApi("./ajax/getBusStopId8DigitList.php", opts, function(response) {
	//		console.log(response);
	if (response.status == 0) {
		$.each(response.busstop, function(i, busstop) {
							// 出発バス停ドロップダウン選択肢
			$('#fromBS').append(
				'<option value="' + busstop.busstop_id8 + '">' + busstop.busstop_name + '</option>'
			);
							// 到着バス停ドロップダウン選択肢
			$('#toBS').append(
				'<option value="' + busstop.busstop_id8 + '">' + busstop.busstop_name + '</option>'
			);
		});
	}
	});
	}
	// // $('#fromBS').append('<option value="9999">現在地</option>'); // 現在地から検索機能は一旦カット

	if (fromBS != 0) {
		console.log(fromBS);
		$('#toBS').children('option').remove();
		$('#toBS').append('<option value="0" selected="selected"></option>');
		var opts = {"buscategory_cd": buscategory_cd, "course_id": course_id, "fromBS": fromBS, "toBS": 0, "orientation": 0};
		console.log(opts);
    tci.runApi("./ajax/getBusStopId8DigitList.php", opts, function(response) {
			console.log(response);
		if (response.status == 0) {
			$.each(response.busstop, function(i, busstop) {
                // 出発バス停ドロップダウン選択肢
				// $('#fromBS').append(
				// 	'<option value="' + busstop.busstop_id8 + '">' + busstop.busstop_name + '</option>'
				// );
                // 到着バス停ドロップダウン選択肢
				$('#toBS').append(
					'<option value="' + busstop.busstop_id8 + '">' + busstop.busstop_name + '</option>'
				);
			});
		}
		});
	}
	if (toBS != 0) {
		console.log(toBS);
		$('#fromBS').children('option').remove();
		$('#fromBS').append('<option value="0" selected="selected"></option>');
		var opts = {"buscategory_cd": buscategory_cd, "course_id": course_id, "fromBS": 0, "toBS": toBS, "orientation": 1};
		console.log(opts);
    tci.runApi("./ajax/getBusStopId8DigitList.php", opts, function(response) {
			console.log(response);
		if (response.status == 0) {
			$.each(response.busstop, function(i, busstop) {
                // 出発バス停ドロップダウン選択肢
				$('#fromBS').append(
					'<option value="' + busstop.busstop_id8 + '">' + busstop.busstop_name + '</option>'
				);
			});
		}
		});
	}

	}
/*
// (出発・到着)主要施設の選択肢を生成
function setLandmarkSelect(buscategory_cd, course_id) {
	// いったんoptionを全削除
	$('.custom_select .selector#fromLM').children('option').remove();
	$('.custom_select .selector#toLM').children('option').remove();
	// 選択項目をリセット
	$('.custom_select_text#fromLM').children('span').text("");
	$('.custom_select_text#toLM').children('span').text("");
	// 空要素を追加
	$('.custom_select .selector#fromLM').append('<option value="0" selected="selected"></option>');
	$('.custom_select .selector#toLM').append('<option value="0" selected="selected"></option>');
	$.ajax({
		type: "POST",
		url: "./ajax/getLandmark.php",
		dataType: "JSON",
		data: {"areaCd": buscategory_cd, "routeCd": course_id},
		success: function(obj) {
			if (obj.status == 0) {
				$.each(obj.landmark, function(i, landmark) {
                    var html = '<option value="' + landmark.busstop_id + '">' + landmark.landmark_name + '</option>';
					$('.custom_select .selector#fromLM').append(html);
					$('.custom_select .selector#toLM').append(html);
				});
			}
		}
	});
}

// エリア以外の選択ボックス・検索ボタン等を表示・非表示
function searchOptionsIsHide(flg) {
	if (flg == 0) {
		$('.custom_select#route').hide();
		$('.custom_select#fromBS').hide();
		$('.custom_select#fromLM').hide();
		$('.custom_select#toBS').hide();
		$('.custom_select#toLM').hide();
		$('#btn_search').hide();
	} else {
		$('.custom_select#route').show();
		$('.custom_select#fromBS').show();
		$('.custom_select#fromLM').hide();
		$('.custom_select#toBS').show();
		$('.custom_select#toLM').hide();
		$('#btn_search').show();
	}
}
*/

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
        currentLng: lng
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
                if(listLength > 10) {
                    $('#timetable').append('<div class="more" onclick=""><a href="#"></a></div>');
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
//    var fromTime = result.from.substr(0, 5); // 遅れ時間を加味した時刻
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
    // 遅れ情報
/*
    if (result.delay == 0) {
        html += '<div class="bus-info-text">通常運行</div>';
    } else {
        html += '<div class="bus-info-text">現在' + result.delay + '分程度の遅れで運行中</div>';
    }
*/
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

/**
 * コースコードを引数に、路線ドロップダウンよりコース名称を取得する
 *
function getCourseName(courseCd) {
	var courseName = '';
	var options = $('.custom_select .selector#route').children('option');
	for (var i = 0; i < options.length; i++) {
		if ($(options[i]).val() == courseCd) {
			courseName = $(options[i]).text();
			break;
		}
	}
	return courseName;
}
*/

/**
 * 乗換情報リンク取得
 *
function getTransferLink(opts, no) {
    tci.runApi("./ajax/getTransferLink.php", opts, function(response) {
        if (response.status == 0) { // 正常終了
            if (response.url != 0) {
                var html = '<br>　【乗換情報】<br>' + response.url;
                $('#transfer_link_' + no).html(html);
            }
        } else if (response.status == 1) { // Exeからのエラー
            tci.showErrModal(response.url);
        } else { // その他予期せぬエラー
            tci.systemErr();
        }
    });
}
*/


// // -----------５０音順番
// var TKBS = {};

// TKBS.inherits = function(childCtor, parentCtor) {
//   function tempCtor() {};
//   tempCtor.prototype = parentCtor.prototype;
//   childCtor.superClass_ = parentCtor.prototype;
//   childCtor.prototype = new tempCtor();
//   childCtor.prototype.constructor = childCtor;
// };

// TKBS.escape = function(string) {
//   if (typeof string !== 'string') {
//     return string;
//   }
//   return string.replace(/[&'`"<>]/g, function(match) {
//     return {
//       '&': '&amp;',
//       "'": '&#x27;',
//       '`': '&#x60;',
//       '"': '&quot;',
//       '<': '&lt;',
//       '>': '&gt;',
//     }[match]
//   });
// }

// TKBS.rosenColors = {
//   1: '#5FC35B',
//   2: '#5FC35B',
//   3: '#BB61A1',
//   4: '#BB61A1',
//   5: '#EA0316',
//   6: '#EA0316',
//   7: '#00ACED',
//   8: '#00ACED',
//   9: '#DD3489',
//   10: '#DD3489',
//   11: '#D38400',
//   12: '#D38400',
//   13: '#106AC6',
//   14: '#106AC6'
// };

// /*
//  * データ格納用
//  */
// TKBS.Session = function(data) {
//   this.allRosens = []; //全路線
//   this.allBusstops = []; //全バス停
//   this.allLandmarks = []; //全主要施設

//   this.rosens = []; //路線の選択肢
//   this.departures = []; //出発バス停の選択肢
//   this.arrivals = []; //到着バス停の選択肢

//   /*
//    * search option:
//    *  rosen, departure, arrival, hour, minute, departureLandmark, arrivalLandmark
//    */
//   this.option = {};
// }

// TKBS.Session.prototype.init = function(data) {
//   this.allRosens = data.rosens; //全路線
//   this.allBusstops = data.busstops; //全バス停
//   this.allLandmarks = data.landmarks; //全主要施設

//   this.allBusstops.forEach(function(busstop) {
//     busstop.landmarks = [];
//   });

//   var self = this;
//   this.allLandmarks.forEach(function(landmark) {
//     landmark.busstops.forEach(function(busstopCd) {
//       self.findBusstop(busstopCd).landmarks.push(landmark.cd);
//     });
//   });

//   this.clear();
// }

// TKBS.Session.prototype.clear = function() {
//   this.rosens = this.allRosens.map(function(value, index, array) {
//     return value.cd;
//   });
//   this.departures = this.allBusstops.map(function(value, index, array) {
//     return value.cd;
//   });
//   this.arrivals = this.departures;

//   this.option = {};
// }

// TKBS.Session.prototype.findRosen = function(routeCd) {
//   for (var i = 0; i < this.allRosens.length; i++) {
//     var rosen = this.allRosens[i];
//     if (rosen.cd == routeCd) {
//       return rosen;
//     }
//   }
//   return null;
// };

// TKBS.Session.prototype.findBusstop = function(busstopCd) {
//   for (var i = 0; i < this.allBusstops.length; i++) {
//     var busstop = this.allBusstops[i];
//     if (busstop.cd == busstopCd) {
//       return busstop;
//     }
//   }
//   return null;
// };

// TKBS.Session.prototype.findLandmark = function(landmarkCd) {
//   for (var i = 0; i < this.allLandmarks.length; i++) {
//     var landmark = this.allLandmarks[i];
//     if (landmark.cd == landmarkCd) {
//       return landmark;
//     }
//   }
//   return null;
// };

// TKBS.Session.prototype.canBeDeparture = function(busstopCd) {
//   return this.departures.indexOf(busstopCd) >= 0;
// }

// TKBS.Session.prototype.canBeArrival = function(busstopCd) {
//   return this.arrivals.indexOf(busstopCd) >= 0;
// }


// TKBS.AccordionHelper = function(id, option) {

//   this.element = $('#' + id);
//   this.onSelect = option.select;
//   this.onHide = option.hide;
//   this.onResize = option.resize;

//   this.element.append('<span class="acc-text">' + option.title);
//   this.element.append('<img class="tooltip" src="images/question.png" title="' + option.info + '"></span>');
//   this.element.append('<div class="accordion"></div>');

//   var titles = [{
//     layer1: "あ〜こ",
//     layer2: ["あ", "い", "う", "え", "お", "か", "き", "く", "け", "こ"]
//   }, {
//     layer1: "さ〜と",
//     layer2: ["さ", "し", "す", "せ", "そ", "た", "ち", "つ", "て", "と"]
//   }, {
//     layer1: "な〜ほ",
//     layer2: ["な", "に", "ぬ", "ね", "の", "は", "ひ", "ふ", "へ", "ほ"]
//   }, {
//     layer1: "ま〜わ",
//     layer2: ["ま", "み", "む", "め", "も", "や", "ゆ", "よ", "わ"]
//   }];

//   var html = '';
//   titles.forEach(function(e) {
//     html += '<div class="acc-layer1">';
//     html += '<div class="index-group">' + e.layer1 + '</div>';
//     e.layer2.forEach(function(e) {
//       html += '<div class="acc-layer2">';
//       html += '<div class="index index-' + this.getKanaCode(e) + '">' + e + '</div>';
//       html += '</div>';
//     }, this);
//     html += '</div>';
//   }, this);
//   html += '<div class="acc-back"><a href="javascript:void(0)" class="back">戻る</a></div>'
//   this.element.children('.accordion').append(html).find('.back').click(this.onHide);

//   var self = this;
//   this.element.find('.index-group').click(function() {
//     $(this).siblings('.acc-layer2').slideToggle('fast', self.onResize);
//   });
//   this.element.find('.index').click(function(e) {
//     $(this).siblings('.acc-layer3').slideToggle('fast', self.onResize);
//     e.stopPropagation();
//   });
// };

// TKBS.inherits(TKBS.AccordionHelper, TKBS.Panel);

// TKBS.AccordionHelper.prototype.addItem = function(cd, kana, name) {

//   var target = this.element.find('.acc-layer2 > .' + 'index-' + this.getKanaCode(kana));

//   target.parent().append('<div class="acc-layer3 ' + cd + '">' + TKBS.escape(name) + '</div>');

//   var self = this;
//   this.element.find('.' + cd).click(function() {
//     self.onSelect(cd);
//   });
// };

// TKBS.AccordionHelper.prototype.removeItems = function() {
//   this.element.find('.acc-layer3').remove();
// };

// TKBS.AccordionHelper.prototype.refresh = function() {
//   //アコーディオン:子要素がない場合は非活性
//   this.element.find('.acc-layer2:not(:has(.acc-layer3))').children().css({
//     'color': '#FFF',
//     'background-color': '#D9D9D9',
//     'cursor': 'default'
//   });
//   this.element.find('.acc-layer2:has(.acc-layer3)').children().css({
//     'color': '#000000',
//     'background-color': '#9987CB',
//     'cursor': 'pointer'
//   });
//   this.element.find('.acc-layer3').click(function(e) {
//     e.stopPropagation();
//   });
// };

// TKBS.AccordionHelper.prototype.show = function() {
//   TKBS.Panel.prototype.show.call(this);
//   this.onResize();
// };

// TKBS.AccordionHelper.prototype.hide = function() {
//   TKBS.Panel.prototype.hide.call(this);
//   this.element.find('.acc-layer2').css('display', 'none');
//   this.element.find('.acc-layer3').css('display', 'none');
//   this.onResize();
// };

// TKBS.AccordionHelper.prototype.getKanaCode = function(kana) {
//   var kanaList = [
//     "あ", "い", "う", "え", "お",
//     "か", "き", "く", "け", "こ",
//     "さ", "し", "す", "せ", "そ",
//     "た", "ち", "つ", "て", "と",
//     "な", "に", "ぬ", "ね", "の",
//     "は", "ひ", "ふ", "へ", "ほ",
//     "ま", "み", "む", "め", "も",
//     "や", "ゆ", "よ", "わ"
//   ];
//   var codeList = [
//     "a", "i", "u", "e", "o",
//     "ka", "ki", "ku", "ke", "ko",
//     "sa", "si", "su", "se", "so",
//     "ta", "ti", "tu", "te", "to",
//     "na", "ni", "nu", "ne", "no",
//     "ha", "hi", "hu", "he", "ho",
//     "ma", "mi", "mu", "me", "mo",
//     "ya", "yu", "yo", "wa"
//   ];
//   return codeList[kanaList.indexOf(kana)];
// };

/*
//  * DialogHelper
//  */
// TKBS.DialogHelper = function(id) {
//   this.element = $('#' + id);
//   this.element.dialog({
//     autoOpen: false
//   });
// };

// TKBS.DialogHelper.prototype.addMessage = function(message) {
//   this.element.append('<p>' + message + '</p>');
// };

// TKBS.DialogHelper.prototype.addItem = function(cd, name, callback) {
//   var id = 'dialog-' + cd;

//   this.element.append('<div id="' + id + '" class="dialog-btn" >' + TKBS.escape(name) + '</div>');

//   this.element.find('#' + id).click(function() {
//     callback();
//   });
// };

// TKBS.DialogHelper.prototype.clear = function() {
//   this.element.empty();
// };

// TKBS.DialogHelper.prototype.show = function(title) {
//   if (this.isOpen()) {
//     return;
//   }
//   var self = this;
//   this.element.dialog({
//     title: title,
//     modal: true,
//     buttons: {
//       'キャンセル': function() {
//         self.hide();
//       }
//     }
//   }).dialog('open');
// };

// TKBS.DialogHelper.prototype.hide = function() {
//   this.element.dialog('close');
// };

// TKBS.DialogHelper.prototype.showInfo = function(message, callback) {
//   if (this.isOpen()) {
//     return;
//   }
//   this.clear();
//   this.addMessage(message);
//   var self = this;
//   this.element.dialog({
//     title: '情報',
//     modal: true,
//     buttons: {
//       'ＯＫ': function() {
//         self.hide();
//         if (callback) {
//           callback();
//         }
//       },
//     }
//   }).dialog('open');
// };

// TKBS.DialogHelper.prototype.showError = function(message, callback) {
//   if (this.isOpen()) {
//     return;
//   }
//   this.clear();
//   this.addMessage(message);
//   var self = this;
//   this.element.dialog({
//     title: 'エラー',
//     modal: true,
//     buttons: {
//       'ＯＫ': function() {
//         self.hide();
//         if (callback) {
//           callback();
//         }
//       },
//     }
//   }).dialog('open');
// };

// TKBS.DialogHelper.prototype.showConfirm = function(message, callback) {
//   if (this.isOpen()) {
//     return;
//   }
//   this.clear();
//   this.addMessage(message);
//   var self = this;
//   this.element.dialog({
//     title: '確認',
//     modal: true,
//     buttons: {
//       'ＯＫ': function() {
//         self.hide();
//         if (callback) {
//           callback();
//         }
//       },
//       'キャンセル': function() {
//         self.hide();
//       }
//     }
//   }).dialog('open');
// };

// TKBS.DialogHelper.prototype.isOpen = function() {
//   return this.element.dialog('isOpen');
// };

// /*
//  * ResultPanel
//  */
// TKBS.ResultPanel = function(id, option) {
//   this.element = $('#' + id);
//   this.element.append('<div class="title"></div>');
//   this.element.append('<div class="r-content"></div>');
//   this.body = this.element.find('.r-content');
//   this.onResize = option.resize;
// };

// TKBS.inherits(TKBS.ResultPanel, TKBS.Panel);

// TKBS.ResultPanel.prototype.show = function() {
//   TKBS.Panel.prototype.show.call(this);
//   this.onResize();
// };

// TKBS.ResultPanel.prototype.hide = function() {
//   TKBS.Panel.prototype.hide.call(this);
//   this.onResize();
// };

// TKBS.ResultPanel.prototype.setTitle = function(title) {
//   this.element.children('.title').text(title);
// };

// TKBS.ResultPanel.prototype.setResults = function(data) {
//   var now = new Date();
//   var h = ('0' + now.getHours()).slice(-2)
//   var m = ('0' + now.getMinutes()).slice(-2);

//   this.setTitle('運行状況 ' + h + ':' + m + '現在');
//   this.body.empty();
//   data.forEach(function(e, i, arr) {
//     this.addResult(e);
//   }, this);
// };

// TKBS.ResultPanel.prototype.addResult = function(result) {
//   var result = '<div class="r-rosen">' + TKBS.escape(result.rosen.name) + '</div>' +
//     '<div class="r-detail">' +
//     '<div class="r-departure">' + TKBS.escape(result.departure.name) + '（定刻 ' + formatDia(result.departure.dia) + ' 発）遅延 ' + TKBS.escape(result.delay) + ' 分</div>' +
//     createArrivalInfo(result) + createTransferInfo(result) + '</div>';

//   this.body.append(result);

//   function createArrivalInfo(result) {
//     if (result.arrival == null || result.arrival.cd == 0) {
//       return '';
//     }
//     var color = TKBS.rosenColors[result.rosen.cd];
//     var html = '<div class="r-time r-rosen-color" style="border-left-color: ' + color + '">所要時間 ' + TKBS.escape(result.duration) + ' 分</div>' +
//       '<div class="r-arrival">' + TKBS.escape(result.arrival.name) + '（定刻 ' + formatDia(result.arrival.dia) + ' 着）</div>';
//     return html;
//   }

//   function createTransferInfo(result) {
//     if (result.transfer == null) {
//       return '';
//     }
//     var html = '';
//     result.transfer.forEach(function(e, i, arr) {
//       if (i == 0) {
//         html += '<div class="r-transfer"><span>■乗換情報</span><ul>';
//       }
//       html += '<li><a href="' + TKBS.escape(e.url) + '" target="_blank">' + TKBS.escape(e.link) + '</a></li>';
//       if (i == arr.length - 1) {
//         html += '</ul></div>';
//       }
//     });
//     return html;
//   }

//   function formatDia(dia) {
//     if (dia.length == 8) { // 00:00:00
//       return dia.slice(0, -3);
//     } else {
//       return dia;
//     }
//   }
// };