$(function(){
    $("ul.menu").hide();
    $("div.category").click(function(){
        $("ul.menu").hide();
        if($("+ul",this).css("display")=="none"){
            $("+ul",this).show();
        }
    });
    var menu = $("div.js-menu");
    var search = $("div.js-search");
    menu.hide();
    search.hide();
    $("div.menu").on("click", {a: menu, b: search}, slide);
    $("div.search").on("click", {a: search, b: menu}, slide);
    function slide(event) {
        if (event.data.a.css("display") === "none") {
            event.data.a.slideDown(250);
            event.data.b.slideUp(250);
        } else {
            event.data.a.slideUp(250);
        }
    }
	//バス停IDの取得
    //var getData = tci.getQueryString();
    var getData = tci.getQueryString()['bsid']
    if(!getData) {
        getData = "0000010101";
    }
    var opts = { busstop_id: getData};

    tci.runApi('./ajax/getBusTimetable.php',opts,function(response) {
    	//バス停名の取得
        html = response["name"]["busstop_name"] + '<br /><span class="glay">' + response["name"]["busstop_kana"] + '</span>';
    	$('.bus-stop').empty();
    	$('.bus-stop').append(html);
        //今日の曜日取得
        var today = new Date();
        var week = today.getDay();
        //曜日に対応するリストの作成
        $.each(response['ybkbn'], function(index,dayInfo){
            html = '<li id=' + dayInfo['ybkbn'] + '><span>' + dayInfo['ybkbn_name'] + '</span></li>'
            $('.days').append(html);
        });
        //時刻表の作成
        $.each(response['ybkbn'], function(index,dayInfo){
            html = '<table class="timetabel" id="table' + dayInfo['ybkbn'] + '">'; 
            for(var hour=4;hour<=23;hour++){
                html = html + '<tr><td class="hour">' + tci.zeroPadding(hour,2) + '<span>時</span></td><td id=table' + dayInfo['ybkbn'] +'_hour' + hour + '></td></tr>';
            }
        html = html + '</table>';
        $('#timetable-content').append(html);
        });
        //時刻表中身作成
        $.each(response['ybkbn'], function(index,dayInfo){
            $.each(response['busdia'][dayInfo['ybkbn']], function(index,diaInfo){
                hour = '#table' + dayInfo['ybkbn'] + '_hour' + diaInfo["hour"];
                html = tci.zeroPadding(diaInfo["minute"],2) + " ";
                $(hour).append(html);
            });
        });
        //targetの指定
        $.each(response['ybkbn'], function(index,dayInfo){
            table_id = "#table" + dayInfo["ybkbn"];
            if(dayInfo["ybkbn"] == response["day"]["ybkbn"]){
                id = "#" + dayInfo["ybkbn"];
                $(id).addClass("target");
                $(table_id).css("display","table");
            }else {
                $(table_id).css("display","none");
            }
        });
        //クリックしたときの処理
        $.each(response['ybkbn'], function(index,dayInfo){
            ybkbn = dayInfo['ybkbn'];
            id = "#" + dayInfo['ybkbn'];
            $(id).on('click', {e : ybkbn},function(event) {
                id = "#" + event.data.e;
                $.each(response['ybkbn'], function(index,dayInfo){
                    table_id = "#table" + dayInfo["ybkbn"];
                    if(event.data.e == dayInfo['ybkbn']){
                        //targetクラスの追加,テーブルを追加
                        $(id).addClass("target");
                        $(table_id).css("display","table");
                    }else {
                        //targetクラスの除去,テーブルを削除
                        id_tmp = "#" + dayInfo['ybkbn'];
                        $(id_tmp).removeClass("target");
                        $(table_id).css("display","none");
                    }
                });
            });
        });
    });
});

