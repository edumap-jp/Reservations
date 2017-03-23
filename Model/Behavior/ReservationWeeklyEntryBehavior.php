<?php
/**
 * ReservationWeeklyEntry Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationAppBehavior', 'Reservations.Model/Behavior');
App::uses('ReservationSupport', 'Reservations.Utility');
App::uses('ReservationTime', 'Reservations.Utility');

/**
 * ReservationWeeklyEntryBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model\Behavior
 */
class ReservationWeeklyEntryBehavior extends ReservationAppBehavior {

/**
 * Default settings
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2015, NetCommons Project
 */
	protected $_defaults = array(
	);

/**
 * 週周期の登録
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param ssary $rruleData rruleData
 * @param array $eventData eventデータ(ReservationEventのモデルデータ)
 * @param int $first 最初のデータかどうか 1:最初である  0:最初ではない
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return array $result 結果
 */
	public function insertWeekly(Model &$model, $planParams, $rruleData, $eventData,
		$first = 0, $createdUserWhenUpd = null) {
		//CakeLog::debug("DBGXXX: insertWeekly (model, planParams, rruleData, eventData, first[" .
		//	$first . "]) start. startDateTime [" . $eventData['ReservationEvent']['start_date'] .
		//	$eventData['ReservationEvent']['start_time'] . "] endDateTime[" .
		//	$eventData['ReservationEvent']['end_date'] . $eventData['ReservationEvent']['end_time'] . "]");

		//ユーザタイムゾーンを取得しておく。
		$userTz = (new NetCommonsTime())->getUserTimezone();

		// 開始日と終了日の週の日曜日の日付時刻および現在週のセット
		$currentWeek = '';
		$this->setStartEndSundayDateAndTime($model, $eventData, $currentWeek, $first, $userTz);

		//CakeLog::debug("DBGXXX: after setStartEndSundayDateAndTime(). startDateTime [" .
		//	$eventData['ReservationEvent']['start_date'] . $eventData['ReservationEvent']['start_time'] .
		//	"] endDateTime[" . $eventData['ReservationEvent']['end_date'] .
		//	$eventData['ReservationEvent']['end_time'] . "]");

		//setStartEndSundayDateAndTime()の中で、インターバル値を加算した サーバー系時刻
		//start_date,start_time, end_date, end_timeを$eventData['ReservationEvent']の該当項目に
		//代入しているので、そのまま、isRepeatable()の引数としてつかってOK.
		$result = ReservationSupport::isRepeatable(
			$model->rrule,
			($eventData['ReservationEvent']['start_date'] . $eventData['ReservationEvent']['start_time']),
			$eventData['ReservationEvent']['timezone_offset'],
			$model->isOverMaxRruleIndex
		);
		if (! $result) {
			return true;
		}

		foreach ($model->rrule['BYDAY'] as $val) {
			$index = array_search($val, self::$reservationWdayArray);
			//CakeLog::debug("DBGX: array_search(" . $val . ") returned index[" . $index . "]");
			if ($first && $currentWeek >= $index) {
				//CakeLog::debug("DBGX: continue case. first[" . $first .
				//"] is TRUE and  currentWeek[" . $currentWeek . "] >= index[" . $index . "]");
				continue;
			}
			//insertWeeklyInterval()のinsert結果は、$eventDataにセットされる。
			$result = $this->insertWeeklyInterval($model, $planParams, $rruleData, $eventData,
				$index, $userTz, $createdUserWhenUpd);
			if ($result === false) {
				//CakeLog::debug("DBGX: insertWeeklyInterval() returned FALSE. so i will return.");
				return $result;
			}
		}

		//NC3では内部はサーバー系時刻なのでtimezoneDate変換は不要
		//
		//$startTime = $eventData['ReservationEvent']['start_date'] . $eventData['ReservationEvent']['start_time'];
		//$endTime = $eventData['ReservationEvent']['end_date'] . $eventData['ReservationEvent']['end_time'];
		//
		//$eventData['ReservationEvent']['start_date'] = ReservationTime::timezoneDate($startTime, 1, 'Ymd');
		//$eventData['ReservationEvent']['start_time'] = ReservationTime::timezoneDate($startTime, 1, 'His');
		//$eventData['ReservationEvent']['end_date'] = ReservationTime::timezoneDate($endTime, 1, 'Ymd');
		//$eventData['ReservationEvent']['end_time'] = ReservationTime::timezoneDate($endTime, 1, 'His');

		return $this->insertWeekly($model, $planParams, $rruleData, $eventData,
			0, $createdUserWhenUpd);
	}

/**
 * インターバル用週周期の登録
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param ssary $rruleData rruleData
 * @param array $eventData eventデータ(ReservationEventのモデルデータ).
 * @param int $interval インターバル
 * @param string $userTz ユーザー系TZID(Asia/Tokyo)
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return array $result 結果
 */
	public function insertWeeklyInterval(Model &$model, $planParams, $rruleData, $eventData,
		$interval, $userTz, $createdUserWhenUpd = null) {
		//CakeLog::debug("DBGX: insertWeeklyInterval(model, planParams, rruleData, eventData, interval[" . $interval . "] userTz[". $userTz . "] eventData[ReservationEvent]=startDateTime [" . $eventData['ReservationEvent']['start_date'] . $eventData['ReservationEvent']['start_time'] . "] endDateTime[" . $eventData['ReservationEvent']['end_date'] . $eventData['ReservationEvent']['end_time'] . "]) start");

		$model->rrule['INDEX']++;

		//インターバル日数を加算した開始日の計算
		//sTimeはサーバー系時刻
		$sTime = $eventData['ReservationEvent']['start_date'] .
				$eventData['ReservationEvent']['start_time'];

		//以下で使う時間系は、画面上（=ユーザー系）でのカレンダ日付時刻をさしているので、
		//ユーザー系に直す。
		//
		$userStartTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($sTime));
		$userStartTime = ReservationTime::dt2calDt($userStartTime);

		//ユーザー系開始の同年同月の$interval考慮日のタイムスタンプを取得
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系DateTimeObj生成
		$date->setDate(substr($userStartTime, 0, 4),
			substr($userStartTime, 4, 2), substr($userStartTime, 6, 2) + $interval);
		$date->setTime(substr($userStartTime, 8, 2),
			substr($userStartTime, 10, 2), substr($userStartTime, 12, 2));
		$timestamp = $date->getTimestamp();
		$date->setTimeZone(new DateTimeZone('UTC'));	//サーバー系TZに直す
		$svrStartDate = $date->format('Ymd');
		$svrStartTime = $date->format('His');

		//CakeLog::debug("DBGX: BEFORE eventData[start_date]+[start_time][" .
		//	$eventData['ReservationEvent']['start_date'] . $eventData['ReservationEvent']['start_time'] .
		//	"] >> sTime[" . $sTime . "] >> AFTER timestamp[" . $timestamp . "] svrStartDate[" .
		//	$svrStartDate . "] svrStartTime[" . $svrStartTime . "]");

		//インターバル日数を加算した終了日の計算
		//eTimeはサーバー系時刻
		$eTime = $eventData['ReservationEvent']['end_date'] . $eventData['ReservationEvent']['end_time'];

		//以下で使う時間系は、画面上（=ユーザー系）でのカレンダ日付時刻をさしているので、
		//ユーザー系に直す。
		//
		$userEndTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($eTime));
		$userEndTime = ReservationTime::dt2calDt($userEndTime);

		//ユーザー系終了の同年同月の$interval考慮日のタイムスタンプを取得
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系DateTimeObj生成
		$date->setDate(substr($userEndTime, 0, 4),
			substr($userEndTime, 4, 2), substr($userEndTime, 6, 2) + $interval);
		$date->setTime(substr($userEndTime, 8, 2),
			substr($userEndTime, 10, 2), substr($userEndTime, 12, 2));
		$timestamp = $date->getTimestamp();
		$date->setTimeZone(new DateTimeZone('UTC'));	//サーバー系TZに直す
		$svrEndDate = $date->format('Ymd');
		$svrEndTime = $date->format('His');

		if (!ReservationSupport::isRepeatable($model->rrule, ($svrStartDate . $svrStartTime),
			$eventData['ReservationEvent']['timezone_offset'], $model->isOverMaxRruleIndex)) {
			return true;
		}

		//CakeLog::debug("DBGXXX: insert(svrStartDateTime[" . $svrStartDate . $svrStartTime .
		//	"] svrEndDateTime[" . $svrEndDate . $svrEndTime . "])");

		$rEventData = $this->insert($model, $planParams, $rruleData, $eventData,
			($svrStartDate . $svrStartTime), ($svrEndDate . $svrEndTime), $createdUserWhenUpd);
		if ($rEventData['ReservationEvent']['id'] === null) {
			//CakeLog::debug("DBGX: insert() returned id[NULL]. so i return FALSE");
			return false;
		} else {
			//CakeLog::debug("DBGX: insert() returned id[" . $rEventData['ReservationEvent']['id'] .
			//	"]. so i return TRUE");
			//insertした結果の$rEventDataは（eventDataとして)call元へもどす
			//（代入する）必要は、ありません。逆に、eventDataに代入してしま
			//うと、call元のLOOP foreach ($model->rrule['BYDAY'] as $val)
			//の中で日のずれが発生しまうので注意すること。
			return true;
		}
	}

/**
 * 開始日と終了日の週の日曜日の日付時刻のセット
 * 結果は、$eventData['ReservationEvent']のstart_date,time,end_date,timeにサーバ系時刻を
 * セットして返す。
 *
 * @param Model &$model モデル
 * @param array &$eventData eventデータ
 * @param string &$currentWeek currnetWeek文字列
 * @param int $first 最初のデータかどうか 1:最初である  0:最初ではない
 * @param string $userTz ユーザー系TZ文字列(Asia/Tokyo)
 * @return void
 */
	public function setStartEndSundayDateAndTime(&$model, &$eventData, &$currentWeek,
		$first, $userTz) {
		//開始日の週の日曜日の日付時刻
		//NC3ではサーバー系時刻なので、timezoneDateはつかわない
		$sTime = $eventData['ReservationEvent']['start_date'] .
			$eventData['ReservationEvent']['start_time']; //cat してYmdHisにする

		//以下で使う時間系は施設予約画面上（=ユーザー系）でのカレンダ
		//日付時刻をさしているので、ユーザー系に直す。
		//
		$userStartTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($sTime));
		$userStartTime = ReservationTime::dt2calDt($userStartTime);

		//ユーザー系開始日の同年同月の日＋インターバール(0 or rrule[INTERVAL]の7の倍数)
		//日数のタイムスタンプを取得
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系DateTimeObj生成
		$date->setDate(substr($userStartTime, 0, 4), substr($userStartTime, 4, 2),
			substr($userStartTime, 6, 2) + ($first ? 0 : (7 * $model->rrule['INTERVAL'])));
		$date->setTime(substr($userStartTime, 8, 2),
			substr($userStartTime, 10, 2), substr($userStartTime, 12, 2));
		$timestamp = $date->getTimestamp();
		//施設予約上(=ユーザ系)の日の曜日idx(0-6)取得
		$currentWeek = $date->format('w');

		//ユーザー系の開始日(または開始日＋インターバイル日数)の週の日曜日を求める。
		$sundayTimestamp = $timestamp - $currentWeek * 86400;

		//ユーザー系開始の日曜日を、サーバー系TZに変えてから、YmdとHisを取得し、
		//eventData[ReservationEvent]のstart_date, start_timeに代入する。
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系DateTimeObj生成
		$date->setTimestamp($sundayTimestamp);	//ユーザー系日曜日のtimestamp
		$date->setTimeZone(new DateTimeZone('UTC'));	//サーバー系TZに直す
		$svrStartDate = $date->format('Ymd');
		$svrStartTime = $date->format('His');
		$eventData['ReservationEvent']['start_date'] = $svrStartDate;
		$eventData['ReservationEvent']['start_time'] = $svrStartTime;

		//CakeLog::debug("DBGX: BEFORE eventData[start_date]+[start_time][" .
		//	$eventData['ReservationEvent']['start_date'] . $eventData['ReservationEvent']['start_time'] .
		//	"] >> sTime[" . $sTime . "] >> AFTER timestamp[" . $timestamp . "] currentWeek[" .
		//	$currentWeek . "] sundayTimestamp[" . $sundayTimestamp . "] SUNサーバ系start_date[" .
		//	$svrStartDate . "] start_time[" . $svrStartTime . "]");

		//終了日の週の日曜日の日付時刻
		//NC3ではサーバー系時刻なので、timezoneDateはつかわない
		//catしてYmdHisにする
		$eTime = $eventData['ReservationEvent']['end_date'] . $eventData['ReservationEvent']['end_time'];

		//以下で使う時間系は施設予約画面上（=ユーザー系）でのカレンダ
		//日付時刻をさしているので、ユーザー系に直す。
		//
		$userEndTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($eTime));
		$userEndTime = ReservationTime::dt2calDt($userEndTime);

		//ユーザー系終了日の同年同月の日＋インターバール(0 or rrule[INTERVAL]の7の
		//倍数)日数のタイムスタンプを取得
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系DateTimeObj生成
		$date->setDate(substr($userEndTime, 0, 4),
			substr($userEndTime, 4, 2),
			substr($userEndTime, 6, 2) + ($first ? 0 : (7 * $model->rrule['INTERVAL'])));
		$date->setTime(substr($userEndTime, 8, 2),
			substr($userEndTime, 10, 2), substr($userEndTime, 12, 2));
		$endTimestamp = $date->getTimestamp();

		//終了日(または終了日＋インターバル日数)の週の日曜日を求める。
		$endSundayTimestamp = $endTimestamp - $currentWeek * 86400;

		//ユーザー系終了の日曜日を、サーバー系TZに変えてから、YmdとHisを取得し、
		//eventData[ReservationEvent]のend_date, end_timeに代入する。
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系DateTimeObj生成
		$date->setTimestamp($endSundayTimestamp);	//ユーザー系日曜日のtimestamp
		$date->setTimeZone(new DateTimeZone('UTC'));	//サーバー系TZに直す
		$svrEndDate = $date->format('Ymd');
		$svrEndTime = $date->format('His');
		$eventData['ReservationEvent']['end_date'] = $svrEndDate;
		$eventData['ReservationEvent']['end_time'] = $svrEndTime;
	}
}
