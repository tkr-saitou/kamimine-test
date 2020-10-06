// ************************************************************
// 名前空間
// ************************************************************
if (typeof tci == "undefined") {
    var tci = {};
}
// ************************************************************
// HTTP関連
// ************************************************************
/**
 * HTTP GETパラメータ取得
 * @return Array配列
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
}
 */

/**
 * 予期せぬエラー
 */
tci.systemErr = function() {
    tci.showErrModal("予期せぬエラーが発生しました。");
}

/**
 * Ajax通信中の画面遷移時にAjax通信をアボートする処理
 */
$(document).bind("ajaxSend", function(c, xhr) {
    $(window).bind('beforeunload', function() {
        xhr.abort();
    })
});
// ************************************************************
// API実行
// ************************************************************
/**
 * API実行
 */
tci.runApi = function(apiName,opts,func,showIndicatorFlg) {
    if (showIndicatorFlg) {
        // インジケータ表示
        tci.showIndicator(true);
    }
    $.ajax({
        type: "POST",
        url: apiName,
        dataType: "JSON",
        data: opts,
        success: function(response) {
            // Ajax終了後の関数実行
            if(typeof func == "function") {
                func(response);
            }
            if (showIndicatorFlg) {
                // インジケータ表示終了
                tci.showIndicator(false);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            if (showIndicatorFlg) {
                // インジケータ表示終了
                tci.showIndicator(false);
            }
            tci.systemErr();
        }
    });
}
// ************************************************************
// インジケータ表示  true: 表示、false: 非表示
// ************************************************************
/*
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
*/
