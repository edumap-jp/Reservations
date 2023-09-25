/**
 * @fileoverview Reservation Javascript
 * @author info@allcreator.net (Allcreator Co.)
 */


/**
 * angularJS, NonANgularJS共通で使う、プラグイン名前空間
 */
var ReservationJS = {};  //専用空間

ReservationJS = {};  //専用空間


/**
 * ReservationJS.variables
 *
 * @type {Object.<string>}
 */
ReservationJS.variables = {
  REPEAT_FREQ_DAILY: 'DAILY',
  REPEAT_FREQ_WEEKLY: 'WEEKLY',
  REPEAT_FREQ_MONTHLY: 'MONTHLY',
  REPEAT_FREQ_YEARLY: 'YEARLY',

  RRULE_TERM_COUNT: 'COUNT',
  RRULE_TERM_UNTIL: 'UNTIL'
};

/**
 * angularJSをつかったJavaScriptプログラム(後半に、"NonAngularJS"コードあり)
 */


/**
 * YYYY-MM形式の年月を、言語別のフォーマットに変形するフィルター
 */
NetCommonsApp.filter('formatYyyymm', function() {
  return function(value, languageId) {
    if (!angular.isString(value)) {  //valueが文字列でなければ加工しない
      return value;
    }
    languageId = (languageId + '') || '2';    //lang指定なければデフォルト言語
    switch (languageId) {  //言語別 YYYY-MM 整形
      case '2':
        value = value.replace(/^(\d{1,4})-(\d{1,2})$/, '$1年$2月');
        break;
      default:
    }
    return value;
  }
});


/**
 * YYYY-MM-DD形式の年月を、言語別のフォーマットに変形するフィルター
 */
NetCommonsApp.filter('formatYyyymmdd', function() {
  return function(value, languageId) {
    if (!angular.isString(value)) {  //valueが文字列でなければ加工しない
      return value;
    }

    languageId = (languageId + '') || '2';    //lang指定なければデフォルト言語
    switch (languageId) {  //言語別 YYYY-MM-DD 整形
      case '2':
        value = value.replace(/^(\d{1,4})-(\d{1,2})-(\d{1,2})$/, '$1年$2月$3日');
        break;
      default:
    }
    return value;
  }
});

NetCommonsApp.controller('ReservationLocation',
    ['$scope', 'NetCommonsWysiwyg', function($scope, NetCommonsWysiwyg) {

      /**
   * tinymce
   *
   * @type {object}
   */
      $scope.tinymce = NetCommonsWysiwyg.new();

      $scope.init = function(data) {
        $scope.data = data;

        var boolKeys = ['use_all_rooms', 'use_workflow', 'use_private'];
        angular.forEach($scope.data.ReservationLocation, function(value, key) {
          if ($.inArray(key, boolKeys) >= 0) {

            if (value === '1' || value == true) {
              $scope.data.ReservationLocation[key] = true;
            } else {
              $scope.data.ReservationLocation[key] = false;
            }
          }
        });

        // postされたbool値変換
        // if ($scope.data.ReservationLocation.use_all_rooms === '1') {
        //   $scope.data.ReservationLocation.use_all_rooms = true;
        // } else {
        //   $scope.data.ReservationLocation.use_all_rooms = false;
        // }
        // // postされたbool値変換
        // if ($scope.data.ReservationLocation.use_workflow === '1') {
        //   $scope.data.ReservationLocation.use_workflow = true;
        // } else {
        //   $scope.data.ReservationLocation.use_workflow = false;
        // }
        // // postされたbool値変換
        // if ($scope.data.ReservationLocation.use_private === '1') {
        //   $scope.data.ReservationLocation.use_private = true;
        // } else {
        //   $scope.data.ReservationLocation.use_private = false;
        // }

        console.log($scope.data.ReservationLocation);
        if ($scope.data.ReservationLocation.start_time == '00:00' &&
         ($scope.data.ReservationLocation.end_time == '00:00' ||
         $scope.data.ReservationLocation.end_time == '24:00')) {
          $scope.allDay = true;
        }
      };
      $scope.checkAllDay = function() {
        if ($scope.allDay) {
          $scope.data.ReservationLocation.start_time = '00:00';
          $scope.data.ReservationLocation.end_time = '24:00';
          console.log($scope.data.ReservationLocation.end_time);
        }
      };
    }]);

NetCommonsApp.controller('ReservationSchedule', ['$scope', function($scope) {
  $scope.initialize = function(data) {
    $scope.isCollapsed = data.isCollapsed;
  };
}]);

NetCommonsApp.controller('ReservationsTimeline', ['$scope', function($scope) {
  /**
   * イニシャライズ処理
   *
   * @param {string} frameId
   * @return {void}
   */
  $scope.initialize = function(frameId) {
    //タイムラインdiv
    var coordinateOrigins = $('#frame-' + frameId + ' .reservation-vertical-timeline');

    //指定時間のindex値を、タイムラインdivの属性から取り出し
    var idx = $(coordinateOrigins[0]).attr('data-daily-start-time-idx') - 0;

    //00:00の行のtop 誤差をなくすため2300に変更
    //var row0 = $('.reservation-daily-timeline-0000');
    //var row0Top = row0[0].getBoundingClientRect().top;

    //01:00の行のtop
    var row1 = $('#frame-' + frameId + ' .reservation-daily-timeline-0100');
    var row1Top = row1[0].getBoundingClientRect().top;

    //23:00の行のtop
    var row23 = $('#frame-' + frameId + ' .reservation-daily-timeline-2300');
    var row23Top = row23[0].getBoundingClientRect().top;

    //1行(=１時間)の高さ
    //var rowHeight = row1Top - row0Top;
    var rowHeight = (row23Top - row1Top) / 22;
    //指定時間が最初になるよう、divの縦スクロールを移動
    // coordinateOrigins[0].scrollTop = rowHeight * idx;
    $('.reservation-vertical-timeline tbody').scrollTop(rowHeight * idx);
    //$scope.origin = coordinateOrigins[0].scrollTop;
    $scope.rowHeight = rowHeight;

    //0:00高さ固定
    $('#frame-' + frameId + ' .reservation-timeline-data-area').height(rowHeight);

    var row1Width = row1[0].getBoundingClientRect().width;
    $scope.rowWidth = row1Width;
    console.log('rowWitdh %d', row1Width);

    //初期化
    $scope.prevMargin = 0;
    $scope.maxLineNum = 0;
    $scope.Column = [];
    $scope.Column[0] = [];
  };
}]);

NetCommonsApp.controller('ReservationsTimelinePlan', ['$scope', function($scope) {
  $scope.reservationPlans = [];

  $scope.initialize = function(data) {
    $scope.reservationPlans = data.reservationPlans;

    //位置情報を設定
    for (var i = 0; i < data.reservationPlans.length;
        i++) {
      $scope.setTimelinePos(i, $scope.reservationPlans[i].
          fromTime, $scope.reservationPlans[i].toTime);
    }
  };

  $scope.setTimelinePos = function(id, fromTime, toTime) {
    var planObj = document.getElementById('plan' + String(id));

    var start = fromTime.split(':');
    var end = toTime.split(':');

    var startHour = parseInt(start[0]);
    var startMin = parseInt(start[1]);

    var endHour = parseInt(end[0]);
    var endMin = parseInt(end[1]);

    if (endHour < startHour) {
      endHour = 24;
    }

    //高さ
    var height = endHour - startHour;
    height = (height + ((endMin - startMin) / 60)) * $scope.rowHeight;

    //開始位置
    var top = (startHour + (startMin / 60)) * $scope.rowHeight;

    //タイムライン重ならない列数を取得
    var lineNum = $scope.getLineNum(top, (height + top));

    //位置決定
    planObj.style.height = String(height) + 'px';
    planObj.style.top = String(top - $scope.prevMargin) + 'px'; //(調整)

    //前回の位置が蓄積されてくる※位置調整のため
    $scope.prevMargin = $scope.prevMargin + height;

    //次回の重なりチェックのため、値保持
    var data = {x: top, y: (height + top)};
    $scope.Column[lineNum].push(data);

    //左からの位置
    planObj.style.left = String((lineNum * ($scope.rowWidth + 15)) + 5) + 'px';
    planObj.style.position = 'relative';
  };

  $scope.getLineNum = function(x, y) {

    //0列目からチェック
    for (var i = 0; i <= $scope.maxLineNum; i++) {
      if ($scope.checkColumn(i, x, y) == false) {
        return i; //重なりの無い列を返却
      }
    }

    $scope.maxLineNum++; //新しい列
    $scope.Column[$scope.maxLineNum] = [];
    return $scope.maxLineNum;
  };

  $scope.checkColumn = function(checkColumn, x, y) {

    //指定列の重なりチェック
    for (var i = 0; i < $scope.Column[checkColumn].length; i++) {
      if ($scope.checkOverlap($scope.Column[checkColumn][i].
          x, $scope.Column[checkColumn][i].y, x, y) == true) {
        return true;
      }
    }
    return false; //重なりなし
  };

  $scope.checkOverlap = function(x1, y1, x2, y2) {

    //線分1と線分2の重なりチェック
    if (x1 >= x2 && x1 >= y2 &&
        y1 >= x2 && y1 >= x2) {
      return false;
    }
    if (x2 >= x1 && x2 >= y1 &&
        y2 >= x1 && y2 >= y1) {
      return false;
    }
    return true; //重なりあり
  };

}]);

// 週表示時間枠
NetCommonsApp.controller('ReservationsVerticalTimeframe', ['$scope', function($scope) {
  $scope.init = function(data) {
    $scope.data = data;
    angular.forEach($scope.data.timeframes, function(timeframe, key) {
      timeframe.style = {
        backgroundColor: timeframe.color
      };
      var start = timeframe.start.split(':');
      var end = timeframe.end.split(':');

      var startHour = parseInt(start[0]);
      var startMin = parseInt(start[1]);

      var endHour = parseInt(end[0]);
      var endMin = parseInt(end[1]);

      if (endHour < startHour) {
        endHour = 24;
      }
      //高さ
      var height = endHour - startHour;
      height = (height + ((endMin - startMin) / 60)) * $scope.rowHeight;

      //開始位置
      var top = (startHour + (startMin / 60)) * $scope.rowHeight;

      //位置決定
      timeframe.style.top = top - $scope.prevMargin;
      timeframe.style.height = height;
      // planObj.style.height = String(height) + 'px';
      // planObj.style.top = String(top - $scope.prevMargin) + 'px'; //(調整)

    });

  };
}]);

// 日タイムラインでの時間枠表示
NetCommonsApp.controller('ReservationsHorizontalTimeframe', ['$scope', function($scope) {
  $scope.init = function(data) {
    $scope.data = data;
    angular.forEach($scope.data.timeframes, function(timeframe, key) {
      timeframe.style = {
        backgroundColor: timeframe.color
      };
      var start = timeframe.start.split(':');
      var end = timeframe.end.split(':');

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

      //位置決定
      timeframe.style.width = String(width) + 'px';
      timeframe.style.left = String(left) + 'px'; //(調整)
      timeframe.style.margin = 0; //(調整)
    });
  };
}]);

// 週間表示タイムライン
NetCommonsApp.controller('ReservationsWeeklyTimelinePlan', ['$scope', function($scope) {
  // $scope.reservationPlans = [];

  $scope.initialize = function(data) {
    // 曜日毎に繰り返し呼び出されることは想定されてない
    // $scope.reservationPlans = data.reservationPlans;
    //console.log($scope.reservationPlans);
    //位置情報を設定
    for (var i = 0; i < data.reservationPlans.length;
         i++) {
      // $scope.setTimelinePos(i, $scope.reservationPlans[i].
      //     fromTime, $scope.reservationPlans[i].toTime);
      $scope.setTimelinePos(data.reservationPlans[i].element_id, data.reservationPlans[i].
          fromTime, data.reservationPlans[i].toTime);
    }
  };

  $scope.setTimelinePos = function(elementId, fromTime, toTime) {
    console.log(toTime);
    if (toTime == '00:00') {
      // 終端の00:00は24:00のこと
      toTime = '24:00';
    }
    // var planObj = document.getElementById('plan' + String(id));
    var planObj = document.getElementById(elementId);

    var start = fromTime.split(':');
    var end = toTime.split(':');

    var startHour = parseInt(start[0]);
    var startMin = parseInt(start[1]);

    var endHour = parseInt(end[0]);
    var endMin = parseInt(end[1]);

    if (endHour < startHour) {
      endHour = 24;
    }
    //高さ
    var height = endHour - startHour;
    height = (height + ((endMin - startMin) / 60)) * $scope.rowHeight;

    //開始位置
    var top = (startHour + (startMin / 60)) * $scope.rowHeight;

    //タイムライン重ならない列数を取得
    var lineNum = $scope.getLineNum(top, (height + top));

    //位置決定
    planObj.style.height = String(height) + 'px';
    planObj.style.top = String(top - $scope.prevMargin) + 'px'; //(調整)

    //前回の位置が蓄積されてくる※位置調整のため
    $scope.prevMargin = $scope.prevMargin + height;

    //次回の重なりチェックのため、値保持
    var data = {x: top, y: (height + top)};
    $scope.Column[lineNum].push(data);

    //左からの位置
    planObj.style.left = String((lineNum * ($scope.rowWidth + 15)) + 5) + 'px';
    planObj.style.position = 'relative';
  };

  $scope.getLineNum = function(x, y) {
    // weeklyは1列表示なので。
    return 0;

    //0列目からチェック
    //for (var i = 0; i <= $scope.maxLineNum; i++) {
    //  if ($scope.checkColumn(i, x, y) == false) {
    //    return i; //重なりの無い列を返却
    //  }
    //}

    $scope.maxLineNum++; //新しい列
    $scope.Column[$scope.maxLineNum] = [];
    return $scope.maxLineNum;
  };

  $scope.checkColumn = function(checkColumn, x, y) {

    //指定列の重なりチェック
    for (var i = 0; i < $scope.Column[checkColumn].length; i++) {
      if ($scope.checkOverlap($scope.Column[checkColumn][i].
              x, $scope.Column[checkColumn][i].y, x, y) == true) {
        return true;
      }
    }
    return false; //重なりなし
  };

  $scope.checkOverlap = function(x1, y1, x2, y2) {

    //線分1と線分2の重なりチェック
    if (x1 >= x2 && x1 >= y2 &&
        y1 >= x2 && y1 >= x2) {
      return false;
    }
    if (x2 >= x1 && x2 >= y1 &&
        y2 >= x1 && y2 >= y1) {
      return false;
    }
    return true; //重なりあり
  };

}]);

//リサイズ 日跨ぎライン対応
NetCommonsApp.directive('resize', ['$window', function($window) {
  return function(scope, element) {
    var w = angular.element($window);
    w.bind('resize', function() {
      scope.$apply();
    });
  };
}]);

NetCommonsApp.controller('ReservationsMonthlyLinePlan', ['$scope', function($scope) {
  $scope.reservationPlans = [];

  $scope.initialize = function(data) {

    $scope.reservationLinePlans = data.reservationLinePlans;

    //行(日)のtop
    var line1 = $('.reservation-monthly-line-1');
    var line1Top = line1[0].getBoundingClientRect().top;
    var line1Left = line1[0].getBoundingClientRect().left;

    //console.log('line1Top! %d Left %d', line1Top, line1Left);

    //行（土）のtop
    var line7 = $('.reservation-monthly-line-7');
    var line7Top = line7[0].getBoundingClientRect().top;
    var line7Left = line7[0].getBoundingClientRect().left;

    //console.log('line7Top! %d Left %d', line7Top, line7Left);

    //1日cellの横幅基準
    aDayWidth = (line7Left - line1Left) / 6;
    $scope.aDayWidth = aDayWidth;
    //console.log('aDayWidth %d', $scope.aDayWidth);

    //初期化
    $scope.week = [];

    //$scope.week.prevTop = 0; //前回のtop位置（divタグの高さ調整用）
    $scope.sameDiv = 0; //test

    for (var i = 0; i < $scope.reservationLinePlans.length; i++) {  //第n週ループ
      $scope.week[i] = [];
      $scope.week[i].maxLineNum = 0;
      $scope.week[i].prevMargin = 0; //蓄積されているマージン
      $scope.week[i].Column = []; //保持するデータ（重なりチェック）
      $scope.week[i].Column[0] = [];
      $scope.week[i].divTopTotal = []; //調整用高さ（合計）

      for (var celCnt = 0; celCnt < 7; celCnt++) {
        $scope.week[i].divTopTotal[celCnt] = 0;
      }

      $scope.week[i].celCnt = [];
      for (var celCnt = 0; celCnt < 7; celCnt++) { // cell数カウント初期化
        $scope.week[i].celCnt[celCnt] = 0;
      }

    }

    //LINE高さと横幅の調整
    //var beforeHeight = 0;// 一つ前の高さ
    var beforeFromCell = -1; //一つ前の開始セル

    for (var i = 0; i < $scope.reservationLinePlans.length; i++) {  //第n週ループ
      beforeFromCell = -1;
      for (var j = 0; j < $scope.reservationLinePlans[i].length; j++) {
        $scope.setLinePos(
            i, $scope.reservationLinePlans[i][j].id,
            $scope.reservationLinePlans[i][j].fromCell,
            $scope.reservationLinePlans[i][j].toCell, beforeFromCell);
        beforeFromCell = $scope.reservationLinePlans[i][j].fromCell;
      }
    }

    //console.log('$scope.reservationLinePlans.length %d', $scope.reservationLinePlans.length);

    //縦位置の調整
    for (var i = 0; i < $scope.reservationLinePlans.length; i++) {  //第n週ループ
      for (var celCnt = 0; celCnt < 7; celCnt++) { // 各セルに高さ設定
        var divObj = document.getElementById('divline' + String(i) + '_' + String(celCnt));
        //console.log('divTopTotal week %d cell %d divTopTotal %d',
        // i, celCnt, $scope.week[i].divTopTotal[celCnt]);
        //console.log('celCnt week %d cell %d celCnt %d', i, celCnt, $scope.week[i].celCnt[celCnt]);

        divObj.style.height =
            String(($scope.week[i].celCnt[celCnt]) * 25 - $scope.week[i].divTopTotal[celCnt]) +
            'px';
      }
    }
  };

  $scope.setLinePos = function(week, id, fromCell, toCell, beforeFromCell) {
    //console.log('LINE.Plan!setLinePos!.id[%d] fromCell [%d] toCell[%d] beforeHeight[%d]
    // beforeFromCell[%d].', id, fromCell, toCell, beforeHeight, beforeFromCell);
    var planObj = document.getElementById('planline' + String(id) + '_' + String(week));

    //幅設定
    planObj.style.width = String((toCell - fromCell + 1) * $scope.aDayWidth) + 'px';

    //重ならない行数を取得
    var lineNum = $scope.getLineNum(week, fromCell, toCell);

    //Top設定
    var top = 0;
    if (fromCell == beforeFromCell) { // 開始divが同じ場合
      //console.log('SameBefore!! id[%d] fromCell[%d] prevTop[%d]',id , fromCell, $scope.prevTop);
      //top = $scope.prevTop + 5; //前回のtopから5pxだけずらす（divタグの高さ分考慮する）
      $scope.sameDiv++; //test
      top = (25 * lineNum) - (20 * $scope.sameDiv); //

      //console.log('divTopTotal!! week[%d] Cell[%d] divTopTotal[%d]',
      //week , fromCell, $scope.week[week].divTopTotal[fromCell]);
      $scope.week[week].divTopTotal[fromCell] =
          $scope.week[week].divTopTotal[fromCell] + 20; //div高さの差分を累積
    } else { //開始divが異なる
      $scope.sameDiv = 0; //test
      top = (25 * lineNum);
      $scope.week[week].divTopTotal[fromCell] =
          $scope.week[week].divTopTotal[fromCell] + 20; //div高さの差分を累積
    }

    //console.log('top %d', top);

    planObj.style.top = (top) + 'px'; // Top設定
    //planObj.style.position = 'relative';
    //$scope.prevTop = top; // 前回の値を保持(開始divのあるセルはずらす幅が異なるため)

    //fromからToまでlineNumを入れておく
    var celCnt = fromCell;
    for (; celCnt <= toCell; celCnt++) {
      if ($scope.week[week].celCnt[celCnt] <= lineNum) {  // lineNumが大きいとき
        $scope.week[week].celCnt[celCnt] = (lineNum + 1);  //0行と1行の識別のため＋１
      }
    }

    //次回の重なりチェックのため、値保持
    var data = {a: fromCell, b: toCell};
    $scope.week[week].Column[lineNum].push(data);
    return; //planObj.getBoundingClientRect().top;
  };

  $scope.getLineNum = function(week, a, b) {
    //console.log('Monthly.Plan!getLineNum!week[%d] a[%d] b[%d]', week, a, b);

    //0行目からチェック
    for (var i = 0; i <= $scope.week[week].maxLineNum; i++) {
      if ($scope.checkColumn(week, i, a, b) == false) {
        return i; //重なりの無い列を返却
      }
    }

    $scope.week[week].maxLineNum++; //新しい列
    $scope.week[week].Column[$scope.week[week].maxLineNum] = [];
    return $scope.week[week].maxLineNum;
  };

  $scope.checkColumn = function(week, checkColumn, a, b) {
    //console.log('Monthly.Plan!checkColumn!..');

    //指定列の重なりチェック
    for (var i = 0; i < $scope.week[week].Column[checkColumn].length; i++) {
      if ($scope.checkOverlap($scope.week[week].Column[checkColumn][i].
          a, $scope.week[week].Column[checkColumn][i].b, a, b) == true) {
        //console.log('OVER!!!!!! checkColumn %d', i);
        return true;
      }
    }
    return false; //重なりなし
  };

  $scope.checkOverlap = function(a1, b1, a2, b2) {
    //console.log('Monthly.Plan!checkOverlap!..a1[%d] b1[%d] a2[%d] b2[%d]');

    //線分1と線分2の重なりチェック
    if (a1 > a2 && a1 > b2 &&
        b1 > a2 && b1 > a2) {
      return false;
    }
    if (a2 > a1 && a2 > b1 &&
        b2 > a1 && b2 > b1) {
      return false;
    }
    return true; //重なりあり
  };

}]);

NetCommonsApp.controller('ReservationDetailEditWysiwyg',
    ['$scope', 'NetCommonsWysiwyg', function($scope, NetCommonsWysiwyg) {
      /**
       * tinymce
       *
       * @type {object}
       */
      $scope.tinymce = NetCommonsWysiwyg.new();
    }]
);
NetCommonsApp.controller('Reservations.selectLocation',
    ['$scope', '$location', 'NetCommonsModal', '$http', 'NC3_URL', 'filterFilter', '$window',
      function($scope, $location, NetCommonsModal, $http, NC3_URL, filterFilter, $window) {
        $scope.initialize = function(data) {
          // console.log(data);
          $scope.frameId = data.frameId;
          $scope.data = angular.fromJson(data);
          $scope.selectedLocation = data.selectedLocation;
          // console.log($scope.selectedLocation);
          // $scope.locationOptions = $scope.data.locations;
        };

        $scope.changeLocation = function(displayStyle) {
          // if (displayStyle === undefined) {
          //   displayStyle = 'largemonthly';
          // }
          $scope.selectLocation =
              filterFilter($scope.data.locations,
                  {ReservationLocation: {key: $scope.selectedLocation}})[0];
          //http://127.0.0.1:9090/reservations/reservations/index?year=2017&month=02&day=26&style=largemonthly&frame_id=42
          var url = NC3_URL + '/reservations/reservations/index' +
                        '?style=' + displayStyle + '&frame_id=' + $scope.frameId;
          url = url + '&location_key=' + $scope.selectLocation.ReservationLocation.key;
          //console.log(url);
          // $location.path(url);
          // $location.html5Mode(true);
          // console.log($location.search());
          $window.location.href = url;

          // console.log($scope.selectLocation);
        };
      }
    ]);

NetCommonsApp.controller('ReservationsDetailEdit',
    ['$scope', '$location', 'NetCommonsModal', '$http', 'NC3_URL', 'filterFilter',
      function($scope, $location, NetCommonsModal, $http, NC3_URL, filterFilter) {
       $scope.repeatArray = [];  //key=Frame.id、value=T/F of checkbox
       //key=Frame.id,value=index number
       //of option elements
       $scope.exposeRoomArray = [];
       //key=Frame.id,value=T/F of checkbox
       $scope.selectRepeatPeriodArray = [];
       $scope.targetYear = '2016';
       $scope.startDate = [];
       $scope.startDatetime = [];
       $scope.useTime = [];
       $scope.monthlyDayOfTheWeek = [];
       $scope.monthlyDate = [];
       $scope.yearlyDayOfTheWeek = [];
       $scope.selectRepeatEndType = [];
       $scope.useNoticeMail = [];

       $scope.detailStartDate;
       $scope.detailStartDatetime;
       $scope.detailEndDate;
       $scope.detailEndDatetime;

       $scope.rruleUntil;

       // $scope.debugShow = function() {
       //   console.log($scope.data.ReservationActionPlan.plan_room_id);
       // }

       $scope.initialize = function(data) {
         // console.log(data);
         $scope.data = angular.fromJson(data);
         // console.log($scope.data);
         // console.log($scope.data);
         $scope.locationOptions = $scope.data.locations;
         // console.log($scope.locationOptions);

         $scope.ReservationActionPlan = {
           location_key: null
         };

         //送信ボタンセットアップ
         $scope.buttons = {
           draft: false,
           disapproved: false,
           approvalWaiting: false,
           published: false
         };
         $scope.setLocationKey($scope.data.ReservationActionPlan.location_key);


       };

       // $scope.initRoomOption = function(options, defaultRoom ) {
       //    $scope.roomOptions = options;
       //    console.log($scope.roomOptions);
       //    $scope.selectRoom = '5';
       //    // $scope.selectRoom = {5:options[defaultRoom]};
       //    // $scope.selectRoom = filterFilter($scope.roomOptions, {})
       //   console.log($scope.selectRoom);
       //
       // };
       // $scope.changeRoomOption = function() {
       //   console.log($scope.selectRoom);
       // }

       $scope.setLocationKey = function(locationKey) {
          $scope.selectLocation =
              filterFilter($scope.data.locations,
                  {ReservationLocation: {key: locationKey}})[0];
          $scope.setupButtons();

       };

       // デフォルトの「公開対象セット」
       $scope.initReservableRooms = function(rooms, selectedRoom) {
         $scope.roomList = rooms;
         $scope.selectedRoom = selectedRoom;
       };

        $scope.setupButtons = function() {
          // console.log($scope.selectLocation);
          //    承認不要のケースもあるな
          if (! $scope.selectLocation) {
            $scope.buttons = {
              draft: false,
              disapproved: false,
              approvalWaiting: false,
              published: false
            };
          } else if ($scope.selectLocation.ReservationLocation.use_workflow) {
            if ($scope.selectLocation.approvalUserIds.hasOwnProperty(Number($scope.data.userId))) {
              // 承認ユーザ
              // 要承認　承認権限ありなら　一時保存と公開　ただしステータスが公開待ち(2)の時だけ一時保存→差し戻し
              if ($scope.data.event.hasOwnProperty('ReservationEvent') &&
                  $scope.data.event.ReservationEvent.status == 2) {
                $scope.buttons = {
                  draft: false,
                  disapproved: true,
                  approvalWaiting: false,
                  published: true
                };
              } else {
                $scope.buttons = {
                  draft: true,
                  disapproved: false,
                  approvalWaiting: false,
                  published: true
                };
              }
            } else {
              // 要承認　承認権限なしなら　一時保存と公開申請
              $scope.buttons = {
                draft: true,
                disapproved: false,
                approvalWaiting: true,
                published: false
              };
            }
          } else {
            // 承認不要なら一時保存と公開
            $scope.buttons = {
              draft: true,
              disapproved: false,
              approvalWaiting: false,
              published: true
            };
          }
        };

       // 施設カテゴリ選択
       $scope.locationOptions = [];
       $scope.locationCategory = 'all';
       $scope.selectLocationCategory = function() {
         // all　絞り込み解除
         // ''  -> nullだけに絞り込み
         if ($scope.locationCategory == 'all') {
           $scope.locationOptions = $scope.data.locations;
         } else if ($scope.locationCategory == '') {
           $scope.locationOptions = filterFilter($scope.data.locations, {Category: {id: null}});
         } else {
           $scope.locationOptions = filterFilter(
                $scope.data.locations, {Category: {id: $scope.locationCategory}}
           );
         }
       };
       $scope.changeLocation = function() {
         $scope.setupButtons();

         if (! $scope.selectLocation) {
           $scope.selectedRoom = [];
           return;
         }
         var url = NC3_URL + '/reservations/reservations/fetch_rooms_to_publish_reservation.json';
         let locationKey = $scope.selectLocation.ReservationLocation.key;
         $('#ReservationActionPlanPlanRoomId').prop('disabled', true);
         $('#ReservationActionPlanPlanRoomId').addClass('reservation-loading-dropdown');
         $http.get(url,
             {params: {location_key: locationKey}})
             .then(function(response) {
               var data = response.data;
               // 取得したルーム一覧に現在選択中のルームがなければ1番目のルーム（無指定のはず）にする
               if (!data.rooms.some(
                   function(room) {
                     return room.roomId === $scope.selectedRoom.roomId;
                   }
                   // room => room.roomId === $scope.selectedRoom.roomId
               )) {
                 $scope.selectedRoom = data.rooms[0];
               }
               // ルームドロップダウン変更
               $scope.roomList = data.rooms;
               $('#ReservationActionPlanPlanRoomId').removeClass('reservation-loading-dropdown');
               $('#ReservationActionPlanPlanRoomId').prop('disabled', false);
             }, function(response) {
             });
       };

       $scope.changeEditRrule = function(frameId, firstSibEditLink) {
         var nums = ['0', '1', '2'];
         for (var num in nums) {
           var checked = $('#ReservationActionPlanEditRrule' + num).prop('checked');
           if (checked) {
             if (num === '0') { //0
               //console.log('num[' + num + ']が選択されたので、予定の繰り返しを非表示にします。');
               //$('div.form-group[data-reservation-name=inputRruleInfo]').css('display', 'none');
             } else {  //1, 2
               //console.log('num[' + num + ']が選択されたので、予定の繰り返しを表示します。');
               //$('div.form-group[data-reservation-name=inputRruleInfo]').css('display', 'block');

               if (num === '2') {
                 if (firstSibEditLink !== '') {
                   angular.element('#ReservationActionPlanEditForm input').prop('disabled', true);
                   angular.element('#ReservationActionPlanEditForm select').prop('disabled', true);
                   window.location = firstSibEditLink;
                 } else {
                   //console.log('全ての繰り返しを選択したのに、繰返しの先頭eventへのeditLinkがないのはおかしい');
                 }
               }
             }
             break;
           }
         }
       };
       $scope.getUseTimeFlag = function() {
          var useTimeFlag;
          angular.forEach($scope.useTime, function(value, key) {
            useTimeFlag = value;
          }, useTimeFlag);
          return useTimeFlag;
       };

       $scope.changeDetailStartDate = function(targetId) {
         //
         // 期間指定フラグONのときは日の設定しない
         var useTimeFlag = $scope.getUseTimeFlag();
         if (useTimeFlag == true) {
           return;
         }

         if ($scope.detailStartDate != '') {
           $('#' + targetId).val($scope.detailStartDate);

           //簡易では、開始日と終了日が統一され「終日」
           //(実質開始のみ）１つとなった。
           //そのため、開始日の値を、終了日のDOMの値に代入する
           //
           var endTargetId = targetId.replace(/Start/g, 'End');
           $('#' + endTargetId).val($scope.detailStartDate);

         }
       };

       $scope.changeDetailStartDatetime = function(targetId) {
         // 期間指定フラグOFFのときは時間の設定しない
         var useTimeFlag = $scope.getUseTimeFlag();
         if (useTimeFlag == false) {
           return;
         }
         //
         if ($scope.detailStartDatetime != '') {
           $('#' + targetId).val($scope.detailStartDatetime);
           //
         }
       };

       $scope.changeDetailEndDate = function(targetId) {
         // 期間指定フラグONのときは日の設定しない
         var useTimeFlag = $scope.getUseTimeFlag();
         if (useTimeFlag == true) {
           return;
         }

         //
         if ($scope.detailEndDate != '') {
           $('#' + targetId).val($scope.detailEndDate);
           //
         }
       };

       $scope.changeDetailEndDatetime = function(targetId) {
         // 期間指定フラグOFFのときは時間の設定しない
         var useTimeFlag = $scope.getUseTimeFlag();
         if (useTimeFlag == false) {
           return;
         }
         //
         if ($scope.detailEndDatetime != '') {
           $('#' + targetId).val($scope.detailEndDatetime);
           //
         }
       };

       $scope.changeYearMonth = function(prototypeUrl) {
         var elms = $scope.targetYear.split('-');
         var url = prototypeUrl.replace('YYYY', elms[0]);
         url = url.replace('MM', elms[1]);
         window.location = url;
       };
       $scope.changeYearMonthDay = function(prototypeUrl) {
         //console.log('DEBUGGING...' + $scope.targetYear);

         var elms = $scope.targetYear.split('-');
         var url = prototypeUrl.replace('YYYY', elms[0]);
         url = url.replace('MM', elms[1]);
         url = url.replace('DD', elms[2]);
         window.location = url;
       };

       $scope.toggleRepeatArea = function(frameId) {
         var elm = $('.reservation-repeat-a-plan-detail_' + frameId);
         if ($scope.repeatArray[frameId]) {
           elm.show();
         } else {
           elm.hide();
         }
       };

       $scope.changeRoom = function(myself, frameId) {
         var elm = $('.reservation-plan-share_' + frameId);
         if ($scope.exposeRoomArray[frameId].toString() === myself.toString()) {
           //console.log('グループ共有が有効になる');
           elm.show();
         } else {
           //console.log('グループ共有が無効になる');
           elm.hide();
         }
       };

       $scope.setInitRepeatPeriod = function(frameId, idx) {
         //これで、画面をリフレッシュ
         $scope.selectRepeatPeriodArray[frameId] = idx;
       };

       $scope.changePeriodType = function(frameId) {
         var elmDaily = $('.reservation-daily-info_' + frameId);
         var elmWeekly = $('.reservation-weekly-info_' + frameId);
         var elmMonthly = $('.reservation-monthly-info_' + frameId);
         var elmYearly = $('.reservation-yearly-info_' + frameId);

         switch ($scope.selectRepeatPeriodArray[frameId]) {
           case ReservationJS.variables.REPEAT_FREQ_DAILY:
             elmDaily.removeClass('hidden').addClass('show');
             elmWeekly.removeClass('show').addClass('hidden');
             elmMonthly.removeClass('show').addClass('hidden');
             elmYearly.removeClass('show').addClass('hidden');
             break;
           case ReservationJS.variables.REPEAT_FREQ_WEEKLY:
             elmDaily.removeClass('show').addClass('hidden');
             elmWeekly.removeClass('hidden').addClass('show');
             elmMonthly.removeClass('show').addClass('hidden');
             elmYearly.removeClass('show').addClass('hidden');
             break;
           case ReservationJS.variables.REPEAT_FREQ_MONTHLY:
             elmDaily.removeClass('show').addClass('hidden');
             elmWeekly.removeClass('show').addClass('hidden');
             elmMonthly.removeClass('hidden').addClass('show');
             elmYearly.removeClass('show').addClass('hidden');
             break;
           case ReservationJS.variables.REPEAT_FREQ_YEARLY:
             elmDaily.removeClass('show').addClass('hidden');
             elmWeekly.removeClass('show').addClass('hidden');
             elmMonthly.removeClass('show').addClass('hidden');
             elmYearly.removeClass('hidden').addClass('show');
             break;
         }
       };

       $scope.initDescription = function(descriptionVal) {
         $scope.reservationActionPlan = {};
         $scope.reservationActionPlan.description = descriptionVal;
       };

       $scope.toggleEnableTime = function(frameId) {
         if ($scope.useTime[frameId]) {
           //時刻なし(YYYY-MM-DD) -> 時刻あり(YYYY-MM-DD HH:mm)
           if ($scope.detailStartDatetime && $scope.detailStartDatetime.indexOf(':') >= 0) {
             //$scopeの方はYYYY-MM-DD HH:mm
             var domVal = $('#ReservationActionPlanDetailStartDatetime').val();
             if (!domVal || domVal.indexOf(':') === (-1)) {
               //DOMの方は YYYY-MM-DD HH:mm「ではない」.未定義かYYYY-MM-DD
               //なので、$scopeの値を、DOMに反映する。
               $('#ReservationActionPlanDetailStartDatetime').val($scope.detailStartDatetime);
             }
           }
           if ($scope.detailEndDatetime && $scope.detailEndDatetime.indexOf(':') >= 0) {
             //$scopeの方はYYYY-MM-DD HH:mm
             var domVal = $('#ReservationActionPlanDetailEndDatetime').val();
             if (!domVal || domVal.indexOf(':') === (-1)) {
               //DOMの方は YYYY-MM-DD HH:mm「ではない」.未定義かYYYY-MM-DD
               //なので、$scopeの値を、DOMに反映する。
               $('#ReservationActionPlanDetailEndDatetime').val($scope.detailEndDatetime);
             }
           }
         } else {
           //時刻あり(YYYY-MM-DD HH:mm) -> 時刻なし(YYYY-MM-DD)
           if ($scope.detailStartDate && $scope.detailStartDate.indexOf(':') === (-1)) {
             //$scopeの方はYYYY-MM-DD
             var domVal = $('#ReservationActionPlanDetailStartDatetime').val();
             if (!domVal || domVal.indexOf(':') > 0) {
               //DOMの方は YYYY-MM-DD「ではない」.未定義かYYYY-MM-DD HH:mm
               //なので、$scopeの値を、DOMに反映する。
               $('#ReservationActionPlanDetailStartDatetime').val($scope.detailStartDate);
             }
           }
           // 終日設定にされているわけだから終わりの日は最初の日と同じでないと
           $('#ReservationActionPlanDetailEndDatetime').val($scope.detailStartDate);
           /*
           if ($scope.detailEndDate && $scope.detailEndDate.indexOf(':') === (-1)) {
             //$scopeの方はYYYY-MM-DD
             var domVal = $('#ReservationActionPlanDetailEndDatetime').val();
             if (!domVal || domVal.indexOf(':') > 0) {
               //DOMの方は YYYY-MM-DD「ではない」.未定義かYYYY-MM-DD HH:mm
               //なので、$scopeの値を、DOMに反映する。
               $('#ReservationActionPlanDetailEndDatetime').val($scope.detailEndDate);
             }
           }*/

           //checkboxのDOMの値も同期させておく。
           /////$('#ReservationActionPlanEnableTime').prop('checked', true);

         }
       };

       $scope.changeMonthlyDayOfTheWeek = function(frameId) {
         if ($scope.monthlyDayOfTheWeek[frameId] !== '') {
           $scope.monthlyDate[frameId] = '';
         }
       };

       $scope.changeMonthlyDate = function(frameId) {
         if ($scope.monthlyDate[frameId] !== '') {
           $scope.monthlyDayOfTheWeek[frameId] = '';
         }
       };

       $scope.changeYearlyDayOfTheWeek = function(frameId) {
         //yearlyの方は、monthlyと違いDateの方がない.つまり
         //DayOfTheWeekとDateをトグルする必要がないので、なにもしない。
       };

       $scope.setInitRepeatEndType = function(frameId, idx) {
         $scope.selectRepeatEndType[frameId] = idx;  //画面をリフレッシュ
       };

       $scope.changeRepeatEndType = function(frameId) {
         var elmCount = $('.reservation-repeat-end-count-info_' + frameId);
         var elmEndDate = $('.reservation-repeat-end-enddate-info_' + frameId);

         switch ($scope.selectRepeatEndType[frameId]) {
           case ReservationJS.variables.RRULE_TERM_COUNT:
             elmCount.removeClass('hidden').addClass('show');
             elmEndDate.removeClass('show').addClass('hidden');
             break;
           case ReservationJS.variables.RRULE_TERM_UNTIL:
             elmCount.removeClass('show').addClass('hidden');
             elmEndDate.removeClass('hidden').addClass('show');
             break;
         }
       };

       $scope.selectCancel = function() {
       };
       $scope.doSelect = function() {
       };

       $scope.showRepeatTypeSelect = function(frameId, action, $event, eventId) {
         //クリックのデフォルト動作(この場合form のsubmit)を抑止しておく。
         $event.preventDefault();
         if (action === 'delete') {
           //３選択をエコーバックさせるために、modalを使う。modalの中ではCRUDはさせない.
           var modalInstance = NetCommonsModal.show($scope, 'ReservationsDetailEdit',
           NC3_URL + '/reservations/reservation_plans/select/event:' + eventId +
           '?frame_id=' + frameId);
           //コールバックセット
           modalInstance.result.then(
           function(result) {
             $scope.result = result;
             $scope.event = 'close';
           },
           function(resutl) {
             $scope.result = result;
             $scope.event = 'dismiss';
           }
           );
         }
       };

       $scope.showRepeatConfirmEx = function(frameId,
       action, $event, eventKey, firstSibEventId, originEventId, isRecurrence) {

         var url = NC3_URL + '/reservations/reservation_plans/delete';
         url = url + '/' + eventKey;
         url = url + '?frame_id=' + frameId;
         if (action != '') {
           url = url + '&action=' + action;
         }
         if (firstSibEventId > 0) {
           url = url + '&first_sib_event_id=' + firstSibEventId;
         }
         if (originEventId > 0) {
           url = url + '&origin_event_id=' + originEventId;
         }
         if (isRecurrence == 1) {
           url = url + '&is_recurrence=1';
         } else {
           url = url + '&is_recurrence=0';
         }

         //NetCommonsModal.show()の実体は
         // $uibModal.open()です。
         //show()の戻り値は、$udiModal.open()の戻り値です。
         //
         var modalInstance = NetCommonsModal.show(
         $scope,
         'Reservations.showRepeatConfirmExModal',
         url
         );

         //callbackの登録をします。
         modalInstance.result.then(
         function(result) {
           //決定ボタンをクリック
           //クリックのデフォルト動作(この場合form のsubmit)を抑止しておく。
           $event.preventDefault();
           return true;

         },
         function() {
           //背景部分クリックや
           //キャンセルボタンクリックをすると
           //失敗扱いで、ここにくる。
           //クリックのデフォルト動作(この場合form のsubmit)を抑止しておく。
           $event.preventDefault();
           return false;

         }
         );

         $event.preventDefault();
         return false;

       };

       $scope.setInitNoticeMailSetting = function(frameId, bVal) {
         $scope.useNoticeMail[frameId] = bVal;  //画面をリフレッシュ
       };

       $scope.toggleNoticeMailSetting = function(frameId) {
         if ($scope.useNoticeMail[frameId]) {
           //メール通知を使用する
           $('.reservation-mail-setting_' + frameId).show();
         } else {
           //メール通知を使用しない
           $('.reservation-mail-setting_' + frameId).hide();
         }
       };

     }]
);


/**
 * showRepeatConfirmEx Modal
 */
NetCommonsApp.controller('Reservations.showRepeatConfirmExModal',
    ['$scope', '$uibModalInstance', function($scope, $uibModalInstance) {
      /**
       * dialog cancel
       *
       * @return {void}
       */
      $scope.cancel = function() {
        //alert('キャンセルＡ');
        //削除POPUPでメニューのＸマークや、
        //「キャンセル」ボタンがクリックされた時は、
        //ここがcallされる。
        $uibModalInstance.dismiss('cancel');
      };
    }]
);

NetCommonsApp.controller('ReservationsDelete',
    ['$scope', '$uibModalInstance', function($scope, $uibModalInstance) {
    }]
);

NetCommonsApp.controller('ReservationModalCtrl', [
  '$scope', '$modalInstance', function($scope, $modalInstance) {
    $scope.ok = function() {
      $modalInstance.close();
    };
    $scope.cancel = function() {
      $modalInstance.dismiss();
    };
  }
]);


/**
 * ReservationFrameSettings Javascript
 *
 * @param {string} Controller name
 * @param {function($scope)} Controller
 */
NetCommonsApp.controller('ReservationFrameSettings', [
  '$scope', function($scope) {
    /**
     * variables
     *
     * @type {Object.<string>}
     */
    var variables = {
      RESERVATION_DISP_TYPE_CATEGORY_WEEKLY: '1',  //カテゴリー別 - 週表示
      RESERVATION_DISP_TYPE_CATEGORY_DAILY: '2',   //カテゴリー別 - 日表示
      RESERVATION_DISP_TYPE_LOCATION_MONTHLY: '3', //施設別 - 月表示
      RESERVATION_DISP_TYPE_LOCATION_WEEKLY: '4'  //施設別 - 週表示
    };

    $scope.initialize = function(data) {
      $scope.data = angular.fromJson(data);
      //$scope.data.frameId;
      //$scope.data.reservationFrameSetting
      //$scope.data.reservationFrameSettingSelectRoom
      //$scope.data.displayTypeOptions
      //が格納される。

      $scope.displayTypes = [];

      $scope.setIsShowElement();

      angular.forEach($scope.data.displayTypeOptions, function(val, key, obj) {
        $scope.displayTypes.push({
          label: val,
          value: key
        });
      });
    };

    $scope.displayChange = function() {
      $scope.setIsShowElement();
    };
    /**
    * 各種選択要素を出してよいかどうか
    */
    $scope.setIsShowElement = function() {
      var type = $scope.data.reservationFrameSetting.displayType;
      if (type == variables.RESERVATION_DISP_TYPE_LOCATION_MONTHLY) {
        $scope.isShowStartPos = false;
        $scope.isShowDisplayCount = false;
        $scope.isShowTimelineStart = true;
        $scope.isShowSelectLocation = true;
        $scope.isShowSelectCategory = false;
      } else if (type == variables.RESERVATION_DISP_TYPE_CATEGORY_WEEKLY) {
        $scope.isShowStartPos = false;
        $scope.isShowDisplayCount = false;
        $scope.isShowTimelineStart = true;
        $scope.isShowSelectLocation = false;
        $scope.isShowSelectCategory = true;
      } else if (type == variables.RESERVATION_DISP_TYPE_CATEGORY_DAILY) {
        $scope.isShowStartPos = false;
        $scope.isShowDisplayCount = false;
        $scope.isShowTimelineStart = true;
        $scope.isShowSelectLocation = false;
        $scope.isShowSelectCategory = true;
      } else if (type == variables.RESERVATION_DISP_TYPE_LOCATION_WEEKLY) {
        $scope.isShowStartPos = false;
        $scope.isShowDisplayCount = false;
        $scope.isShowTimelineStart = true;
        $scope.isShowSelectLocation = true;
        $scope.isShowSelectCategory = false;
      // } else if (type == variables.RESERVATION_DISP_TYPE_TSCHEDULE ||
      //     type == variables.RESERVATION_DISP_TYPE_MSCHEDULE) {
      //   $scope.isShowStartPos = true;
      //   $scope.isShowDisplayCount = true;
      //   $scope.isShowTimelineStart = false;
      } else {
        $scope.isShowStartPos = false;
        $scope.isShowDisplayCount = false;
        $scope.isShowTimelineStart = false;
      }
    };
  }
]);

NetCommonsApp.controller('ReservationFrameSettings.selectLocation', [
  '$scope', 'filterFilter', function($scope, filterFilter) {

    $scope.initialize = function(data) {
      $scope.data = angular.fromJson(data);
      // console.log($scope.data);
      $scope.locationOptions = $scope.data.locations;
      // console.log($scope.locationOptions);

      $scope.ReservationActionPlan = {
        location_key: null
      };
    };

    $scope.setLocationKey = function(locationKey) {
      $scope.selectLocation = filterFilter(
          $scope.data.locations, {ReservationLocation: {key: locationKey}})[0];
    };

    // 施設カテゴリ選択
    $scope.locationOptions = [];
    $scope.locationCategory = 'all';
    $scope.selectLocationCategory = function() {
      // all　絞り込み解除
      // ''  -> nullだけに絞り込み
      if ($scope.locationCategory == 'all') {
        $scope.locationOptions = $scope.data.locations;
      } else if ($scope.locationCategory == '') {
        $scope.locationOptions = filterFilter($scope.data.locations, {Category: {id: null}});
      } else {
        $scope.locationOptions = filterFilter(
            $scope.data.locations, {Category: {id: $scope.locationCategory}});
      }
    };
  }
]);

/**
 * angularJSに依存しないJavaScriptプログラム(NonAngularJS)
 */
//var ReservationJS = {};  //専用空間

$(function() {
  var expr = '.reservation-col-week, .reservation-easy-edit,';
  expr += ' .reservation-daily-disp, .reservation-detail-edit';
  $(expr).on('click', function(evt) {
    var url = $(evt.target).attr('data-url');
    // イベント発火要素が１階層もぐりこんでいる構造の場合があるので
    if (! url) {
      var p = $(evt.target).parent(expr);
      if (p) {
        url = $(p.get(0)).attr('data-url');
      }
    }
    if (! url) {
      return;
    }
    window.location = url;
  });
  $('.reservation-plan-list').on('click', function(evt) {
    var url = $(evt.target).attr('data-url');
    if (url == undefined) {
      url = $(evt.target).parents('td.reservation-plan-list').attr('data-url');
    }
    window.location = url;
  });
  $('.reservation-easy-edit-area').on('click', function(evt) {
    var url = $(evt.target).attr('data-url');
    if (url == undefined) {
      var expr = 'div.reservation-easy-edit-area';
      url = $(evt.target).parents(expr).attr('data-url');
    }
    window.location = url;
  });
  $('.reservation-plan-show').on('click', function(evt) {
    var url = $(evt.target).attr('data-url');
    if (url == undefined) {
      var expr = 'p.reservation-plan-show';
      url = $(evt.target).parents(expr).attr('data-url');
    }
    window.location = url;
  });
  $('.select-expose-target').on('change', function(evt) {
    //console.log("selectExposeTarget change");
    var myself = $('.select-expose-target').attr('data-myself');
    var frameId = $('.select-expose-target').attr('data-frame-id');
    var elm = $('.reservation-plan-share_' + frameId);
    if ($('.select-expose-target option:selected').val() == myself) {
      //console.log('グループ共有が有効になる');
      elm.show();
    } else {
      //console.log('グループ共有が無効になる');
      elm.hide();
    }
  });
});


/**
 * ReservationLocationOrders Javascript
 *
 * @param {string} Controller name
 * @param {function($scope)} Controller
 */
NetCommonsApp.controller('ReservationLocationOrders', ['$scope', function($scope) {

  /**
   * ReservationLocations
   *
   * @type {object}
   */
  $scope.reservationLocations = [];

  /**
   * initialize
   *
   * @return {void}
   */
  $scope.initialize = function(data) {
    $scope.reservationLocations = data.reservationLocations;
    $scope.reservationLocationsMap = data.reservationLocationsMap;
  };

  /**
   * move
   *
   * @return {void}
   */
  $scope.move = function(type, index) {
    var dest = (type === 'up') ? index - 1 : index + 1;
    if (angular.isUndefined($scope.reservationLocations[dest])) {
      return false;
    }

    var destQuestion = angular.copy($scope.reservationLocations[dest]);
    var targetQuestion = angular.copy($scope.reservationLocations[index]);
    $scope.reservationLocations[index] = destQuestion;
    $scope.reservationLocations[dest] = targetQuestion;
  };

  /**
   * Get index
   *
   * @return {void}
   */
  $scope.getIndex = function(key) {
    return $scope.reservationLocationsMap[key];
  };

}]);


/**
 * Tasks.Charge Javascript
 *
 * @param {string} Controller name
 * @param {function($scope, NetCommonsWysiwyg)} Controller
 */
NetCommonsApp.controller('ReservationLocationsApprovalUser',
    function($scope) {

      /**
       * tinymce
       *
       * @type {object}
       */
      $scope.target = false;

      /**
       * Initialize
       *
       * @param {object} TaskContents data
       * @return {void}
       */
      $scope.initialize = function(value) {
        $scope.target = value;
      };

      $scope.switchCharge = function($event) {
        $scope.target = $event.target.value;
      };

    });
