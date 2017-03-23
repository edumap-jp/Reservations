<?php
/**
 * ReservationCrudPlanCommon Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationAppBehavior', 'Reservations.Model/Behavior');
App::uses('ReservationRruleUtil', 'Reservations.Utility');

/**
 * ReservationCrudPlanCommonBehavior
 *
 * @property array $reservationWdayArray reservation weekday array 施設予約曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model\Behavior
 */
class ReservationCrudPlanCommonBehavior extends ReservationAppBehavior {

/**
 * saveRruleData
 *
 * RruleDataへのデータ登録共通処理
 *
 * @param Model &$model モデル
 * @param array $rruleData rruleData
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return array $rruleDataを返す
 * @throws InternalErrorException
 */
	public function saveRruleData(Model &$model, $rruleData, $createdUserWhenUpd = null) {
		if (!(isset($model->ReservationRrule))) {
			$model->loadModels(['ReservationRrule' => 'Reservations.ReservationRrule']);
		}

		if ($createdUserWhenUpd !== null) {
			$rruleData['ReservationRrule']['created_user'] = $createdUserWhenUpd;
		}

		$model->ReservationRrule->set($rruleData);

		if (!$model->ReservationRrule->validates()) {
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->ReservationRrule->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!$model->ReservationRrule->save($rruleData, false)) {
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->ReservationRrule->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$rruleData['ReservationRrule']['id'] = $model->ReservationRrule->id;

		return $rruleData;
	}

/**
 * doArrangeData
 *
 * $planParamsデータを整える
 *
 * @param Model &$model モデル
 * @param array $planParams planParamsデータ
 * @return array $planParamsを返す
 * @throws InternalErrorException
 */
	public function doArrangeData(Model &$model, $planParams) {
		//開始日付と開始時刻は必須
		if (!isset($planParams['start_date']) && !isset($planParams['start_time'])) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//終了日付と終了時刻は必須
		if (!isset($planParams['end_date']) && !isset($planParams['end_time'])) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!isset($planParams['status'])) { //statusは必須
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!isset($planParams['language_id'])) { //language_idは必須
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$this->_arrangeShareUsers($planParams);

		return $planParams;
	}

/**
 * auditEventOrRewriteUntil
 *
 * 浮きリソースチェック、そして浮きEvent削除またはrruleDataのrruleのUNTIL条件書き換え
 *
 * @param Model &$model モデル
 * @param array $eventData eventData
 * @param array $rruleData rruleData
 * @param string $baseDtstart ベース開始日付時刻(baseDtstart)
 * @return void
 * @throws InternalErrorException
 */
	public function auditEventOrRewriteUntil(Model &$model, $eventData, $rruleData, $baseDtstart) {
		//////////////////////////////
		//(2) eventsを消した後、rruleIdを親にもつeventDataの件数を調べる。
		// 0件なら、不要となった親(rrule)なので、浮きリソースとならないよう、消す。
		//
		//注）「dtstar >= 自分のdtstart」で消しているので、指定(自分)のeventデータも含めて
		// 消している。が、rruleをこの後新規に作り直すので、それ自体は問題ない。
		// 「時間・繰返し系が変更された場合」keyを振り直すと仕様をきめているので、
		// 問題なし。('ReservationEvent.id <>' => $eventIdという条件はふくめなくて、よい）
		//
		if (!(isset($model->ReservationRrule))) {
			$model->loadModels([
				'ReservationRrule' => 'Reservations.ReservationRrule',
			]);
		}
		if (!(isset($model->ReservationEvent))) {
			$model->loadModels([
				'ReservationEvent' => 'Reservations.ReservationEvent',
			]);
		}
		$params = array(
			'conditions' => array(
				'ReservationEvent.reservation_rrule_id' =>
								$eventData['ReservationEvent']['reservation_rrule_id'],
			),
		);
		$count = $model->ReservationEvent->find('count', $params);
		if (!is_int($count)) {	//整数以外が返ってきたらエラー
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		if ($count === 0) {
			//(2)-1. 今の親rruleDataは、子を一切持たなくなった。
			//（自分の新しい親rruleDataをこの後つくるので）現在の親rruleDataは浮きリソースになるので、
			// 消しておく。
			$result = $model->ReservationRrule->delete(
				$eventData['ReservationEvent']['reservation_rrule_id'], false
			);
			if (! $result) {
				//delete失敗
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		} else {
			///////////////////////////////////////
			//(2)-2 今の親rruleDataは、自分(eventData)以外の子（時間軸では自分より前の時間）を持っている。
			//なので、今の親rruleDataのrruleのUNTIL値を「自分の直前まで」に書き換える。
			//自分を今の親rruleDataの管理下から切り離す。(自分の新しい親rruleDataはこのあと作る）
			//

			//親のrruleDataはすでに取得しているので、rrule文字列はすぐに取得できる。
			$rruleArr = (new ReservationRruleUtil())->parseRrule($rruleData['ReservationRrule']['rrule']);
			//FREQ以外を篩い落とす
			$freq = $rruleArr['FREQ'];
			$rruleArr['FREQ'] = $freq;

			//$baseDtstart(=$eventData['ReservationEvent']['dtstart'])は、YYYYMMDDhhmmss(UTC)です。
			//なので、UNTILは単純に、YYYYMMDDThhmmssにすればいいだけだとおもう。
			//FIXME:
			//厳密には、UNTILが施設予約で時間を指定できない＝ユーザー系の00:00:00に
			//なっているので、どうやって、時分秒をajustするか。要検討。

			$rruleArr['UNTIL'] = substr($baseDtstart, 0, 8) . 'T' . substr($baseDtstart, 8);

			//$timestamp = mktime(0, 0, 0,
			//			substr($planParams['dtstart'], 4, 2),
			//			substr($planParams['dtstart'], 6, 2),
			//			substr($planParams['dtstart'], 0, 4));
			//UNTILを自分の直前までにする。
			//$rruleArr['UNTIL'] = date('Ymd', $timestamp) . 'T' . substr($planParams['dtstart'], 8);

			$rruleBeforeStr = (new ReservationRruleUtil())->concatRrule($rruleArr);

			//今のrruleDataデータのrrule文字列を書き換える。
			$rruleDataBefore = $rruleData;
			$rruleDataBefore['ReservationRrule']['rrule'] = $rruleBeforeStr;
			$model->ReservationRrule->clear();
			//rruleDataNowのidは、現rruleDataのidであるので、更新となる。
			if (!$model->ReservationRrule->save($rruleDataBefore, false)) {
				//save失敗
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}
	}
}
