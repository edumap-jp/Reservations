<?php
/**
 * ReservationDeletePlan Behavior
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
 * ReservationDeletePlanBehavior
 *
 * @property array $reservationWdayArray reservation weekday array 施設予約曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model\Behavior
 * @SuppressWarnings(PHPMD)
 */
class ReservationDeletePlanBehavior extends ReservationAppBehavior {

/**
 * Default settings
 *
 * VeventTime(+VeventRRule)の値自動変更
 * registered_into to reservation_information
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2015, NetCommons Project
 */
	protected $_defaults = array(
		'reservationRruleModel' => 'Reservations.ReservationRrule',
		'fields' => array(
			'registered_into' => 'reservation_information',
			),
		);
		//上記のfields定義は、以下の意味です。
		//   The (event|todoplugin|journal) was registerd into the reservation information.
		// ＝イベント(またはToDoまたは日報)が予定表の情報に登録されました。

/**
 * deletePlan
 *
 * 予定の削除
 *
 * @param Model &$model 実際のモデル名
 * @param array $curPlan 現世代予定（この現世代予定に対して削除を行う)
 * @param bool $isOriginRepeat 現予定が繰返しありかなしか
 * @param string $editRrule 編集ルール (この予定のみ、この予定以降、全ての予定)
 * @return 変更成功時 string reservationEventKey
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function deletePlan(Model &$model, $curPlan,
		$isOriginRepeat, $editRrule = self::CALENDAR_PLAN_EDIT_THIS) {
		$eventKey = $curPlan['cur_event_key'];

		//ReservationEventの対象データ取得
		$this->loadEventAndRruleModels($model);

		//対象となるデータを$eventData、$rruleDataそれぞれにセット
		$eventData = $rruleData = array();

		list($eventData, $rruleData) = $this->setCurEventDataAndRruleData($model, $curPlan);

		//「全削除」、「指定以降削除」、「この予定のみ削除or元予定に繰返しなし」
		if ($editRrule === self::CALENDAR_PLAN_EDIT_ALL) {
			//「この予定ふくめ全て削除」
			$eventKey = $this->deletePlanAll($model, $rruleData, $eventData, $curPlan);
			return $eventKey;	//復帰
		} elseif ($editRrule === self::CALENDAR_PLAN_EDIT_AFTER) {
			//「この予定以降を削除」
			$eventKey = $this->deletePlanByAfter(
				$model, $rruleData, $eventData, $curPlan);

			return $eventKey;	//復帰
		} else {
			//「この予定のみ削除or元予定に繰返しなし」

			//いずれのケースも、まず対象のeventDataは削除する。

			//eventのidではなく、keyで消さないといけない。（なぜなら同一キーをもつ過去世代が複数あり
			//１つのidをけしても、同一keyの他のidのデータが拾われて表示されてしまうため。

			//(1)-1 実際に削除する。
			$conditions = array(
				array(
					$model->ReservationEvent->alias . '.key' => $curPlan['cur_event_key'],
				)
			);
			//第２引数cascade==trueで、関連する子 ReservationEventShareUsers, ReservationEventContentを
			//ここで消す。
			//第３引数callbacks==trueで、メール関連のafterDeleteを動かす？ FIXME: 要確認
			//
			if (!$model->ReservationEvent->deleteAll($conditions, true, true)) {
				$model->validationErrors = Hash::merge(
					$model->validationErrors, $model->ReservationEvent->validationErrors);
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//$isOriginRepeatについて
			//
			//$isOriginRepeat==trueの時、元予定に繰返しあり、の「この予定のみ削除」ケース
			//
			//他の兄弟がいるので、親のrruleは残しておく可能性が高いが、
			//自分が最後の子の場合、それを消すと、結果,rruleDataが浮く
			//可能性がある
			//
			//一方、$isOriginRepeat==falseの時、「元予定に繰返しなし」=元予定は単一予定
			//
			//親のrruleは浮きリソースになる.
			//
			//つまり、両方を加味して、rrule配下に子がいなければ、rrule自体を消す処理を
			//以下に書く。
			//
			$params = array(
				'conditions' => array(
					'ReservationEvent.reservation_rrule_id' => $curPlan['cur_rrule_id'],
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
				if (!$model->ReservationRrule->delete($curPlan['cur_rrule_id'], false)) {
					//delete失敗
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}

			return $eventKey;	//復帰
		}
	}

/**
 * $planParamsデータを整える
 *
 * @param array &$planParams planParamsデータ
 * @return void
 * @throws InternalErrorException
 */
	public function arrangeData(&$planParams) {
		//開始日付と開始時刻は必須
		if (!isset($planParams['start_date']) && !isset($planParams['start_time'])) {
			//throw new InternalErrorException(__d('Reservations', 'No start_date or start_time'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//終了日付と終了時刻は必須
		if (!isset($planParams['end_date']) && !isset($planParams['end_time'])) {
			//throw new InternalErrorException(__d('Reservations', 'No end_date or end_time.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!isset($planParams['status'])) { //statusは必須
			//throw new InternalErrorException(__d('Reservations', 'status is required.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!isset($planParams['language_id'])) { //language_idは必須
			//throw new InternalErrorException(__d('Reservations', 'language_id is required.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$this->_arrangeShareUsers($planParams);
	}

/**
 * ReservationEventの対象データ取得
 *
 * @param Model &$model 実際のモデル名
 * @param int $eventId ReservationEvent.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @return 成功時 array 条件にマッチするReservationEventDataとそのbelongsTo,hasOne関係のデータ（実際には、ReservationRruleData), 失敗時 空配列
 */
	public function getReservationEventAndRrule(Model &$model, $eventId, $editRrule) {
		$params = array(
			'conditions' => array('ReservationEvent.id' => $eventId),
			'recursive' => 0,		//belongTo, hasOneの１跨ぎの関係までとってくる。
			'fields' => array('ReservationEvent.*', 'ReservationRrule.*'),
			'callbacks' => false
		);
		return $model->ReservationEvent->find('first', $params);
	}

/**
 * RruleDataへのデータをdateへセット
 *
 * @param array $rruleData rruleData
 * @param array &$data data
 * @return void
 */
	public function setRruleData2Data($rruleData, &$data) {
		//$data['ReservationRrule']['location'] = $rruleData['ReservationRrule']['location'];
		//$data['ReservationRrule']['contact'] = $rruleData['ReservationRrule']['contact'];
		//$data['ReservationRrule']['description'] = $rruleData['ReservationRrule']['description'];
		$data['ReservationRrule']['rrule'] = $rruleData['ReservationRrule']['rrule'];
		$data['ReservationRrule']['room_id'] = $rruleData['ReservationRrule']['room_id'];
		//$data['ReservationRrule']['status'] = $rruleData['ReservationRrule']['status'];
		//$data['ReservationRrule']['language_id'] = $rruleData['ReservationRrule']['language_id'];
	}

/**
 * 予定データの全削除
 *
 * @param Model &$model モデル 
 * @param array $rruleData rruleData
 * @param array $eventData eventData(編集画面のevent)
 * @param array $curPlan 現世代予定データ
 * @return string eventKeyを返す
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function deletePlanAll(Model &$model, $rruleData, $eventData, $curPlan) {
		if (!(isset($model->ReservationRrule))) {
			$model->loadModels([
				'ReservationRrule' => 'Reservations.ReservationRrule',
			]);
		}
		$eventKey = $curPlan['cur_event_key'];
		$rruleId = $curPlan['cur_rrule_id'];

		////////////////////////
		//(1)現在のrrule配下の全eventDataを消す

		//まずcurPlanより、消す対象のeventのidをすべて抽出する。
		//eventのidではなく、keyで消すこと。
		//そうしないと、直近のidだけ消しても、過去世代の同一keyの別idの
		//eventデータが拾われてしますから。
		$eventKeys = Hash::extract($curPlan, 'ReservationEvent.{n}.key');

		//(2) 実際に削除する。
		$conditions = array(
			array(
			$model->ReservationEvent->alias . '.key' => $eventKeys,
			)
		);
		//第２引数cascade==trueで、子のReservationEventShareUsers, ReservationEventContentを消す。
		//第３引数callbacks==trueで、メール関連のデキューをここでおこなう？ FIXME:要確認
		//
		if (!$model->ReservationEvent->deleteAll($conditions, true, true)) {
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->ReservationEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		/////////////////////////////
		//(2) eventsを消した後、rruleIdを親にもつeventDataの件数を調べる。
		// 0件なら、不要となった親(rrule)なので、浮きリソースとならないよう、消す。
		//
		$params = array(
			'conditions' => array(
				'ReservationEvent.reservation_rrule_id' => $rruleId,
			),
		);
		$count = $model->ReservationEvent->find('count', $params);
		if (!is_int($count)) {	//整数以外が返ってきたらエラー
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		if ($count === 0) {
			//(2)-1. 今の親rruleDataは、子を一切持たなくなった。
			//現在の親rruleDataは浮きリソースになるので、消しておく。
			if (!$model->ReservationRrule->delete($rruleId, false)) {
				//delete失敗
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		return $eventKey;
	}

/**
 * 指定eventデータ以降の予定の削除
 *
 * @param Model &$model 実際のモデル名
 * @param array $rruleData rruleData
 * @param array $eventData eventData
 * @param array $curPlan 現世代予定データ
 * @return string $eventKeyを返す
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function deletePlanByAfter(Model &$model, $rruleData, $eventData, $curPlan) {
		if (!(isset($model->ReservationRrule))) {
			$model->loadModels([
				'ReservationRrule' => 'Reservations.ReservationRrule',
			]);
		}

		$eventKey = $curPlan['cur_event_key'];

		////////////////////////
		//(1)指定eventのdtstart以降の全eventDataを消す

		//まずcurPlanより、基準日時以後の消す対象eventのidをすべて抽出する。
		//注）ここに来る前に、setEventDataAndRruleData()で、
		//rruleData, eventDataには、curPlanより指定したものが抽出・セットされているので、
		//それを使う。
		//ここでは、指定された元予定の時刻をつかわないといけない。
		//

		//開始の日付時刻を、$baseDtstartにする。
		$baseDtstart = $eventData['ReservationEvent']['dtstart']; //基準日時

		//eventのidではなく、keyで消さないといけない。（なぜなら同一キーをもつ過去世代が複数あり
		//１つのidをけしても、同一keyの他のidのデータが拾われて表示されてしまうため。
		$eventKeys = Hash::extract($curPlan['ReservationEvent'], '{n}[dtstart>=' . $baseDtstart . '].key');

		//(1)-1 実際に削除する。
		$conditions = array(
			array(
				$model->ReservationEvent->alias . '.key' => $eventKeys,
			)
		);
		//第２引数cascade==trueで、関連する子 ReservationEventShareUsers, ReservationEventContentを
		//ここで消す。
		//第３引数callbacks==trueで、メール関連のafterDeleteを動かす？ FIXME: 要確認
		//
		if (!$model->ReservationEvent->deleteAll($conditions, true, true)) {
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->ReservationEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//////////////////////////////
		//(2) eventsを消した後、rruleIdを親にもつeventDataの件数を調べる。
		//(2)-1. eventData件数==0、つまり、今の親rruleDataは、子を一切持たなくなった。
		// 現在の親rruleDataは浮きリソースになるので消す。
		// 注）「dtstar >= 自分のdtstart」で消しているので、指定(自分)のeventデータも含めて
		// 消している。
		//
		//(2)-2. eventData件数!=0、つまり、今の親rruleDataは自分(eventData)以外の子（時間軸では自分より前の時間）
		// を持っている。
		// なので、今の親rruleDataのrruleのUNTIL値を「自分の直前まで」に書き換える。
		// 自分を今の親rruleDataの管理下から切り離す。
		//
		// ＝＞これらの(2)の一連処理を実行する関数 auditEventOrRewriteUntil() をcallする。
		//
		if (!$model->Behaviors->hasMethod('auditEventOrRewriteUntil')) {
			$model->Behaviors->load('Reservations.ReservationCrudPlanCommon');
		}
		$model->auditEventOrRewriteUntil($eventData, $rruleData, $baseDtstart); //aaaaaa

		return $eventKey;
	}

/**
 * curPlanより現在eventDataとrruleDataに値セット
 *
 * @param Model &$model モデル
 * @param array $curPlan 現世代予定
 * @return array array($eventData, $rruleData)を返す
 * @throws InternalErrorException
 */
	public function setCurEventDataAndRruleData(Model &$model, $curPlan) {
		$rruleData['ReservationRrule'] = $curPlan['ReservationRrule'];
		$events = Hash::extract($curPlan, 'ReservationEvent.{n}[id=' . $curPlan['cur_event_id'] . ']');
		$eventData['ReservationEvent'] = $events[0];
		return array($eventData, $rruleData);
	}

/**
 * getEditRruleForUpdate
 *
 * request->data情報より、editRruleモードを決定し返す。
 *
 * @param Model &$model モデル
 * @param array $data data
 * @return string 成功時editRruleモード(0/1/2)を返す。失敗時 例外をthrowする
 * @throws InternalErrorException
 */
	public function getEditRruleForUpdate(Model &$model, $data) {
		if (empty($data['ReservationActionPlan']['edit_rrule'])) {
			//edit_rruleが存在しないか'0'ならば、「この予定のみ変更」
			return self::CALENDAR_PLAN_EDIT_THIS;
		}
		if ($data['ReservationActionPlan']['edit_rrule'] == self::CALENDAR_PLAN_EDIT_AFTER) {
			return self::CALENDAR_PLAN_EDIT_AFTER;
		}
		if ($data['ReservationActionPlan']['edit_rrule'] == self::CALENDAR_PLAN_EDIT_ALL) {
			return self::CALENDAR_PLAN_EDIT_ALL;
		}
		//ここに流れてくる時は、モードの値がおかしいので、例外throw
		throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
	}
}
