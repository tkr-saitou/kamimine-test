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
    
    $('.initial').click(function() {
        var opts = { initial: $(this).text() };
        tci.runApi('./ajax/getBusStopSearch.php',opts,function(response) {
            var html = '';
            var count = 0;
            //行ごとにhtmlを作成
            $.each(response['busstop'], function(index,stopInfo){
                count = count + 1;
                html = html + '<li class="busstoplist clearfix" id = ' + stopInfo['busstop_id'] + '><a href="#"><span class="gray func">'
                          + stopInfo['busstop_name'] + '（' + stopInfo['busstop_kana'] + '）'
                          + '</span><span>行き先：' + stopInfo['last_busstop_name'];
                          
                // 方向表示がある場合は出力
                if(stopInfo['route_direction']){
                    html = html + '（' + stopInfo['route_direction'] + '）';
                }
                html = html + '</span></a></li>';
            });
            //テーブルの表示
            $('.table').empty();
            $('.table').append(html);
            $('.result-title').empty();
            var html = '『<span>' + response['initial'] +'</span>』で検索した結果 <span>' + count + '件</span>の停留所が見つかりました。'
            $('.result-title').append(html);

            // 行クリックイベントの登録 
            $('.busstoplist').bind('click',function() {
                var bsid = $(this).attr('id');
                var BusStatus = '/kamimine/timetable.php?bsid='+ bsid;
                location.href = BusStatus
            });
        });
    });

    //戻るボタン
    $('#backbutton').click(function() {
        location.href = '/kamimine/index.php';
    });
    
});

