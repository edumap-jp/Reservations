<?php
/**
 * ReservationYearlyEntry Behavior
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
 * ReservationYearlyEntryBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model\Behavior
 */
class ReservationYearlyEntryBehavior extends ReservationAppBehavior {

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
 * 年周期の登録
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param ssary $rruleData rruleData
 * @param array $eventData eventデータ(ReservationEventのモデルデータ)
 * @param int $first 最初のデータかどうか 1:最初である  0:最初ではない
 * @param int $bymonthday bymonthday
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return array $result 結果
 */
	public function insertYearly(Model &$model, $planParams, $rruleData, $eventData,
		$first = 0, $bymonthday = 0, $createdUserWhenUpd = null) {
		//CakeLog::debug("DBG: insertYearly(first[" . $first . "] bymonthday[" . $bymonthday . "] start.");

		//ユーザタイムゾーンを取得しておく。
		$userTz = (new NetCommonsTime())->getUserTimezone();

		//setStartEndDateAndTime()が返す日と時はサーバー系
		list($startDate, $startTime, $endDate, $endTime, $diffNum) =
			$this->setStartEndDateAndTime($model, $eventData, $first, $userTz);
		//CakeLog:debug("DBG: setStartEndDateAndTime()の結果. startDate[" . $startDate .
		//	"] startTime[" . $startTime . "] endDate[" . $endDate . "] endTime[" . $endTime .
		//	"] diffNum[" . $diffNum . "]");

		//CakeLog::debug("DBG: before isRepeatable(). startDateTime[" .
		//	$startDate . $startTime . "]");

		if (!ReservationSupport::isRepeatable($model->rrule,
			($startDate . $startTime), $eventData['ReservationEvent']['timezone_offset'],
			$model->isOverMaxRruleIndex)) {
			//CakeLog::debug("DBG: isRepeatable() がFALSEを返した. よって復帰する.");
			//insertYearly()は再帰callされるが、ここ(isRepeatable()===falseになった時、復帰する。
			return true;
		}

		if ($first && empty($model->rrule['BYDAY'])) {	//rrule['BYDAY']は配列です
			//サーバー系時刻の文字列から日の部分をそのまま切り出し$monthdayとして
			//使うことはできない。なぜなら、monthdayの日は、施設予約上(＝ユー
			//ザ系)が代入されることを前提しているため。よって、ユーザー系になおして
			//から、monthdayを代入する。
			//
			$userStartDateTime = (new NetCommonsTime())->toUserDatetime(
				ReservationTime::calDt2dt($startDate . $startTime));
			$userStartDateTime = ReservationTime::dt2calDt($userStartDateTime);

			//最初で、かつ、日の間隔ルールがないなら、ユーザー系開始日付時刻の日
			//をbymonthdayとする。
			$bymonthday = intval(substr($userStartDateTime, 6, 2));
			//CakeLog::debug("DBG: rrule[BYDAY] is EMPTY! bymonthday[" . $bymonthday . "]");
		}

		$startDateTime = $endDateTime = '';
		$dtArray = array($startDate, $startTime, $endDate, $endTime);

		//CakeLog::debug("DBG: before setStartAndEndDateTimeEtc() first[" . $first .
		//	"] bymonthday[" . $bymonthday . "] diffNum[" . $diffNum . "] userTz[" . $userTz . "]");

		$etcArray = array($userTz, $createdUserWhenUpd);
		$ret = $this->setStartAndEndDateTimeEtc($model, $planParams,
			$rruleData, $dtArray, $first, $bymonthday, $diffNum, $eventData, $etcArray);

		if ($ret === false) {
			//CakeLog::debug(
			//	"DBG: setStartAndEndDateTimeEtc()がFALSEを返したので、return falseします。");
			return false;
		}
		//startDateTime,endDateTimeはサーバー系時刻YmdHisで返ってくる。
		list($eventData, $startDateTime, $endDateTime) = $ret;

		//CakeLog::debug("DBG: after setStartAndEndDateTimeEtc()結果. startDateTime[" .
		//	$startDateTime . "] endDateTime[" . $endDateTime . "]");

		//
		//eventDataの開始・終了の日付と時刻を更新するしてから、insertYearly()を再帰callする。
		//
		//NC3では内部はサーバ系日付時刻なので、timezoneDateはつかわず、YmdHisを単純にYmdとHisに分割する。
		$eventData['ReservationEvent']['start_date'] = substr($startDateTime, 0, 8);
		$eventData['ReservationEvent']['start_time'] = substr($startDateTime, 8);
		$eventData['ReservationEvent']['end_date'] = substr($endDateTime, 0, 8);
		$eventData['ReservationEvent']['end_time'] = substr($endDateTime, 8);

		//CakeLog::debug("DBG: eventData[ReservationEvent]の各項目。start_date[" .
		//	$eventData['ReservationEvent']['start_date'] . "] start_time[" .
		//	$eventData['ReservationEvent']['start_time'] . "] end_date[" .
		//	$eventData['ReservationEvent']['end_date'] . "] end_time[" .
		//	$eventData['ReservationEvent']['end_time'] . "]");

		if (!empty($model->rrule['BYDAY']) && count($model->rrule['BYDAY']) > 0) {

			//CakeLog::debug("DBG: rrule[BYDAY]がemptyで無く、且つ、rrule[BYDAY]の要素数[" .
			//	count($model->rrule['BYDAY']) . "]が0より大きい時の、insertYearly() 再帰call.");

			//insertYearly()の再帰call ケース1
			return $this->insertYearly($model, $planParams, $rruleData, $eventData,
				0, 0, $createdUserWhenUpd);
		} else {

			//CakeLog::debug("DBG: もう一方の時の、insertYearly() 再帰call. 0 bymonthday[" .
			//	$bymonthday . "]");

			//insertYearly()の再帰call ケース2
			return $this->insertYearly($model, $planParams, $rruleData, $eventData,
				0, $bymonthday, $createdUserWhenUpd);
		}
	}

/**
 * 開始と終了の日付、時刻の生成
 *
 * @param Model &$model モデル
 * @param array $eventData event配列データ
 * @param int $first 最初のデータかどうか. 1:最初 0:最初ではない
 * @param string $userTz ユーザー系タイムゾーンID ('Asia/Tokyo')
 * @return array サーバー系の各日と時と差分日数の配列($startDate, $startTime, $endDate, $endTime, $diffNum)を返す
 */
	public function setStartEndDateAndTime(Model &$model, $eventData, $first, $userTz) {
		//NC3では内部はサーバー系時刻になっているのでtimezoneDateはつかわない。
		$sTime = $eventData['ReservationEvent']['start_date'] .
			$eventData['ReservationEvent']['start_time']; //catしてYmdHisにする
		$eTime = $eventData['ReservationEvent']['end_date'] .
			$eventData['ReservationEvent']['end_time']; //catしてYmdHisにする

		//以下で使うmkdateの「1日00:00:00」とは、画面上（=ユーザー系）でのカレンダ
		//日付時刻をさしているので、ユーザー系に直す。
		//
		$userStartTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($sTime));
		$userStartTime = ReservationTime::dt2calDt($userStartTime);
		$userEndTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($eTime));
		$userEndTime = ReservationTime::dt2calDt($userEndTime);

		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系TZでDateTimeObj生成
		$date->setDate(substr($userStartTime, 0, 4),
			substr($userStartTime, 4, 2), substr($userStartTime, 6, 2));
		$date->setTime(0, 0, 0);
		$startTimestamp = $date->getTimestamp();

		$date->setDate(substr($userEndTime, 0, 4),
			substr($userEndTime, 4, 2), substr($userEndTime, 6, 2));
		$date->setTime(0, 0, 0);
		$endTimestamp = $date->getTimestamp();

		//ユーザー系開始日付(00:00:00)と終了日付(00:00:00)のタイムスタンプから、その「差分日数」を計算する。
		//
		$diffNum = ($endTimestamp - $startTimestamp) / 86400;	// a

		if ($first) {
			//初回の時は、userStartTime, userEneTimeのタイムスタンプをそのまま使う。
			//
			$date->setDate(substr($userStartTime, 0, 4),
				substr($userStartTime, 4, 2), substr($userStartTime, 6, 2));
			$date->setTime(substr($userStartTime, 8, 2),
				substr($userStartTime, 10, 2), substr($userStartTime, 12, 2));
			$startTimestamp = $date->getTimestamp();

			$date->setDate(substr($userEndTime, 0, 4),
				substr($userEndTime, 4, 2), substr($userEndTime, 6, 2));
			$date->setTime(substr($userEndTime, 8, 2),
				substr($userEndTime, 10, 2), substr($userEndTime, 12, 2));
			$endTimestamp = $date->getTimestamp();

		} else {
			//２回目以降は、startTimestampはインターバル考慮した年の1月1日
			//endTimestampはインターバル考慮した年の1月(1+差分日数(a))日
			//
			$date->setDate(substr($userStartTime, 0, 4) + $model->rrule['INTERVAL'], 1, 1);
			$date->setTime(substr($userStartTime, 8, 2),
				substr($userStartTime, 10, 2), substr($userStartTime, 12, 2));
			$startTimestamp = $date->getTimestamp();

			$date->setDate(substr($userEndTime, 0, 4) + $model->rrule['INTERVAL'], 1, 1 + $diffNum);
			$date->setTime(substr($userEndTime, 8, 2),
				substr($userEndTime, 10, 2), substr($userEndTime, 12, 2));
			$endTimestamp = $date->getTimestamp();
		}

		//TZをユーザー系からサーバー系に切り替える
		$date->setTimeZone(new DateTimeZone('UTC'));
		$date->setTimestamp($startTimestamp);
		$startDate = $date->format('Ymd');
		$startTime = $date->format('His');

		$date->setTimestamp($endTimestamp);
		$endDate = $date->format('Ymd');
		$endTime = $date->format('His');

		return array($startDate, $startTime, $endDate, $endTime, $diffNum);
	}

/**
 * 開始・終了の日付と時刻等をセットする。
 *
 * @param Model &$model モデル
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $dtArray サーバ系時刻startDate,startTime,endDate,endTimeをカプセル化した配列
 * @param int $first first 最初かどうか。1:最初 0:最初でない
 * @param int $bymonthday bymonthday 毎月ｘ日のx日のこと(ユーザー系の日であることに注意すること）
 * @param int $diffNum diffNum開始日と終了日の差分日数
 * @param array $eventData eventData配列データ
 * @param array $etcArray userTz(ユーザー系タイムゾーンID)とcreatedUserWhenUpdをカブセル化した配列
 * @return mixed 成功時、array($eventData, サーバ系時刻$startDateTime, サーバ系時刻$endDateTime)を返す。失敗時 falseを返す。
 */
	public function setStartAndEndDateTimeEtc(Model &$model, $planParams, $rruleData,
		$dtArray, $first, $bymonthday, $diffNum, $eventData, $etcArray) {
		list($startDate, $startTime, $endDate, $endTime) = $dtArray;
		list($userTz, $createdUserWhenUpd) = $etcArray;

		$result = true;
		$userStartDateTime = (new NetCommonsTime())->toUserDatetime(
			ReservationTime::calDt2dt($startDate . $startTime));
		$userStartDateTime = ReservationTime::dt2calDt($userStartDateTime);

		//ユーザー系開始日付の月を、現在の月とする。
		$currentMonth = intval(substr($userStartDateTime, 4, 2));
		foreach ($model->rrule['BYMONTH'] as $month) {
			//currentMonthはユーザー系の月、一方rrule['BYMONTH']の配列値($month)も
			//ユーザー系の月。よって、比較してもOK.
			if ($first && $currentMonth > $month) {
				continue;
			}

			////$this->setDtStartendData($first, $currentMonth, $month, $startDate, $startTime, $endDate, $endTime, $diffNum, $userTz, $eventData);
			$workParams = array($first, $currentMonth, $month, $startDate, $startTime,
				$endDate, $endTime, $diffNum, $userTz);
			$this->setDtStartendData($workParams, $eventData);

			if (!empty($model->rrule['BYDAY']) && count($model->rrule['BYDAY']) > 0) {
				$result = $this->insertYearlyByday($model, $planParams, $rruleData, $eventData,
					$first, $createdUserWhenUpd);
			} else {
				$result = $this->insertYearlyByMonthday($model, $planParams, $rruleData, $eventData,
					$bymonthday, $first, $createdUserWhenUpd);
			}
			if ($result === false) {
				return false;
			}
		}
		$startDateTime = $startDate . $startTime;
		$endDateTime = $endDate . $endTime;
		if (is_array($result)) {
			list($eventData, $startDateTime, $endDateTime) = $result;
		}

		return array($eventData, $startDateTime, $endDateTime);
	}

/**
 * eventDataへのデータセット
 *
 * @param array $workParams 各種変数を格納しているwork配列
 * @param array &$eventData eventData
 * @return void
 */
	public function setDtStartendData($workParams, &$eventData) {
		//work配列より、以下の変数を復元
		//
		//int $first 初回かどうか (1:初回 0:２回目以降)
		//int $currentMonth currentMonth ユーザー系のカレント月
		//int $month month ユーザー系の月
		//string $startDate startDate サーバー系開始日のYmd
		//string $startTime startTime サーバ系開始日のHis
		//string $endDate endDate サーバー系終了日のYmd
		//string $endTime endTime サーバー系終了日のHis
		//int $diffNum diffNum
		//string $userTz ユーザー系タイムゾーンID ('Asia/Tokyo')
		list($first, $currentMonth, $month, $startDate, $startTime, $endDate, $endTime,
			$diffNum, $userTz) = $workParams;

		if ($first && $currentMonth == $month) {
			//startDate,startTime,endDate,endTimeはすべてサーバー系なので直接
			//$eventData['ReservationEvent']の該当項目に直接代入する。
			//
			$eventData['ReservationEvent']['start_date'] = $startDate;
			$eventData['ReservationEvent']['start_time'] = $startTime;
			$eventData['ReservationEvent']['end_date'] = $endDate;
			$eventData['ReservationEvent']['end_time'] = $endTime;
		} else {
			//startDate,startTime,endDate,endTimeはサーバー系時間であり、施設予約上
			//(=ユーザー系)の時間ベースで日付加工していたNC2ベースのロジックでは、
			//時差分の誤差がでる。依って、一旦ユーザー系に変換し、日付加工処理を行ない
			//その結果を、サーバー系時間に戻してからeventData['ReservationEvent']の該当
			//項目に代入する。

			//まずは、start,endをユーザー系に直しておく。
			$userStartDateTime = (new NetCommonsTime())->toUserDatetime(
				ReservationTime::calDt2dt($startDate . $startTime));
			$userStartDateTime = ReservationTime::dt2calDt($userStartDateTime);
			$userEndDateTime = (new NetCommonsTime())->toUserDatetime(
				ReservationTime::calDt2dt($endDate . $endTime));
			$userEndDateTime = ReservationTime::dt2calDt($userEndDateTime);

			//ユーザー系の開始日の年＆指定月１日を生成し、サーバー系に変換後、$eventData['ReservationEvent']の
			//start_dateとstart_time項目に代入する。
			//
			$date = new DateTime('now', new DateTimeZone($userTz));
			$date->setDate(substr($userStartDateTime, 0, 4), $month, 1);
			$date->setTime(substr($userStartDateTime, 8, 2),
				substr($userStartDateTime, 10, 2), substr($userStartDateTime, 12, 2));
			$date->setTimeZone(new DateTimeZone('UTC'));	//サーバー系TZに変換する
			$eventData['ReservationEvent']['start_date'] = $date->format('Ymd');
			$eventData['ReservationEvent']['start_time'] = $date->format('His');

			//ユーザー系の開始日の年＆指定月の(1+差分日数)日＋ユーザー系終了日の時刻を生成し、
			//サーバー系に変換後、$eventData['ReservationEvent']のend_dateとend_time項目に代入する。
			//
			$date = new DateTime('now', new DateTimeZone($userTz));
			$date->setDate(substr($userStartDateTime, 0, 4), $month, 1 + $diffNum);
			$date->setTime(substr($userEndDateTime, 8, 2),
				substr($userEndDateTime, 10, 2), substr($userEndDateTime, 12, 2));
			$date->setTimeZone(new DateTimeZone('UTC'));	//サーバー系TZに変換する
			$eventData['ReservationEvent']['end_date'] = $date->format('Ymd');
			$eventData['ReservationEvent']['end_time'] = $date->format('His');
		}
	}

/**
 * 年周期の登録（年単位－開始日と同日）
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $eventData eventデータ(ReservationEventのモデルデータ)
 * @param int $bymonthday bymonthday
 * @param int $first 最初のデータかどうか 1:最初である  0:最初ではない
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return mixed boolean true:登録せず終了 false:失敗、array 登録成功: array(insertした結果のrEventData, 登録したサーバ系開始年月日時分秒, 登録したサーバ系終了年月日時分秒)
 */
	public function insertYearlyByMonthday(Model &$model, $planParams, $rruleData, $eventData,
		$bymonthday, $first, $createdUserWhenUpd = null) {
		//CakeLog::debug("DBG: insertYearlyByMonthday() start. bymonthday[" . $bymonthday .
		//	"] first[" . $first . "] rrule[INDEX]=[" . $model->rrule['INDEX'] . "]");

		$model->rrule['INDEX']++;

		//ユーザタイムゾーンを取得しておく。
		$userTz = (new NetCommonsTime())->getUserTimezone();

		//開始日付時刻の処理
		$userStartTime = $userEndTime = $firstNumOfDaysOfMth = '';
		$startTimestamp = $endTimestamp = $currentDay = $intervalDay = 0;

		$this->__setYearlyByMonthdayStartDtProc($eventData, $userStartTime, $startTimestamp,
			$currentDay, $firstNumOfDaysOfMth, $bymonthday, $first, $intervalDay, $userTz);
		//CakeLog::debug("DBG: bymonthday[" . $bymonthday . "] firstNumOfDaysOfMth[" .
		//	$firstNumOfDaysOfMth . "] intervalDay[" . $intervalDay . "]");

		//CakeLog::debug("DBG: call復帰条件: first [" . $first . "] currentDay[" . $currentDay .
		//	"] bymonthday[" . $bymonthday . "]");

		//call復帰条件のチェック
		if ($first && $currentDay >= $bymonthday) {
			//CakeLog::debug("DBG: 復帰条件 (first && currentDay >= bymonthday) が" .
			//	"真になったので、call復帰します。");

			//現在日が毎月ｘ日のｘ日を超したら、行き過ぎなので、INDEXをデクリメントして、callから復帰する。
			$model->rrule['INDEX']--;
			return true;
		}

		//終了日付時刻の処理
		$this->__setYearlyByMonthdayEndDtProc($eventData, $userTz, $userEndTime, $endTimestamp);

		//開始日と終了日の差分日数の計算
		$diffNum = ($endTimestamp - $startTimestamp) / 86400;

		//毎月ｘ日のｘ日を考慮した開始日付時刻の実日付時刻を計算
		//
		//(毎月ｘ日のｘ日が、開始日の日と同じかより後ならその差分の日数(=インターバル日数)を引くので、開始日の日をつかった実日数計算になる。）
		//(毎月ｘ日のｘ日が、開始日の日より前ならその差分の日数(=インターバル日数)は０なので、毎月ｘ日のｘ日をつかった実日数計算になる。）
		//
		////$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系TZのDateTimeObjを生成
		////$date->setDate(substr($userStartTime, 0, 4),
		////	substr($userStartTime, 4, 2), $bymonthday - $intervalDay);
		////$date->setTime(substr($userStartTime, 8, 2),
		////	substr($userStartTime, 10, 2), substr($userStartTime, 12, 2));
		$date = (new ReservationTime())->getDtObjWithTzDateTime($userTz,
			substr($userStartTime, 0, 4),
			substr($userStartTime, 4, 2), $bymonthday - $intervalDay,
			substr($userStartTime, 8, 2), substr($userStartTime, 10, 2), substr($userStartTime, 12, 2));
		//$startTimestamp = $date->getTimestamp();
		$date->setTimezone(new DateTimeZone('UTC'));	//サーバー系TZに切り替える
		$svrStartDate = $date->format('Ymd');
		$svrStartTime = $date->format('His');

		//毎月ｘ日のｘ日を考慮した終了日付時刻の実日付時刻を計算
		//
		//(毎月ｘ日のｘ日が、開始日の日と同じかより後ならその差分の日数(=インターバル日数)を引くので、開始日の日をつかった実日数計算になる。）
		//(毎月ｘ日のｘ日が、開始日の日より前ならその差分の日数(=インターバル日数)は０なので、毎月ｘ日のｘ日をつかった実日数計算になる。）
		//(上記２ケースとも、日に開始日と終了日の差分日数を加算しているので、終了の日となる。）
		//
		////$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系TZのDateTimeObjを生成
		////$date->setDate(substr($userStartTime, 0, 4),
		////	substr($userStartTime, 4, 2), $bymonthday - $intervalDay + $diffNum);
		////$date->setTime(substr($userEndTime, 8, 2),
		////	substr($userEndTime, 10, 2), substr($userEndTime, 12, 2));
		$date = (new ReservationTime())->getDtObjWithTzDateTime($userTz,
			substr($userStartTime, 0, 4), substr($userStartTime, 4, 2),
			$bymonthday - $intervalDay + $diffNum,
			substr($userEndTime, 8, 2), substr($userEndTime, 10, 2), substr($userEndTime, 12, 2));
		//$endTimestamp = $date->getTimestamp();
		$date->setTimezone(new DateTimeZone('UTC'));	//サーバー系TZに切り替える
		$svrEndDate = $date->format('Ymd');
		$svrEndTime = $date->format('His');

		//CakeLog::debug("DBG: diffNum[" . $diffNum . "] svrStartDate[" . $svrStartDate .
		//	"] svrStartTime[" . $svrStartTime . "] svrEndDate[" .
		//	$svrEndDate . "] svrEndTime[" . $svrEndTime . "]");

		if (!ReservationSupport::isRepeatable($model->rrule, ($svrStartDate . $svrStartTime),
			$eventData['ReservationEvent']['timezone_offset'], $model->isOverMaxRruleIndex)) {
			//繰返しがとまったので、callから復帰する。
			return true;
		}

		//CakeLog::debug("DBG: insert(svrStartDateTime[" . $svrStartDate . $svrStartTime .
		//	"] svrEndDateTime[" . $svrEndDate . $svrEndTime . "])実行");

		$rEventData = $this->insert($model, $planParams, $rruleData, $eventData,
			($svrStartDate . $svrStartTime), ($svrEndDate . $svrEndTime), $createdUserWhenUpd);
		if ($rEventData['ReservationEvent']['id'] === null) {
			return false;
		} else {
			return array($rEventData, ($svrStartDate . $svrStartTime), ($svrEndDate . $svrEndTime));
		}
	}

/**
 * 年周期の登録（年単位－第Ｍ週Ｎ曜日)
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $eventData eventデータ(ReservationEventのモデルデータ)
 * @param int $first 最初のデータかどうか 1:最初である  0:最初ではない
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return mixed boolean true:登録せず終了 false:失敗、array 登録成功: array(insertした結果のrEventData, 登録したサーバ系開始年月日時分秒, 登録したサーバ系終了年月日時分秒)
 */
	public function insertYearlyByday(Model &$model, $planParams, $rruleData, $eventData,
		$first = 0, $createdUserWhenUpd = null) {
		//CakeLog::debug("DBG: insertYearlyByday() start. first[" . $first .
		//	"] rrule[INDEX]=[" . $model->rrule['INDEX'] . "]");

		$model->rrule['INDEX']++;

		//ユーザタイムゾーンを取得しておく。
		$userTz = (new NetCommonsTime())->getUserTimezone();

		//BYDAYは'2MO','3SA'といった形式である
		//よって、wdayNumにはSUなら0, SAなら6とった値になる。
		$wdayNum = array_search(substr($model->rrule['BYDAY'][0], -2), self::$reservationWdayArray);
		//よって、weekには、、最後２文字を取り除いた、第x月曜、第y土曜のx、yが取り出せる。
		$week = intval(substr($model->rrule['BYDAY'][0], 0, -2));	//-2で最後２文字をけずる。

		//NC3は内部はサーバー系時刻なのでtimezoneDateはつかわない
		$sTime = $eventData['ReservationEvent']['start_date'] .
				$eventData['ReservationEvent']['start_time'];
		$eTime = $eventData['ReservationEvent']['end_date'] . $eventData['ReservationEvent']['end_time'];

		//以下で使うmkdateの「1日00:00:00」とは、画面上（=ユーザー系）でのカレンダ
		//日付時刻をさしているので、ユーザー系に直す。
		//
		$userStartTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($sTime));
		$userStartTime = ReservationTime::dt2calDt($userStartTime);

		//ユーザー系開始日の同年同月1日の00:00:00のtimestampを求める。
		$date = new DateTime('now', (new DateTimeZone($userTz)));
		$date->setDate(substr($userStartTime, 0, 4), substr($userStartTime, 4, 2), 1);
		$date->setTime(0, 0, 0);
		$timestamp = $date->getTimestamp();

		//タイムスタンプとユーザタイムゾーンを引数にわたしてgetByday()をcallする。
		//getByday()はサーバ系のYmdHis形式の文字列を返す。
		$byday = ReservationSupport::getByday($timestamp, $week, $wdayNum, $userTz);

		//CakeLog::debug("DBG: call復帰条件: first [" . $first . "] sTime[" . $sTime .
		//	"] byday[" . $byday . "]");

		//call復帰条件のチェック
		if ($first && $sTime >= $byday) {
			//CakeLog::debug("DBG: 復帰条件 (first && sTime >= byday) が" .
			//	"真になったので、call復帰します。");

			//開始日(対象日？）が繰返しENDのb(第x週第y曜日の実日）を超したら、行き過ぎなので、INDEXをデクリメントして、callから復帰する。
			$model->rrule['INDEX']--;
			return true;
		}

		//setStartDateTiemAndEndDateTime()より返される時刻系はサーバー系です
		$svrStartDate = $svrStartTime = $svrEndDate = $svrEndTime = '';
		$this->setStartDateTiemAndEndDateTime($sTime, $eTime, $byday, $userTz,
			$svrStartDate, $svrStartTime, $svrEndDate, $svrEndTime);

		//CakeLog::debug("DBG: setStartDateTiemAndEndDateTime()結果. svrStartDate[" .
		//	$svrStartDate . "] svrStartTime[" . $svrStartTime . "] svrEndDate[" .
		//	$svrEndDate . "] svrEndTime[" . $svrEndTime . "]");

		if (!ReservationSupport::isRepeatable($model->rrule, ($svrStartDate . $svrStartTime),
			$eventData['ReservationEvent']['timezone_offset'], $model->isOverMaxRruleIndex)) {
			//CakeLog::debug("DBG: 繰返しがとまったので、復帰する。");

			//繰返しがとまったので、復帰する。
			return true;
		}

		//CakeLog::debug("DBG: insert(svrStartDateTime[" . $svrStartDate . $svrStartTime .
		//	"] svrEndDateTime[" . $svrEndDate . $svrEndTime . "])実行");

		$rEventData = $this->insert($model, $planParams, $rruleData, $eventData,
			($svrStartDate . $svrStartTime), ($svrEndDate . $svrEndTime), $createdUserWhenUpd);
		if ($rEventData['ReservationEvent']['id'] === null) {
			return false;
		}

		return array($rEventData, ($svrStartDate . $svrStartTime), ($svrEndDate . $svrEndTime));
	}

/**
 * __setYearlyByMonthdayStartDtProc
 *
 * 年周期のbymonthdayでの開始日処理
 *
 * @param array &$eventData eventData
 * @param string &$userStartTime userStartTime
 * @param int &$startTimestamp startTimestamp
 * @param string &$currentDay currentDay
 * @param mixed &$firstNumOfDaysOfMth firstNumOfDaysOfMth
 * @param int &$bymonthday bymonthday
 * @param int &$first first
 * @param int &$intervalDay intervalDay
 * @param string &$userTz userTz
 * @return void
 */
	private function __setYearlyByMonthdayStartDtProc(&$eventData, &$userStartTime, &$startTimestamp,
		&$currentDay, &$firstNumOfDaysOfMth, &$bymonthday, &$first, &$intervalDay, &$userTz) {
		$sTime = $eventData['ReservationEvent']['start_date'] .
				$eventData['ReservationEvent']['start_time'];

		//以下で使う時間系は「1日00:00:00」など、画面上（=ユーザー系）でのカレンダ
		//日付時刻をさしているので、ユーザー系に直す。
		//
		//$userStartTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($sTime));
		//$userStartTime = ReservationTime::dt2calDt($userStartTime);
		$userStartTime = (new ReservationTime())->svr2UserYmdHis($sTime);

		//ユーザー系開始日の00:00:00のタイムスタンプを取得
		$date = new DateTime('now', (new DateTimeZone($userTz)));
		$date->setDate(substr($userStartTime, 0, 4),
			substr($userStartTime, 4, 2), substr($userStartTime, 6, 2));
		$date->setTime(0, 0, 0);
		$startTimestamp = $date->getTimestamp();

		//ユーザー系開始日を使った、インターバル日数の計算
		$currentDay = intval(substr($userStartTime, 6, 2));

		//ユーザー系開始日の同年同月1日の00:00:00のタイムスタンプ算出
		////$date = new DateTime('now', (new DateTimeZone($userTz)));
		////$date->setDate(substr($userStartTime, 0, 4), substr($userStartTime, 4, 2), 1);
		////$date->setTime(0, 0, 0);
		$date = (new ReservationTime())->getDtObjWithTzDateTime($userTz,
			substr($userStartTime, 0, 4), substr($userStartTime, 4, 2), 1, 0, 0, 0);
		//$firstTimestamp = $date->getTimestamp();
		$firstNumOfDaysOfMth = $date->format('t');	//指定月の日数y (28-31)

		if ($bymonthday > $firstNumOfDaysOfMth) {
			//毎月ｘ日のｘ日が、開始日の日より後なら、その差分の日数を、インターバル日数とする。
			$intervalDay = $bymonthday - $firstNumOfDaysOfMth;
		} else {
			//毎月ｘ日のｘ日が、開始日の日と同じか前なら、インターバル日数を０とする。
			$intervalDay = 0;
		}
	}

/**
 * __setYearlyByMonthdayEndDtProc
 *
 * 年周期のbymonthdayでの終了日処理
 *
 * @param array &$eventData eventData
 * @param string &$userTz userTz
 * @param string &$userEndTime userEndTime
 * @param int &$endTimestamp endTimestamp
 * @return void
 */
	private function __setYearlyByMonthdayEndDtProc(&$eventData, &$userTz, &$userEndTime,
		&$endTimestamp) {
		//NC3は内部はサーバー系時刻なのでtimezoneDateはつかわない
		$eTime = $eventData['ReservationEvent']['end_date'] . $eventData['ReservationEvent']['end_time'];

		//以下で使う時間系は、00:00:00など画面上（=ユーザー系）でのカレンダ日付時刻を
		//さしているので、ユーザー系に直す。
		//
		//$userEndTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($eTime));
		//$userEndTime = ReservationTime::dt2calDt($userEndTime);
		$userEndTime = (new ReservationTime())->svr2UserYmdHis($eTime);

		//ユーザー系終了日の00:00:00のタイムスタンプを取得
		////$date = new DateTime('now', (new DateTimeZone($userTz)));
		////$date->setDate(substr($userEndTime, 0, 4),
		////	substr($userEndTime, 4, 2), substr($userEndTime, 6, 2));
		////$date->setTime(0, 0, 0);
		$date = (new ReservationTime())->getDtObjWithTzDateTime($userTz,
			substr($userEndTime, 0, 4),
			substr($userEndTime, 4, 2),
			substr($userEndTime, 6, 2), 0, 0, 0);
		$endTimestamp = $date->getTimestamp();
	}
}
