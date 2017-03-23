<?php
/**
 * Reservation Rrule Plan Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('ReservationRruleUtil', 'Reservations.Utility');
App::uses('NetCommonsTime', 'NetCommons.Utility');
App::uses('ReservationTime', 'Reservations.Utility');

/**
 * ReservationRrulePlan Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\View\Helper
 */

class ReservationPlanRruleHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommonsForm',
		'NetCommonsHtml',
		'Html',
		'Form',
		'NetCommons.Button',
		'Reservations.ReservationCommon',
		'Reservations.ReservationUrl',
		//'Reservations.ReservationMonthly',
	);

/**
 * getStringRrule
 *
 * rrule配列またはrrule文字列を解析し、画面表示用のhtml文字列を出力する
 *
 * @param mixed $rrule rrule配列またはrrule文字列
 * @return string HTML
 */
	public function getStringRrule($rrule) {
		$resultStr = '';
		if (!is_array($rrule)) {
			//表示用繰返し配列を取得する。(登録用のparseRrule()を使ってはいけない。要注意）
			$rrule = (new ReservationRruleUtil())->mkViewArrayOfRrule($rrule);
			if (empty($rrule)) {
				return '';
			}
		}

		$freq = $rrule['FREQ'];
		if (!isset($rrule[$freq])) {
			return '';
		}

		$bymonthStr = $this->__getBymonthVals($freq, $rrule);

		$bydayStr = $this->__getBydayVals($freq, $rrule);

		$bymonthdayStr = $this->__getBymonthdayVals($freq, $rrule);

		switch ($freq) {
			case 'NONE':
				$resultStr .= __d('reservations', 'none');
				break;
			case 'YEARLY':
				$resultStr .= $this->__addStrWhenYearlyCase($freq, $rrule,
					$bymonthStr, $bydayStr, $bymonthdayStr);
				break;
			case 'MONTHLY':
				$resultStr .= $this->__addStrWhenMonthlyCase($freq, $rrule,
					$bymonthStr, $bydayStr, $bymonthdayStr);
				break;
			case 'WEEKLY':
				$resultStr .= $this->__addStrWhenWeeklyCase($freq, $rrule,
					$bymonthStr, $bydayStr, $bymonthdayStr);
				break;
			case 'DAILY':
				$resultStr .= $this->__addStrWhenDailyCase($freq, $rrule,
					$bymonthStr, $bydayStr, $bymonthdayStr);
				break;
			default:
		}

		$resultStr .= $this->__addStrWhenTermCase($rrule);

		return $resultStr;
	}

/**
 * __getBymonthVals
 *
 * rrule配列より指定freqごとのBYMONTHの値を取り出し、編集し、区切り文字で連結して返す
 *
 * @param string $freq DAILY,WEEKLY,MONTHLY,YEARLYいずれかの文字列.(NONEはあり得ない）
 * @param array $rrule rrule配列
 * @return string BYMONTHの加工後連結文字列
 */
	private function __getBymonthVals($freq, $rrule) {
		$bymonthStr = '';
		$monthNames = explode('|',
			__d('reservations',
				'|January|February|March|April|May|June|July|August|September|October|November|December'
			));
		if (isset($rrule[$freq]['BYMONTH'])) {
			foreach ($rrule[$freq]['BYMONTH'] as $val) {
				$bymonthStr .= ReservationsComponent::CALENDAR_RRULE_PAUSE . $monthNames[$val];
			}
		}
		return $bymonthStr;
	}

/**
 * __getBymonthdayVals
 *
 * rrule配列より指定freqごとのBYMONTHDAYの値を取り出し、編集し、区切り文字で連結して返す
 *
 * @param string $freq DAILY,WEEKLY,MONTHLY,YEARLYいずれかの文字列.(NONEはあり得ない）
 * @param array $rrule rrule配列
 * @return string BYMONTHDAYの加工後連結文字列
 */
	private function __getBymonthdayVals($freq, $rrule) {
		$bymonthdayStr = '';
		if (isset($rrule[$freq]['BYMONTHDAY'])) {
			foreach ($rrule[$freq]['BYMONTHDAY'] as $val) {
				$bymonthdayStr .= ReservationsComponent::CALENDAR_RRULE_PAUSE .
					sprintf(__d('reservations', 'Day %s'), $val);
			}
		}
		return $bymonthdayStr;
	}

/**
 * __getBydayVals
 *
 * rrule配列より指定freqごとのBYDAYの値を取り出し、編集し、区切り文字で連結して返す
 *
 * @param string $freq DAILY,WEEKLY,MONTHLY,YEARLYいずれかの文字列.(NONEはあり得ない）
 * @param array $rrule rrule配列
 * @return string BYDAYの加工後連結文字列
 */
	private function __getBydayVals($freq, $rrule) {
		$bydayStr = '';
		$wdays = explode('|', ReservationsComponent::CALENDAR_REPEAT_WDAY);
		$weekNameArray = explode('|',
			__d('reservations', 'Sunday|Monday|Tuesday|Wednesday|Thursday|Friday|Saturday'));
		if (isset($rrule[$freq]['BYDAY'])) {
			foreach ($rrule[$freq]['BYDAY'] as $val) {
				$wday = substr($val, -2);
				$num = intval(substr($val, 0, -2));
				$index = array_search($wday, $wdays);
				if ($index !== false && $index !== null) {
					$wName = $weekNameArray[$index];
				} else {
					continue;
				}
				if ($freq == 'WEEKLY') {
					$bydayStr .= ReservationsComponent::CALENDAR_RRULE_PAUSE;
				} else {
					$bydayStr .= $this->__getBydayStrWithWeekNum($num, $freq);
				}
				$bydayStr .= $wName;
			}
		}
		return $bydayStr;
	}

/**
 * __addStrWhenYearlyCase
 * 
 * YEARLY時の文字列追加処理
 * 
 * @param string $freq YEARLY
 * @param array $rrule rrule配列
 * @param string $bymonthStr bymonth文字列
 * @param string $bydayStr byday文字列
 * @param string $bymonthdayStr bymonthday文字列
 * @return string 追加すべきYEARLY時の文字列
 */
	private function __addStrWhenYearlyCase($freq, $rrule, $bymonthStr, $bydayStr, $bymonthdayStr) {
		$wkResultStr = '';
		if ($rrule[$freq]['INTERVAL'] == 1) {
			$wkResultStr .= __d('reservations', 'every year');
		} else {
			$wkResultStr .= sprintf(__d('reservations', 'every %s years'), $rrule[$freq]['INTERVAL']);
		}
		$wkResultStr .= $bymonthStr;
		if ($bydayStr == '') {
			$bydayStr = '&nbsp;/&nbsp;' . __d('reservations', 'Start date');
		}
		$wkResultStr .= $bydayStr;
		return $wkResultStr;
	}

/**
 * __addStrWhenMonthlyCase
 * 
 * MONTHLY時の文字列追加処理
 * 
 * @param string $freq MONTHLY
 * @param array $rrule rrule配列
 * @param string $bymonthStr bymonth文字列
 * @param string $bydayStr byday文字列
 * @param string $bymonthdayStr bymonthday文字列
 * @return string 追加すべきMONTHLY時の文字列
 */
	private function __addStrWhenMonthlyCase($freq, $rrule, $bymonthStr, $bydayStr, $bymonthdayStr) {
		$wkResultStr = '';
		if ($rrule[$freq]['INTERVAL'] == 1) {
			$wkResultStr .= __d('reservations', 'every month');
		} else {
			$wkResultStr .= sprintf(__d('reservations', 'every %s months'), $rrule[$freq]['INTERVAL']);
		}
		$wkResultStr .= $bydayStr;
		$wkResultStr .= $bymonthdayStr;
		return $wkResultStr;
	}

/**
 * __addStrWhenWeeklyCase
 * 
 * WEEKLY時の文字列追加処理
 * 
 * @param string $freq WEEKLY
 * @param array $rrule rrule配列
 * @param string $bymonthStr bymonth文字列
 * @param string $bydayStr byday文字列
 * @param string $bymonthdayStr bymonthday文字列
 * @return string 追加すべきWEEKLY時の文字列
 */
	private function __addStrWhenWeeklyCase($freq, $rrule, $bymonthStr, $bydayStr, $bymonthdayStr) {
		$wkResultStr = '';
		if ($rrule[$freq]['INTERVAL'] == 1) {
			$wkResultStr .= __d('reservations', 'every week');
		} else {
			$wkResultStr .= sprintf(__d('reservations', 'every %s weeks'), $rrule[$freq]['INTERVAL']);
		}
		$wkResultStr .= $bydayStr;
		return $wkResultStr;
	}

/**
 * __addStrWhenDailyCase
 * 
 * DAILLY時の文字列追加処理
 * 
 * @param string $freq DAILY
 * @param array $rrule rrule配列
 * @param string $bymonthStr bymonth文字列
 * @param string $bydayStr byday文字列
 * @param string $bymonthdayStr bymonthday文字列
 * @return string 追加すべきDAILY時の文字列
 */
	private function __addStrWhenDailyCase($freq, $rrule, $bymonthStr, $bydayStr, $bymonthdayStr) {
		$wkResultStr = '';
		if ($rrule[$freq]['INTERVAL'] == 1) {
			$wkResultStr .= __d('reservations', 'every day');
		} else {
			$wkResultStr .= sprintf(__d('reservations', 'every %s days'), $rrule[$freq]['INTERVAL']);
		}
		return $wkResultStr;
	}

/**
 * __addStrWhenTermCase
 * 
 * 繰返し期限(=COUNTorUNTIL)についての文字列追加処理
 * 
 * @param array $rrule rrule配列
 * @return string 追加すべき文字列
 */
	private function __addStrWhenTermCase($rrule) {
		$wkResultStr = '';
		if (isset($rrule['UNTIL'])) {
			$wkResultStr .= '&nbsp;/&nbsp;'; //'<br />';
			$wkResultStr .=
				(new ReservationTime())->dateFormat(substr($rrule['UNTIL'], 0, 8) .
				substr($rrule['UNTIL'], -6), null, 0, __d('reservations', 'Until Y/m/d'), 1);
		} elseif (isset($rrule['COUNT'])) {
			$wkResultStr .= '&nbsp;/&nbsp;';	//'<br />';
			$wkResultStr .= sprintf(__d('reservations', '%s times'), $rrule['COUNT']);
		}
		return $wkResultStr;
	}

/**
 * __getBydayStrWithWeekNum
 * 
 * 第n週or最終週ごとの文字列生成
 * 
 * @param int $num 第n週のn.最終週は-1.
 * @param string $freq DAILY,WEEKLY,MONTHLY,YEARLY,NONEいずれかの文字列.
 * @return string bydayStrに追加すべき文字列
 */
	private function __getBydayStrWithWeekNum($num, $freq) {
		$wkBydayStr = '';
		if ($freq == 'MONTHLY') {
			$sepa = ReservationsComponent::CALENDAR_RRULE_PAUSE;
		} else {
			$sepa = '&nbsp;/&nbsp;';
		}
		switch ($num) {
			// <br />セパレータではなく、"/"セパレータを使う。
			case 1:
				$wkBydayStr .= $sepa . __d('reservations', 'First week');
				break;
			case 2:
				$wkBydayStr .= $sepa . __d('reservations', 'Second week');
				break;
			case 3:
				$wkBydayStr .= $sepa . __d('reservations', 'Third week');
				break;
			case 4:
				$wkBydayStr .= $sepa . __d('reservations', 'Forth week');
				break;
			default:
				$wkBydayStr .= $sepa . __d('reservations', 'last week');
		}
		return $wkBydayStr;
	}
}
