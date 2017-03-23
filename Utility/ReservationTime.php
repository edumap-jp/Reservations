<?php
/**
 * ReservationTime Utility
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('NetCommonsTime', 'NetCommons.Utility');

/**
 * ReservationTime Utility
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Utility
 */
class ReservationTime {

/**
 * getDtObjWithTzDateTimeString
 *
 * TZ、日付および時刻を指定したDataTimeオブジェクト取得
 *
 * @param string $tzId タイムゾーンID (UTC, Asia/Tokyoなど)
 * @param string $datetime Y-m-d H-i
 * @return DateTime DateTimeオブジェクト
 */
	public function getDtObjWithTzDateTimeString($tzId, $datetime) {
		$ncTime = new NetCommonsTime();
		$userTz = $ncTime->getUserTimezone();
		$date = new DateTime($datetime, (new DateTimeZone($userTz)));
		if ($tzId != $userTz) {
			$date->setTimezone(new DateTimeZone($tzId));
		}
		return $date;
	}

/**
 * getDtObjWithTzDateTime
 *
 * TZ、日付および時刻を指定したDataTimeオブジェクト取得
 *
 * @param string $tzId タイムゾーンID (UTC, Asia/Tokyoなど)
 * @param int $year year
 * @param int $month month
 * @param int $day day
 * @param int $hour hour
 * @param int $minute minute
 * @param int $second  second
 * @return DateTime DateTimeオブジェクト
 */
	public function getDtObjWithTzDateTime($tzId, $year, $month, $day, $hour, $minute, $second) {
		$date = new DateTime('now', (new DateTimeZone($tzId)));
		$date->setDate($year, $month, $day);
		$date->setTime($hour, $minute, $second);
		return $date;
	}

/**
 * svr2UserYmdHis
 *
 * サーバー系YmdHis文字列をユーザー系YmdHis文字列に変換する
 *
 * @param string $svrYmdHis サーバー系YmdHis文字列
 * @return string ユーザー系YmdHis文字列を返す
 */
	public function svr2UserYmdHis($svrYmdHis) {
		$userYmdHisWithSepa =
			(new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($svrYmdHis));
		$userYmdHis = ReservationTime::dt2calDt($userYmdHisWithSepa);
		return $userYmdHis;
	}

/**
 * convUserFromTo2SvrFromTo
 *
 * ユーザー系の開始日と終了日とタイムゾーンを、サーバ系の開始日の00:00:00から終了翌日の00:00:00に変換し返す
 *
 * @param string $userYmdFrom "YYYY-MM-DD"形式(ユーザ系)
 * @param string $userYmdTo "YYYY-MM-DD"形式(ユーザ系)
 * @param string $userTimezoneOffset タイムゾーン文字列（ex.Asia/Tokyo）
 * @param bool $isAllDay 終日予定なのかどうか（これによってgetNextDayの取得を制御する
 * @return array 開始日の00:00から終了日翌日の00:00をサーバ系時刻になおして返す。
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function convUserFromTo2SvrFromTo(
		$userYmdFrom, $userYmdTo, $userTimezoneOffset, $isAllDay = false) {
		$nctm = new NetCommonsTime();
		$dttmFrom = new DateTime($userYmdFrom);
		$dttmTo = new DateTime($userYmdTo);
		$diffDate = date_diff($dttmTo, $dttmFrom);
		//From日付の00:00:00
		$startDateZero = $userYmdFrom . ' 00:00:00';
		$serverStartDate = $nctm->toServerDatetime($startDateZero, $userTimezoneOffset);

		//To日付の翌日00:00:00をEndにセット
		if ($isAllDay && $diffDate->d >= 1) {
			$yearOfEndNextDay = $dttmTo->format('Y');
			$monthOfEndNextDay = $dttmTo->format('m');
			$endNextDay = $dttmTo->format('d');
		} else {
			list($yearOfEndNextDay, $monthOfEndNextDay, $endNextDay) = ReservationTime::
			getNextDay(intval(substr($userYmdTo, 0, 4)), intval(substr($userYmdTo, 5, 2)),
				intval(substr($userYmdTo, 8, 2)));
		}
		$serverEndNextDate = $nctm->toServerDatetime(sprintf("%04d-%02d-%02d 00:00:00",
			$yearOfEndNextDay, $monthOfEndNextDay, $endNextDay), $userTimezoneOffset);

		return array($serverStartDate, $serverEndNextDate);
	}

/**
 * convUserDate2SvrFromToDateTime
 *
 * ユーザー系の日とタイムゾーンを、サーバ系の終日(From,To)日付時刻に変換
 *
 * @param string $userYmd "YYYY-MM-DD"形式(ユーザ系)
 * @param string $userTimezoneOffset タイムゾーン文字列（ex.Asia/Tokyo）
 * @return array その日の00:00から翌日の00:00をサーバ系時刻になおして返す。
 */
	public function convUserDate2SvrFromToDateTime($userYmd, $userTimezoneOffset) {
		$nctm = new NetCommonsTime();
		$startDateZero = $userYmd . ' 00:00:00';
		$serverStartDateZero = $nctm->toServerDatetime($startDateZero, $userTimezoneOffset);
		list($yearOfNextDay, $monthOfNextDay, $nextDay) = ReservationTime::
			getNextDay(intval(substr($startDateZero, 0, 4)), intval(substr($startDateZero, 5, 2)),
			intval(substr($startDateZero, 8, 2 )));
		$serverNextDateZero = $nctm->toServerDatetime(sprintf("%04d-%02d-%02d 00:00:00", $yearOfNextDay,
			$monthOfNextDay, $nextDay), $userTimezoneOffset);

		return array($serverStartDateZero, $serverNextDateZero);
	}

/**
 * addDashColonAndSp
 *
 * "YmdHis", "Ymd", "His"形式の指定日付時刻に記号と空白(-,:,SPACE)を付与して、"YYYY-MM-DD hh:mm:ss"などに変換して返す。
 *
 * @param string $data 整形前の日付時刻
 * @return string 整形後の日付時刻
 */
	public static function addDashColonAndSp($data) {
		//YYYYMMDDhhmmss
		if (preg_match("/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/", $data, $matches) === 1) {
			return sprintf("%04d-%02d-%02d %02d:%02d:%02d", $matches[1], $matches[2], $matches[3],
				$matches[4], $matches[5], $matches[6]);
		}
		//YYYYMMDD
		if (preg_match("/^(\d{4})(\d{2})(\d{2})$/", $data, $matches) === 1) {
			return sprintf("%04d-%02d-%02d", $matches[1], $matches[2], $matches[3]);
		}
		//hhmmss
		if (preg_match("/^(\d{2})(\d{2})(\d{2})$/", $data, $matches) === 1) {
			return sprintf("%02d:%02d:%02d", $matches[1], $matches[2], $matches[3]);
		}
		//変換の必要がなかったケース.そのまま返す.
		return $data;
	}

/**
 * stripDashColonAndSp
 *
 * "Y-m-d H:i:s","Y-m-d","H:i:s"形式の指定日付時刻から記号と空白(-,:,SPACE)を取り除く
 *
 * @param string $data 整形前の日付時刻
 * @return string 整形後の日付時刻
 */
	public static function stripDashColonAndSp($data) {
		return preg_replace('/( |:|-)/', '', $data);
	}
/**
 * getTheTime
 *
 * "Y-m-d H:i:s"形式の指定日付時刻からの日付時刻(from,to)を取得
 *
 * @param string $ymdHis "Y-m-d H:i:s"形式の指定日付時刻
 * @return array 直近１時間の日付、from日付時刻, to日付時刻の配列を返す。
 */
	public static function getTheTime($ymdHis) {
		$baseHi = self::getHourColonMin($ymdHis);	// hh:mm
		$hour = intval(substr($baseHi, 0, 2));
		if ($hour <= 22) {
			$ymd = substr($ymdHis, 0, 10);	//YYYY-MM-DD
			$fromYmdHi = sprintf("%s %02d:00", $ymd, $hour);	//YYYY-MM-DD hh:mm
			$toYmdHi = sprintf("%s %02d:00", $ymd, $hour + 1);	//YYYY-MM-DD hh:mm
		} else {
			//23時は特例1. toはその日の24:00を指定したい、、が
			//datetimepickerには24:00という表記は存在しないので、翌日の00:00をセットする。
			$ymd = substr($ymdHis, 0, 10);	//YYYY-MM-DD
			$fromYmdHi = sprintf("%s 23:00", $ymd);
			list($yearOfNextDay, $monthOfNextDay, $nextDay) = self::getNextDay(intval(
				substr($ymdHis, 0, 4)), intval(substr($ymdHis, 5, 2)), intval(substr($ymdHis, 8, 2 )));
			$toYmdHi = sprintf("%04d-%02d-%02d 00:00", $yearOfNextDay, $monthOfNextDay, $nextDay);
		}
		return array($ymd, $fromYmdHi, $toYmdHi);
	}

/**
 * getTheTimeInTheLastHour
 *
 * "Y-m-d H:i:s"形式の指定日付時刻からの直近１時間の日付時刻(from,to)を取得
 *
 * @param string $ymdHis "Y-m-d H:i:s"形式の指定日付時刻
 * @return array 直近１時間の日付、from日付時刻, to日付時刻の配列を返す。
 */
	public static function getTheTimeInTheLastHour($ymdHis) {
		$baseHi = self::getHourColonMin($ymdHis);	// hh:mm
		$hour = intval(substr($baseHi, 0, 2));
		if ($hour <= 21) {
			$ymd = substr($ymdHis, 0, 10);	//YYYY-MM-DD
			$fromYmdHi = sprintf("%s %02d:00", $ymd, $hour + 1);	//YYYY-MM-DD hh:mm
			$toYmdHi = sprintf("%s %02d:00", $ymd, $hour + 2);	//YYYY-MM-DD hh:mm
		} else {
			//22,23時は特例1. toはその日の24:00を指定したい、、が
			//datetimepickerには24:00という表記は存在しないので、翌日の00:00をセットする。
			$ymd = substr($ymdHis, 0, 10);	//YYYY-MM-DD
			$fromYmdHi = sprintf("%s 23:00", $ymd);
			list($yearOfNextDay, $monthOfNextDay, $nextDay) = self::getNextDay(intval(
				substr($ymdHis, 0, 4)), intval(substr($ymdHis, 5, 2)), intval(substr($ymdHis, 8, 2 )));
			$toYmdHi = sprintf("%04d-%02d-%02d 00:00", $yearOfNextDay, $monthOfNextDay, $nextDay);
		}
		return array($ymd, $fromYmdHi, $toYmdHi);
	}

/**
 * getHourColonMin
 *
 * "Y-m-d H:i:s"形式より"H:i"を抜き出す
 *
 * @param string $ymdHis "Y-m-d H:i:s"形式のDatetime文字列
 * @return string "H:i"形式の文字列
 */
	public static function getHourColonMin($ymdHis) {
		return substr($ymdHis, 11, 5);
	}

/**
 * getNextDay
 *
 * 年月日の次日を取得する
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return array 次日の年と月と日を配列で返す。
 */
	public static function getNextDay($year, $month, $day) {
		//mktimeのday引数の「その月の日数より大きい値は、
		//翌月以降の該当する日を表す」仕様を応用して次日の年月日を求める。
		list($yearOfNextDay, $monthOfNextDay, $nextDay) =
			explode('/', date('Y/m/d', mktime(0, 0, 0, $month, $day + 1, $year)));
		return array($yearOfNextDay, $monthOfNextDay, $nextDay);
	}

/**
 * getPrevMonth
 *
 * 年月の前月を取得する
 *
 * @param int $year 年
 * @param int $month 月
 * @return array 前月の年と月を配列で返す。
 */
	public static function getPrevMonth($year, $month) {
		$yearOfPrevMonth = $year;
		$prevMonth = $month - 1;
		if ($month == 1) {
			$yearOfPrevMonth = $year - 1;
			$prevMonth = 12;
		}
		return array($yearOfPrevMonth, $prevMonth);
	}

/**
 * getNextMonth
 *
 * 年月の次月を取得する
 *
 * @param int $year 年
 * @param int $month 月
 * @return array 次月の年と次月の月を配列で返す。
 */
	public static function getNextMonth($year, $month) {
		$yearOfNextMonth = $year;
		$nextMonth = $month + 1;
		if ($month == 12) {
			$yearOfNextMonth = $year + 1;
			$nextMonth = 1;
		}
		return array($yearOfNextMonth, $nextMonth);
	}

/**
 * dt2CalDt
 *
 * Y-m-d H:i:s形式からYmdHis形式に変換する
 *
 * @param string $datetime "Y-m-d H:i:s"形式の日付時刻
 * @return string "YmdHis"形式の日付時刻
 */
	public static function dt2CalDt($datetime) {
		return (substr($datetime, 0, 4) . substr($datetime, 5, 2) . substr($datetime, 8, 2) .
			substr($datetime, 11, 2) . substr($datetime, 14, 2) . substr($datetime, 17, 2));
	}

/**
 * calDt2dt
 *
 * YmdHis形式からY-m-d H:i:s形式に変換する
 *
 * @param string $datetime "YmdHis"形式の日付時刻
 * @return string "Y-m-d H:i:s"形式の日付時刻
 */
	public static function calDt2dt($datetime) {
		return sprintf('%s-%s-%s %s:%s:%s',
			substr($datetime, 0, 4), substr($datetime, 4, 2), substr($datetime, 6, 2),
			substr($datetime, 8, 2), substr($datetime, 10, 2), substr($datetime, 12, 2));
	}

/**
 * getWdayAlt
 *
 * 年月日から曜日(0-6)を返す (PHP施設予約関数を使用しないversion.)
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return int 曜日(0-6)
 */
	public function getWdayAlt($year, $month, $day) {
		$userTz = (new NetCommonsTime())->getUserTimezone();
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系
		$date->setDate($year, $month, $day);	//指定日の00:00:00
		$date->setTime(0, 0, 0);
		$wDay = $date->format('w'); //指定日の曜日を返す
		return $wDay;
	}

/**
 * getMonthlyInfoAlt
 *
 * 月施設予約で必要な情報を返す (PHP施設予約関数を使用しない版)
 *
 * @param int $year 年
 * @param int $month 月
 * @return array 前月、次月、今月の月施設予約情報の配列
 */
	public function getMonthlyInfoAlt($year, $month) {
		$userTz = (new NetCommonsTime())->getUserTimezone();
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系

		$date->setDate($year, $month, 1);	//当月1日の00:00:00
		$date->setTime(0, 0, 0);
		$wdayOf1stDay = $date->format('w'); //当月1日の曜日を返す

		$date->setDate($year, $month + 1, 1);	//翌月1日 00:00:00
		$date->setTime(0, 0, 0);
		$wkTm = $date->getTimestamp();
		$date->setTimestamp($wkTm - 10);	//翌月1日00:00:00の10秒前は当月末日
		$daysInMonth = $date->format('d');	//当月の月日数
		$wdayOfLastDay = $date->format('w'); //当月末日の曜日

		$numOfWeek = ceil(($daysInMonth + $wdayOf1stDay) / 7);	//当月の週数

		$date->setDate($year, $month, 1); //当月１日00:00:00
		$date->setTime(0, 0, 0);
		$wkTm = $date->getTimestamp();
		$date->setTimestamp($wkTm - 10);	//当月1日00:00:00の10秒前は前月末日
		$yearOfPrevMonth = $date->format('Y');	//前月の年
		$prevMonth = $date->format('m');	//前月の月
		$daysInPrevMonth = $date->format('d');	//前月の月日数

		$date->setDate($year, $month + 2, 1);	//翌々月1日00:00:00
		$date->setTime(0, 0, 0);
		$wkTm = $date->getTimestamp();
		$date->setTimestamp($wkTm - 10);	//翌々月1日00:00:00の10秒前は翌月末日

		$yearOfNextMonth = $date->format('Y');	//翌月の年
		$nextMonth = $date->format('m');	//翌月の月
		$daysInNextMonth = $date->format('d');	//翌月の月日数

		return array(
			'yearOfPrevMonth' => intval($yearOfPrevMonth),
			'prevMonth' => intval($prevMonth),
			'daysInPrevMonth' => intval($daysInPrevMonth),
			'yearOfNextMonth' => intval($yearOfNextMonth),
			'nextMonth' => intval($nextMonth),
			'daysInNextMonth' => intval($daysInNextMonth),
			'year' => intval($year),
			'month' => intval($month),
			'wdayOf1stDay' => intval($wdayOf1stDay),
			'daysInMonth' => intval($daysInMonth),
			'wdayOfLastDay' => intval($wdayOfLastDay),
			'numOfWeek' => intval($numOfWeek),
		);
	}

/**
 * transFromYmdHisToArray
 *
 * Y-m-d H:i:s形式の文字列から配列に変える
 *
 * @param string $strYmdHis Y-m-d H:i:s形式の文字列
 * @return mix 成功時は、各時間の構成要素からなる配列(array). 失敗時はfalse.
 */
	public static function transFromYmdHisToArray($strYmdHis) {
		$tmArray = array();
		if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/',
			$strYmdHis, $matches) !== 1) {
			return false;
		}
		$tmArray['year'] = $matches[1];
		$tmArray['month'] = $matches[2];
		$tmArray['day'] = $matches[3];
		$tmArray['hour'] = $matches[4];
		$tmArray['min'] = $matches[5];
		$tmArray['sec'] = $matches[6];
		return $tmArray;
	}

/**
 * 日付フォーマット関数
 *
 * @param string $time time YmdHis形式の文字列. insertFlag=1の時ユーザー系.insertFlag=0の時サーバー系であることを想定している。
 * @param mixed $timezoneOffset 値(-12.0 - 12.0)が入っていればその時差を使う。nullならtimezoneOffsetはつかわず、insertFlagだけで処理する
 * @param int $insertFlag insertFlag (1:登録=サーバ系にする. 0:表示=ユーザー系にする)
 * @param string $timeFormat 時間表示形式
 * @param int $toFlag これが1の時はその日の最後を24:00:00として表示する
 * @return string 生成した日付フォーマット文字列
 */
	public function dateFormat($time, $timezoneOffset = null, $insertFlag = 0,
		$timeFormat = 'YmdHis', $toFlag = 0) {
		$userTz = (new NetCommonsTime())->getUserTimezone();

		if (isset($timezoneOffset)) {
			//ユーザー系、サーバー系ではなく、具体的は時差情報がわたされたので、それで計算するケース
			$timezoneMinuteOffset = 0;
			if (round($timezoneOffset) != intval($timezoneOffset)) {
				$timezoneOffset = ($timezoneOffset > 0) ? floor($timezoneOffset) : ceil($timezoneOffset);
				$timezoneMinuteOffset = ($timezoneOffset > 0) ? 30 : -30;	// 0.5minute
			}
			if ($insertFlag) {
				//登録=サーバー系に直す.よってユーザーTZ系時間に時差を引きUTCにする
				$timezoneOffset = -1 * $timezoneOffset;
			} else {
				//表示=ユーザー系に直す。よって、UTCに時差を足しユーザー系時間にする。
			}
			//NC2の時mktime(), date()は php.ini=date.tiemzone=Aisa/Tokyo時の値を返していた。
			//それを前提に補正計算する。
			$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系にする
			$date->setDate(intval(substr($time, 0, 4)),
				intval(substr($time, 4, 2)), intval(substr($time, 6, 2)));
			$date->setTime(intval(substr($time, 8, 2)) + $timezoneOffset,
				intval(substr($time, 10, 2)) + $timezoneMinuteOffset, intval(substr($time, 12, 2)));
			//ユーザー系のままだが、insertFlag=1の時はサーバー系のYmdHis、insertFlag=0の時はユーザー系のYmdHisを返す
			$time = $date->format('YmdHis');
		} else {
			//オフセット時間の指定がないので、insertFlag=1(登録=ユーザ系toサーバー系)か
			//insertFlag=0(表示=サーバー系toユーザー系)で判断する。
			//少し冗長だが、変化の流れが分かるように書いた。
			if ($insertFlag) {
				//登録.ユーザー系toサーバー系YmdHis
				$userDateTime = $time;
				$svrDateTime = (new NetCommonsTime())->toServerDatetime(
					ReservationTime::calDt2dt($userDateTime)
				);
				$svrDateTime = ReservationTime::dt2calDt($svrDateTime);
				$time = $svrDateTime;
			} else {
				//表示.サーバー系toユーザー系YmdHis
				$svrDateTime = $time;
				$userDateTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($svrDateTime));
				$userDateTime = ReservationTime::dt2calDt($userDateTime);
				$time = $userDateTime;
			}
		}

		//タイムスタンプ計算
		//
		$date = new DateTime('now', (new DateTimeZone($userTz)));
		if ($toFlag && substr($time, 8) == "000000") {
			//x月y日24:00:00とするための工夫
			$timeFormat = str_replace("H", "24", $timeFormat);
			$timeFormat = str_replace("is", "0000", $timeFormat);
			$timeFormat = str_replace("i", "00", $timeFormat);

			//この時点で、$timeFormatは "Ymd240000"になっている。

			//タイムスタンプを求める。
			//0,0,0があるから、ここにくるのは、表示.サーバ系toユーザー系の時とおもわれるが、、
			$date->setDate(intval(substr($time, 0, 4)),
				intval(substr($time, 4, 2)), intval(substr($time, 6, 2)));
			$date->setTime(0, 0, 0);
			$timestamp = $date->getTimestamp();

			//タイムスタンプから1秒だけ引く。これは、24:00:00としつつも、翌日にならずに当日の最後にする工夫
			$timestamp = $timestamp - 1;
		} else {
			$date->setDate(intval(substr($time, 0, 4)),
				intval(substr($time, 4, 2)), intval(substr($time, 6, 2)));
			$date->setTime(intval(substr($time, 8, 2)),
				intval(substr($time, 10, 2)), intval(substr($time, 12, 2)));
			$timestamp = $date->getTimestamp();
		}

		$date->setTimestamp($timestamp);
		$week = $date->format('w');	//曜日index (0-6)
		//言語別のCALENDAR_WEEK
		$weekNameArray = explode('|', __d('reservations', 'Sun|Mon|Tue|Wed|Thu|Fri|Sat'));
		return $date->format(sprintf($timeFormat, $weekNameArray[$week]));
	}
}
