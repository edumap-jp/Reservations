<?php
/**
 * ReservationSupport Utiltiy
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

//App:uses('ReservationTime', 'Reservations.Utility');

/**
 * ReservationSupport Utiltiy
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Utility
 */
class ReservationSupport {

/**
 * getMixedToString
 *
 * 配列の最初の要素を取り出す。文字列であればそのまま返す。
 *
 * @param mixed $data data
 * @return mixed 成功時：$dateの最初要素を取り出す。空配列は''を返す。変数が文字列ならそのまま返す。失敗：falseを返す。
 */
	public static function getMixedToString($data) {
		if (is_string($data)) {
			return $data;
		}
		if (is_array($data)) {
			return Hash::get($data, '0');
			/*
				if (count($data) === 0 || !isset($data[0])) {
					return '';
				} else {
					return (string)$data[0];
				}
			*/
		}
		return false;
	}

/**
 * 繰返し可能かどうか
 *
 * @param array $rrule rrule配列
 * @param string $startDateTime 開始日付時刻文字列 YmdHis形式
 * @param float $timezoneOffset timezoneOffset (-12.0～+12.0)
 * @param bool &$isOverMaxRruleIndex isOverMaxRruleIndex
 * @return true or false
 */
	public static function isRepeatable($rrule, $startDateTime, $timezoneOffset,
		&$isOverMaxRruleIndex) {
		if (isset($rrule['UNTIL'])) {
			////$until = self::reservationDateFormat((substr($rrule['UNTIL'], 0, 8) . substr($rrule['UNTIL'], -6)), $timezoneOffset, 0, $reservationWeek); ////$reservationWeek reservation_week(Sun|Mon|....Sat、日|月|....|土 などの文字列。初期値は''
			//NC3では、rrule['UNTIL']はサーバ系(UTC)になっている。また、$startDateTimeもサーバ系(UTC)になっている。
			//なので、reservationDateFormatをつかってTZ考慮したreservationDateFormatはつかわなくてＯＫ。
			$until = substr($rrule['UNTIL'], 0, 8) . substr($rrule['UNTIL'], -6);

			//CakeLog::debug("DBG:
			//	In isRepeatable(). startDateTime[" . $startDateTime . "] until[" . $until . "]");
			if ($startDateTime >= $until) {
				//CakeLog::debug("DBG: isRepeatable() return FALSE!
				//	 startDateTime[" . $startDateTime . "] >= until[" . $until . "]");
				return false;
			}
		} else {
			$count = isset($rrule['COUNT']) ? intval($rrule['COUNT']) : 3;	//初期値は３回繰り返す
			//CakeLog::debug("DBG: In isRepeatable(COUNT case).
			//	 rrule[INDEX][" . $rrule['INDEX'] . "] count[" . $count . "]");
			if ($rrule['INDEX'] > $count) {
				//CakeLog::debug("DBG: isRepeatable(COUNT case) return FALSE!
				//	 rrule[INDEX][" . $rrule['INDEX'] . "] > count[" . $count . "]");
				return false;
			}
		}

		//CakeLog::debug("DBG: rrule[INDEX]=[" . $rrule['INDEX'] . "]");
		$maxIndexCnt = intval(ReservationsComponent::CALENDAR_RRULE_COUNT_MAX);
		if ($rrule['INDEX'] > ($maxIndexCnt)) {
			CakeLog::info("INDEX[" . $rrule['INDEX'] . "]が、最大回数[" . $maxIndexCnt . "]を" .
				"超過したので強制的に抜けます.");
			$isOverMaxRruleIndex = true;	//忘れずに、isOverをtrueにておくこと。

			return false;
		}

		return true;
	}

/**
 * タイムゾーンつきの日付をフォーマットする
 *
 * @param float $timezoneOffset timezoneoffset (-12.0 ～ +12.0) 初期値はnull
 * @param int $insertFlag insertFlag  1:登録 0:非登録(＝更新)  初期値は0
 * @param string $time 日付時刻文字列 (YmdHis形式)
 * @return string タイムゾーンを考慮した書式化された日付時刻文字列
 */
	public static function setDateFormatWithTimezoneoffset($timezoneOffset, $insertFlag, $time) {
		$timezoneMiniteOffset = 0;
		if (round($timezoneOffset) !== intval($timezoneOffset)) {
			$timezoneOffset = ($timezoneOffset > 0) ? floor($timezoneOffset) : ceil($timezoneOffset);
			$timezoneMiniteOffset = ($timezoneOffset > 0) ? 30 : -30;			// 0.5minute
		}
		if ($insertFlag) {
			$timezoneOffset = -1 * $timezoneOffset;
		}
		return date('YmdHis', mktime(intval(substr($time, 8, 2)) + $timezoneOffset,
			intval(substr($time, 10, 2)) + $timezoneMiniteOffset, intval(substr($time, 12, 2)),
				intval(substr($time, 4, 2)), intval(substr($time, 6, 2)), intval(substr($time, 0, 4))));
	}

/**
 * bydayデータ生成
 *
 * @param int $timestamp timestamp
 * @param int $week week
 * @param int $wdayNum wdayNum
 * @param string $userTz ユーザタイムゾーン(ex.Asia/Tokyo)
 * @return string $byday 生成されたbyday(このとき、サーバー系(UTC)にもどして返す。
 */
	public static function getByday($timestamp, $week, $wdayNum, $userTz) {
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//TZはユーザ系

		$date->setTimestamp($timestamp);
		$year = $date->format('Y');
		$month = $date->format('m');
		if ($week === -1) {
			//ここに飛び込むのは、BYDAYが'-1SA'といった値-1+曜日の書式のとき
			$lastDay = $date->format('t');	//$timestampの月の日数

			//施設予約上(=ユーザー系)の指定月の月末日の00:00:00のタイムスタンプ
			$date->setDate($year, $month, $lastDay);	//日付の再設定
			$date->setTime(0, 0, 0);	//時刻の再設定
			$timestamp = $date->getTimestamp();
			$wLastDay = $date->format('w');	//施設予約上の月末日の曜日のindex(0-6)

			//wdayNumはBYDAY(ex.3SA)の曜日(SA)のindex値(0-6,SAだと6)が入っている
			//($wdayNum <= $wLastDay)の場合は、月末日の曜日indexをそのままつかう。
			//($wdayNum > $wLastDay)の場合は、月末日の曜日indexに+7する
			//
			$wLastDay = ($wdayNum <= $wLastDay ? $wLastDay : 7 + $wLastDay);

			//下記は、施設予約上の月末日に一番近い(=その月の最後の）BYDAYの
			//指定曜日に該当する日付の00:00:00のタイムスタンプを求める。
			//
			$date->setDate($year, $month, $lastDay - $wLastDay + $wdayNum); //日付の再設定
			$date->setTime(0, 0, 0);	//時刻の再設定
			$timestamp = $date->getTimestamp();
		} else {
			$w1Day = $date->format('w');	//timestampの曜日のindex a を取り出す
			// aがBYDAYの指定曜日以前ならaに1週加算。
			$w1Day = ($w1Day <= $wdayNum ? 7 + $w1Day : $w1Day);
			//第nＸ曜日(3MOなら第３月曜日)の日を計算する。
			$day = $week * 7 + $wdayNum + 1;
			//$day-$w1Dayで実際の1日が実曜日スタートになるよう調整した日のタイムスタンプ b を計算。
			$date->setDate($year, $month, $day - $w1Day);	//日付の再設定
			$date->setTime(0, 0, 0);	//時刻の再設定
			$timestamp = $date->getTimestamp();
		}

		// b(第x週第y曜日の実際の日)を日付時刻文字列型に変換
		//このとき、ユーザー系TZからサーバー系TZに直す
		$date->setTimestamp($timestamp);
		$date->setTimezone(new DateTimeZone('UTC'));
		$byday = $date->format('YmdHis');
		return $byday;
	}

/**
 * __getInitailTerm
 *
 * 繰返し期限配列の初期値を返す
 *
 * @param string $ymd ymd(YYYY-MM-DD形式)
 * @return array 繰返し期限配列の初期値
 */
	private function __getInitailTerm($ymd) {
		$term = array(
			'REPEAT_COUNT' => 1,
			'REPEAT_UNTIL' => 0,
			'COUNT' => 3,
			'UNTIL' => $ymd, //YYYY-MM-DD
		);
		return $term;
	}

/**
 * __getInitialFreq
 *
 * 繰返し周期性配列の初期値を返す
 *
 * @param array $wdays array('SU', 'MO', ... , 'SA')
 * @param int $wdayIndex $ways配列内の曜日index(0-6)
 * @param mixed $month 月(1-12)
 * @return array 繰返し周期性配列の初期値
 */
	private function __getInitialFreq($wdays, $wdayIndex, $month) {
		$freq = array(
			'DAILY' => array(
				'INTERVAL' => 1,	//1日ごと
			),
			'WEEKLY' => array(
				'INTERVAL' => 1,	//1週ごと
				//指定日の曜日('SU'など)を返す
				'BYDAY' => array($wdays[$wdayIndex]), //FRなどを要素のもつ配列
			),
			'MONTHLY' => array(
				'INTERVAL' => 1,	//１月ごと
				'BYDAY' => array(),	//空配列
				'BYMONTHDAY' => array(),	//空配列
			),
			'YEARLY' => array(
				'INTERVAL' => 1,	//１年ごと
				'BYMONTH' => array(intval($month)), //12などを要素にもつ配列
				'BYDAY' => array(),	//空配列
			),
		);
		return $freq;
	}

/**
 * getInitialReservationActionPlanForView
 *
 * 表示用ReservationActionPlan初期データ生成
 *
 * @param string $year year
 * @param string $month month
 * @param string $day day
 * @param string $hour hour
 * @param string $minitue minitue
 * @param string $second second
 * @param bool $enableTime 時間指定が有効か
 * @param array $exposeRoomOptions 公開対象ルーム配列
 * @return array 生成された表示用ReservationActionPlan配列
 */
	public function getInitialReservationActionPlanForView($year, $month, $day,
		$hour, $minitue, $second, $enableTime, $exposeRoomOptions) {
		$userTz = (new NetCommonsTime())->getUserTimezone();

		// 時間指定のある場合かない場合かで
		if ($enableTime) {
			//"Y-m-d H:i:s"形式の指定日付時刻の日付時刻(from,to)を取得
			list($ymdOfLastHour, $fromYmdHiOfLastHour, $toYmdHiOfLastHour) =
				ReservationTime::getTheTime(sprintf('%04d-%02d-%02d %02d:%02d:%02d',
					$year, $month, $day, $hour, $minitue, $second));
		} else {
			//"Y-m-d H:i:s"形式の指定日付時刻からの直近１時間の日付時刻(from,to)を取得
			list($ymdOfLastHour, $fromYmdHiOfLastHour, $toYmdHiOfLastHour) =
				ReservationTime::getTheTimeInTheLastHour(sprintf('%04d-%02d-%02d %02d:%02d:%02d',
					$year, $month, $day, $hour, $minitue, $second));
		}
		$date = (new ReservationTime())->getDtObjWithTzDateTime($userTz,
			$year, $month, $day, $hour, $minitue, $second);
		$wdayIndex = intval($date->format('w'));	//0-6
		$wdays = explode('|', ReservationsComponent::CALENDAR_REPEAT_WDAY);

		$rooms = array_keys($exposeRoomOptions);
		if (in_array(Current::read('Room.id'), $rooms)) {
			//公開対象ルーム一覧にCurrentのRoom.idが存在する
			$planRoomId = Current::read('Room.id');
		} else {
			//公開対象ルーム一覧にCurrentのRoom.idが存在しない時は、親
			//のペアレント(パブリック、プライベート）の仮想room_idを
			//セットする。
			$planRoomId = Current::read('Room.parent_id');
		}

		$initialCapForView = array(
			'GroupsUser' => array(),	//共有なし
			'ReservationActionPlan' => array(
				'edit_rrule' => 0,
				'title' => '',
				'title_icon' => '',
				'enable_time' => $enableTime,
				//YYYY-MM-DD hh:mm
				'detail_start_datetime' =>
					$ymdOfLastHour . ' ' . substr($fromYmdHiOfLastHour, 11),
				//YYYY-MM-DD hh:mm
				'detail_end_datetime' =>
					substr($toYmdHiOfLastHour, 0, 10) . ' ' . substr($toYmdHiOfLastHour, 11),
				'plan_room_id' => $planRoomId,
				'timezone_offset' => (new NetCommonsTime())->getUserTimezone(),
				'is_detail' => 0,
				'location' => '',
				'contact' => '',
				'description' => '',
				'is_repeat' => 0,
				'repeat_freq' => 'DAILY',
				'FREQ' => $this->__getInitialFreq($wdays, $wdayIndex, $month),
				'TERM' => $this->__getInitailTerm($ymdOfLastHour),
				'enable_email' => 0,
				'email_send_timing' => 5,
			),
		);
		return $initialCapForView;
	}

/**
 * getReservationActionPlanForView
 *
 * eventデータを元にした表示用ReservationActionPlanデータ生成
 *
 * @param array $event event
 * @return array 生成された表示用ReservationActionPlan配列
 */
	public function getReservationActionPlanForView($event) {
		$userStartYmdHis =
			(new ReservationTime())->svr2UserYmdHis($event['ReservationEvent']['dtstart']);

		//YYYY-MM-DD hh:mm:ss
		$userStartDatetime = ReservationTime::addDashColonAndSp($userStartYmdHis);

		if ($event['ReservationEvent']['is_allday']) {
			//注) 終日なので、dtendは、dtstar+24時間になっており、そのままViewに渡すと
			//翌日になってしまうので、ここではdtendを使わずdtstartを代理利用する。
			$userEndYmdHis =
				(new ReservationTime())->svr2UserYmdHis($event['ReservationEvent']['dtstart']);
			//YYYY-MM-DD hh:mm:ss
			$userEndDatetime = ReservationTime::addDashColonAndSp($userEndYmdHis);
		} else {
			//開始、終了なので、dtendを素直にそのまま使う
			$userEndYmdHis =
				(new ReservationTime())->svr2UserYmdHis($event['ReservationEvent']['dtend']);
			//YYYY-MM-DD hh:mm:ss
			$userEndDatetime = ReservationTime::addDashColonAndSp($userEndYmdHis);
		}

		$userTz = (new NetCommonsTime())->getUserTimezone();
		$tmArray = ReservationTime::transFromYmdHisToArray($userStartDatetime);
		$date = (new ReservationTime())->getDtObjWithTzDateTime($userTz,
			$tmArray['year'], $tmArray['month'], $tmArray['day'],
			$tmArray['hour'], $tmArray['min'], $tmArray['sec']);
		$wdayIndex = intval($date->format('w'));	//0-6
		$wdays = explode('|', ReservationsComponent::CALENDAR_REPEAT_WDAY);

		$isDetail = 0;
		if ($event['ReservationEvent']['location'] !== '' ||
			$event['ReservationEvent']['contact'] !== '' ||
			$event['ReservationEvent']['description'] !== '') {
			$isDetail = 1;
		}

		$capForView = $this->__makeCapForViewSubset($event, $userStartDatetime, $userEndDatetime,
			$isDetail, $wdays, $wdayIndex, $tmArray);

		$rrule = (new ReservationRruleUtil())->parseRrule($event['ReservationRrule']['rrule']);

		$this->__setRruleFreqParamsToCapForView($rrule, $capForView);

		$this->__setRruleTermParamsToCapForView($rrule, $capForView);

		$capForView['GroupsUser'] = array();
		if (isset($event['ReservationEventShareUser']) && is_array($event['ReservationEventShareUser'])) {
			foreach ($event['ReservationEventShareUser'] as $shareUser) {
				//request->data['GroupsUser']の格納形式に合わせる。
				$capForView['GroupsUser'][] = array('user_id' => $shareUser['share_user']);
			}
		}

		return $capForView;
	}

/**
 * convTzOffset2TzId
 *
 * timezoneOffset(数字-12.0-12.0）からtzId(Asia/Tokyoなど)への変換
 *
 * @param double $tzOffsetVal timezoneのoffset値(-12.0 - 12.0)
 * @return string timezoneId
 */
	public function convTzOffset2TzId($tzOffsetVal) {
		$tzId = (new NetCommonsTime())->getUserTimezone(); //初期値
		$tzTbl = ReservationsComponent::getTzTbl();
		foreach ($tzTbl as $tzInfo) {
			if ($tzInfo[ReservationsComponent::CALENDAR_TIMEZONE_OFFSET_VAL] == $tzOffsetVal) {
				$tzId = $tzInfo[ReservationsComponent::CALENDAR_TIMEZONE_ID];
				break;
			}
		}
		return $tzId;
	}

/**
 * __makeCapForViewSubset
 *
 * CapForView配列のサブセット生成
 *
 * @param array $event event
 * @param string $userStartDatetime userStartDatetime
 * @param string $userEndDatetime userEndDatetime
 * @param int $isDetail isDetail
 * @param array $wdays wdays
 * @param int $wdayIndex wdayIndex
 * @param array $tmArray tmArray
 * @return array 生成されたCapForView配列のサブセット
 */
	private function __makeCapForViewSubset($event, $userStartDatetime, $userEndDatetime,
		$isDetail, $wdays, $wdayIndex, $tmArray) {
		$tzId = $this->convTzOffset2TzId($event['ReservationEvent']['timezone_offset']);
		$capForView = array(
			'ReservationActionPlan' => array(
				'edit_rrule' => 0,	//tableにはない項目なのでinitと同じ値
				'title' => $event['ReservationEvent']['title'],
				'title_icon' => $event['ReservationEvent']['title_icon'],
				'enable_time' => ($event['ReservationEvent']['is_allday']) ? 0 : 1,
				'easy_start_date' => substr($userStartDatetime, 0, 10), //YYYY-MM-DD
				'easy_hour_minute_from' => substr($userStartDatetime, 11, 5), //hh:mm
				'easy_hour_minute_to' => substr($userEndDatetime, 11, 5), //hh:mm
				//YYYY-MM-DD hh:mm
				'detail_start_datetime' => substr($userStartDatetime, 0, 16),
				//YYYY-MM-DD hh:mm
				'detail_end_datetime' => substr($userEndDatetime, 0, 16),
				'plan_room_id' => $event['ReservationEvent']['room_id'],
				'timezone_offset' => $tzId,
				'is_detail' => $isDetail,
				'location' => $event['ReservationEvent']['location'],
				'contact' => $event['ReservationEvent']['contact'],
				'description' => $event['ReservationEvent']['description'],
				'is_repeat' => 0,	//まずは初期値
				'repeat_freq' => 'DAILY',	//まずは初期値
				'FREQ' => $this->__getInitialFreq($wdays, $wdayIndex, $tmArray['month']),
				'TERM' => $this->__getInitailTerm(substr($userStartDatetime, 0, 10)),
				'enable_email' => intval($event['ReservationEvent']['is_enable_mail']), //名違いに注意
				'email_send_timing' => intval($event['ReservationEvent']['email_send_timing']),
				'location_key' => $event['ReservationEvent']['location_key']
			),
		);
		return $capForView;
	}

/**
 * __setRruleFreqParamsToCapForView
 *
 * 繰返し規則の周期性のパラメータをcapForView配列にセットする。
 *
 * @param array $rrule 元となるrrule配列
 * @param array &$capForView 代入すべきcapForView配列
 * @return void
 */
	private function __setRruleFreqParamsToCapForView($rrule, &$capForView) {
		if ($rrule['FREQ'] === 'DAILY') {
			$capForView['ReservationActionPlan']['is_repeat'] = 1;
			$capForView['ReservationActionPlan']['repeat_freq'] = 'DAILY';
			$capForView['ReservationActionPlan']['FREQ']['DAILY']['INTERVAL'] = $rrule['INTERVAL'];
		}
		if ($rrule['FREQ'] === 'WEEKLY') {
			$capForView['ReservationActionPlan']['is_repeat'] = 1;
			$capForView['ReservationActionPlan']['repeat_freq'] = 'WEEKLY';
			$capForView['ReservationActionPlan']['FREQ']['WEEKLY']['INTERVAL'] = $rrule['INTERVAL'];
			$capForView['ReservationActionPlan']['FREQ']['WEEKLY']['BYDAY'] = $rrule['BYDAY']; //配列
		}
		if ($rrule['FREQ'] === 'MONTHLY') {
			$capForView['ReservationActionPlan']['is_repeat'] = 1;
			$capForView['ReservationActionPlan']['repeat_freq'] = 'MONTHLY';
			$capForView['ReservationActionPlan']['FREQ']['MONTHLY']['INTERVAL'] = $rrule['INTERVAL'];
			$capForView['ReservationActionPlan']['FREQ']['MONTHLY']['BYDAY'] = array('');
			$capForView['ReservationActionPlan']['FREQ']['MONTHLY']['BYMONTHDAY'] = array('');
			if (isset($rrule['BYDAY'])) {
				$capForView['ReservationActionPlan']['FREQ']['MONTHLY']['BYDAY'] =
					$rrule['BYDAY'];
			}
			if (isset($rrule['BYMONTHDAY'])) {
				$capForView['ReservationActionPlan']['FREQ']['MONTHLY']['BYMONTHDAY'] =
					$rrule['BYMONTHDAY'];
			}
		}
		if ($rrule['FREQ'] === 'YEARLY') {
			$capForView['ReservationActionPlan']['is_repeat'] = 1;
			$capForView['ReservationActionPlan']['repeat_freq'] = 'YEARLY';
			$capForView['ReservationActionPlan']['FREQ']['YEARLY']['INTERVAL'] = $rrule['INTERVAL'];
			$capForView['ReservationActionPlan']['FREQ']['YEARLY']['BYMONTH'] = $rrule['BYMONTH'];
			$capForView['ReservationActionPlan']['FREQ']['YEARLY']['BYDAY'] = array('');
			if (isset($rrule['BYDAY'])) {
				$capForView['ReservationActionPlan']['FREQ']['YEARLY']['BYDAY'] = $rrule['BYDAY'];
			}
		}
	}

/**
 * __setRruleTermParamsToCapForView
 *
 * 繰返し規則の期限のパラメータをcapForView配列にセットする。
 *
 * @param array $rrule 元となるrrule配列
 * @param array &$capForView 代入すべきcapForView配列
 * @return void
 */
	private function __setRruleTermParamsToCapForView($rrule, &$capForView) {
		if (isset($rrule['REPEAT_COUNT']) && $rrule['REPEAT_COUNT'] == 1) {
			$capForView['ReservationActionPlan']['TERM']['REPEAT_COUNT'] = 1;
			$capForView['ReservationActionPlan']['TERM']['REPEAT_UNTIL'] = 0;
			$capForView['ReservationActionPlan']['TERM']['COUNT'] = $rrule['COUNT'];
		}
		if (isset($rrule['REPEAT_UNTIL']) && $rrule['REPEAT_UNTIL'] == 1) {
			$capForView['ReservationActionPlan']['TERM']['REPEAT_COUNT'] = 0;
			$capForView['ReservationActionPlan']['TERM']['REPEAT_UNTIL'] = 1;

			//ユーザーTZ=JSTのカレンダ編集画面より期限"2016-05-31まで"とすると、
			//ReservationRruleのrruleには、UNTIL=20160531T150000(=UTC)が入る。
			//parseRrule()して配列化して取り出すと、$rule[UNTIL]=20160531150000となる。
			//これをJSTになおすと2016-06-01 00:00:00となる。
			//時間の大小計算の場合、これ「2016-06-01 00:00:00まで(=未満)」
			//との比較が、カレンダ編集画面に再表示する時は、
			//「2016-05-31まで」と引く１日して代入・表現しないと、
			//予定登録画面の表記と１日ずれてしまう。
			//なので、ここで補正します。
			//
			$date = new DateTime('now', (new DateTimeZone('UTC')));	//サーバ系
			$date->setDate(substr($rrule['UNTIL'], 0, 4),
				substr($rrule['UNTIL'], 4, 2), substr($rrule['UNTIL'], 6, 2));
			$date->setTime(substr($rrule['UNTIL'], 8, 2),
				substr($rrule['UNTIL'], 10, 2), substr($rrule['UNTIL'], 12, 2));
			$userTz = (new NetCommonsTime())->getUserTimezone();
			$date->setTimezone(new DateTimeZone($userTz));	//ユーザ系へ変換
			$date->setDate($date->format('Y'),
				$date->format('m'), intval($date->format('d')) - 1);	//1日前にする
			$capForView['ReservationActionPlan']['TERM']['UNTIL'] = $date->format('Y-m-d');
		}
	}
}
