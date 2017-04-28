<?php
/**
 * Reservation Trun Reservation Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Reservation Turn Reservation Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\View\Helper
 */
class ReservationTurnReservationHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
		'Form',
		'Reservations.ReservationCommon',
		'Reservations.ReservationUrl',

	);

/**
 * getTurnReservationOperationsWrap
 *
 * 施設予約上部の年月移動オペレーション部
 *
 * @param string $type month, week, day のいずれか
 * @param string $pos position
 * @param array $vars 施設予約日付情報
 * @return string html
 */
	public function getTurnReservationOperationsWrap($type, $pos, $vars) {
		$html = '';
		$html .= '<div class="row"><div class="col-xs-12">';
		$html .= $this->getTurnReservationOperations($type, $pos, $vars);
		$html .= '</div></div>';
		return $html;
	}
/**
 * getTurnReservationOperations
 *
 * 施設予約上部の年月移動オペレーション部
 *
 * @param string $type month, week, day のいずれか
 * @param string $pos position
 * @param array $vars 施設予約日付情報
 * @return string html
 */
	public function getTurnReservationOperations($type, $pos, $vars) {
		$prevUrl = $this->_getUrl('prev', $type, $vars);
		$nextUrl = $this->_getUrl('next', $type, $vars);
		$thisDayUrl = $this->_getUrl('now', $type, $vars);

		$html = '';
		$htmlClass = 'reservation-date-move-operations reservation-date-move-operations-' . $pos;
		if ($pos == 'top') {
			$htmlClass .= ' pull-left';
		}
		$html .= '<div class="' . $htmlClass . '">';

		if ($prevUrl) {
			$html .= $this->NetCommonsHtml->link(
				'<span class="glyphicon glyphicon-chevron-left"></span>',
				$prevUrl,
				array('escape' => false)
			);
		}

		$html .= $this->_getDateTitle($type, $pos, $vars);

		if ($nextUrl) {
			$html .= $this->NetCommonsHtml->link(
				'<span class="glyphicon glyphicon-chevron-right"></span>',
				$nextUrl,
				array('escape' => false)
			);
		}

		if ($thisDayUrl) {
			$html .= '<div class="reservation-today">';
			$html .= $this->NetCommonsHtml->link(
				$this->_getNowButtonTitle($type),
				$thisDayUrl,
				array('escape' => false)
			);
			$html .= '</div>';
		}
		$html .= '</div>';
		return $html;
	}

/**
 * 施設予約上部の年月表示部
 *
 * @param string $type month, week, day のいずれか
 * @param string $pos position
 * @param array $vars 施設予約日付情報
 * @return string html
 */
	protected function _getDateTitle($type, $pos, $vars) {
		$textColor = '';
		if ($type == 'day') {
			// 文字色
			$textColor = $this->ReservationCommon->makeTextColor(
				$vars['year'], $vars['month'], $vars['day'], $vars['holidays'], $vars['week']);
		}
		$turnNavId = 'ReservationEventTargetYear_' . Current::read('Frame.id') . '_' . $pos;

		$dateTimePickerInput = $this->_getDateTimePickerForMoveOperation($type, $pos, $vars);

		$html = '';
		if ($pos == 'bottom') {
			$html .= $dateTimePickerInput;
		}
		$html .= '<label class="reservation_event_target" for="' . $turnNavId . '">';
		$html .= '<h2 class="' . $textColor . '">';
		switch($type) {
			case 'month':
			case 'week':
				$html .= sprintf(__d('reservations', '<small>%d/</small> %d'),
					$vars['mInfo']['year'], $vars['mInfo']['month']);
				break;
			case 'day':
				/* 祝日タイトル */
				$holidayTitle = $this->ReservationCommon->getHolidayTitle(
					$vars['year'], $vars['month'], $vars['day'], $vars['holidays'], $vars['week']);
				$html .= sprintf(__d('reservations',
						'<small>%d/</small>%d/%d<small class="%s">(%s)&nbsp;<br class="visible-xs" />%s</small>'),
					$vars['year'],
					$vars['month'],
					$vars['day'],
					$textColor,
					$this->ReservationCommon->getWeekName($vars['week']),
					$holidayTitle
				);
				break;

		}
		$html .= '</h2></label>';
		if ($pos == 'top') {
			$html .= $dateTimePickerInput;
		}
		return $html;
	}
/**
 * _getNowButtonTitle
 *
 * 施設予約上部の現在へのボタン
 *
 * @param string $type month, week, day のいずれか
 * @return string html
 */
	protected function _getNowButtonTitle($type) {
		switch ($type) {
			case 'month':
				$ret = __d('reservations', 'This month');
				break;
			case 'week':
				$ret = __d('reservations', 'This week');
				break;
			case 'day':
				$ret = __d('reservations', 'Today');
		}
		return $ret;
	}

/**
 * _getUrl
 *
 * 施設予約上部の前へのURL
 *
 * @param string $prevNext prev, next, now のいずれか
 * @param string $type month, week, day のいずれか
 * @param array $vars 施設予約日付情報
 * @return string html
 */
	protected function _getUrl($prevNext, $type, $vars) {
		if ($prevNext == 'prev') {
			$dateArr = $this->_getPrevDate($type, $vars);
		} elseif ($prevNext == 'next') {
			$dateArr = $this->_getNextDate($type, $vars);
		} else {
			$dateArr = $this->_getNowDate($type, $vars);
		}
		// 指定されたdateArrが施設予約範囲を超えるものの場合はfalseを返す
		$day = Hash::get($dateArr, 'day');
		if (! $day) {
			$day = 1;
		}
		$tmstamp = mktime(0, 0, 0, $dateArr['month'], $day, $dateArr['year']);
		if ($tmstamp < ReservationsComponent::CALENDAR_RRULE_TERM_UNTIL_TM_MIN ||
			$tmstamp > ReservationsComponent::CALENDAR_RRULE_TERM_UNTIL_TM_MAX) {
			return false;
		}

		$urlArray = array(
			'plugin' => 'reservations',
			'controller' => 'reservations',
			'action' => 'index',
			'block_id' => '',
			'frame_id' => Current::read('Frame.id'),
			'?' => Hash::merge(
				array(
					'style' => $vars['style'],
					'location_key' => $vars['location_key']
				),
				$dateArr),
			'category_id' => Hash::get($this->request->named, 'category_id')
		);
		//if (isset($vars['tab'])) {
		//	$urlArray['?']['tab'] = $vars['tab'];
		//}
		$url = $this->ReservationUrl->getReservationUrlAsArray($urlArray);
		return $url;
	}
/**
 * _getPrevDate
 *
 * 施設予約上部の前への日付
 *
 * @param string $type month, week, day のいずれか
 * @param array $vars 施設予約日付情報
 * @return array
 */
	protected function _getPrevDate($type, $vars) {
		$ret = array();
		switch($type) {
			case 'month':
				$ret = array(
					'year' => sprintf("%04d", $vars['mInfo']['yearOfPrevMonth']),
					'month' => sprintf("%02d", $vars['mInfo']['prevMonth']),
				);
				break;
			case 'week':
				$prevtimestamp =
					mktime(0, 0, 0, $vars['month'], ($vars['day'] - 7 ), $vars['year']);
				$ret = array(
					'year' => sprintf("%04d", date('Y', $prevtimestamp)),
					'month' => sprintf("%02d", date('m', $prevtimestamp)),
					'day' => date('d', $prevtimestamp),
				);
				break;
			case 'day':
				$prevtimestamp =
					mktime(0, 0, 0, $vars['month'], ($vars['day'] - 1 ), $vars['year']);
				$ret = array(
					'year' => sprintf("%04d", date('Y', $prevtimestamp)),
					'month' => sprintf("%02d", date('m', $prevtimestamp)),
					'day' => date('d', $prevtimestamp),
				);
				break;
		}
		return $ret;
	}
/**
 * _getNextDate
 *
 * 施設予約上部次への日付
 *
 * @param string $type month, week, day のいずれか
 * @param array $vars 施設予約日付情報
 * @return array
 */
	protected function _getNextDate($type, $vars) {
		$ret = array();
		switch($type) {
			case 'day':
				$prevtimestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] + 1 ), $vars['year']);
				$ret = array(
					'year' => sprintf("%04d", date('Y', $prevtimestamp)),
					'month' => sprintf("%02d", date('m', $prevtimestamp)),
					'day' => date('d', $prevtimestamp),
				);
				break;
			case 'week':
				$prevtimestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] + 7 ), $vars['year']);
				$ret = array(
					'year' => sprintf("%04d", date('Y', $prevtimestamp)),
					'month' => sprintf("%02d", date('m', $prevtimestamp)),
					'day' => date('d', $prevtimestamp),
				);
				break;
			case 'month':
				$ret = array(
					'year' => sprintf("%04d", $vars['mInfo']['yearOfNextMonth']),
					'month' => sprintf("%02d", $vars['mInfo']['nextMonth']),
				);
				break;
		}
		return $ret;
	}
/**
 * _getNowDate
 *
 * 施設予約上部今への日付
 *
 * @param string $type month, week, day のいずれか
 * @param array $vars 施設予約日付情報
 * @return array
 */
	protected function _getNowDate($type, $vars) {
		$ret = array();
		switch($type) {
			case 'month':
				$ret = array(
					'year' => sprintf("%04d", $vars['today']['year']),
					'month' => sprintf("%02d", $vars['today']['month']),
				);
				break;
			case 'week':
				$ret = array(
					'year' => sprintf("%04d", $vars['today']['year']),
					'month' => sprintf("%02d", $vars['today']['month']),
					'day' => $vars['today']['day'],
				);
				break;
			case 'day':
				$ret = array(
					'year' => sprintf("%04d", $vars['today']['year']),
					'month' => sprintf("%02d", $vars['today']['month']),
					'day' => sprintf("%02d", $vars['today']['day']),
				);
		}
		return $ret;
	}
/**
 * _getDateTimePickerForMoveOperation
 *
 * 施設予約上部今への日付
 *
 * @param string $type month, week, day のいずれか
 * @param string $pos position
 * @param array $vars 施設予約日付情報
 * @return array
 */
	protected function _getDateTimePickerForMoveOperation($type, $pos, $vars) {
		if ($type == 'month') {
			$prototypeUrlOpt = array(
				'year' => 'YYYY',
				'month' => 'MM',
			);
			$pickerOpt = str_replace('"', "'", json_encode(array(
				'format' => 'YYYY-MM',
				'viewMode' => 'years',
				'minDate' => ReservationsComponent::CALENDAR_RRULE_TERM_UNTIL_MIN,
				'maxDate' => ReservationsComponent::CALENDAR_RRULE_TERM_UNTIL_MAX
			)));
			$year = sprintf("%04d",
				$vars['mInfo']['year']);
			$targetYearMonth = sprintf("%04d-%02d",
				$vars['mInfo']['year'],
				$vars['mInfo']['month']);
			$ngChange = 'changeYearMonth';
		} else {
			$prototypeUrlOpt = array(
				'year' => 'YYYY',
				'month' => 'MM',
				'day' => 'DD',
			);
			$pickerOpt = str_replace('"', "'", json_encode(array(
				'format' => 'YYYY-MM-DD',
				'viewMode' => 'days',
				'minDate' => ReservationsComponent::CALENDAR_RRULE_TERM_UNTIL_MIN,
				'maxDate' => ReservationsComponent::CALENDAR_RRULE_TERM_UNTIL_MAX
			)));
			if (!isset($vars['mInfo']['day'])) {
				$vars['mInfo']['day'] = $vars['day'];
			}
			$year = sprintf("%04d", $vars['year']);
			$targetYearMonth = sprintf("%04d-%02d-%02d",
				$vars['mInfo']['year'],
				$vars['mInfo']['month'],
				$vars['mInfo']['day']);
			$ngChange = 'changeYearMonthDay';
		}
		//angularJSのdatetimepicker変化の時に使う雛形URL
		$prototypeUrlOpt['style'] = $vars['style'];
		$prototypeUrl = $this->ReservationUrl->getReservationUrl(array(
			'controller' => 'reservations',
			'action' => 'index',
			'frame_id' => Current::read('Frame.id'),
			'?' => $prototypeUrlOpt
		));

		$dateTimePickerInput = $this->NetCommonsForm->input('ReservationEvent.target_year', array(
			'div' => false,
			'label' => false,
			'id' => 'ReservationEventTargetYear_' . Current::read('Frame.id') . '_' . $pos,
			'data-toggle' => 'dropdown',
			'aria-haspopup' => "true", 'aria-expanded' => "false",
			'datetimepicker' => 'datetimepicker',
			'datetimepicker-options' => $pickerOpt,
			'value' => (empty($year)) ? '' : intval($year),
			'class' => 'reservation-datetimepicker',
			'error' => false,
			'ng-model' => 'targetYear',
			'ng-init' => "targetYear='" . $targetYearMonth . "'",
			'ng-change' => $ngChange . '("' . $prototypeUrl . '")',
		));
		return $dateTimePickerInput;
	}
}

