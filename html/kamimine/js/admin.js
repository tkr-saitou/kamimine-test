/* ---------------------------------------------------------------------------------
 * グローバル変数 
 * -------------------------------------------------------------------------------*/
// 定数はgetVariables.phpでPHP側から取得する
var buscategory_cd;
var SEARCH_GRAPH;

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
            buscategory_cd = variables["buscategory_cd"];
            SEARCH_GRAPH = variables["search_graph"];
            // 初期化
            initialize();
        } else { // その他の予期せぬエラー
            tci.systemErr();
        }
    });
}

function initialize() {
    // datepicker
    $('#date, #from_date, #to_date').datepicker({
        showOn: 'focus'
    }).val('日付を選択して下さい...');

    // 系統ドロップダウンの初期生成
    setCourseDropdown(buscategory_cd);

    // 仕業ドロップダウンの初期生成
    setShiftDropdown("", 0);

    // 現在の運行状況表示
    getServiceSummary();

    // 運行状況の警告表示
    getServiceAlert();

    // 検索状況確認欄ON/OFF
    if (!SEARCH_GRAPH) {
        $('#search-graph-area').css('display', 'none');
    }

    // イベントリスナーを登録する
    registerListener();
};

/**
 * イベントリスナーを登録する
 */
function registerListener() {

    // 系統ドロップダウン変更による仕業ドロップダウンの取得
	$('#course_id').change(function() {
        if ($('#course_id').val() != 0 && ($('#date').val() == "" || $('#date').val() == '日付を選択して下さい...')) {
            $('#course_id').val(0);
            tci.showErrModal("日付を入力してください");
            return;
        } else {
            setShiftDropdown($('#date').val(), $('#course_id').val());
        }
    });

    // 運行状況一覧の表示
    $('#serviceListBtn').click(function() {
        if ($('#date').val() == "" || $('#date').val() == '日付を選択して下さい...') {
            tci.showErrModal("日付を入力してください");
            return;
        } else {
            getServiceTable($('#date').val(), $('#course_id').val(), $('#shift_pattern_cd').val());
        }
    });

    // 運行状況確認グラフの表示
    $('.information-row').live("click", function() {
        var params = $(this).attr("id").split('_');
        if (params[1] == 1) {
            getServiceAnalysisData(params[2], params[3], params[4]);
        } else {
            tci.showErrModal('この便は運行実績データが存在しないため、グラフを表示できません。');
        }
    });

    // 検索状況確認グラフの表示
    $('#searchGraphBtn').click(function() {
        if (($('#from_date').val() == "" || $('#from_date').val() == "日付を選択して下さい...")
         || ($('#to_date').val() == "" || $('#to_date').val() == "日付を選択して下さい...")) {
            tci.showErrModal('期間を入力してください');
        } else {
            getSearchHistoryData();
            getSearchRankingData();
        }
    });
}

/* ---------------------------------------------------------------------------------
 * 本日の状況
 * -------------------------------------------------------------------------------*/
/**
 * 現在の運行状況取得
 */
function getServiceSummary() {
 	// 現在の運行状況リセット
	$('.alert').empty();
    // 現在の運行状況を取得
    tci.runApi("./ajax/getServiceSummary.php", null, function(response) {
        if (response.status == 0) {
            setServiceSummary(response.data)
		} else { // その他予期せぬエラー
            tci.systemErr();
		}
	}); 
}

/**
 * 現在の運行状況表示
 */
function setServiceSummary(data) {
    var html = '<table><tr>'
            + '<th>系統</th>'
            + '<th>運行<br>予定</th>'
            + '<th>運行中</th>'
            + '<th>運行<br>完了</th>'
            + '<th>-</th>'
            + '</tr>';
    var cnt = 0;
    for (key in data) {
        if (cnt % 2 == 0) html += '<tr>';
        else html += '<tr class="gray">';
        cnt++;
        html += '<td>' + data[key]['course_name'] + '</td>'
            + '<td>' + data[key]['plan'] + '</td>'
            + '<td>' + data[key]['mid'] + '</td>'
            + '<td>' + data[key]['comp'] + '</td>'
            + '<td>' + data[key]['unknown'] + '</td>'
            + '</tr>';
    }
    $('.situation-table').html(html);

}

/**
 * 運行状況の警告表示
 */
function getServiceAlert() {
	// 警告をリセット
	$('.alert').empty();
    // 警告一覧を取得
    tci.runApi("./ajax/getServiceAlert.php", null, function(response) {
        if (response.status == 0) {
            $('.alert').html('<div id="alert"><p>現在、正常に運行しています。</p></div>');
            //$('.alert').css('display', 'none');
		} else if (response.status == 1) {
            var shiftList = '';
            for (key in response.data) {
                shiftList += response.data[key]['shift_pattern_name'] + ',';
            }
            var html = '<div id="alert"><p>仕業パターン' + shiftList.substr(0, (shiftList.length - 1)) + 'でGPS情報が取得出来ていません。<br>ご確認ください。</p></div>';
            $('.alert').html(html);
		} else { // その他予期せぬエラー
            tci.systemErr();
		}
	});
}


/* ---------------------------------------------------------------------------------
 * 運行状況確認
 * -------------------------------------------------------------------------------*/
/**
 * 路線（Course）のドロップダウン生成
 */
function setCourseDropdown(buscategory_cd) {
	// いったんoptionを全削除、選択項目をリセット
	$('#course_id').children('option').remove();
	// 空要素を追加
	$('#course_id').append('<option value="0" selected="selected">コースを選択して下さい...</option>');
    // 路線一覧を取得
    tci.runApi("./ajax/getCourseList.php", null, function(response) {
		if (response.status == 0) {
			$.each(response.route, function(i, route) {
                var html = '<option value="' + route.course_id + '">' + route.course_name + '</option>'
				$('#course_id').append(html);
			});
		} else { // その他予期せぬエラー
            tci.systemErr();
		}
	});
}

/**
 * 仕業のドロップダウン生成
 */
function setShiftDropdown(date, course_id) {
	// いったんoptionを全削除、選択項目をリセット
	$('#shift_pattern_cd').children('option').remove();
	// 空要素を追加
	$('#shift_pattern_cd').append('<option value="0" selected="selected">仕業を選択して下さい...</option>');
    // 路線一覧を取得
    var opts = {
        "date":             date,
        "course_id":        course_id
    }
    tci.runApi("./ajax/getShiftList.php", opts, function(response) {
		if (response.status == 0) {
			$.each(response.data, function(i, shift) {
                var shift_pattern_cd = shift.shift_pattern_cd;
                var shift_pattern_name = shift.shift_pattern_name;
                var html = '<option value="' + shift_pattern_cd + '">' + shift_pattern_name
                         + '</option>';
				$('#shift_pattern_cd').append(html);
			});
		} else { // その他予期せぬエラー
            tci.systemErr();
		}
	});
}

/**
 * 運行状況一覧テーブルの取得
 */
function getServiceTable(date, course_id, shift_pattern_cd) {
    var opts = {
        "date":             date,
        "course_id":        course_id,
        "shift_pattern_cd": shift_pattern_cd
    }
    tci.runApi("./ajax/getServiceTable.php", opts, function(response) {
        $('.traffic-information-table').empty();
        $('#service-graph-area').empty();
        $('#service-graph-area').html('<p id="msg">運行状況一覧からグラフを表示したい便を選択して下さい</p>');
		if (response.status == 0) {
            console.log(response.data);
            setServiceTable(response.data);
        } else if (response.status == 1) {
            tci.showErrModal("指定日付、コース、仕業のデータが存在しません。条件を指定し直して下さい。");
		} else { // その他予期せぬエラー
            tci.systemErr();
		}
	});
}

/**
 * 運行状況確認用テーブルの作成
 */
function setServiceTable(data) {
    var html = '<table>'
        + '<tr>'
		+ '<th>仕業</th>'
		+ '<th>便No</th>'
		+ '<th>系統</th>'
        + '<th colspan="2">FROM</th>'
		+ '<th colspan="2">TO</th>'
		+ '<th colspan="2">運行状況</th>'
		+ '<th>グラフ</th>'
		+ '</tr>';
    for (key in data) {
        if (key % 2 == 0) html += '<tr class="information-row"';
        else html += '<tr class="information-row gray"';

        if (data[key]['busstop_id'] != null) {
            html += ' id="row_1_' + $('#date').val() + '_' + data[key]['course_id'] + '_' + data[key]['bin_no'] + '">';
        } else {
            html += ' id="row_0">';
        }
        html += '<td>' + data[key]['shift_pattern_name'] + '</td>'
            + '<td>' + data[key]['bin_no'] + '</td>'
            + '<td>' + data[key]['course_name'] + '</td>'
            + '<td>' + data[key]['from_busstop_name'] + '</td>'
            + '<td>' + data[key]['from_dia_time'] + '</td>'
            + '<td>' + data[key]['to_busstop_name'] + '</td>'
            + '<td>' + data[key]['to_dia_time'] + '</td>';
        // 運行状況
        if (data[key]['busstop_id'] == null) {
            if (data[key]['is_plan'] == 1) {
                // 運行予定
                html += '<td>運行予定</td><td></td><td>-</td>';
            } else {
                // GPS情報が取得できていない
                html += '<td>-</td><td>GPS情報が取得出来ていません</td><td>-</td>';
            }
        } else if (data[key]['busstop_id'] == data[key]['to_busstop_id']) {
            if (data[key]['reg_type'] == 3) {
                html += '<td>運行完了</td><td>' + data[key]['pre_busstop_name'] + '以降はGPS情報が取得できていない可能性があります</td><td>△</td>';
            } else {
                html += '<td>運行完了</td><td></td><td>○</td>';
            }
        } else {
            html += '<td>運行中</td><td>現在' + data[key]['busstop_name'] + 'を通過しました</td><td>△</td>';
        }
        html += '</tr>';
    }
    html += '</table>';
    $('.traffic-information-table').html(html);
}


/* ---------------------------------------------------------------------------------
 * 運行実績グラフ関連
 * -------------------------------------------------------------------------------*/

/**
 * 運行実績データの取得
 */
function getServiceAnalysisData(date, course_id, bin_no) {
    var opts = {
        "date":         date,
        "course_id":    course_id,
        "bin_no":       bin_no
    }
    tci.runApi("./ajax/getServiceAnalysisData.php", opts, function(response) {
		if (response.status == 0) {
            drawServiceAnalysisGraph(response.data, date);
            $('#warning').html(response.data["warning"]);
        } else if (response.status == 1) {
            tci.showErrModal("指定日付、便のデータが存在しません。日付、便を指定し直してください。");
		} else { // その他予期せぬエラー
            tci.systemErr();
		}
	});
}

/**
 * 運行実績グラフの描画
 */
function drawServiceAnalysisGraph (data, date) {
    //***************************************************************
    // 初期化設定
    //***************************************************************
    // SVGエリアのリセット
    $('#service-graph-area').children().remove();

    // 設定
//    var mag = 0.8;              // グラフエリア横幅の画面横幅に対する倍率
    var minuteHeight = 20;      // ダイヤ時刻1分の高さ
    var axisFontSize = "15px";  // 軸ラベルのフォントサイズ
    var labelFontSize = "13px"; // データラベルのフォントサイズ
    var xOffset;                // ラベル表示のXオフセット
    var yOffset;                // ラベル表示のYオフセット
    var preDiaTime, preDiaTime2;// 1つ前のバス停のダイヤ時刻を保存する変数
    var initDate = new Date('1970-1-1 0:00:00'); // 時刻変数初期化用

    // ダイヤ時刻が同じ場合に30秒ずらすためのオフセット
    var compOffset = minuteHeight / 2;

    // スケール用軸の両極
    var minXScale = new Date(data["minXScale"]);
    var maxXScale = new Date(data["maxXScale"]);
    var minYScale = new Date(data["minYScale"]);
    var maxYScale = new Date(data["maxYScale"]);

    // SVGエリアのサイズ
//    var width = $(window).width() * mag;
    var width = 1000;
    var xPaddingL = 250;    // SVGエリアのパディング(左) ※長いバス停名がある場合に調整
    var xPaddingR = 100;     // SVGエリアのパディング(右)
    var yPadding = 50;      // SVGエリアのパディング
    var height = ((maxYScale.getTime() - minYScale.getTime()) / 1000 / 60) * minuteHeight + yPadding * 2;

    // SVGの表示領域の設定
    var svg = d3.select("#service-graph-area").append("svg")
        .attr("width", width)
        .attr("height", height);


    //***************************************************************
    // スケールの設定
    //***************************************************************
    // Xスケール
    // ドメイン：入力データの最早時間－5分～最遅時間＋5分
    var xScale = d3.time.scale()
        .domain([minXScale, maxXScale])
        .range([xPaddingL, width - xPaddingR]);

    // Yスケール
    // ドメイン：ダイヤ時刻の最早時間～最遅時間
    var yScale = d3.time.scale()
        .domain([minYScale, maxYScale])
        .range([yPadding, height - yPadding]);


    //***************************************************************
    // データセットの作成、バインド
    //***************************************************************
    var dataSet = [];
    for (key in data["data"]) {
        var dia_time = new Date(date + " " + data["data"][key].dia_time);
        var real_time = new Date(date + " " + data["data"][key].real_time);
        if (key == 0) { // 始発バス停は遅れていなければダイヤ時刻にする
            if (real_time.getTime() < dia_time.getTime()) {
                real_time = dia_time;
            }
        }
        var item = {
            "dia_time": dia_time,
            "real_time": real_time,
            "busstop_name": data["data"][key].busstop_name
        }
        dataSet.push(item);
    }

    // 点列プロット用データセットをバインド
    circles = svg.selectAll("circle")
        .data(dataSet);

    // テキストにデータセットをバインド
    var text = svg.selectAll("text")
        .data(dataSet);


    //***************************************************************
    // Y軸の描画
    //***************************************************************
    // 軸直線の生成
    var busstopLine = d3.svg.line()
        .x(function(d) { return xPaddingL; })
        .y(function(d) { return yScale(d.dia_time); });

    // 軸の描画
    svg.append("path")
        .attr({
            "d": busstopLine(dataSet), 
            "stroke": "black",          // 線の色
            "fill": "none",             // 塗り潰し
            "stroke-width": 2           // 線の太さ
        });

    // 補助線の描画
    svg.selectAll("line")
        .data(dataSet)
        .enter()
        .append("line")
        .attr({
            "x1": function(d) { return xPaddingL; }, 
            "y1": function(d, i) {
                if (i == 0) preDiaTime = initDate; // 初期化
                if (preDiaTime.getTime() == d.dia_time.getTime()) {
                    return yScale(d.dia_time) + compOffset;
                } else {
                    preDiaTime = d.dia_time; 
                    return yScale(d.dia_time);
                }
            },
            "x2": function(d) { return width - xPaddingR; }, 
            "y2": function(d, i) {
                if (i == 0) preDiaTime2 = initDate; // 初期化
                if (preDiaTime2.getTime() == d.dia_time.getTime()) {
                    return yScale(d.dia_time) + compOffset;
                } else {
                    preDiaTime2 = d.dia_time; 
                    return yScale(d.dia_time);
                }
            },
            "stroke": "gray",           // 線の色
            "fill": "none",             // 塗り潰し
            "stroke-width": 1           // 線の太さ
        });

    // 点列を描画
    circles.enter()
        .append("circle")
        .attr({
            cx: function(d) { return xPaddingL; },
            cy: function(d, i) {
                if (i == 0) preDiaTime = initDate; // 初期化
                if (preDiaTime.getTime() == d.dia_time.getTime()) {
                    return yScale(d.dia_time) + compOffset;
                } else {
                    preDiaTime = d.dia_time; 
                    return yScale(d.dia_time);
                }
            },
            "r": 4
        })
        .attr("fill", "red");           // 点の色

    // バス停名の出力
    xOffset = -10;  // ラベル表示のXオフセット
    yOffset = 5;    // ラベル表示のYオフセット
    text.enter()
        .append("text")
        .attr({
            "x": xPaddingL + xOffset,
            "y": function(d, i) {
                if (i == 0) preDiaTime = initDate; // 初期化
                if (preDiaTime.getTime() == d.dia_time.getTime()) {
                    return yScale(d.dia_time) + yOffset + compOffset;
                } else {
                    preDiaTime = d.dia_time; 
                    return yScale(d.dia_time) + yOffset;
                }
            },
            "font-family": "Meiryo",
            "font-size": axisFontSize,
            "text-anchor": "end"
        })
        .text( function (d) { return d.busstop_name; });


    //***************************************************************
    // ダイヤ時刻の折れ線グラフ
    //***************************************************************
    // 折れ線グラフの生成
    var diaTimeLine = d3.svg.line()
        .x(function(d) { return xScale(d.dia_time); })
        .y(function(d, i) {
            if (i == 0) preDiaTime = initDate; // 初期化
            if (preDiaTime.getTime() == d.dia_time.getTime()) {
                return yScale(d.dia_time) + compOffset;
            } else {
                preDiaTime = d.dia_time; 
                return yScale(d.dia_time);
            }
        });

    function test() {

    }

    // 折れ線グラフの描画
    svg.append("path")
        .attr("d", diaTimeLine(dataSet)) 
        .attr("stroke", "black")            // 線の色
        .attr("fill", "none")               // 塗り潰し
        .attr("stroke-width", 2)            // 線の太さ


    //***************************************************************
    // ダイヤ時刻の点列をプロット
    //***************************************************************
    // 点列を描画
    circles.enter()
        .append("circle")
        .attr({
            cx: function(d) { return xScale(d.dia_time); },
            cy: function(d, i) {
                if (i == 0) preDiaTime = initDate; // 初期化
                if (preDiaTime.getTime() == d.dia_time.getTime()) {
                    return yScale(d.dia_time) + compOffset;
                } else {
                    preDiaTime = d.dia_time; 
                    return yScale(d.dia_time);
                }
            },
            "r": 4
        })
        .attr("fill", "black");             // 点の色

    // ダイヤ時刻の出力
    xOffset = -5;   // ラベル表示のXオフセット
    yOffset = 15;   // ラベル表示のYオフセット
    text.enter()
        .append("text")
        .attr({
            "x": function(d) { return xScale(d.dia_time) + xOffset; },
            "y": function(d, i) {
                if (i == 0) preDiaTime = initDate; // 初期化
                if (preDiaTime.getTime() == d.dia_time.getTime()) {
                    return yScale(d.dia_time) + yOffset + compOffset;
                } else {
                    preDiaTime = d.dia_time; 
                    return yScale(d.dia_time) + yOffset;
                }
            },
            "font-family": "Meiryo",
            "font-size": labelFontSize,
            "text-anchor": "end"
        })
        .text( function (d) {
            return ("0" + d.dia_time.getHours()).slice(-2) + ":" + ("0" + d.dia_time.getMinutes()).slice(-2);
        });


    //***************************************************************
    // 運行実績の折れ線グラフ
    //***************************************************************
    // 折れ線グラフの生成
    var realTimeLine = d3.svg.line()
        .x(function(d) { return xScale(d.real_time); })
        .y(function(d, i) {
            if (i == 0) preDiaTime = initDate; // 初期化
            if (preDiaTime.getTime() == d.dia_time.getTime()) {
                return yScale(d.dia_time) + compOffset;
            } else {
                preDiaTime = d.dia_time; 
                return yScale(d.dia_time);
            }
        });

    // 折れ線グラフの描画
    svg.append("path")
        .attr("d", realTimeLine(dataSet))
        .attr("stroke", "red")              // 線の色
        .attr("fill", "none")               // 塗り潰し
        .attr("stroke-width", 2)            // 線の太さ
        .attr("stroke-dasharray", "5, 2");  // 点線の設定


    //***************************************************************
    // 運行実績の点列をプロット
    //***************************************************************
    // 点列を描画
    circles.enter()
        .append("circle")
        .attr({
            cx: function(d) { return xScale(d.real_time); },
            cy: function(d, i) {
                if (i == 0) preDiaTime = initDate; // 初期化
                if (preDiaTime.getTime() == d.dia_time.getTime()) {
                    return yScale(d.dia_time) + compOffset;
                } else {
                    preDiaTime = d.dia_time; 
                    return yScale(d.dia_time);
                }
            },
            "r": 3
        })
        .attr("fill", "red");   // 点の色

    // 遅れ時間の出力
    xOffset = 5;    // ラベル表示のXオフセット
    yOffset = -5;   // ラベル表示のYオフセット
    text.enter()
        .append("text")
        .attr({
            "x": function(d) { return xScale(d.real_time) + xOffset; },
            "y": function(d, i) {
                if (i == 0) preDiaTime = initDate; // 初期化
                if (preDiaTime.getTime() == d.dia_time.getTime()) {
                    return yScale(d.dia_time) + yOffset + compOffset;
                } else {
                    preDiaTime = d.dia_time; 
                    return yScale(d.dia_time) + yOffset;
                }
            },
            "font-family": "Meiryo",
            "font-size": labelFontSize,
            "fill": function(d) {
                delayTime = d.real_time.getTime() - d.dia_time.getTime();
                if (delayTime <= 0) return "blue";
                else return "red";
            }
        })
        .text( function (d) {
            delayTime = d.real_time.getTime() - d.dia_time.getTime();
            if (delayTime < 0) return "▲"+(delayTime/1000/60*(-1)).toFixed(1)+"分"; 
            else return (delayTime/1000/60).toFixed(1)+"分";
        });

    //***************************************************************
    // X軸(タイムライン)の描画
    //***************************************************************
    svg.append("g")
        .attr("class", "axis")
        .attr("transform", "translate(0," + yPadding + ")")
        .call(d3.svg.axis()
            .scale(xScale)
            .ticks(4)
            .tickPadding(15)
            .tickFormat(d3.time.format('%H:%M'))
            .orient("top")
        );

}

/* ---------------------------------------------------------------------------------
 * 検索履歴グラフ関連
 * -------------------------------------------------------------------------------*/
/**
 * 検索履歴データの取得
 */
function getSearchHistoryData() {
    var opts = {
        "from": $('#from_date').val(),
        "to":   $('#to_date').val()
    }
    tci.runApi("./ajax/getSearchHistory.php", opts, function(response) {
		if (response.status == 0) {
            console.log(response);
            drawSearchAnalysisGraph(response);
		} else { // その他予期せぬエラー
            tci.systemErr();
		}
	});
}

/**
 * 検索件数推移グラフの描画
 */
function drawSearchAnalysisGraph(args) {
    //***************************************************************
    // 初期化設定
    //***************************************************************
    // SVGエリアのリセット
    $('#search-analysis-graph-area').children().remove();
    $('#search-analysis-graph-area').append('<h2 class="dashboard">検索件数推移</h2>');

    // 設定
    //var mag = 0.8;              // グラフエリア横幅の画面横幅に対する倍率

    // スケール用軸の両極
    var minXScale = new Date(args["minXScale"]);
    var maxXScale = new Date(args["maxXScale"]);
    var minYScale = args["minYScale"];
    var maxYScale = args["maxYScale"];

    // SVGエリアのサイズ
    //var width = $(window).width() * mag;
    var width = 1000;
    var height = 400;
    var xPadding = width * 0.1; // SVGエリアのパディング
    var yPadding = 50;          // SVGエリアのパディング

    // SVGの表示領域の設定
    var svg = d3.select("#search-analysis-graph-area").append("svg")
        .attr("width", width)
        .attr("height", height);


    //***************************************************************
    // スケールの設定
    //***************************************************************
    // Xスケール
    var xScale = d3.time.scale()
        .domain([minXScale, maxXScale])
        .range([xPadding, width - xPadding]);

    // Yスケール
    var yScale = d3.scale.linear()
        .domain([minYScale, maxYScale])
        .range([height - yPadding, yPadding]);


    //***************************************************************
    // 軸の描画
    //***************************************************************
    // X軸
    adjustAxis = 4;
    svg.append("g")
        .attr("class", "axis")
        .attr("transform", "translate(" + adjustAxis + ", " + (height - yPadding) + ")")
        .call(d3.svg.axis()
            .scale(xScale)
            .tickFormat(d3.time.format('%m/%d'))
        );
    // Y軸
    svg.append("g")
        .attr("class", "axis")
        .attr("transform", "translate(" + (xPadding + adjustAxis) + ", 0)")
        .call(d3.svg.axis()
            .scale(yScale)
            .orient("left")
        );


    //***************************************************************
    // データセットの作成、バインド
    //***************************************************************
    var data = args["data"];
    var dataSet = [];
    for (device in data) {
        var itemList = [];
        for (key in data[device]) {
            var date = new Date(data[device][key]["date"]);
            var count = data[device][key]["count"];
            var item = {
                "date": date,
                "count": count
            };
            itemList.push(item);
        }
        dataSet[device] = itemList;
    }

    //***************************************************************
    // 検索件数推移の折れ線グラフ
    //***************************************************************
    // 折れ線グラフの生成
    var line = d3.svg.line()
        .interpolate('basis')
        .x(function(d) { return xScale(d.date); })
        .y(function(d) { return yScale(d.count); });

    var area = d3.svg.area()
        .interpolate('basis')
        .x(function(d) { return xScale(d.date); })
        .x(function(d) { return xScale(d.date); })
        .y0(height - yPadding)
        .y1(function(d) { return yScale(d.count); });

    // 折れ線グラフの描画
    for (key in args["devicePattern"]) {
        device = args["devicePattern"][key];
        svg.append("path")
            .attr("d", line(dataSet[device]))
            .attr("stroke", args["color"][device])  // 線の色
            .attr("stroke-width", 2)                // 線の太さ
            .attr("fill", "none");
        svg.append("path")
            .attr("d", area(dataSet[device]))
            .attr("fill", args["color"][key]);
    }

}


/* ---------------------------------------------------------------------------------
 * 検索履歴グラフ関連
 * -------------------------------------------------------------------------------*/
/**
 * 検索履歴データの取得
 */
function getSearchRankingData() {
    var opts = {
        "from": $('#from_date').val(),
        "to":   $('#to_date').val()
    }
    tci.runApi("./ajax/getSearchRanking.php", opts, function(response) {
		if (response.status == 0) {
            console.log(response);
            drawSearchRankingGraph(response);
		} else { // その他予期せぬエラー
            tci.systemErr();
		}
	});
}

/**
 * 検索バス停ランキンググラフの描画
 */
function drawSearchRankingGraph(args) {
    //***************************************************************
    // 初期化設定
    //***************************************************************
    // SVGエリアのリセット
    $('#search-ranking-graph-area').children().remove();
    $('#search-ranking-graph-area').append('<h2 class="dashboard">検索件数ランキング</h2>');

    // 設定
    //var mag = 0.8;              // グラフエリア横幅の画面横幅に対する倍率

    // スケール用軸の両極
    var minXScale = args["minXScale"];
    var maxXScale = args["maxXScale"];
    var minYScale = args["minYScale"];
    var maxYScale = args["maxYScale"];

    // SVGエリアのサイズ
    //var width = $(window).width() * mag;
    var width = 1000;
    var height = 700;
    var xPaddingL = 250;    // SVGエリアのXパディング(左)
    var xPaddingR = 100;     // SVGエリアのXパディング(右)
    var yPadding = 50;      // SVGエリアのYパディング

    // SVGの表示領域の設定
    var svg = d3.select("#search-ranking-graph-area").append("svg")
        .attr("width", width)
        .attr("height", height);


    //***************************************************************
    // スケールの設定
    //***************************************************************
    // Xスケール
    var xScale = d3.scale.linear()
        .domain([minXScale, maxXScale])
        .range([xPaddingL, width - xPaddingR]);

    // Yスケール
    var yScale = d3.scale.linear()
        .domain([minYScale, maxYScale])
        .range([yPadding, height - yPadding]);


    //***************************************************************
    // 軸の描画
    //***************************************************************
    // X軸
    xAxisPadding = 10;
    svg.append("g")
        .attr("class", "axis")
        .attr("transform", "translate(0, " + (yPadding - xAxisPadding) + ")")
        .call(d3.svg.axis()
            .scale(xScale)
            .orient("top")
        );

    // 軸直線の生成
    yAxisData = [yPadding - xAxisPadding, height - yPadding]
    var yAxis = d3.svg.line()
        .x(xPaddingL)
        .y(function(d, i) { return d; });

    // 軸の描画
    svg.append("path")
        .attr({
            "d": yAxis(yAxisData), 
            "stroke": "#000000",          // 線の色
            "fill": "none",             // 塗り潰し
            "stroke-width": 1           // 線の太さ
        });


    //***************************************************************
    // 検索バス停ランキングの棒グラフ
    //***************************************************************
    // 棒グラフの描画
    var barHeight = height * 0.02;
    svg.selectAll("rect")
        .data(args["data"])
        .enter()
        .append("rect")
        .attr("x", xScale(0))
        .attr("y", function(d, i) { return yScale(i); })
        .attr("width", function(d) { return xScale(d.count) - xPaddingL; })
        .attr("height", barHeight)
        .attr("fill", "#1E90FF");

    // データラベルの描画
    var labelPadding = 4;
    svg.selectAll("dataLabel")
        .data(args["data"])
        .enter()
        .append("text")
        .attr("x", function(d) { return xScale(d.count) + labelPadding; })
        .attr("y", function(d, i) { return yScale(i) + barHeight; })
        .text(function(d, i) { return d.count; });

    // バス停名の描画
    xOffset = -10;  // ラベル表示のXオフセット
    svg.selectAll("busstop")
        .data(args["data"])
        .enter()
        .append("text")
        .attr({
            "x": xPaddingL + xOffset,
            "y": function(d, i) { return yScale(i) + barHeight; },
            "text-anchor": "end"
        })
        .text(function(d, i) { return d.busstop_name; });

}

