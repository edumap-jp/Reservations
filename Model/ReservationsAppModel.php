<?php
/**
 * Reservations App Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppModel', 'Model');
App::uses('ReservationRruleUtil', 'Reservations.Utility');
App::uses('Space', 'Rooms.Model');

/**
 * ReservationsApp Model
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model
 */
class ReservationsAppModel extends AppModel {

/**
 * getReadableRoomIds関数が何度も繰り返し呼び出された時のための保持変数
 *
 * @var array
 */
	protected $_readableRoomIds = null;

/**
 * getReadableRoomIdsWithOutPrivate()が何度も繰り返し呼び出された時のための保持変数
 *
 * @var array
 */
	protected $_notPrivateRoomIds = null;

/**
 * getReadableRoomIds
 *
 * 読み取り可能なルームのID配列を返す
 *
 * @return array
 */
	public function getReadableRoomIds() {
		// 読み取り可能なルームを取得
		// 読み取り可能なルームはフレームが異なろうとも、１アクセス中で変わることはないので
		// 保存して使いまわす
		if (! is_null($this->_readableRoomIds)) {
			return $this->_readableRoomIds;
		}
		$this->Room = ClassRegistry::init('Rooms.Room', true);
		$condition = $this->Room->getReadableRoomsConditions();
		$roomBase = $this->Room->find('all', $condition);
		$roomIds = Hash::combine($roomBase, '{n}.Room.id', '{n}.Room.id');
		// 施設予約は特別にプライベートスペースIDを入れる
		// 施設予約は特別に全会員向けルームIDを入れる
		if (Current::read('User.id')) {
			if (Hash::extract($roomBase, '{n}.Room[space_id=' . Space::PRIVATE_SPACE_ID . ']')) {
				$privateRoomId = Space::getRoomIdRoot(Space::PRIVATE_SPACE_ID);
				$roomIds[$privateRoomId] = $privateRoomId;
			}
			$communityRoomId = Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID);
			$roomIds[$communityRoomId] = $communityRoomId;
		}
		$this->_readableRoomIds = $roomIds;
		return $roomIds;
	}

/**
 * プライベートルームを除外したアクセス可能なルームIDリスト
 *
 * @return array
 */
	public function getReadableRoomIdsWithOutPrivate() {
		if (! is_null($this->_notPrivateRoomIds)) {
			return $this->_notPrivateRoomIds;
		}

		$this->Room = ClassRegistry::init('Rooms.Room', true);
		$condition = $this->Room->getReadableRoomsConditions();
		$rooms = $this->Room->find('all', $condition);
		$roomIds = [];
		foreach ($rooms as $room) {
			if ($room['Room']['space_id'] !== Space::PRIVATE_SPACE_ID) {
				$roomIds[] = $room['Room']['id'];
			}
		}
		$this->_notPrivateRoomIds = $roomIds;

		return $roomIds;
	}

/**
 * _setAndMergeDateTime
 *
 * 日付時刻系のパラメータを整形し、予定パラメータにマージして返す
 *
 * @param array $planParam merge前の予定パラメータ
 * @param array $data POSTされたデータ
 * @return array 成功時 整形,merged後の予定パラメータ. 失敗時 例外をthrowする.
 * @throws InternalErrorException
 */
	protected function _setAndMergeDateTime($planParam, $data) {
		//詳細画面からのデータ
		//時間指定なしの場合
		//  [detail_start_datetime] => 2016-03-06 　ユーザ系日付（から）
		//  [detail_end_datetime] => 2016-03-08		ユーザ系日付（まで）
		//時間指定有の場合
		//  [detail_start_datetime] => 2016-03-06 09:00		ユーザ系日付時刻（秒はなし）（から）
		//  [detail_end_datetime] => 2016-03-08 12:00		ユーザ系日付時刻（秒はなし）（まで）

		if ($data[$this->alias]['enable_time']) {
			$planParam['is_allday'] = 0;
			//時間指定あり
			$nctm = new NetCommonsTime();

			$serverStartDatetime = $nctm->toServerDatetime(
			$data[$this->alias]['detail_start_datetime'] . ':00', $data[$this->alias]['timezone_offset']);
			$planParam['start_date'] =
				ReservationTime::stripDashColonAndSp(substr($serverStartDatetime, 0, 10));
			$planParam['start_time'] =
				ReservationTime::stripDashColonAndSp(substr($serverStartDatetime, 11, 8));
			$planParam['dtstart'] = $planParam['start_date'] . $planParam['start_time'];

			$serverEndDatetime =
				$nctm->toServerDatetime($data[$this->alias]['detail_end_datetime'] . ':00',
				$data[$this->alias]['timezone_offset']);
			$planParam['end_date'] =
				ReservationTime::stripDashColonAndSp(substr($serverEndDatetime, 0, 10));
			$planParam['end_time'] = ReservationTime::stripDashColonAndSp(substr($serverEndDatetime, 11, 8));
			$planParam['dtend'] = $planParam['end_date'] . $planParam['end_time'];
		} else {
			$planParam['is_allday'] = 1;
			//ユーザー系の開始日と終了日とタイムゾーンを、サーバ系の開始日の00:00:00から終了翌日の00:00:00に変換する
			// FUJI start, end が翌日に設定されているときはNextDateにするなとわたす
			list($serverStartDate, $serverEndNextDate) =
				(new ReservationTime())->convUserFromTo2SvrFromTo(
					$data[$this->alias]['detail_start_datetime'],
					$data[$this->alias]['detail_end_datetime'],
					$data[$this->alias]['timezone_offset'],
					$planParam['is_allday']);

			$planParam['start_date'] = ReservationTime::stripDashColonAndSp(substr($serverStartDate, 0, 10));
			$planParam['start_time'] = ReservationTime::stripDashColonAndSp(substr($serverStartDate, 11, 8));
			$planParam['dtstart'] = $planParam['start_date'] . $planParam['start_time'];

			$planParam['end_date'] = ReservationTime::stripDashColonAndSp(substr($serverEndNextDate, 0, 10));
			$planParam['end_time'] = ReservationTime::stripDashColonAndSp(substr($serverEndNextDate, 11, 8));
			$planParam['dtend'] = $planParam['end_date'] . $planParam['end_time'];
		}
		return $planParam;
	}

/**
 * _setAndMergeRrule
 *
 * Rruleのパラメータを整形し、予定パラメータにマージして返す
 *
 * @param array $planParam merge前の予定パラメータ
 * @param array $data POSTされたデータ
 * @return array 成功時 整形,merged後の予定パラメータ. 失敗時 例外をthrowする.
 * @throws InternalErrorException
 */
	protected function _setAndMergeRrule($planParam, $data) {
		//CakeLog::debug("DBGY: data[" . $this->alias . "]=[" . print_r($data[$this->alias], true), "]");

		$rrule = array();

		if ($data[$this->alias]['is_repeat']) {
			//$wdayArray = explode('|', ReservationsComponent::CALENDAR_REPEAT_WDAY);
			$repeatFreq = $data[$this->alias]['repeat_freq'];
			$rruleInterval = $data[$this->alias]['rrule_interval'];

			$rruleByday = $data[$this->alias]['rrule_byday'];
			$rruleBymonthday = $data[$this->alias]['rrule_bymonthday'];

			$rruleBymonth = $data[$this->alias]['rrule_bymonth'];

			$rruleTerm = $data[$this->alias]['rrule_term'];

			//validateはすでに終わっているので、以下では、データ加工のみに集中する。

			//
			//rruleTermの設定
			//
			if ($rruleTerm === 'COUNT') {
				$rruleCount = $data[$this->alias]['rrule_count'];
				//validateはすでにおわっているので省略
				$rrule['COUNT'] = $rruleCount;
			}

			if ($rruleTerm === 'UNTIL') {
				$this->__doRruleTermUntil($planParam, $data, $rrule, $repeatFreq, $rruleInterval,
				$rruleByday, $rruleBymonthday, $rruleBymonth, $rruleTerm);
			}

			//
			// repeatFreqの設定
			//
			switch ($repeatFreq) {
				case ReservationsComponent::CALENDAR_REPEAT_FREQ_DAILY:	//日単位
					$rrule['FREQ'] = $repeatFreq;
					$rrule['INTERVAL'] = intval($rruleInterval[$repeatFreq]);
					break;
				case ReservationsComponent::CALENDAR_REPEAT_FREQ_WEEKLY:	//週単位
					$rrule['FREQ'] = $repeatFreq;
					$rrule['INTERVAL'] = intval($rruleInterval[$repeatFreq]);
					$rrule['BYDAY'] = $rruleByday[$repeatFreq];
					break;
				case ReservationsComponent::CALENDAR_REPEAT_FREQ_MONTHLY:	//月単位
					$this->__doRruleRepeatFreqMonthly(
						$planParam, $data, $rrule, $repeatFreq, $rruleInterval,
						$rruleByday, $rruleBymonthday, $rruleBymonth, $rruleTerm);
					break;
				case ReservationsComponent::CALENDAR_REPEAT_FREQ_YEARLY:	//年単位
					$this->__doRruleRepeatFreqYearly(
						$planParam, $data, $rrule, $repeatFreq, $rruleInterval, $rruleByday,
						$rruleBymonthday, $rruleBymonth, $rruleTerm);
					break;
				default:
					$rrule['FREQ'] = 'NONE';
			}
		} else {
			//繰返しなし
			$rrule['FREQ'] = 'NONE';
		}
		//rrule配列を、concatRrule()を使って文字列化
		$planParam['rrule'] = (new ReservationRruleUtil())->concatRrule($rrule);

		return $planParam;
	}

/**
 * __doRruleTermUntil
 *
 * RruleTermUntilのrrule設定処理
 *
 * @param array &$planParam planParam
 * @param array &$data data
 * @param array &$rrule rrule 最終的な結果はこのrruleにセットする。
 * @param array &$repeatFreq  repeatFreq
 * @param array &$rruleInterval rruleInterval
 * @param mixed &$rruleByday rruleByday
 * @param mixed &$rruleBymonthday rruleBymonthday
 * @param array &$rruleBymonth rruleBymonth
 * @param array &$rruleTerm rruleTerm
 * @return void 参照引数$rruleに値をセットして返すので戻り値なし
 */
	private function __doRruleTermUntil(&$planParam, &$data, &$rrule, &$repeatFreq, &$rruleInterval,
		&$rruleByday, &$rruleBymonthday, &$rruleBymonth, &$rruleTerm) {
		//$rruleUntil = $data[$this->alias]['rrule_until'];
		//validateはすでにおわっているので省略

		//注釈） NC2の時は、UNTILを同日24:00:00から作り出していたが、NC3では、翌日00:00:00をUNTILとしている。
		//
		$untilDateS = $data[$this->alias]['rrule_until'] . ' 00:00:00'; //Y-m-d H:i:s形式
		$untilDateA = ReservationTime::transFromYmdHisToArray($untilDateS);
		list($yearOfNextDay, $monthOfNextDay, $nextDay) =
			ReservationTime::getNextDay($untilDateA['year'], $untilDateA['month'], $untilDateA['day']);
		$nextDayOfUntilDateS =
			sprintf("%04d-%02d-%02d 00:00:00", (int)$yearOfNextDay, (int)$monthOfNextDay,
				(int)$nextDay);
		//untilDateSの翌日00:00:00を作り出し、サーバー系に直す
		$nctm = new NetCommonsTime();
		$svrNxtDayOfUntilDtS =
			$nctm->toServerDatetime($nextDayOfUntilDateS, $data[$this->alias]['timezone_offset']);
		$ymdHis = ReservationTime::dt2CalDt($svrNxtDayOfUntilDtS);
		$rrule['UNTIL'] = substr($ymdHis, 0, 8) . 'T' . substr($ymdHis, 8);
	}

/**
 * __doRruleRepeatFreqMonthly
 *
 * RruleRepeatFreqの月のrrule設定処理
 *
 * @param array &$planParam planParam
 * @param array &$data data
 * @param array &$rrule rrule 最終的な結果はこのrruleにセットする。
 * @param array &$repeatFreq  repeatFreq
 * @param array &$rruleInterval rruleInterval
 * @param mixed &$rruleByday rruleByday
 * @param mixed &$rruleBymonthday rruleBymonthday
 * @param array &$rruleBymonth rruleBymonth
 * @param array &$rruleTerm rruleTerm
 * @return void 参照引数$rruleに値をセットして返すので戻り値なし
 */
	private function __doRruleRepeatFreqMonthly(
		&$planParam, &$data, &$rrule, &$repeatFreq, &$rruleInterval, &$rruleByday,
			&$rruleBymonthday, &$rruleBymonth, &$rruleTerm) {
		$rrule['FREQ'] = $repeatFreq;
		$rrule['INTERVAL'] = intval($rruleInterval[$repeatFreq]);

		//CakeLog::debug("DBG: MONTHLY case. repeatFreq[" . $repeatFreq . "]  rruleByday[" . $repeatFreq . "]=[" . print_r($rruleByday[$repeatFreq], true) . "]");

		if (isset($rruleByday) && isset($rruleByday[$repeatFreq])) {
			$this->__toArrayRrruleByanyway($rruleByday, $repeatFreq);
			$rrule['BYDAY'] = $this->__makeArrayOfRruleByDay($rruleByday, $repeatFreq);
		}
		if (isset($rruleBymonthday) && isset($rruleBymonthday[$repeatFreq])) {
			$this->__toArrayRrruleByanyway($rruleBymonthday, $repeatFreq);
			$rrule['BYMONTHDAY'] = $this->__makeArrayOfRruleByMonthday($rruleBymonthday, $repeatFreq);
		}
		//実際には、$byday, $bymonthdayいずれか一方は空配列で、他方に１要素のみ存在する形になることがvalidatorで保証されている。
	}

/**
 * __toArrayRrruleByanyway
 *
 * Rrule用汎用配列統一関数
 *
 * @param mixed &$rruleByanyway rruleByanyway
 * @param string &$repeatFreq repeatFreq
 * @return void 参照引数$rruleByanywayに値をセットして返すので戻り値なし
 */
	private function __toArrayRrruleByanyway(&$rruleByanyway, &$repeatFreq) {
		//NC3のcakePHPのViewの場合、singleSelectの場合、配列ではなく文字列で渡ってくる.
		//が、本プログラムの繰り返し処理は、rrule_byday[MONTHLY] or rrule_bymonthday[MONTHLY] or rrule_byday[YEARLY]
		//が配列であるとを想定して実装してある。
		//よって、文字列の場合、配列化する。
		if (!is_array($rruleByanyway[$repeatFreq])) {
			if (empty($rruleByanyway[$repeatFreq])) {	//空文字=未選択の場合
				$rruleByanyway[$repeatFreq] = array();
			} else {
				$rruleByanyway[$repeatFreq] = array($rruleByanyway[$repeatFreq]);
			}
		}
	}

/**
 * __makeArrayOfRruleByDay
 *
 * Rrule用BYDAY配列生成関数
 *
 * @param array &$rruleByday rruleByday
 * @param string &$repeatFreq repeatFreq
 * @return array 作成したbyday配列を返す
 */
	private function __makeArrayOfRruleByDay(&$rruleByday, &$repeatFreq) {
		$wdays = explode('|', ReservationsComponent::CALENDAR_REPEAT_WDAY); //SU,MO, ... ,SA

		$byday = array();
		foreach ($rruleByday[$repeatFreq] as $val) {
			$wday = substr($val, -2);	//4SA,-1MOより、"SA","MO"の部分を抜き出す。
			$num = intval(substr($val, 0, -2));	//4SA,-1MOより、"4","-1"の部分を抜き出し整数化
			if ($num === 0) {	//YEARYの「開始日と同日」($val==='')のケース
				$val = $wday;
			}
			if (!in_array($wday, $wdays)) {	//YEARYの「開始日と同日」指定時は代入しない
				continue;
			}
			//$wdayがSU-SAにあるかどうか、$numが-1以上4以下であるかのチェックは、すでにvalidateでおわっているので省略
			$byday[] = $val;
		}
		return $byday;
	}

/**
 * __makeArrayOfRruleByMonthday
 *
 * Rrule用BYMONTHDAY配列生成関数
 *
 * @param array &$rruleBymonthday rruleBymonthday
 * @param string &$repeatFreq repeatFreq
 * @return array 作成したbymonthday配列を返す
 */
	private function __makeArrayOfRruleByMonthday(&$rruleBymonthday, &$repeatFreq) {
		$bymonthday = array();
		foreach ($rruleBymonthday[$repeatFreq] as $val) {
			$val = intval($val);
			if ($val > 0 && $val <= 31) {
				$bymonthday[] = $val;
			}
		}
		return $bymonthday;
	}

/**
 * __doRruleRepeatFreqYearly
 *
 * RruleRepeatFreqの年のrrule設定処理
 *
 * @param array &$planParam planParam
 * @param array &$data data
 * @param array &$rrule rrule 最終的な結果はこのrruleにセットする。
 * @param array &$repeatFreq  repeatFreq
 * @param array &$rruleInterval rruleInterval
 * @param mixed &$rruleByday rruleByday
 * @param mixed &$rruleBymonthday rruleBymonthday
 * @param array &$rruleBymonth rruleBymonth
 * @param array &$rruleTerm rruleTerm
 * @return void 参照引数$rruleに値をセットして返すので戻り値なし
 */
	private function __doRruleRepeatFreqYearly(
		&$planParam, &$data, &$rrule, &$repeatFreq, &$rruleInterval, &$rruleByday,
			&$rruleBymonthday, &$rruleBymonth, &$rruleTerm) {
		$rrule['FREQ'] = $repeatFreq;
		$rrule['INTERVAL'] = intval($rruleInterval[$repeatFreq]);

		$bymonth = array();
		foreach ($rruleBymonth[$repeatFreq] as $val) {
			$val = intval($val);
			if ($val > 0 && $val <= 12) {
				$bymonth[] = $val;
			}
		}
		$rrule['BYMONTH'] = $bymonth;

		if (isset($rruleByday) && isset($rruleByday[$repeatFreq])) {
			$this->__toArrayRrruleByanyway($rruleByday, $repeatFreq);
			$rrule['BYDAY'] = $this->__makeArrayOfRruleByDay($rruleByday, $repeatFreq);
		}
	}

/**
 * _getStatus
 *
 * $data['save_N']のN(=status)を抜き出す
 *
 * @param array $data request->data配列
 * @return mixed 成功した時はstatus。失敗した時はfalseを返す。
 */
	protected function _getStatus($data) {
		$statuses = array(
			WorkflowComponent::STATUS_PUBLISHED,
			WorkflowComponent::STATUS_APPROVAL_WAITING,
			WorkflowComponent::STATUS_IN_DRAFT,
			WorkflowComponent::STATUS_DISAPPROVED,
		);
		foreach ($statuses as $status) {
			$saveStatus = 'save_' . $status;
			//save_Nの値は空なので、emptyではなくissetで判断すること
			if (isset($data[$saveStatus])) {
				return $status;
			}
		}
		return false;
	}
}
