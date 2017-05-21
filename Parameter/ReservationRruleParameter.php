<?php
/**
 * ReservationRruleParameter.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Class ReservationRruleParameter
 */
class ReservationRruleParameter {

/**
 * @var string モデル名
 */
	protected $_modelName = 'ReservationActionPlan';

/**
 * @var array rrule
 */
	protected $_rrule = null;

/**
 * request dataのセット
 *
 * @param array $data リクエストデータ。
 * @return void
 */
	public function setData($data) {
		// リピートか？
		$data = $data[$this->_modelName];
		if ($data['is_repeat']) {
			// repeat
			// 日、週、月、年のいずれか？
			$this->_rrule['FREQ'] = $data['repeat_freq'];
			switch ($data['repeat_freq']){
				case 'DAILY':
					$this->_rrule['INTERVAL'] = $data['rrule_interval']['DAILY'];
					break;
				case 'WEEKLY':
					$this->_makeWeeklyRrule($data);
					break;
				case 'MONTHLY':
					$this->_makeMonthlyRrule($data);
					break;
				case 'YEARLY':
					$this->_makeYearyRrule($data);
					break;
			}
			// count or until
			if ($data['rrule_term'] == 'COUNT') {
				// count
				$this->_rrule['COUNT'] = $data['rrule_count'];
			} else {
				//until
				$this->_rrule['UNTIL'] = $data['rrule_until'];
			}
		}
	}

/**
 * rruleをかえす
 *
 * @return array rrule
 */
	public function getRrule() {
		return $this->_rrule;
	}

/**
 * 年繰り返しのrrule生成
 *
 * @param array $data request data
 * @return void
 */
	protected function _makeYearyRrule($data) {
		$this->_rrule['INTERVAL'] = $data['rrule_interval']['YEARLY'];
		if ($data['rrule_bymonth']['YEARLY']) {
			$this->_rrule['BYMONTH'] = $data['rrule_bymonth']['YEARLY']; // 配列まま
		}
		if ($data['rrule_byday']['YEARLY']) {
			// ex 2SU 第2日曜
			$this->_rrule['BYDAY'] = $data['rrule_byday']['YEARLY'];
		}
	}

/**
 * 月繰り返しのrrule生成
 *
 * @param array $data request data
 * @return void
 */
	protected function _makeMonthlyRrule($data) {
		$this->_rrule['INTERVAL'] = $data['rrule_interval']['MONTHLY'];
		if ($data['rrule_byday']['MONTHLY']) {
			// ex 2SU 第2日曜
			$this->_rrule['BYDAY'] = $data['rrule_byday']['MONTHLY'];
		}
		if ($data['rrule_bymonthday']['MONTHLY']) {
			$this->_rrule['BYMONTHDAY'] = $data['rrule_byday']['MONTHLY'];
		}
	}

/**
 * 週繰り返しのrrule生成
 *
 * @param array $data request data
 * @return void
 */
	protected function _makeWeeklyRrule($data) {
		$this->_rrule['INTERVAL'] = $data['rrule_interval']['WEEKLY'];
		$this->_rrule['BYDAY'] = $data['rrule_byday']['WEEKLY']; //配列のまま
	}
}