<?php
/**
 * ReservationPlanTimeValidate Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');
App::uses('ReservationTime', 'Reservations.Utility');

/**
 * ReservationPlanTimeValidate Behavior
 *
 * @package  Reservations\Reservations\Model\Befavior
 * @author Allcreator <info@allcreator.net>
 */
class ReservationPlanTimeValidateBehavior extends ModelBehavior {

/**
 * checkReverseStartEndDateTime
 *
 * 開始日（時）と終了日（時）並びチェック
 *
 * @param Model &$model モデル変数
 * @param array $check 入力値
 * @param string $editType 編集タイプ
 * @return bool 成功時true, 失敗時false
 */
	public function checkReverseStartEndDateTime(Model &$model, $check, $editType) {
		$startDate = false;
		$startTime = false;
		if (!$this->_isYmdHi($model, $model->data[$model->alias]['detail_start_datetime'],
			$editType, $startDate, $startTime)) {
			return false;
		}
		$endDate = false;
		$endTime = false;
		if (!$this->_isYmdHi($model, $model->data[$model->alias]['detail_end_datetime'],
			$editType, $endDate, $endTime)) {
			return false;
		}
		if (!$startDate && !$endDate) {
			//無条件trueなのでそのまま返す
			return true;
		}
		if ($startTime && $endTime) {
			//YYYY-MM-DD hh:ss ケース
			$start = sprintf("%s%s%s%s%s",
				substr($startDate, 0, 4), substr($startDate, 5, 2), substr($startDate, 8, 2),
				substr($startTime, 0, 2), substr($startTime, 3, 2));
			$end = sprintf("%s%s%s%s%s",
				substr($endDate, 0, 4), substr($endDate, 5, 2), substr($endDate, 8, 2),
				substr($endTime, 0, 2), substr($endTime, 3, 2));
		} else {
			//YYYY-MM-DD ケース
			$start = sprintf("%s%s%s",
				substr($startDate, 0, 4), substr($startDate, 5, 2), substr($startDate, 8, 2));
			$end = sprintf("%s%s%s",
				substr($endDate, 0, 4), substr($endDate, 5, 2), substr($endDate, 8, 2));
		}
		return ($start <= $end);
	}

/**
 * customDatetime
 *
 * 詳細施設予約用日付時刻チェック
 *
 * @param Model &$model モデル変数
 * @param string $check  入力値（日付 or 日付時刻）
 * @param string $editType 編集タイプ
 * @return bool 成功時true, 失敗時false
 */
	public function customDatetime(Model &$model, $check, $editType) {
		$value = array_values($check);
		$value = $value[0];

		$date = false;
		$time = false;
		if (! $this->_isYmdHi($model, $value, $editType, $date, $time)) {
			//失敗なのでそのまま返す
			return false;
		}
		//数字であること、位置桁が一致していることから、ＯＫとする。。
		/*
		if ($date) {
			if (! self::date($date, 'ymd')) {	//YYYY-MM-DD
				return false;
			}
		}
		if ($time) {
			if (! self::time($time)) {	//hh:mm
				return false;
			}
		}
		*/
		return true;
	}

/**
 * _isYmdHi
 *
 * 入力値よりYmd+HiまたはYmdの判定をしつつ、日付と時刻を取り出す
 *
 * @param Model &$model モデル変数
 * @param string $datetimeStr  入力配列（日付 or 日付時刻）
 * @param string $editType 編集タイプ
 * @param mixed &$date 日付(YYYY:MM:DD)
 * @param mixed &$time 時刻(hh:mm)
 * @return bool 成功時true, 失敗時false
 */
	protected function _isYmdHi(Model &$model, $datetimeStr, $editType, &$date, &$time) {
		$date = false;
		$time = false;
		$isDetailEdit = (isset($model->data[$model->alias]['is_detail']) &&
			$model->data[$model->alias]['is_detail']) ? true : false;
		if ($editType === 'detail' && (! $isDetailEdit)) {
			//detailの時だけチェックしろの指示で、非detail=easy画面だったので、スルーする。
			return true;
		}
		if (isset($model->data[$model->alias]['enable_time']) &&
			($model->data[$model->alias]['enable_time'])) {
			//時間の指定がある. YYYY-MM-DD hh:mm形式
			$regex = "/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2})$/";
		} else {
			//時間の指定がない. YYYY-MM-DD形式
			$regex = "/^(\d{4}-\d{2}-\d{2})$/";
		}
		if (preg_match($regex, $datetimeStr, $matches) !== 1) {
			return false;
		}
		$date = $matches[1]; //YYYY-MM-DD
		if (isset($matches[2])) {
			// 存在すれば hh:mm
			$time = $matches[2];
		}
		return true;
	}

/**
 * allowedTimezoneOffset
 *
 * 許可されたタイムゾーンオフセットかどうか
 *
 * @param Model &$model モデル変数
 * @param array $check 入力配列（timezone）
 * @return bool 成功時true, 失敗時false
 */
	public function allowedTimezoneOffset(Model &$model, $check) {
		$value = array_values($check);
		$value = $value[0];

		$SiteSetting = new SiteSetting();
		$SiteSetting->prepare();

		if (isset($SiteSetting->defaultTimezones[$value])) {
			return true;
		}
		return false;
		//
		//$tzTbl = ReservationsComponent::getTzTbl();
		//foreach ($tzTbl as $tzData) {
		//	if ($tzData[2] === $value) {
		//		return true;
		//	}
		//}
		//return false;
	}

/**
 * checkReverseStartEndTime
 *
 * 開始時間と終了時間の並びと範囲チェック
 *
 * @param Model &$model モデル変数
 * @param array $check 入力配列
 * @param string $editType 編集タイプ
 * @return bool 成功時true, 失敗時false
 */
	public function checkReverseStartEndTime(Model &$model, $check, $editType) {
		$value = array_values($check);
		$value = $value[0];

		$isDetailEdit = (isset($model->data[$model->alias]['is_detail'])
			&& $model->data[$model->alias]['is_detail']) ? true : false;
		if ($editType === 'easy' && $isDetailEdit) {
			//easyの時だけチェックしろの指示で、detail画面だったので、スルーする。
			return true;
		}

		if (isset($model->data[$model->alias]['enable_time']) &&
			(! $model->data[$model->alias]['enable_time'])) {
			return true;	//開始時間と終了時間の指定がないので、ノーチェック
		}

		//並びと範囲を調べ結果を返す
		return $this->_doEasyCheckReverseRange($model);
	}

/**
 * _doEasyCheckReverseRange
 *
 * 簡易画面の開始時間と終了時間の並びおよび範囲チェック
 *
 * @param Model &$model モデル変数
 * @return bool 成功時true, 失敗時false
 */
	protected function _doEasyCheckReverseRange(Model &$model) {
		$fromTo = array('from', 'to');
		foreach ($fromTo as $keyword) {
			if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):\d{2}$/",
				$model->data[$model->alias]['easy_hour_minute_' . $keyword]) !== 1) {
				return false;
			}
		}
		//並び順チェック
		//フォーマットが保証されているので、直接文字列同士で大小比較してＯＫ.
		if ($model->data[$model->alias]['easy_hour_minute_from'] >
			$model->data[$model->alias]['easy_hour_minute_to']) {
			return false;
		}

		//form, toが予定日の許容範囲かのチェック （予定日の00:00:00-予定翌日の00:00:00内ならＯＫ。それからはずれたらＮＧ）
		//
		list($serverStartDateZero, $serverNextDateZero) =
			(new ReservationTime())->convUserDate2SvrFromToDateTime(
				$model->data[$model->alias]['easy_start_date'],
				$model->data[$model->alias]['timezone']);

		if ($model->data[$model->alias]['easy_hour_minute_from'] < $serverStartDateZero ||
			$serverNextDateZero < $model->data[$model->alias]['easy_hour_minute_to']) {
			return false;
		}

		return true;
	}
}
