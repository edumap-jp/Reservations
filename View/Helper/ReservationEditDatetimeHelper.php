<?php
/**
 * Reservation Edit Datetime Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('NetCommonsTime', 'NetCommons.Utility');
App::uses('ReservationTime', 'Reservations.Utility');

/**
 * Reservation Edit Datetime Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\View\Helper
 */
class ReservationEditDatetimeHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
		'Form',
	);

/**
 * makeEditDatetimeHiddens
 *
 * @param array $fieldNames 対象のデータのフィールド名（複数
 * @return string
 */
	public function makeEditDatetimeHiddens($fieldNames) {
		$html = '';
		foreach ($fieldNames as $fieldName) {
			$html .= $this->_getHiddens($fieldName);
		}
		return $html;
	}
/**
 * _getHiddens
 *
 * Hiddenエリア
 *
 * @param string $fieldName 対象のデータのフィールド名
 * @return string HTML
 */
	protected function _getHiddens($fieldName) {
		$dtValue = Hash::get($this->request->data, 'ReservationActionPlan.' . $fieldName);
		// そのフィールドはDatetimePickerでいじるのでunlockFieldとしておく
		$this->NetCommonsForm->unlockField('ReservationActionPlan.' . $fieldName);
		// 隠しフィールド必須
		$html = $this->NetCommonsForm->hidden('ReservationActionPlan.' . $fieldName, array(
			'value' => $dtValue
		));
		return $html;
	}
/**
 * makeEditDatetimeHtml
 *
 * 予定日時入力用DatetimePicker作成
 *
 * @param array $vars 施設予約情報
 * @param string $type dateのタイプかdatetimeのタイプか
 * @param string $label ラベル
 * @param string $fieldName 対象のデータのフィールド名
 * @param string $ngModel Ng-Model名
 * @param string $jsFuncName JSで処理するファンクション名
 * @return string HTML
 */
	public function makeEditDatetimeHtml($vars, $type, $label, $fieldName, $ngModel, $jsFuncName) {
		//なおdatetimepickerのTZ変換オプション(convert_timezone)をfalseにしているので
		//ここで準備するYmdHisはユーザー系TZであることに留意してください。
		$html = '';

		// 指定フィールドのデータ取り出し
		$dtValue = Hash::get($this->request->data, 'ReservationActionPlan.' . $fieldName);

		$calTime = new ReservationTime();
		$dttmObj = $calTime->getDtObjWithTzDateTimeString(
			$this->request->data['ReservationActionPlan']['timezone_offset'],
			$dtValue
		);
		$dtValue = $dttmObj->format('Y-m-d H-i');

		$addNgInit = $jsFuncName . "('ReservationActionPlan" . Inflector::camelize($fieldName) . "')";

		//$enableTime = $this->request->data['ReservationActionPlan']['enable_time'];
		//
		if ($type == 'datetime') {
			if (strpos($dtValue, ':') !== false) {
				$dtDatetimeVal = $dtValue;
			} else {
				$dtDatetimeVal = $dtValue . ' 00:00';
			}
			$jsFormat = 'YYYY-MM-DD HH:mm';
		} elseif ($type == 'date') {
			if (strpos($dtValue, ':') !== false) {
				$dtDatetimeVal = substr($dtValue, 0, 10);
			} else {
				$dtDatetimeVal = $dtValue;
			}
			$jsFormat = 'YYYY-MM-DD';
		}
		$ngInit = sprintf("%s = '%s'; ", $ngModel, $dtDatetimeVal) . $addNgInit;

		//if ($type == 'datetime') {
		//	if ($enableTime) {
		//		$ngInit .= $addNgInit;
		//	}
		//} elseif ($type == 'date') {
		//	if (! $enableTime) {
		//		$ngInit .= $addNgInit;
		//	}
		//}

		$pickerOpt = str_replace('"', "'", json_encode(array(
			'format' => $jsFormat,
			'minDate' => ReservationsComponent::CALENDAR_RRULE_TERM_UNTIL_MIN,
			'maxDate' => ReservationsComponent::CALENDAR_RRULE_TERM_UNTIL_MAX
		)));

		$html .= $this->NetCommonsForm->input('ReservationActionPlanForDisp.' . $fieldName,
			array(
				'div' => false,
				'label' => $label,
				'data-toggle' => 'dropdown',
				'datetimepicker' => 'datetimepicker',
				'datetimepicker-options' => $pickerOpt,
				//日付だけの場合、User系の必要あるのでoffし、施設予約側でhandlingする。
				'convert_timezone' => false,
				'ng-model' => $ngModel,
				'ng-change' => $addNgInit,	//FIXME: selectイベントに変えたい。
				'ng-init' => $ngInit,
			));

		return $html;
	}
}
