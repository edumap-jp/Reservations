<?php
/**
 * ReservationMonthlyEntry Behavior
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
 * ReservationMonthlyEntryBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model\Behavior
 */
class ReservationMonthlyEntryBehavior extends ReservationAppBehavior {

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
 * 月周期の登録（月単位－各月の指定日）
 *
 * @param Model $model 実際のモデル名
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $eventData eventデータ(ReservationEventのモデルデータ)
 * @param int $bymonthday bymonthday
 * @param int $first 最初のデータかどうか 1:最初である  0:最初ではない. 初期値は0
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return mixed boolean true:登録せず終了 false:失敗、array 登録成功: array(登録した開始年月日時分秒, 登録した終了年月日時分秒)
 */
	public function insertMonthlyByMonthday(Model $model, $planParams, $rruleData,
	$eventData, $bymonthday, $first = 0, $createdUserWhenUpd = null) {
		CakeLog::debug("DBG: insertMonthlyByMonthday() start. 
			rrule[INDEX]=[" . $model->rrule['INDEX'] . "]");

		$model->rrule['INDEX']++;

		//ユーザタイムゾーンを取得しておく。
		$userTz = (new NetCommonsTime())->getUserTimezone();

		//開始日付時刻の処理
		$userStartTime = '';
		$startTimestamp = $currentDay = $interval = $intervalDay = 0;
		$this->__setMonthlyByMonthdayStartDtProc($eventData, $userStartTime, $startTimestamp,
			$userTz, $currentDay, $first, $model, $interval, $intervalDay);

		CakeLog::debug("DBG: 開始日付時刻処理. startTimestamp[" . $startTimestamp . "]
			 first[" . $first . "] currentDay[" . $currentDay . "] 
			 rrule[BYMONTHDAY][0]=[" . $model->rrule['BYMONTHDAY'][0] . "]
			 interval[" . $interval . "] intervalDay[" . $intervalDay . "]");

		//終了日付時刻の処理
		//NC3は内部はサーバー系時刻なのでtimezoneDateはつかわない
		$eTime = $eventData['ReservationEvent']['end_date'] .
			$eventData['ReservationEvent']['end_time']; //catしてYmdHisにする

		//以下で使う時間系は、00:00:00など画面上（=ユーザー系）でのカレンダ日付時刻を
		//さしているので、ユーザー系に直す。
		//
		$userEndTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($eTime));
		$userEndTime = ReservationTime::dt2calDt($userEndTime);

		//ユーザー系終了日の00:00:00のタイムスタンプを取得
		$date = new DateTime('now', (new DateTimeZone($userTz)));
		$date->setDate(substr($userEndTime, 0, 4),
			substr($userEndTime, 4, 2), substr($userEndTime, 6, 2));
		$date->setTime(0, 0, 0);
		$endTimestamp = $date->getTimestamp();

		//開始日と終了日の差分日数の計算
		$diffNum = ($endTimestamp - $startTimestamp) / 86400;

		CakeLog::debug("DBG: 終了日付時刻処理. eTime[" . $eTime . "]
			endTimestamp[" . $endTimestamp . "] 開始日と終了日の差分日数[" . $diffNum . "]");

		//ユーザー系開始日、終了日をつかった、インターバル月数とインター
		//バル日数を考慮した開始・終了日付時刻の実計算
		$date = new DateTime('now', (new DateTimeZone($userTz)));
		$date->setDate(substr($userStartTime, 0, 4),
			substr($userStartTime, 4, 2) + $interval,
			$model->rrule['BYMONTHDAY'][0] - $intervalDay);
		$date->setTime(substr($userStartTime, 8, 2),
			substr($userStartTime, 10, 2), substr($userStartTime, 12, 2));
		$startTimestamp = $date->getTimestamp();	//debug出力用にtimestamp取得
		//isRepeatable(),insert()の引数は、サーバー系なので「ここで」TZを変換
		$date->setTimeZone(new DateTimeZone('UTC'));
		$svrStartDate = $date->format('Ymd');
		$svrStartTime = $date->format('His');

		$date = new DateTime('now', (new DateTimeZone($userTz)));
		$date->setDate(substr($userStartTime, 0, 4),	//年月は"userStartTime"を計算に使う
			substr($userStartTime, 4, 2) + $interval,
			$model->rrule['BYMONTHDAY'][0] - $intervalDay + $diffNum);
		$date->setTime(substr($userEndTime, 8, 2),	//時分秒は"userEndTime"を使う
			substr($userEndTime, 10, 2), substr($userEndTime, 12, 2));
		$endTimestamp = $date->getTimestamp();	//debug出力用にtimestamp取得
		//isRepeatable(),insert()の引数は、サーバー系なので「ここで」TZを変換
		$date->setTimeZone(new DateTimeZone('UTC'));
		$svrEndDate = $date->format('Ymd');
		$svrEndTime = $date->format('His');

		CakeLog::debug("DBG: startTimestamp[" . $startTimestamp . "]
			svrStartDate[" . $svrStartDate . "] svrStartTime[" . $svrStartTime . "]
			endTimestamp[" . $endTimestamp . "] svrEndDate[" . $svrEndDate . "]
			svrEndTime[" . $svrEndTime . "]");

		if (!ReservationSupport::isRepeatable($model->rrule, ($svrStartDate . $svrStartTime),
			$eventData['ReservationEvent']['timezone'], $model->isOverMaxRruleIndex)) {
			CakeLog::debug("DBG: 繰返しがとまったので、callから復帰する。");
			//繰返しがとまったので、callから復帰する。
			return true;
		}

		CakeLog::debug("DBG: insert(svrStartDateTime[" . $svrStartDate . $svrStartTime . "]
			svrEndDateTime[" . $svrEndDate . $svrEndTime . "])実行");
		$rEventData = $this->insert($model, $planParams, $rruleData, $eventData,
			($svrStartDate . $svrStartTime), ($svrEndDate . $svrEndTime), $createdUserWhenUpd);
		if ($rEventData['ReservationEvent']['id'] === null) {
			return false;
		}

		return $this->insertMonthlyByMonthday($model, $planParams, $rruleData, $rEventData,
			$bymonthday, 0, $createdUserWhenUpd);
	}

/**
 * 月周期の登録（月単位－第Ｎ週Ｍ曜日)
 *
 * @param Model $model 実際のモデル名
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $eventData eventデータ(ReservationEventのモデルデータ)
 * @param int $first 最初のデータかどうか 1:最初である  0:最初ではない
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return mixed boolean true:登録せず終了 false:失敗、array 登録成功: array(登録した開始年月日時分秒, 登録した終了年月日時分秒)
 */
	public function insertMonthlyByDay(Model $model, $planParams, $rruleData, $eventData,
		$first = 0, $createdUserWhenUpd = null) {
		CakeLog::debug("DBG: insertMonthlyByDay() start. rrule[INDEX]=[" . $model->rrule['INDEX'] . "]");
		$model->rrule['INDEX']++;

		//ユーザタイムゾーンを取得しておく。
		$userTz = (new NetCommonsTime())->getUserTimezone();

		//setStimeEtimeAndByday()返ってくる$sTime, $eTime, $bydayはすべてサーバー系日付時刻です。
		$this->setStimeEtimeAndByday($model->rrule, $eventData, $first, $userTz, $sTime, $eTime, $byday);

		CakeLog::debug("DBG: setStimeEtimeAndByday(first[" . $first . "] userTz[" . $userTz . "])
			結果. sTime[" . $sTime . "] eTime[" . $eTime . "] byday[" . $byday . "]");

		//call復帰条件のチェック
		if ($first && $sTime >= $byday) {
			CakeLog::debug("DBG: first[" . $first . "] is TRUE and
				sTime[" . $sTime . "] >= byday[" . $byday, "]. i DEC and ReCall.");
			//開始日(対象日？）が繰返しENDのb(第x週第y曜日の実日）を超したら、行き過ぎなので、INDEXをデクリメントして、自分を再帰callする。
			$model->rrule['INDEX']--;
			return $this->insertMonthlyByDay($model, $planParams, $rruleData, $eventData,
				0, $createdUserWhenUpd);
		}

		//setStartDateTiemAndEndDateTime()より返される時刻系はサーバー系です
		$svrStartDate = $svrStartTime = $svrEndDate = $svrEndTime = '';
		$this->setStartDateTiemAndEndDateTime($sTime, $eTime, $byday, $userTz, $svrStartDate,
			$svrStartTime, $svrEndDate, $svrEndTime);

		CakeLog::debug("DBG: setStartDateTiemAndEndDateTime処理結果.
			svrStartDate[" . $svrStartDate . "] svrStartTime[" . $svrStartTime . "]
			svrEndDate[" . $svrEndDate . "] svrEndTime[" . $svrEndTime . "]");

		if (!ReservationSupport::isRepeatable($model->rrule, ($svrStartDate . $svrStartTime),
			$eventData['ReservationEvent']['timezone'], $model->isOverMaxRruleIndex)) {

			CakeLog::debug("DBG: isRepeatable() がFALSEを返したので、
				繰返しをとめて復帰します。");

			//繰り返しがとまったので、復帰いたします。
			return true;
		}

		CakeLog::debug("DBG: insert(svrStartDateTime[" . $svrStartDate . $svrStartTime . "]
			svrEndDateTime[" . $svrEndDate . $svrEndTime . "])実行");

		$rEventData = $this->insert($model, $planParams, $rruleData, $eventData,
			($svrStartDate . $svrStartTime), ($svrEndDate . $svrEndTime), $createdUserWhenUpd);
		if ($rEventData['ReservationEvent']['id'] === null) {
			return false;
		}

		return $this->insertMonthlyByDay($model, $planParams, $rruleData, $rEventData,
			0, $createdUserWhenUpd);
	}

/**
 * sTime,eTimeおよびbydayの設定
 *
 * @param array $rrule rrule配列
 * @param array $eventData eventData
 * @param int $first first 1:最初のデータ 0:最初のデータでない
 * @param string $userTz userTz ユーザー系のタイムゾーンID
 * @param string &$sTime sTime サーバー系開始日付時刻をセットして返す。
 * @param string &$eTime eTime サーバー系終了日付時刻をセットして返す。
 * @param string &$byday byday
 * @return void
 */
	public function setStimeEtimeAndByday($rrule, $eventData, $first, $userTz, &$sTime,
		&$eTime, &$byday) {
		//BYDAYは'2MO','3SA'といった形式である
		//よって、wdayNumにはSUなら0, SAなら6とった値になる。
		$wdayNum = array_search(substr($rrule['BYDAY'][0], -2), self::$reservationWdayArray);
		//よって、weekには、、最後２文字を取り除いた、第x月曜、第y土曜のx、yが取り出せる。
		$week = intval(substr($rrule['BYDAY'][0], 0, -2));

		//NC3は内部はサーバー系時刻なのでtimezoneDateはつかわない
		//このsTime, eTimeはcall元に返すための変数.
		$sTime = $eventData['ReservationEvent']['start_date'] .
			$eventData['ReservationEvent']['start_time']; //catしてYmdHisにする
		$eTime = $eventData['ReservationEvent']['end_date'] .
			$eventData['ReservationEvent']['end_time']; //catしてYmdHisにする

		//以下で使うmkdateの「1日00:00:00」とは、画面上（=ユーザー系）でのカレンダ
		//日付時刻をさしているので、ユーザー系に直す。
		//
		$userStartTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($sTime));
		$userStartTime = ReservationTime::dt2calDt($userStartTime);

		//ユーザー系開始日の同年インターバル月数考慮月1日のtimestampを求める。
		$date = new DateTime('now', (new DateTimeZone($userTz)));
		$date->setDate(substr($userStartTime, 0, 4),
			substr($userStartTime, 4, 2) + ($first ? 0 : $rrule['INTERVAL']), 1);
		$date->setTime(0, 0, 0);
		$timestamp = $date->getTimestamp();

		//タイムスタンプとユーザタイムゾーンを引数にわたしてgetByday()をcallする。
		//getByday()はサーバ系のYmdHis形式の文字列を返す。
		$byday = ReservationSupport::getByday($timestamp, $week, $wdayNum, $userTz);
	}

/**
 * __setMonthlyByMonthdayStartDtProc
 *
 * 月周期のbymonthdayでの開始日処理
 *
 * @param array &$eventData eventData
 * @param string &$userStartTime userStartTime
 * @param int &$startTimestamp startTimestamp
 * @param string &$userTz userTz
 * @param int &$currentDay currentDay
 * @param int &$first first
 * @param Model $model model
 * @param int &$interval interval
 * @param int &$intervalDay intervalDay
 * @return void
 */
	private function __setMonthlyByMonthdayStartDtProc(&$eventData, &$userStartTime,
		&$startTimestamp, &$userTz, &$currentDay, &$first, $model, &$interval, &$intervalDay) {
		//NC3は内部はサーバー系時刻なのでtimezoneDateはつかわない
		$sTime = $eventData['ReservationEvent']['start_date'] .
			$eventData['ReservationEvent']['start_time']; //catしてYmdHisにする

		//以下で使う時間系は「1日00:00:00」など、画面上（=ユーザー系）でのカレンダ
		//日付時刻をさしているので、ユーザー系に直す。
		//
		$userStartTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($sTime));
		$userStartTime = ReservationTime::dt2calDt($userStartTime);

		//ユーザー系開始日の00:00:00のタイムスタンプを取得
		$date = new DateTime('now', (new DateTimeZone($userTz)));
		$date->setDate(substr($userStartTime, 0, 4), substr($userStartTime, 4, 2),
			substr($userStartTime, 6, 2));
		$date->setTime(0, 0, 0);
		$startTimestamp = $date->getTimestamp();

		//ユーザー系開始日をつかった、インターバル月数の計算
		$currentDay = intval(substr($userStartTime, 6, 2));

		if ($first && $currentDay < $model->rrule['BYMONTHDAY'][0]) {
			$interval = 0;
		} else {
			$interval = $model->rrule['INTERVAL'];
		}

		//ユーザー系開始日をつかった、指定月の１日（ついたち）の日付時刻の計算(指定月はインターバル月数を考慮して計算）
		$date = new DateTime('now', (new DateTimeZone($userTz)));
		$date->setDate(substr($userStartTime, 0, 4), substr($userStartTime, 4, 2) + $interval, 1);
		$date->setTime(0, 0, 0);
		//$firstTimestamp = $date->getTimestamp();
		$firstNumOfDaysOfMth = $date->format('t'); //指定月の日数y (28-31)
		if ($model->rrule['BYMONTHDAY'][0] > $firstNumOfDaysOfMth) {
			$intervalDay = $model->rrule['BYMONTHDAY'][0] - $firstNumOfDaysOfMth;
			//毎月x日のxが、指定月の日数(y)より大きき時、その差分日数(x-y)をインターバイル日数とする。
		} else {
			$intervalDay = 0;
			//毎月x日のxが、指定月の日数(y)以下の時、インターバイル日数は０（なし）とする。
		}
	}
}
