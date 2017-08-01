<?php
/**
 * Reservation Plan Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('ReservationPermissiveRooms', 'Reservations.Utility');

/**
 * Reservation plan Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\View\Helper
 */
class ReservationPlanHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'Html',
		'Form',
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
		'NetCommons.Button',
		'Reservations.ReservationMonthly',
		'Reservations.ReservationCommon',
		'Reservations.ReservationUrl',
	);

/**
 * makeDatetimeWithUserSiteTz
 *
 * サーバ系日付時刻、タイムゾーン、言語より、言語別ユーザ系日付時刻曜日文字列を生成
 * ユーザーTZ or サイトTZ を暗黙裡に使う。登録時の現地TZは、ここではつかわない。
 *
 * @param string $YmdHis "YYYYMMDDhhmmss"形式のシステム系日付時刻
 * @param bool $isAllday 終日フラグ
 * @return string HTML
 */
	public function makeDatetimeWithUserSiteTz($YmdHis, $isAllday) {
		$nctm = new NetCommonsTime();
		$serverDatetime = ReservationTime::addDashColonAndSp($YmdHis);
		//toUserDatetime()が内部でユーザTZorサイトTZを使う.
		$userDatetime = $nctm->toUserDatetime($serverDatetime);
		$tma = ReservationTime::transFromYmdHisToArray($userDatetime);
		$unixtm = mktime(intval($tma['hour']), intval($tma['min']), intval($tma['sec']),
			intval($tma['month']), intval($tma['day']), intval($tma['year']));

		$html = sprintf(__d('reservations', '%s/%s/%s'), $tma['year'], $tma['month'], $tma['day']);
		$wdayArray = $this->ReservationCommon->getWdayArray();
		$dateInfo = getdate($unixtm);
		$html .= '(' . $wdayArray[$dateInfo['wday']] . ')';
		if (!$isAllday) {
			$html .= ' ' . sprintf(__d('reservations', '%s:%s'), $tma['hour'], $tma['min']);
		}
		return $html;
	}

/**
 * makeDateWithUserSiteTz
 *
 * サーバ系日付時刻、タイムゾーン、言語より、言語別ユーザ系日付文字列を生成
 * ユーザーTZ or サイトTZ を暗黙裡に使う。登録時の現地TZは、ここではつかわない。
 *
 * @param string $YmdHis "YYYYMMDDhhmmss"形式のシステム系日付時刻
 * @param bool $isAllday 終日フラグ
 * @return string HTML
 */
	public function makeDateWithUserSiteTz($YmdHis, $isAllday) {
		$nctm = new NetCommonsTime();
		$serverDatetime = ReservationTime::addDashColonAndSp($YmdHis);
		//toUserDatetime()が内部でユーザTZorサイトTZを使う.
		$userDatetime = $nctm->toUserDatetime($serverDatetime);
		$tma = ReservationTime::transFromYmdHisToArray($userDatetime);
		//$unixtm = mktime(intval($tma['hour']), intval($tma['min']), intval($tma['sec']),
		//	intval($tma['month']), intval($tma['day']), intval($tma['year']));

		$html = sprintf(__d('reservations', '%s/%s/%s'), $tma['year'], $tma['month'], $tma['day']);
		return $html;
	}

/**
 * isLinePlan
 *
 * 日跨ぎ(日跨ぎLine)判定
 *
 * @param array $plan 予定
 * @return bool
 */
	public function isLinePlan($plan) {
		$startUserDate = $this->makeDateWithUserSiteTz(
			$plan['ReservationEvent']['dtstart'], $plan['ReservationEvent']['is_allday']);
		$endUserDate = $this->makeDateWithUserSiteTz(
			$plan['ReservationEvent']['dtend'], $plan['ReservationEvent']['is_allday']);

		//日跨ぎ（ユーザー時刻で同一日ではない）
		if ($startUserDate != $endUserDate && $plan['ReservationEvent']['is_allday'] == false) {
			// 翌日0時までは当日扱いにする
			$startUserUnixtime = strtotime($this->_convertUserTime($plan['ReservationEvent']['dtstart']));
			$endUserUnixtime = strtotime($this->_convertUserTime($plan['ReservationEvent']['dtend']));
			if ((($startUserUnixtime + 24 * 60 * 60) > $endUserUnixtime) &&
				date('Hi', $endUserUnixtime) == '0000') {
				return false;
			}
			return true;
		}

		return false;
	}

/**
 * サーバータイムゾーンのYmdHisデータからユーザタイムゾーンへの変換
 *
 * @param string $YmdHis YmdHis形式の日時
 * @return string
 */
	protected function _convertUserTime($YmdHis) {
		$nctm = new NetCommonsTime();
		$serverDatetime = ReservationTime::addDashColonAndSp($YmdHis);
		//toUserDatetime()が内部でユーザTZorサイトTZを使う.
		$userDatetime = $nctm->toUserDatetime($serverDatetime);
		return $userDatetime;
	}

/**
 * makeEditButtonHtml
 *
 * 編集画面のボタンHTML生成
 *
 * @param string $statusFieldName 承認ステータス項目名
 * @param array $vars 施設予約情報
 * @param array $event 予約
 * @return string HTML
 */
	public function makeEditButtonHtml($statusFieldName, $vars, $event) {
		//save,tempsaveのoptionsでpath指定するため、Workflowヘルパーのbuttons()を参考に実装した。
		//$status = Hash::get($this->_View->data, $statusFieldName);
		$options = array(
			'controller' => 'reservations',
			'action' => 'index',
			'frame_id' => Current::read('Frame.id'),
			'?' => array(
				'year' => $vars['year'],
				'month' => $vars['month'],
			)
		);

		$ret = '';

		// キャンセルボタン
		if (isset($vars['returnUrl'])) {
			$cancelUrl = $vars['returnUrl'];
		} else {
			$cancelUrl = $this->ReservationUrl->getReservationUrl($options);
		}
		$cancelOptions = array(
			'ng-click' => 'sending=true',
			'ng-class' => '{disabled: sending}',
		);

		$ret .= $this->Button->cancel( __d('net_commons', 'Cancel'), $cancelUrl, $cancelOptions);

		// 一時保存　差し戻しボタン非表示なら表示
		$saveTempOptions = array(
			'label' => __d('net_commons', 'Save temporally'),
			'class' => 'btn btn-info' . $this->Button->getButtonSize() . ' btn-workflow',
			'name' => 'save_' . WorkflowComponent::STATUS_IN_DRAFT,
			'ng-class' => '{disabled: sending}',
			'ng-show' => 'buttons.draft'
		);
		$ret .= $this->Button->button(__d('net_commons', 'Save temporally'), $saveTempOptions);

		// 差し戻し　承認者でステータスが公開待ちなら　差し戻しボタン表示
		$saveTempOptions = array(
			'label' => __d('net_commons', 'Disapproval'),
			'class' => 'btn btn-warning' . $this->Button->getButtonSize() . ' btn-workflow',
			'name' => 'save_' . WorkflowComponent::STATUS_DISAPPROVED,
			'ng-class' => '{disabled: sending}',
			'ng-show' => 'buttons.disapproved',
		);
		$ret .= $this->Button->button(__d('net_commons', 'Disapproval'), $saveTempOptions);

		// 公開申請　承認者でなければ公開申請表示
		$saveOptions = array(
			'class' => 'btn btn-primary' . $this->Button->getButtonSize() . ' btn-workflow',
			'name' => 'save_' . WorkflowComponent::STATUS_APPROVAL_WAITING,
			'ng-class' => '{disabled: sending}',
			'ng-show' => 'buttons.approvalWaiting'
		);
		// デバッグ時は 'OK' を'承認申請'とかにかえとくとわかりやすい
		$ret .= $this->Button->button(__d('net_commons', 'OK'), $saveOptions);

		// 公開　承認者なら公開ボタンを表示
		$saveOptions = array(
			'label' => __d('net_commons', 'OK'),
			'class' => 'btn btn-primary' . $this->Button->getButtonSize() . ' btn-workflow',
			'name' => 'save_' . WorkflowComponent::STATUS_PUBLISHED,
			'ng-class' => '{disabled: sending}',
			'ng-show' => 'buttons.published'
		);
		$ret .= $this->Button->button(__d('net_commons', 'OK'), $saveOptions);

		return $ret;
	}

/**
 * makeOptionsOfWdayInNthWeek
 *
 * 第N週M曜日のオプション配列生成
 *
 * @param string $firstValue 最初の値
 * @param string $firstLabel 最初の文字列
 * @return array 配列
 */
	public function makeOptionsOfWdayInNthWeek($firstValue, $firstLabel) {
		$options = array();
		$options[$firstValue] = $firstLabel;
		$weeks = array (1, 2, 3, 4, -1);
		$wdays = explode('|', ReservationsComponent::CALENDAR_REPEAT_WDAY);
		foreach ($weeks as $week) {
			foreach ($wdays as $idx => $wday) {
				$key = $week . $wday;
				$weekLabel = $this->__getOrdSuffix($week);
				if ($week > 0) {
					//改行の位置調整のため、半角スペースが必要
					$options[$key] = $weekLabel . ' ' . $this->getWdayString($idx);
				} else {
					$options[$key] = $weekLabel . $this->getWdayString($idx);
				}
			}
		}
		return $options;
	}
/**
 * __getOrdSuffix
 *
 * 第N週のための序数文字列を取得する
 *
 * @param int $num 週数
 * @return string 序数文字列
 */
	private function __getOrdSuffix($num) {
		switch($num) {
			case 1:
				return __d('reservations', '1st week');
			case 2:
				return __d('reservations', '2nd week');
			case 3:
				return __d('reservations', '3rd week');
			case 4:
				return __d('reservations', '4th week');
			case -1:
				return __d('reservations', 'last week');
		}
	}
/**
 * getWdayString
 *
 * n曜日の文字列取得
 *
 * @param int $index 曜日のindex 0=日曜日,1=月曜日, ... , 6=土曜日
 * @return string 曜日の文字列
 */
	public function getWdayString($index) {
		$string = '';
		switch ($index) {
			case 0:
				$string = __d('reservations', 'Sunday');
				break;
			case 1:
				$string = __d('reservations', 'Monday');
				break;
			case 2:
				$string = __d('reservations', 'Tuesday');
				break;
			case 3:
				$string = __d('reservations', 'Wednesday');
				break;
			case 4:
				$string = __d('reservations', 'Thursday');
				break;
			case 5:
				$string = __d('reservations', 'Friday');
				break;
			default:	/* 6 */
				$string = __d('reservations', 'Saturday');
				break;
		}
		return $string;
	}
}
