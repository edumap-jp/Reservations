<?php
/**
 * Reservation Works Component
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('Component', 'Controller');

/**
 * ReservationWorksComponent
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Controller
 */
class ReservationWorksComponent extends Component {

/**
 * getDateTimeParam
 *
 * オプション取得
 *
 * @param array $params $this->request->query配列が渡される
 * @return array 年月日時分秒配列
 */
	public function getDateTimeParam($params) {
		$userTz = (new NetCommonsTime())->getUserTimezone();
		$date = new DateTime('now', (new DateTimeZone($userTz)));

		$enableTime = false;
		$year = $date->format('Y');
		$month = $date->format('m');
		$day = $date->format('d');
		$hour = $date->format('H');
		$minute = $date->format('i');
		$second = $date->format('s');

		if (isset($params['year'])) {
			$year = $params['year'];
		}
		if (isset($params['month'])) {
			$month = $params['month'];
		}
		if (isset($params['day'])) {
			$day = $params['day'];
		}
		if (isset($params['hour'])) {
			$hour = $params['hour'];
			$minute = '00';
			$second = '00';
			$enableTime = true;
		}
		return array($year, $month, $day, $hour, $minute, $second, $enableTime);
	}

/**
 * getOptions
 *
 * オプション取得
 *
 * @return array オプション配列
 */
	public function getOptions() {
		$options = array(
			'controller' => 'reservations',
			'action' => 'index',
			'frame_id' => Current::read('Frame.id'),
		);
		//if (isset($this->request->data['return_style']) && $this->request->data['return_style']) { 未使用
		//	$options['style'] = $this->request->data['return_style'];
		//}
		//if (isset($this->request->data['return_sort']) && $this->request->data['return_sort']) {
		//	$options['sort'] = $this->request->data['return_sort'];
		//}
		return $options;
	}

/**
 * setCapForView2RequestData
 *
 * 表示用配列から$this->request->dataへの反映
 *
 * @param array $capForView 表示用のcap(ReservationActionPlan)情報
 * @param array $data $this->request->dataの配列を受領する。
 * @return array 各種値を設定した$this->request->data配列を返す。
 */
	public function setCapForView2RequestData($capForView, $data) {
		foreach ($capForView['ReservationActionPlan'] as $item => $val) {
			if ($item === 'FREQ') {
				$this->__setFreqData2RequestData($val, $data);
				continue;
			}
			if ($item === 'TERM') {
				$this->__setTermData2RequestData($val, $data);
				continue;
			}

			//FREQ,TERM以外
			if (isset($data['ReservationActionPlan'][$item])) {
				//CakeLog::debug("DBG: item[$item]はdata[ReservationActionPlan]に有り。値は[" .
				//	serialize($data['ReservationActionPlan'][$item]) . "]");
			} else {
				$data['ReservationActionPlan'][$item] = $val;
				//CakeLog::debug("DBG: item[" . $item .
				//	"]はrequest_data[ReservationActionPlan]に無し。よって、capForView値[" .
				//	serialize($val) . "]を代入");
			}
		}

		if (isset($data['GroupsUser'])) {
			//CakeLog::debug("DBG: data[GroupsUser]は有り。値は[" .
			//	serialize($data['GroupsUser']) . "]");
		} else {
			$data['GroupsUser'] = $capForView['GroupsUser'];
			//CakeLog::debug("DBG: data[GroupsUser]は無し。よって、capForView[GroupsUser][" .
			//	serialize($capForView['GroupsUser']) . "]を代入");
		}

		return $data;
	}

/**
 * __setFreqData2RequestData
 *
 * 表示用配列内のFREQ配列から$dataへの反映
 *
 * @param array $freq 繰り返し情報
 * @param array &$data data
 * @return void
 */
	private function __setFreqData2RequestData($freq, &$data) {
		$rrules = array(
			'rrule_interval' => array('DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY'),
			'rrule_byday' => array('WEEKLY', 'MONTHLY', 'YEARLY'),
			'rrule_bymonthday' => array('MONTHLY'),
			'rrule_bymonth' => array('YEARLY'),
		);

		foreach ($rrules as $type => $units) {	//ex. typeは rrule_byday
			if (!isset($data['ReservationActionPlan'][$type])) {
				$data['ReservationActionPlan'][$type] = array();
			}
			foreach ($units as $unit) {	//ex.unitはMONTHLY
				if (!isset($data['ReservationActionPlan'][$type][$unit])) {
					list(, $typeKeyword) = explode('_', $type);	//ex.rruleとbydayに分割
					$typeKeyword = strtoupper($typeKeyword);	//ex.bydayをBYDAYに

					//$data['ReservationActionPlan']['rrule_byday']['MONTHLY'] =
					//  $freq['MONTHLY']['BYDAY']; という形で代入
					$data['ReservationActionPlan'][$type][$unit] = $freq[$unit][$typeKeyword];
				}
			}
		}
	}

/**
 * __setTermData2RequestData
 *
 * 表示用配列内のTERM配列から$dataへの反映
 *
 * @param array $term 期限情報
 * @param array &$data data
 * @return void
 */
	private function __setTermData2RequestData($term, &$data) {
		if (!isset($data['ReservationActionPlan']['rrule_term'])) {
			if ($term['REPEAT_COUNT']) {
				$data['ReservationActionPlan']['rrule_term'] = 'COUNT';
			}
			if ($term['REPEAT_UNTIL']) {
				$data['ReservationActionPlan']['rrule_term'] = 'UNTIL';
			}
		}

		if (!isset($data['ReservationActionPlan']['rrule_count'])) {
			$data['ReservationActionPlan']['rrule_count'] = $term['COUNT'];
		}
		if (!isset($data['ReservationActionPlan']['rrule_until'])) {
			$data['ReservationActionPlan']['rrule_until'] = $term['UNTIL'];
		}
	}
}
