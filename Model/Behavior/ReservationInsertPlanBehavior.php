<?php
/**
 * ReservationInsertPlan Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationAppBehavior', 'Reservations.Model/Behavior');

/**
 * ReservationInsertPlanBehavior
 *
 * @property array $reservationWdayArray reservation weekday array 施設予約曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model\Behavior
 */
class ReservationInsertPlanBehavior extends ReservationAppBehavior {

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
 * 予定の追加
 *
 * @param Model $model 実際のモデル名
 * @param array $planParams  予定パラメータ
 * @param bool $isMyPrivateRoom isMyPrivateRoom 予定の公開対象が自分のプライベートルームかどうか
 * @return int 追加成功時 $eventId(reservationEvent.id)を返す。追加失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	public function insertPlan(Model $model, $planParams, $isMyPrivateRoom) {
		if (!$model->Behaviors->hasMethod('doArrangeData')) {
			$model->Behaviors->load('Reservations.ReservationCrudPlanCommon');
		}
		$planParams = $model->doArrangeData($planParams);

		$rruleData = $this->insertRruleData($model, $planParams); //rruleDataの１件登録

		$eventData = $this->insertEventData($model, $planParams, $rruleData);	//eventDataの１件登録
		if (!isset($eventData['ReservationEvent']['id'])) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$eventId = $eventData['ReservationEvent']['id'];

		if ($rruleData['ReservationRrule']['rrule'] !== '') {	//Rruleの登録
			if (!$model->Behaviors->hasMethod('insertRrule')) {
				$model->Behaviors->load('Reservations.ReservationRruleEntry');
			}
			$model->insertRrule($planParams, $rruleData, $eventData);
		}

		return $eventId;
	}

/**
 * RruleDataへのデータ登録
 *
 * @param Model $model モデル
 * @param array $planParams 予定パラメータ
 * @param string $icalUidPart icalUidPart
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return array $rruleDataを返す
 * @throws InternalErrorException
 */
	public function insertRruleData(Model $model, $planParams,
		$icalUidPart = '', $createdUserWhenUpd = null) {
		if (!(isset($model->ReservationRrule) && is_callable($model->ReservationRrule->create))) {
			$model->loadModels([
				'ReservationRrule' => 'Reservations.ReservationRrule',
			]);
		}
		//rruleData保存のためにモデルをリセット(insert用)
		$rruleData = $model->ReservationRrule->create();

		//rruleDataにplanParamデータを詰め、それをモデルにセット
		$this->setRruleData($model, $planParams, $rruleData);

		//icalUidパーツの指定があれば、それをセットしておく。
		//「この予定以降の変更」で１つのCalenarRruleが２つにスプリットする
		//ケースを想定している。
		//
		if ($icalUidPart !== '') {
			$rruleData['ReservationRrule']['ireservation_uid'] = $icalUidPart;
		}

		if (!$model->Behaviors->hasMethod('saveRruleData')) {
			$model->Behaviors->load('Reservations.ReservationCrudPlanCommon');
		}
		$rruleData = $model->saveRruleData($rruleData, $createdUserWhenUpd);

		return $rruleData;
	}

/**
 * EventDataへのデータ登録
 *
 * @param Model $model モデル
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleデータ
 * @param int $createdUserWhenUpd created_userを明示指定する時にnull以外を指定。updatePlanで主に利用されている。
 * @return array $eventDataを返す
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD)
 */
	public function insertEventData(Model $model, $planParams, $rruleData,
		$createdUserWhenUpd = null) {
		if (!(isset($model->ReservationEvent) && is_callable($model->ReservationEvent->create))) {
			$model->loadModels([
				'ReservationEvent' => 'Reservations.ReservationEvent',
			]);
		}
		//eventData保存のためにモデルをリセット(insert用)
		$eventData = $model->ReservationEvent->create();

		//eventDataにplanParamデータを詰め、それをモデルにセット
		$this->setEventData($planParams, $rruleData, $eventData);

		$model->ReservationEvent->set($eventData);

		if (!$model->ReservationEvent->validates()) {		//eventDataをチェック
			//CakeLog::debug("DBG: validationErrors[ " . print_r($model->ReservationEvent->validationErrors, true) . "}");
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->ReservationEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!$model->ReservationEvent->save(null, false)) {	//保存のみ
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->ReservationEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//施設予約独自の例外追加１）
		//変更後の公開ルームidが、「元予定生成者の＊ルーム」から「編集者・承認者(＝ログイン者）の
		//プライベート」に変化していた場合、created_userを、元予定生成者「から」編集者・承認者(＝ログイン者）
		//「へ」に変更すること。＝＞これを考慮したcreatedUserWhenUpdを使えばよい。
		//
		//尚、saveの中で $createdUserWhenUpd を直接セットせず、以下のsaveField(=UPDATE文)を使ったのは
		//WFのbeforeSaveによりセットしたcreatedUserWhenUpd以外の値の書き換えられる可能性があるため。
		//
		if ($model->ReservationEvent->id > 0 && $createdUserWhenUpd !== null) {
			//saveが成功し、かつ、createdUserWhenUpd がnull以外なら、created_userを更新しておく。
			//modifiedも更新されるが、saveの直後なので誤差の範囲として了とする。
			$model->ReservationEvent->saveField('created_user', $createdUserWhenUpd);
			//UPDATEでセットしたcreatedUserWhenUpdの値をeventDataに記録しておく
			$eventData['ReservationEvent']['created_user'] = $createdUserWhenUpd;
		}

		//採番されたidをeventDataにセットしておく
		$eventData['ReservationEvent']['id'] = $model->ReservationEvent->id;

		//ShareUsersとContentは、reservation_event_id単位に登録するので、ここにもってきた。
		//
		if (!$model->Behaviors->hasMethod('insertShareUsers')) {
			$model->Behaviors->load('Reservations.ReservationShareUserEntry');
		}
		//カレンダ共有ユーザ登録
		$model->insertShareUsers($planParams['share_users'], $eventData['ReservationEvent']['id'],
			$createdUserWhenUpd);
		//注: 他のモデルの組み込みBehaviorをcallする場合、第一引数に$modelの指定はいらない。

		////関連コンテンツの登録
		//if ($eventData['ReservationEventContent']['linked_model'] !== '') {
		//	if (!(isset($model->ReservationEventContent))) {
		//		$model->loadModels(['ReservationEventContent' => 'Reservations.ReservationEventContent']);
		//	}
		//	$model->ReservationEventContent->saveLinkedData($eventData, $createdUserWhenUpd);
		//}

		return $eventData;
	}
}
