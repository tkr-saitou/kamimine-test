// ************************************************************
// 名前空間
// ************************************************************
if (typeof tci == "undefined") {
	var tci = {};
}
// ************************************************************
// 文字列操作 
// ************************************************************
/* 空判定 */
tci.isEmpty = function(val) {
    if (!val) {
        if (!((val === 0) || (val === false))) {
            return true;
        }
    }
    return false;
};
/* 指定文字列の置換 */
tci.replaceAll = function(strBuffer,strBefore,strAfter) {
    return strBuffer.split(strBefore).join(strAfter);
}
/* 数字のゼロパディング */
tci.zeroPadding = function(number,digit) {
     var numberLength = String(number).length;
     if (digit > numberLength) {
        return (new Array((digit - numberLength) + 1).join(0)) + number;
     } else {
        return number;
     }
};
/* 数字に3桁ごとのカンマを付与する */
tci.addFigure = function(value) {
    var num = new String(value).replace(/,/g, "");
    while(num != (num = num.replace(/^(-?\d+)(\d{3})/, "$1,$2")));
    return num;
};
/* カンマ除去 */
tci.removeFigure = function(val) {
    return val.split(",").join("");
};
/* 半角英数字へ置換 */
tci.converttoAscii = function(val) {
	return val.replace(/[Ａ-Ｚａ-ｚ０-９ ＠ － ．：]/g, function(str) {
		    return String.fromCharCode(str.charCodeAt(0) - 0xFEE0);
	    });
};
/* 半角数字チェック */
tci.checkNumber = function(val) {
    if(!val) return true;
    if(val.match(/[^0-9]+/)){
        return false;
    } else {
        return true;
    }
};
/* 半角数字チェック(特殊文字許可) */
tci.checkSpCharNumber = function(val) {
    if(!val) return true;
    if(val.match(/[^0-9]+/)){
        return false;
    } else {
        return true;
    }
};
/* 半角数値チェック 小数点ピリオドとマイナス-を許容 trueがOK*/
tci.checkNumeric = function(val) {
    if(!val) return true;
    if(val.match(/^[-]?[0-9]+(\.[0-9]+)?$/)) {
        return true;
    } else {
        return false;
    }
};
/* 金額入力チェック マイナス-を許容 trueがOK*/
tci.checkCurrency = function(val) {
    if(!val) return true;
    if(val.match(/^[-]?[0-9]+$/)) {
        return true;
    } else {
        return false;
    }
};
/* 半角英数字チェック */
tci.checkAlphaNumber = function(val) {
    if(!val) return true;
    if(val.match(/[^0-9a-zA-Z\-\/]/)) {
        return false;
    } else {
        return true;
    }
};
// ************************************************************
// 日付・時間・時刻関連
// ************************************************************
/* 日付妥当チェック */
tci.checkDate = function(datestr) {
    if(!datestr) return true;
    // 正規表現による書式チェック
    if(!datestr.match(/^\d{4}\/\d{2}\/\d{2}$/)){
        return false;
    }
    var vYear = datestr.substr(0, 4) - 0;
    var vMonth = datestr.substr(5, 2) - 1; // Javascriptは、0-11で表現
    var vDay = datestr.substr(8, 2) - 0;
    // 月,日の妥当性チェック
    if(vMonth >= 0 && vMonth <= 11 && vDay >= 1 && vDay <= 31){
        var vDt = new Date(vYear, vMonth, vDay);
        if(isNaN(vDt)){
            return false;
        }else if(vDt.getFullYear() == vYear && vDt.getMonth() == vMonth && vDt.getDate() == vDay){
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
};
/* 曜日取得 */
tci.getDayOfWeek = function(date) {
    var d = new Date(date);
    var w = ["日","月","火","水","木","金","土"];
    return w[d.getDay()];
};
/* 年月チェック */
tci.isYearMonth = function(val) {
    if(!val) return true;
    var year = val.substr(0,4);
    var month;
    if(val.length == 7) {
        month = val.substr(5,2);
        if(val.substr(4,1) != '/') return false;
    } else {
        month = val.substr(4,2);
    }
    if(month >= 1 && month <= 12) {
        return true;
    } else {
        return false;
    }
};
/* 時刻チェック */
tci.isTimeHHMI = function(val) {
    if(!val) return true;
    if(val.match(/^([01]?[0-9]|2[0-3]):([0-5][0-9])$/)) {
        return true;
    } else {
        return false;
    }
};
// ************************************************************
// 郵便番号・住所
// ************************************************************
/* 郵便番号チェック */
tci.isZipCd = function(val) {
    if(!val) return true;
    if(val.length == 7 ) val = val.substr(0,3) + "-" + val.substr(3,4);
    var val1 = val.substr(0,3);
    var val3 = val.substr(4,4);
    if(val.length != 8) return false;
    if(val1.match(/[^0-9 .]+/)) return false;
    if(val3.match(/[^0-9 .]+/)) return false;
    return true;
};
/* 電話番号チェック */
tci.isTelNo = function(val) {
    if(!val) return true;
    // 電話番号チェック
    data1 = val.match(/^[0-9-]{6,9}$|^[0-9-]{12}$/);
    data2 = val.match(/^\d{1,4}-\d{4}$|^\d{2,5}-\d{1,4}-\d{4}$/);
    // 携帯番号チェック
    data3 = val.match(/^\d{3}-\d{4}-\d{4}$|^\d{11}$/);
    if(!data1 && !data2){
        if(!data3) {
            return false;
        }
    } 
    return true;
};
/* メールアドレスチェック */
tci.isMailAddress = function(val) {
    if(!val) return true;
    if(!val.match(/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/)){
    //if(!val.match(/^[A-Za-z0-9]+[\w-]+@[\w\.-]+\.\w{2,}$/)){
        return false;
    } else {
        return true;
    }
};
// ************************************************************
// Array配列 
// ************************************************************
/* Array配列か否かをチェック */
tci.isArray = function(value) {
    return value &&                             
        typeof value === 'object' &&
        typeof value.length === 'number' &&
        typeof value.splice === 'function' &&
        !(value.propertyIsEnumerable('length'));
};
// ************************************************************
// HTTP関連
// ************************************************************
/* HTTP GETパラメータ取得 @return Array配列 */
tci.getQueryString = function() {
    var vars = {};
    var param = location.search.substring(1).split('&');
    for(var i = 0; i < param.length; i++) {
        var keySearch = param[i].search(/=/);
        var key = '';
        if(keySearch != -1) key = param[i].slice(0, keySearch);
        var val = param[i].slice(param[i].indexOf('=', 0) + 1);
        if(key != '') vars[key] = decodeURI(val);
    }
    return vars;
};
// ************************************************************
// センタリング関数　 
// 引数：セレクタ　ex. "#hogeid" ".hogeclass"
// ************************************************************
/* 指定されたセレクタのオブジェクトを画面中央に配置する */
tci.centering = function(selector) {
    // 幅/高さ取得
    var w = window.innerWidth;;
    var h = window.innerHeight;;
    var cw = $(selector).outerWidth();
    var ch = $(selector).outerHeight();
    // センタリング計算
    var pxleft = ((w - cw)/2);
    var pxtop = ((h - ch)/2);
    // CSS値設定
    $(selector).css({"left": pxleft + "px"});
    $(selector).css({"top": pxtop + "px"});
};
// ************************************************************
// リストheight調整関数　 
// 引数：セレクタ　ex. "#hogeid" ".hogeclass"
// ************************************************************
/* 隣り合う要素の高さをそろえる */
tci.heightMatch = function(selector) {
    var obj = $(selector);
    // 要素の総数
    var objLength = obj.length;
    // 横の列（行）それぞれについて実行
    for(var i = 0 ; i < Math.ceil(objLength / 2) ; i++) {
        var maxHeight = 0;
        //同じ横の列（行）のそれぞれの要素について実行
        for(var j = 0; j < 2; j++){
            if (obj.eq(i * 2 + j).height() > maxHeight) {
                maxHeight = obj.eq(i * 2 + j).height();
            }
        }
        //要素の高さの最大値をそれぞれの要素の高さとして設定
        for(var k = 0; k < 2; k++){
            obj.eq(i * 2 + k).height(maxHeight);
        }
    }
};
// ************************************************************
// インジケータ表示  true: 表示、false: 非表示
// ************************************************************
/* インジケータ表示（CSSも配置が必要） */
tci.showIndicator = function(show) {
	if(show) {
		$('body').append('<div id="tciIndicator"></div>');
		$('#tciIndicator').activity({
			segments:10,
			width:12,
			space:6,
			length:20,
			color:'#fff',
			speed:1.5
		});
	} else {
		setTimeout(function() {
			$("#tciIndicator").activity(false);
		}, 500);
		$('#tciIndicator').remove();
	}
};
// ************************************************************
// 吹き出し表示  divid: 吹き出しを追加するdivタグのID
// ************************************************************
/* 指定されたオブジェクトの上部に吹き出しメッセージを表示 */
tci.showBalloonTop = function(divid,message) {
	$('#'+divid).append('<p class="tciBalloontop">' + message + '</p>');
	setTimeout(function() {
		$('.tciBalloontop').fadeOut("slow");
	}, 4000);
	setTimeout(function() {
		$('.tciBalloontop').remove();
	}, 5000);
};
// ************************************************************
// その他
// ************************************************************
/* アップロード対象ファイルのサイズチェック */
tci.checkUploadSize = function(obj) {
    var maxsize = $(obj).attr("maxsize");
    for(var i=0; i<obj.files.length; i++){
        if(obj.files[i].size > maxsize) {
            return "選択されたファイルサイズ("+tci.addFigure(obj.files[i].size)+"バイト)が上限("+tci.addFigure(maxsize)+"バイト)を超えています。";
        }
    }
};
// ************************************************************
// メッセージモーダル表示
// type: "info"/"err"
// okbtn: true/false
// clazz: モーダルを一意に特定するための文字列
//        ※下記のように複数モーダルが起動する場合に一意に特定して処理する必要があるため。
//        　ex. 「削除しますか？」→OKでサーバアクション→「削除しました」
// ************************************************************
/* 確認モーダル表示 OK/閉じるの選択 */
tci.showConfModal = function(msg,func,clazz) {
    tci.showMsgModal(msg,"info",true,func,clazz);
};
/* インフォメーションモーダル表示 OKのみ表示 */
tci.showInfoModal = function(msg,func,clazz) {
    tci.showMsgModal(msg,"info",false,func,clazz);
};
/* エラーモーダル表示 閉じるのみ表示 */
tci.showErrModal = function(msg,clazz) {
    tci.showMsgModal(msg,"Err",false,null,clazz);
};
/* モーダル表示 */
tci.showMsgModal = function(msg,type,okbtn,func,clazz) {
    // デジタルサイネージの場合は無人なのでモーダルは表示しない
    if(typeof signage_enabled !== "undefined") {
        
        return;
    }
    // Class指定されていない場合は固定値を付与
    if(!clazz) clazz ="tciMsgModal";
    //キーボード操作などにより、オーバーレイが多重起動するのを防止するため、フォーカスを外す
    $(this).blur();
    // オーバーレイ表示 ※現在のモーダルウィンドウを削除して新しく起動している
    if($("#tciMsgModalOverlay." + clazz)[0]) $("#tciMsgModalOverlay." + clazz).remove() ;
    $("body").append('<div id="tciMsgModalOverlay" class="' + clazz + '"></div>');
    $("#tciMsgModalOverlay." + clazz).fadeIn("slow");
    // メッセージテキスト取得
    var msgText = "";
    var msgArray = msg.split(",");
    for (var i = 0; i < msgArray.length; i++) {
        msgText = msgText + '<p>' + msgArray[i] + '</p>';
    }
    // メッセージモーダル生成
    $("body").append('<div id="tciMsgModal" class="' + clazz + '">'
            + '<div class="tciMsgTitle"><p>メッセージ</p></div>'
            + '<div class="tciMsgText">'+msgText+'</div>'
            + '<div id="tciMsgBtnArea"></div>'
            + '</div>');
    // メッセージタイプ設定
    if(type == "info") {
        $('#tciMsgModal.' + clazz).addClass("tciMsgInfo");
    } else {
        $('#tciMsgModal.' + clazz).addClass("tciMsgErr");
    }
    // OK/閉じるボタン追加
    $('#tciMsgModal.' + clazz + ' #tciMsgBtnArea').empty();
    if(okbtn){
        $('#tciMsgModal.' + clazz + ' #tciMsgBtnArea').append('<p class="tcibtn gray tciMsgModalClose col2" tabIndex="0">閉じる</p>');
        $('#tciMsgModal.' + clazz + ' #tciMsgBtnArea').append('<p class="tcibtn green tciMsgModalOk tciMsgModalFunc" tabIndex="0">OK</p>');
    } else {
        $('#tciMsgModal.' + clazz + ' #tciMsgBtnArea').append('<p class="tcibtn gray tciMsgModalClose tciMsgModalFunc col1" tabIndex="0">閉じる</p>');
    }
    tci.centering("#tciMsgModal." + clazz);                // センタリング
    $("#tciMsgModal." + clazz).fadeIn("slow");             // メッセージダイアログ表示
    $('#tciMsgModal.' + clazz + ' .tciMsgModalClose').focus();  // 閉じるボタンにフォーカスを当てる
    // イベント登録: ENTER押下時にクリックイベントを発生させる
    $('#tciMsgModal.' + clazz + ' .tcibtn').keypress(function(e) {
            if ( e.which == 13 || e.which == 32) {
                $(this).click();
                return false;
            }
            });
    // イベント登録：ボタン押下時に指定された関数を実行
    $('#tciMsgModal.' + clazz + ' .tciMsgModalFunc').off('click');
    $('#tciMsgModal.' + clazz + ' .tciMsgModalFunc').on('click',function(e) {
            if(typeof func == "function") {
                func();
            }
            });
    // イベント登録：Tabキー押下時に親画面にフォーカスを戻さない　
    $('#tciMsgModal.' + clazz + ' .tciMsgModalOk').keydown(function(e) {
            if ( e.which == 9) {
                $('#tciMsgModal.' + clazz + ' .tciMsgModalClose').focus();
            }
            });
    // イベント登録:Close機能
    $("#tciMsgModal." + clazz + " .tcibtn").click(function() {
            $("#tciMsgModal." + clazz + ",#tciMsgModalOverlay." + clazz).fadeOut("slow",function(){  //フェードアウト後、HTML(DOM)上から削除
                    $("#tciMsgModalOverlay." + clazz).remove();
                    $("#tciMsgModal." + clazz).remove();
                    //$('input[name^=hasServerMessage]').remove();
                    //$('input[name^=serverMessage]').remove();
                    });
            });
};
// ************************************************************
// 共通処理 ※どのWEBサービスでも必ず組み込むべき共通実装
// ************************************************************
/**
 * Ajax通信中の画面遷移時にAjax通信をアボートする処理
 * JS読み込み前に画面遷移しようとしてシステムエラーとなることを防止
$(document).bind("ajaxSend", function(c, xhr) {
    $(window).bind('beforeunload', function() {
        xhr.abort();
    })
});
 */
