<?php
/**
 * ReservationPlanRruleValidate Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationValidateAppBehavior', 'Reservations.Model/Behavior');

/**
 * ReservationPlanRruleValidate Behavior
 *
 * @package  Reservations\Reservations\Model\Befavior
 * @author Allcreator <info@allcreator.net>
 */
class ReservationPlanRruleValidateBehavior extends ReservationValidateAppBehavior {

/**
 * __checkRruleTerm
 *
 * Rrule規則の繰返しの終了指定チェック（日、週、月、年単位共通）
 *
 * @param Model $model モデル変数
 * @param array $check 入力配列
 * @return bool 成功時true, 失敗時false
 */
	private function __checkRruleTerm(Model $model, $check) {
		if (!isset($model->ReservationActionPlan)) {
			$model->loadModels(['ReservationActionPlan' => 'Reservations.ReservationActionPlan']);
		}

		switch ($model->data[$model->alias]['rrule_term']) {
			case 'COUNT':	//回数指定
				//繰返し回数 'rrule_until'
				$rruleCount = intval($model->data[$model->alias]['rrule_count']);

				if (empty($model->data[$model->alias]['rrule_count'])) {
					$model->ReservationActionPlan->reservationProofreadValidationErrors['rrule_count'] = array();
					$model->ReservationActionPlan->reservationProofreadValidationErrors['rrule_count'][] =
						__d('reservations', 'Input required. (Times repeated)');
					return false;
				}

				if (preg_match('/^\d+$/', $model->data[$model->alias]['rrule_count']) !== 1) {
					$model->ReservationActionPlan->reservationProofreadValidationErrors['rrule_count'] = array();
					$model->ReservationActionPlan->reservationProofreadValidationErrors['rrule_count'][] =
						__d('reservations', 'Only numbers can be entered.');
					//CakeLog::debug("DBG: error case. reservationProofreadValidationErros[" . print_r($model->ReservationActionPlan->reservationProofreadValidationErrors, true) . "]");

					return false;
				}

				if ($rruleCount < ReservationsComponent::CALENDAR_RRULE_COUNT_MIN ||
					$rruleCount > ReservationsComponent::CALENDAR_RRULE_COUNT_MAX) {
					$model->ReservationActionPlan->reservationProofreadValidationErrors['rrule_count'] = array();
					$model->ReservationActionPlan->reservationProofreadValidationErrors['rrule_count'][] =
						sprintf(__d('reservations',
						'The number of repetition is %d or more and %d or less.'),
							ReservationsComponent::CALENDAR_RRULE_COUNT_MIN,
							ReservationsComponent::CALENDAR_RRULE_COUNT_MAX);
					return false;
				}
				break;
			case 'UNTIL':	//終了日指定
				//繰返し終了日 'rrule_until'
				$rruleUntil = $model->data[$model->alias]['rrule_until'];
				$msg = $this->_checkUntilDate($model, $rruleUntil);
				if ($msg !== '') {
					$model->ReservationActionPlan->reservationProofreadValidationErrors['rrule_until'] = array();
					$model->ReservationActionPlan->reservationProofreadValidationErrors['rrule_until'][] = $msg;
					return false;
				}
				break;
			default:
				//CakeLog::error(sprintf(__d('reservations', 'サポートしていない終了期限タイプ[%s]です'), $model->data[$model->alias]['rrule_term']));
				return false;
		}
		return true;
	}

/**
 * checkRrule
 *
 * Rrule規則のチェック
 *
 * @param Model $model モデル変数
 * @param array $check 入力配列
 * @return bool 成功時true, 失敗時false
 */
	public function checkRrule(Model $model, $check) {
		$isRepeat = (isset($model->data[$model->alias]['is_repeat']) &&
			$model->data[$model->alias]['is_repeat']) ? true : false;
		if (!$isRepeat) {
			return true;	//繰返し「無し」なら、true
		}

		//繰返しの終了指定（日、週、月、年単位共通）
		if (!$this->__checkRruleTerm($model, $check)) {
			//CakeLog::debug("DBG: model 1 [" . print_r($model->proofreadValidationErrors, true) . "]");
			return false;
		}

		//繰返し周期 'repeat_freq'
		if (!$this->_checkRepateFreq($model, $check)) {
			//CakeLog::debug("DBG: model 2 [" . print_r($model->proofreadValidationErrors, true) . "]");
			return false;
		}

		return true;
	}

/**
 * nopCheck
 *
 * NOPチェック関数
 *
 * @param Model $model モデル変数
 * @param array $check 入力配列
 * @return bool trueのみ返す
 */
	public function nopCheck(Model $model, $check) {
		return true;
	}

/**
 * _checkUntilDate
 *
 * Until日付Pチェック関数
 *
 * @param Model $model model
 * @param string $rruleUntil  UNTILの日付
 * @return string チェックOkなら空文字、チェックNGならエラーメッセージ文字列
 */
	protected function _checkUntilDate($model, $rruleUntil) {
		if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $rruleUntil, $matches) !== 1) {
			return __d('reservations', 'It is not in the YYYY-MM-DD format.');
		}

		if (!checkdate(intval($matches[2]), intval($matches[3]), intval($matches[1]))) {
			return __d('reservations', 'Invalid input. Day that does not exist has been specified.');
		}

		//繰返し期限日が、開始日より前になっていないかどうかのチェック
		//
		$startDate = $model->data[$model->alias]['detail_start_datetime'];
		if (strpos($startDate, ':') === false) {
			//ユーザー系 YYYY-MM-DD
			$startDate .= ' 00:00:00';
		} else {
			//ユーザー系 YYYY-MM-DD hh:mm
			$startDate .= ':00';
		}
		//開始日（時刻）をサーバー系に直す
		$nctm = new NetCommonsTime();
		$serverStartDate = $nctm->toServerDatetime(
			$startDate, $model->data[$model->alias]['timezone']);
		//until日の翌日を求める
		//Y-m-d H:i:s形式にする。
		$untilDateStr = $model->data[$model->alias]['rrule_until'] . ' 00:00:00';
		$untilDateAry = ReservationTime::transFromYmdHisToArray($untilDateStr);
		list($yearOfNextDay, $monthOfNextDay, $nextDay) =
			ReservationTime::getNextDay(
				$untilDateAry['year'], $untilDateAry['month'], $untilDateAry['day']);
		$nextDayOfUntilDate = sprintf("%04d-%02d-%02d 00:00:00",
			(int)$yearOfNextDay, (int)$monthOfNextDay, (int)$nextDay);
		//untilDateの翌日00:00:00を作り出し、サーバー系に直す
		$svrNxtDayOfUntilDt = $nctm->toServerDatetime(
			$nextDayOfUntilDate, $model->data[$model->alias]['timezone']);
		if ($svrNxtDayOfUntilDt <= $serverStartDate) {
			return __d('reservations', 'Invalid input. Term end date is earlier than the start date.');
		}
		//範囲チェック
		if ($serverStartDate < ReservationsComponent::CALENDAR_RRULE_TERM_UNTIL_MIN ||
			ReservationsComponent::CALENDAR_RRULE_TERM_UNTIL_MAX < $svrNxtDayOfUntilDt) {
			return sprintf(
				__d('reservations', 'date that can be specified is %s or more and %s or less.'),
				ReservationsComponent::CALENDAR_RRULE_TERM_UNTIL_MIN,
				ReservationsComponent::CALENDAR_RRULE_TERM_UNTIL_MAX);
		}
		return '';
	}

/**
 * _makeArrayOfWdayInNthWeek
 *
 * 1SU, ... , -1SA の配列生成関数
 *
 * @return array 生成した配列
 */
	protected function _makeArrayOfWdayInNthWeek() {
		//1SU, ... , -1SA の配列生成
		$bydayMonthly = array();
		$weeks = array (1, 2, 3, 4, -1);
		$wdays = explode('|', ReservationsComponent::CALENDAR_REPEAT_WDAY);
		foreach ($weeks as $week) {
			foreach ($wdays as $wday) {
				$bydayMonthly[] = $week . $wday;
			}
		}
		return $bydayMonthly;
	}
}
