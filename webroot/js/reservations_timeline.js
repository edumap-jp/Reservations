/**
 * @fileoverview Reservation Javascript
 * @author info@allcreator.net (Allcreator Co.)
 */


/**
 * 水平タイムラインデータ初期処理
 *
 */
NetCommonsApp.controller('ReservationsHorizonTimeline', ['$scope', function($scope) {
  /**
   * イニシャライズ処理
   *
   * @param {string} frameId
   * @return {void}
   */
  $scope.initialize = function(frameId) {
    //タイムラインdiv
    var coordinateOrigins = $('#frame-' + frameId + ' .reservation-horizon-timeline');

    //指定時間のindex値を、タイムラインdivの属性から取り出し
    var idx = $(coordinateOrigins[0]).attr('data-daily-start-time-idx') - 0;

    //00:00の行のtop 誤差をなくすため2300に変更
    //var row0 = $('.reservation-daily-timeline-0000');
    //var row0Top = row0[0].getBoundingClientRect().top;

    //01:00の行のtop
    var row1 = $('#frame-' + frameId + ' .reservation-daily-timeline-0100');
    var row1Top = row1[0].getBoundingClientRect().top;
    var row1Left = row1[0].getBoundingClientRect().left;

    //23:00の行のtop
    var row23 = $('#frame-' + frameId + ' .reservation-daily-timeline-2300');
    var row23Top = row23[0].getBoundingClientRect().top;
    var row23Left = row23[0].getBoundingClientRect().left;

    //1列(1時間)の幅
    var rowWidth = (row23Left - row1Left) / 22;

    ////1行(=１時間)の高さ
    ////var rowHeight = row1Top - row0Top;
    //var rowHeight = (row23Top - row1Top) / 22;

    //指定時間が最初になるよう、divの横スクロールを移動
    coordinateOrigins[0].scrollLeft = rowWidth * idx;

    ////指定時間が最初になるよう、divの縦スクロールを移動
    //coordinateOrigins[0].scrollTop = rowHeight * idx;

    //$scope.origin = coordinateOrigins[0].scrollTop;
    //$scope.rowHeight = rowHeight;
    $scope.rowWidth = rowWidth;

    //0:00幅固定
    $('#frame-' + frameId + ' .reservation-timeline-data-area').width(rowWidth);
    ////0:00高さ固定
    //$('.reservation-timeline-data-area').height(rowHeight);

    var row1Top = row1[0].getBoundingClientRect().top;
    $scope.rowTop = row1Top;

    //初期化
    $scope.prevMargin = 0;
    $scope.maxLineNum = 0;
    $scope.Column = [];
    $scope.Column[0] = [];
  };
}]);


/**
 * 水平タイムラインの予約データ
 */
NetCommonsApp.controller('ReservationsHorizonTimelinePlan', ['$scope', function($scope) {
  /**
   * 予約データ保持
   */
  $scope.reservationPlans = [];

  /**
   * イニシャライズ処理
   *
   * @param {type} data
   * @return {void}
   */
  $scope.initialize = function(data) {
    $scope.reservationPlans = data.reservationPlans;

    //位置情報を設定
    for (var i = 0; i < data.reservationPlans.length; i++) {
      $scope.setTimelinePos(
          i,
          $scope.reservationPlans[i].fromTime,
          $scope.reservationPlans[i].toTime,
          $scope.reservationPlans[i].locationKey,
          $scope.reservationPlans[i].eventId
      );
    }
  };

  /**
   * タイムラインのポジションセット
   *
   * @param {type} id
   * @param {type} fromTime
   * @param {type} toTime
   * @return {void}
   */
  $scope.setTimelinePos = function(id, fromTime, toTime, locationKey, eventId) {
    //var planObj = document.getElementById('plan' + String(id));
    if (toTime == '00:00') {
      // 終了時刻が00:00ならそれは24:00のこと
      toTime = '24:00';
    }
    var planObj = $('div[data-event-id=' + eventId + ']')[0];

    var start = fromTime.split(':');
    var end = toTime.split(':');

    var startHour = parseInt(start[0]);
    var startMin = parseInt(start[1]);

    var endHour = parseInt(end[0]);
    var endMin = parseInt(end[1]);

    if (endHour < startHour) {
      endHour = 24;
    }

    //幅
    var width = endHour - startHour;
    width = (width + ((endMin - startMin) / 60)) * $scope.rowWidth;

    //開始位置
    var left = (startHour + (startMin / 60)) * $scope.rowWidth;

    //タイムライン重ならない列数を取得
    var lineNum = $scope.getLineNum(left, (width + left));

    //位置決定
    planObj.style.width = String(width) + 'px';
    planObj.style.left = String(left) + 'px'; //(調整)

    //前回の位置が蓄積されてくる※位置調整のため
    $scope.prevMargin = $scope.prevMargin + width;

    //次回の重なりチェックのため、値保持
    var data = {x: (width + left), y: left};
    $scope.Column[lineNum].push(data);

    //上からの位置
    planObj.style.position = 'absolute';
  };

  /**
   * 時間帯の重なり列を取得
   * 施設予約は、重なりはないので0とする
   *
   * @param {type} x
   * @param {type} y
   * @return {Number}
   */
  $scope.getLineNum = function(x, y) {
    return 0;
    ////0列目からチェック
    //for (var i = 0; i <= $scope.maxLineNum; i++) {
    //  if ($scope.checkColumn(i, x, y) == false) {
    //    return i; //重なりの無い列を返却
    //  }
    //}
    //
    //$scope.maxLineNum++; //新しい列
    //$scope.Column[$scope.maxLineNum] = [];
    //return $scope.maxLineNum;
  };

  //$scope.checkColumn = function(checkColumn, x, y) {
  //
  //  //指定列の重なりチェック
  //  for (var i = 0; i < $scope.Column[checkColumn].length; i++) {
  //    if ($scope.checkOverlap($scope.Column[checkColumn][i].
  //        x, $scope.Column[checkColumn][i].y, x, y) == true) {
  //      return true;
  //    }
  //  }
  //  return false; //重なりなし
  //};

  //$scope.checkOverlap = function(x1, y1, x2, y2) {
  //
  //  //線分1と線分2の重なりチェック
  //  if (x1 >= x2 && x1 >= y2 &&
  //      y1 >= x2 && y1 >= x2) {
  //    return false;
  //  }
  //  if (x2 >= x1 && x2 >= y1 &&
  //      y2 >= x1 && y2 >= y1) {
  //    return false;
  //  }
  //  return true; //重なりあり
  //};

}]);

//// 週間表示タイムライン
//NetCommonsApp.controller('ReservationsWeeklyTimelinePlan', ['$scope', function($scope) {
//  // $scope.reservationPlans = [];
//
//  $scope.initialize = function(data) {
//    // 曜日毎に繰り返し呼び出されることは想定されてない
//    // $scope.reservationPlans = data.reservationPlans;
//    //console.log($scope.reservationPlans);
//    //位置情報を設定
//    for (var i = 0; i < data.reservationPlans.length;
//         i++) {
//      // $scope.setTimelinePos(i, $scope.reservationPlans[i].
//      //     fromTime, $scope.reservationPlans[i].toTime);
//      $scope.setTimelinePos(data.reservationPlans[i].element_id, data.reservationPlans[i].
//          fromTime, data.reservationPlans[i].toTime);
//    }
//  };
//
//  $scope.setTimelinePos = function(elementId, fromTime, toTime) {
//    // var planObj = document.getElementById('plan' + String(id));
//    var planObj = document.getElementById(elementId);
//
//    var start = fromTime.split(':');
//    var end = toTime.split(':');
//
//    var startHour = parseInt(start[0]);
//    var startMin = parseInt(start[1]);
//
//    var endHour = parseInt(end[0]);
//    var endMin = parseInt(end[1]);
//
//    if (endHour < startHour) {
//      endHour = 24;
//    }
//    //高さ
//    var height = endHour - startHour;
//    height = (height + ((endMin - startMin) / 60)) * $scope.rowHeight;
//
//    //開始位置
//    var top = (startHour + (startMin / 60)) * $scope.rowHeight;
//
//    //タイムライン重ならない列数を取得
//    var lineNum = $scope.getLineNum(top, (height + top));
//
//    //位置決定
//    planObj.style.height = String(height) + 'px';
//    planObj.style.top = String(top - $scope.prevMargin) + 'px'; //(調整)
//
//    //前回の位置が蓄積されてくる※位置調整のため
//    $scope.prevMargin = $scope.prevMargin + height;
//
//    //次回の重なりチェックのため、値保持
//    var data = {x: top, y: (height + top)};
//    $scope.Column[lineNum].push(data);
//
//    //左からの位置
//    planObj.style.left = String((lineNum * ($scope.rowWidth + 15)) + 5) + 'px';
//    planObj.style.position = 'relative';
//  };
//
//  $scope.getLineNum = function(x, y) {
//    // weeklyは1列表示なので。
//    return 0;
//
//    //0列目からチェック
//    for (var i = 0; i <= $scope.maxLineNum; i++) {
//      if ($scope.checkColumn(i, x, y) == false) {
//        return i; //重なりの無い列を返却
//      }
//    }
//
//    $scope.maxLineNum++; //新しい列
//    $scope.Column[$scope.maxLineNum] = [];
//    return $scope.maxLineNum;
//  };
//
//  $scope.checkColumn = function(checkColumn, x, y) {
//
//    //指定列の重なりチェック
//    for (var i = 0; i < $scope.Column[checkColumn].length; i++) {
//      if ($scope.checkOverlap($scope.Column[checkColumn][i].
//              x, $scope.Column[checkColumn][i].y, x, y) == true) {
//        return true;
//      }
//    }
//    return false; //重なりなし
//  };
//
//  $scope.checkOverlap = function(x1, y1, x2, y2) {
//
//    //線分1と線分2の重なりチェック
//    if (x1 >= x2 && x1 >= y2 &&
//        y1 >= x2 && y1 >= x2) {
//      return false;
//    }
//    if (x2 >= x1 && x2 >= y1 &&
//        y2 >= x1 && y2 >= y1) {
//      return false;
//    }
//    return true; //重なりあり
//  };
//
//}]);
