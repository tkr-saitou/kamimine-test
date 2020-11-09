// For Google Map
// ************************************************************
// グローバル変数
// ************************************************************
var map;
var busList = [];
var busstopList = [];
var landmarkList = [];
var routeList = [];
var infoWnd = new google.maps.InfoWindow();
var routeColorList = {};

// ************************************************************
// IE8でforEachがエラーになる件、MDNの対応策をそのまま使用
//  MDN:https://developer.mozilla.org/ja/docs/Web/JavaScript/Reference/Global_Objects/Array/forEach
// ************************************************************
// Production steps of ECMA-262, Edition 5, 15.4.4.18
// Reference: http://es5.github.com/#x15.4.4.18
if ( !Array.prototype.forEach ) {
  Array.prototype.forEach = function( callback, thisArg ) {

    var T, k;

    if ( this == null ) {
      throw new TypeError( " this is null or not defined" );
    }

    // 1. Let O be the result of calling ToObject passing the |this| value as the argument.
    var O = Object(this);

    // 2. Let lenValue be the result of calling the Get internal method of O with the argument "length".
    // 3. Let len be ToUint32(lenValue).
    var len = O.length >>> 0; // Hack to convert O.length to a UInt32

    // 4. If IsCallable(callback) is false, throw a TypeError exception.
    // See: http://es5.github.com/#x9.11
    if ( {}.toString.call(callback) != "[object Function]" ) {
      throw new TypeError( callback + " is not a function" );
    }

    // 5. If thisArg was supplied, let T be thisArg; else let T be undefined.
    if ( thisArg ) {
      T = thisArg;
    }

    // 6. Let k be 0
    k = 0;

    // 7. Repeat, while k < len
    while( k < len ) {

      var kValue;

      // a. Let Pk be ToString(k).
      //   This is implicit for LHS operands of the in operator
      // b. Let kPresent be the result of calling the HasProperty internal method of O with argument Pk.
      //   This step can be combined with c
      // c. If kPresent is true, then

      if ( k in O ) {

        // i. Let kValue be the result of calling the Get internal method of O with argument Pk.
        kValue = O[ k ];

        // ii. Call the Call internal method of callback with T as the this value and
        // argument list containing kValue, k, and O.
        callback.call( T, kValue, k, O );
      }
      // d. Increase k by 1.
      k++;
    }
    // 8. return undefined
  };
}

// ************************************************************
// 名前空間
// ************************************************************
if (typeof drawmap == "undefined") {
    var drawmap = {};
}

// ************************************************************
// 地図描画関連
// ************************************************************
// 初期描画
drawmap.mapInitialize = function(lat, lng, fullscreen) {
	if (fullscreen === 'undefined') fullscreen = true;
	var mapDiv = document.getElementById("map_canvas");
	var mapOpts = {
		center : new google.maps.LatLng(lat, lng),
		zoom : zoom,
		panControl : false,
		zoomControl : false,
		mapTypeControl : false,
		scaleControl : false,
		streetViewControl : false,
		overviewControl : false,
		fullscreenControl : fullscreen,
		mapTypeId : google.maps.MapTypeId.ROADMAP
	};
	map = new google.maps.Map(mapDiv, mapOpts);
	google.maps.event.addListener(map, 'zoom_changed', function() {
    		changeIconSize(map.getZoom());
    		changePolylineWeight(map.getZoom());
	});

    // 以下の処理はmap初期化の中ではなく、別で呼ぶべき→移動
	// 本日最新のバスロケ表示
	//drawmap.setBusLocation(0, 0);
	// 全バス停表示
    drawmap.drawBusStopMarkers(0, 0);
	// 全路線表示
    drawmap.drawCoursePolyline(0, 0);

}

// 地図の中心を移動する
drawmap.moveCenter = function(lat, lng, zoom) {
	map.panTo(new google.maps.LatLng(lat, lng));
	map.setZoom(zoom);
}
// マーカーを作成する
function createMarker(opts) {
	var marker = new google.maps.Marker(opts);
	marker.set("id", opts.id);
	marker.set("type", 0);
	google.maps.event.addListener(marker, "click", function() {
		infoWnd.setContent(opts.text);
		infoWnd.open(marker.getMap(),marker);
	});
	return marker;
}
// アイコンのサイズ変更
function changeIconSize(level) {
/*
	if (level < 14) {
		$.each(busstopList, function(i, busstop) {
			if ($('.selector#area').val() == 101) {
				if (Math.floor(busstop.id / 10) == $('.selector#fromBS').val() || Math.floor(busstop.id / 10) == $('.selector#toBS').val()) {
					busstop.setIcon("./images/busstop-normal.png");
				} else {
					busstop.setIcon("./images/busstop-red.png");
				}
			} else {
				if (Math.floor(busstop.id / 10) == $('.selector#fromBS').val() || Math.floor(busstop.id / 10) == $('.selector#toBS').val()) {
					busstop.setIcon("./images/busstop-normal.png");
				} else {
					busstop.setIcon("./images/busstop-red.png");
				}
			}
		});
	} else {
		$.each(busstopList, function(i, busstop) {
			if ($('.selector#area').val() == 101) {
				if (Math.floor(busstop.id / 10) == $('.selector#fromBS').val() || Math.floor(busstop.id / 10) == $('.selector#toBS').val()) {
					busstop.setIcon("./images/busstop-normal.png");
				} else {
					busstop.setIcon("./images/busstop-red.png");
				}
			} else {
				if (Math.floor(busstop.id / 10) == $('.selector#fromBS').val() || Math.floor(busstop.id / 10) == $('.selector#toBS').val()) {
					busstop.setIcon("./images/busstop-normal.png");
				} else {
					busstop.setIcon("./images/busstop-red.png");
				}
			}
		});
	}
*/
}

// ズームレベルに応じたポリラインの太さの変更
function changePolylineWeight(level) {
	var weight = 4;
	if (level > 13) weight = 10;
	$.each(routeList, function(i, route) {
		route.set("strokeWeight", weight);
	});
}

// ************************************************************
// バスアイコンのマーカー表示関連
// DBのプローブ情報より最新のバス情報を取得→バスアイコンのマーカー表示
// ************************************************************
drawmap.drawBusMarkerIcon = function(buscategory_cd, course_id) {
	//自動更新時はmapClear()しないのでバス停のみ削除
	busList.forEach(function(marker, idx) {
		marker.setMap(null);
	});
	busList = []; // グローバル変数初期化
		var opts = {"areaCd": buscategory_cd, "routeCd": course_id};
    tci.runApi("./ajax/getBusLocation.php", opts, function(response) {
		if (response.status == 0) {
			$.each(response.bus, function(i, bus) {
				createBusMarker(response.bus[i]);
			});
		}
	});
}
// 複数系統に属するバスのアイコンを表示する
drawmap.drawMultipleBusMarkerIcon = function(buscategory_cd, courseList) {
	//自動更新時はmapClear()しないのでバス停のみ削除
	busList.forEach(function(marker, idx) {
		marker.setMap(null);
	});
	busList = []; // グローバル変数初期化
    for (var key in courseList) {
        var opts = {"areaCd": buscategory_cd, "routeCd": courseList[key]};
        tci.runApi("./ajax/getBusLocation.php", opts, function(response) {
		    if (response.status == 0) {
			    $.each(response.bus, function(i, bus) {
				    createBusMarker(response.bus[i]);
			    });
		    }
	    });
    }
}

// バスアイコンのマーカー設定
function createBusMarker(bus) {
	if (bus.lat == 0 && bus.lng == 0) return;
	var delay = getBusDelayIcon(bus);
	var angle = getBusAngleIcon(bus);
    var opts = {
        id: bus.device_id,
		position: new google.maps.LatLng(bus.lat, bus.lng),
		icon: "./images/bus/bus_" + angle + "_" + delay + ".png",
		text: bus.text,
		map: map,
		zIndex: 10
	}
    var busMarker = createMarker(opts);
	busList.push(busMarker);
}
// 遅延別バスアイコン番号取得
function getBusDelayIcon(bus) {
    //console.log(bus);
	if (bus.delay < 5) { // 5分未満は遅延無
		return "blue";
	} else if (5 <= bus.delay && bus.delay < 10) { // 5分程度遅延
		return "yellow";
	} else if (10 <= bus.delay) { // 10分以上遅延
		return "red";
	}
}
// 角度別バスアイコン番号取得
function getBusAngleIcon(bus) {
	if (0 <= bus.angle && bus.angle < 11.25) {
		return 0;
	} else if (11.25 <= bus.angle && bus.angle < 33.75) {
		return 22.5;
	} else if (33.75 <= bus.angle && bus.angle < 56.25) {
		return 45;
	} else if (56.25 <= bus.angle && bus.angle < 78.75) {
		return 67.5;
	} else if (78.75 <= bus.angle && bus.angle < 101.25) {
		return 90;
	} else if (101.25 <= bus.angle && bus.angle < 123.75) {
		return 112.5;
	} else if (123.75 <= bus.angle && bus.angle < 146.25) {
		return 135;
	} else if (146.25 <= bus.angle && bus.angle < 168.75) {
		return 157.5;
	} else if (168.75 <= bus.angle && bus.angle < 191.25) {
		return 180;
	} else if (191.25 <= bus.angle && bus.angle < 213.75) {
		return 202.5;
	} else if (213.75 <= bus.angle && bus.angle < 236.25) {
		return 225;
	} else if (236.25 <= bus.angle && bus.angle < 258.75) {
		return 247.5;
	} else if (258.75 <= bus.angle && bus.angle < 281.25) {
		return 270;
	} else if (281.25 <= bus.angle && bus.angle < 303.75) {
		return 292.5;
	} else if (303.75 <= bus.angle && bus.angle < 326.25) {
		return 315;
	} else if (326.25 <= bus.angle && bus.angle < 348.75) {
		return 337.5;
	} else if (348.75 <= bus.angle && bus.angle <= 360) {
		return 360;
	}
}

// ************************************************************
// バス停表示関連
// DBよりバス停リストを取得してマーカーを表示する
// ************************************************************
drawmap.drawBusStopMarkers = function(areaCd, routeCd, displaySet, initFlg, fromBS, toBS) {
    if (initFlg) {
        busstopList.forEach(function(marker, idx) {
	    	marker.setMap(null);
    	});
	    busstopList = [];
    }
    var opts =  {"areaCd": areaCd, "routeCd": routeCd, "fromBS": fromBS, "toBS": toBS};
    tci.runApi("./ajax/getBusStopList.php", opts, function(obj) {
		if (obj.status == 0) {
			$.each(obj.busstop, function(i, busstop) {
				createBusStopMarker(busstop);
			});
            // 検索時の再描画の時のみ
            if (displaySet == 1) {
                drawmap.changeBusStopIcon($("#fromBS").val(), areaCd, 1, false);
								drawmap.changeBusStopIcon($("#toBS").val(), areaCd, 2, true);
								drawmap.changeBusStopIcon($("#kana-departure .acc-layer3").attr('id'), areaCd, 3, false);
								drawmap.changeBusStopIcon($("#kana-arrival .acc-layer3").attr('id'), areaCd, 4, true);
//                for (var key in displaySet) {
//                    drawmap.changeBusStopIcon(displaySet[key]["fromBsCd"], areaCd, 1, false);
//                    drawmap.changeBusStopIcon(displaySet[key]["toBsCd"], areaCd, 2, false);
//                }
            }
		}
	});
}

// バス停マーカーの設置
function createBusStopMarker(busstop) {
	var	icon = "./images/busstop-normal.png";

	if (typeof signage_enabled === "undefined")  {
		var text = '<p><span id="bsname_' + busstop.busstop_id + '">' + busstop.busstop_name + '</span><p>'
		+ '<p><a id="set_fromBS_' + busstop.busstop_id + '" href="#">出発バス停に設定する</a></p></div>'
		+ '<div id="set_toBS_' + busstop.busstop_id + '"><p><a href="#">到着バス停に設定する</a></p></div>';
	} else {
		if ($("#fromBS").val() == "00000101" && busstop.busstop_id.substr(0, 8) == "00000132") {
			return;
		}
		var text = '<p><span id="bsname_' + busstop.busstop_id + '">' + busstop.busstop_name + '</span><p>';
	}
	var opts = {
		id: busstop.busstop_id,
		position: new google.maps.LatLng(busstop.lat, busstop.lng),
		icon: new google.maps.MarkerImage(
            icon,
            new google.maps.Size(25,25),
            new google.maps.Point(0,0),
            new google.maps.Point(12.5,12.5)),
		text: text,
		map: map,
		zIndex: 5
	};

	var bsMarker = createMarker(opts);
	busstopList.push(bsMarker);
}

// 指定バス停マーカーのアイコン変更
drawmap.changeBusStopIcon = function(bscd, areacd, bsType, initFlg) {
	var icon;
	if (bscd == 0) { // バス停選択状態 -> バス停非選択状態
		if (map.getZoom() < 14) {
			if (areacd == 101) {
				defaultIcon = "./images/busstop-normal.png";
				icon = "./images/busstop-red.png";
			} else {
				defaultIcon = "./images/busstop-normal.png";
				icon = "./images/busstop-red.png";
			}
		} else {
			if (areacd == 101) {
				defaultIcon = "./images/busstop-normal.png";
				icon = "./images/busstop-red.png";
			} else {
				defaultIcon = "./images/busstop-normal.png";
				icon = "./images/busstop-red.png";
			}
		}
	} else {
		if (map.getZoom() < 14) {
			defaultIcon = "./images/busstop-normal.png";
			icon = "./images/busstop-red.png";
		} else {
			defaultIcon = "./images/busstop-normal.png";
			icon = "./images/busstop-red.png";
		}
    }
	$.each(busstopList, function(i, busstop) {
		if (busstop.get("id").slice(0, -2) == bscd) {
			busstop.setIcon(
                new google.maps.MarkerImage(
                    icon,
                    new google.maps.Size(25,25),
                    new google.maps.Point(0,0),
                    new google.maps.Point(12.5,12.5)
                )
            );
			busstop.set("type", bsType);
		} else if (initFlg && busstop.get("type") == bsType) {
			busstop.setIcon(
                new google.maps.MarkerImage(
                    defaultIcon,
                    new google.maps.Size(25,25),
                    new google.maps.Point(0,0),
                    new google.maps.Point(12.5,12.5)
                )
            );
			busstop.set("type", 0);
		}
	});
}

// ************************************************************
// ランドマーク表示関連
// DBよりランドマークのリスト取得→マーカー表示
// ************************************************************
function drawLandmarks(areaCd, routeCd) {
	landmarkList.forEach(function(marker, idx) {
		marker.setMap(null);
	});
	landmarkList = [];
    var opts = {"areaCd": areaCd, "routeCd": routeCd};
    tci.runApi("./ajax/getLandmark.php", opts, function(obj) {
		if (obj.status == 0) {
			$.each(obj.landmark, function(i, landmark) {
				createLandmarkMarker(landmark, areaCd);
			});
		}
	});
}
// 主要施設マーカーの設置
function createLandmarkMarker(landmark, areaCd) {
	var icon = "./images/bs_g_l.png";

	var text = '<p><span id="lmname_' + landmark.busstop_id + '">' + landmark.landmark_name + '</span><p>'
		+ '<p><a id="set_fromLM_' + landmark.busstop_id + '" href="#">出発主要施設に設定する</a></p></div>'
		+ '<div id="set_toLM_' + landmark.busstop_id + '"><p><a href="#">到着主要施設に設定する</a></p></div>';
	var opts = {
		id: landmark.landmark_id,
		position: new google.maps.LatLng(landmark.lat, landmark.lng),
		icon: icon,
		text: text,
		map: map,
		zIndex: 5
	};

	var lmMarker = createMarker(opts);
	landmarkList.push(lmMarker);
}

// ************************************************************
// DBより路線（Course）の点列のリストを取得→ポリライン描画
// ************************************************************
drawmap.drawCoursePolyline = function(areaCd, routeCd, initFlg) {
    if (initFlg) {
        routeList.forEach(function(marker, idx) {
	    	marker.setMap(null);
	    });
	    routeList = [];
    }
    var opts = {"areaCd": 0, "routeCd": 0};
    tci.runApi("./ajax/getCoursePointList.php", opts, function(obj) {
		if (obj.status == 0) {
			$.each(obj.route, function(i, route) {
				if (i == routeCd || routeCd == 0) {
					opacity = 1;
				} else {
					opacity = 0.3;
				}
				drawRoutePolyline(i, route, opacity);
			});
		}
	});
}

// 路線ポリラインの描画
function drawRoutePolyline(course_id, route, opacity) {
	var course_name = route.course_name;
	var coordinates = [];
	$.each(route.points, function(i, point) {
		coordinates.push(new google.maps.LatLng(point.lat, point.lng));
	});
	var color = getRouteColor(course_id);
	var routeLine = new google.maps.Polyline({
		path: coordinates,
		strokeColor: color,
		strokeWeight: 4,
		strokeOpacity: opacity,
	});
	routeLine.set("id", course_id);
	routeLine.setMap(map);
	google.maps.event.addListener(routeLine, "click", function(evt) {
		infoWnd.setContent(course_name);
		infoWnd.setPosition(evt.latLng);
		infoWnd.open(routeLine.getMap(), routeLine);
	});
	routeList.push(routeLine);
}

function getRouteColor(syscd) {
    if (syscd == 01) {
      return "#af3131";
    } else if (syscd == 02) {
      return "#32B16C";
    } else if (syscd == 03) {
      return "#315faf";
    } else if (syscd == 04) {
      return "#afaf31";
    } else if (syscd == 05) {
      return "#af31a3";
    } else {
      return;
    }
}

drawmap.setRouteColor = function(syscd, color) {
	routeColorList[syscd] = color;
}