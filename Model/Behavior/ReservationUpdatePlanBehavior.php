<?php
/**
 * ReservationUpdatePlan Behavior
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
 * ReservationUpdatePlanBehavior
 *
 * @property array $reservationWdayArray reservation weekday array 施設予約曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model\Behavior
 * @SuppressWarnings(PHPMD)
 */
class ReservationUpdatePlanBehavior extends ReservationAppBehavior {

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
 * 予定の変更
 *
 * @param Model $model 実際のモデル名
 * @param array $planParams  予定パラメータ
 * @param array $newPlan 新世代予定（この新世代予定に対して変更をかけていく）
 * @param string $status status（Workflowステータス)
 * @param array $isInfoArray （isOriginRepeat、isTimeMod、isRepeatMod、isMyPrivateRoom）を格納した配列
 * @param string $editRrule 編集ルール (この予定のみ、この予定以降、全ての予定)
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return 変更成功時 int reservationEventId
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function updatePlan(Model $model, $planParams, $newPlan, $status,
		$isInfoArray, $editRrule = self::CALENDAR_PLAN_EDIT_THIS, $createdUserWhenUpd = null) {
		$eventId = $newPlan['new_event_id'];

		//bool $isOriginRepeat 元予定が繰返しありかなしか
		//bool $isTimeMod 元予定に対して時間の変更があったかどうか
		//bool $isRepeatMod 元予定に対して繰返しの変更があったかどうか
		//bool $isMyPrivateRoom 新しい予定の公開対象のルームがログイン者のプライベートルームかどうか
		list($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom) = $isInfoArray;

		if (!$model->Behaviors->hasMethod('doArrangeData')) {
			$model->Behaviors->load('Reservations.ReservationCrudPlanCommon');
		}
		$planParams = $model->doArrangeData($planParams);

		//ReservationEventの対象データ取得
		$this->loadEventAndRruleModels($model);

		//対象となるデータを$eventData、$rruleDataそれぞれにセット
		$eventData = $rruleData = array();

		list($eventData, $rruleData) = $this->setEventDataAndRruleData($model, $newPlan);

		//timezoneがなければ、reservation_eventテーブルからセットする。
		if (!isset($planParams['timezone'])) {
			$planParams['timezone'] = $eventData['ReservationEvent']['timezone'];
		}

		//「全更新」、「指定以降更新」、「この予定のみ更新or元予定に繰返しなし」
		if ($editRrule === self::CALENDAR_PLAN_EDIT_ALL) {
			//「この予定ふくめ全て更新」
			$isArray = array($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom);
			$eventId = $this->updatePlanAll($model, $planParams, $rruleData, $eventData,
				$newPlan, $isArray, $status, $editRrule, $createdUserWhenUpd);
			return $eventId;	//復帰

		} elseif ($editRrule === self::CALENDAR_PLAN_EDIT_AFTER) {
			//「この予定以降を更新」
			$isArray = array($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom);
			$eventId = $this->updatePlanByAfter(
				$model, $planParams, $rruleData, $eventData, $newPlan, $isArray,
				$status, $editRrule, $createdUserWhenUpd);

			return $eventId;	//復帰

		} else {
			//「この予定のみ更新or元予定に繰返しなし」
			if ($isOriginRepeat) {
				//元予定に繰返しあり、の「この予定のみ更新」ケース
				if ($isRepeatMod) {
					//繰返し条件が変更になった場合、(b)

					CakeLog::notice(
						"「この予定のみ更新」の場合、" .
						"繰返し予定の変更は許可していない。" .
						"Google施設予約仕様に準拠し、" .
						"繰返し予定の変更は無視し、" .
						"現繰返しルールをそのままつかう。");
				}
				//すでにnewPlanを作成する時rruleDataは生成されているので、
				//rruleDataの上書き(updateRruleData()発行）は無駄なのでしない。
				//
				//補足）newPlanを生成するとき、createdUserWhenUpdを考慮してrruleをcopyしています。
			} else {
				//「元予定に繰返しなし」=元予定は単一予定
				//
				//すでにnewPlanを作成する時rruleDataは生成されている。
				//
				//変更後、繰返し指定になっている可能性もあるので、
				//rruleデータを入力データに従い更新しておく。
				//
				$rruleData = $this->updateRruleData($model, $planParams, $rruleData, $createdUserWhenUpd);
			}

			//選択したeventデータを更新 (a). keyは踏襲されている。
			//

			$this->setEventData($planParams, $rruleData, $eventData);	//eventDataに値セット

			$isArrays = array($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom);
			$eventData = $this->updateDtstartData($model, $planParams, $rruleData, $eventData,
				$isArrays, 1, $editRrule, $createdUserWhenUpd);
			$eventId = $eventData['ReservationEvent']['id'];

			//「この予定のみ更新or元予定に繰返しなし」
			if ($isOriginRepeat) {
				//元予定に繰返しありのケース

				//兄弟eventの情報を書き換える必要はないので、ここではなにもしない。

			} else {
				//「元予定に繰返しなし」=元予定は単一予定

				//元予定に兄弟eventは存在しないので、
				//前出の「選択したeventデータを更新 (a)」を最初のeventとして扱えばよい。
				//（もし繰返し指定があれば、２件目以降のevent生成を行う）
				//
				if ($rruleData['ReservationRrule']['rrule'] !== '') {	//Rruleの登録
					if (!$model->Behaviors->hasMethod('insertRrule')) {
							$model->Behaviors->load('Reservations.ReservationRruleEntry');
					}
					$model->insertRrule($planParams, $rruleData, $eventData, $createdUserWhenUpd);
				}
			}
			return $eventId;	//復帰
		}
	}

/**
 * ReservationEventの対象データ取得
 *
 * @param Model $model 実際のモデル名
 * @param int $eventId ReservationEvent.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @return 成功時 array 条件にマッチするReservationEventDataとそのbelongsTo,hasOne関係のデータ（実際には、ReservationRruleData), 失敗時 空配列
 */
	public function getReservationEventAndRrule(Model $model, $eventId, $editRrule) {
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
 * 予定データの全更新
 *
 * @param Model $model モデル
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleData
 * @param array $eventData eventData(編集画面のevent)
 * @param array $newPlan 新世代予定データ
 * @param array $isArray ($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom)をまとめた配列
 * @param string $status status(Workflowステータス)
 * @param int $editRrule editRrule
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return int eventIdを返す
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function updatePlanAll(Model $model, $planParams, $rruleData, $eventData,
		$newPlan, $isArray, $status, $editRrule, $createdUserWhenUpd) {
		$isOriginRepeat = $isArray[0];
		$isTimeMod = $isArray[1];
		$isRepeatMod = $isArray[2];
		$isMyPrivateRoom = $isArray[3];

		if (!(isset($model->ReservationRrule))) {
			$model->loadModels([
				'ReservationRrule' => 'Reservations.ReservationRrule',
			]);
		}
		//繰返し情報が更新されている時は、rruleDataをplanParasを使って書き換える
		if ($isRepeatMod) {
			//setRruleDataはsave()を呼んでいないフィールドセットだけのmethodなので、
			//setRruleData()+save()のupdateRruleData()の変更する。
			////$this->setRruleData($model, $planParams, $rruleData, self::CALENDAR_UPDATE_MODE);
			$this->updateRruleData($model, $planParams, $rruleData, $createdUserWhenUpd);
		}

		$eventId = null;
		if ($isTimeMod || $isRepeatMod) {
			//時間・繰返し系が変更されたので、

			////////////////////////
			//(0)編集画面のplanParamsをもとに、eventDataを生成する。
			$this->setEventData($planParams, $rruleData, $eventData);

			////////////////////////
			//(1)現在のrrule配下の全eventDataを消す

			//まずnewPlanより、消す対象のeventのidをすべて抽出する。
			//$eventIds = Hash::extract($newPlan,
			//	'ReservationEvent.{n}[language_id=' . Current::read('Langugage.id') . '].id');

			//eventのidではなく、keyで消すこと。
			//そうしないと、直近のidだけ消しても、過去世代の同一keyの別idの
			//eventデータが拾われてしますから。
			////$eventIds = Hash::extract($newPlan, 'ReservationEvent.{n}.id');
			$eventKeys = Hash::extract($newPlan, 'ReservationEvent.{n}.key');
			$this->__deleteOrUpdateAllEvents($model, $status, $eventData, $eventKeys);

			/////////////////
			//(2)新たな時間・繰返し系情報をもとに、eventDataを生成し直す。(keyはすべて新規)
			//＊vreservationでは日付時刻がキーになっているので、繰返し系に変更がなくとも、
			// 時間系が変われば、vreservation的にはキーがかわるので、eventデータのkeyも取り直すこととする。
			//
			//（以下で行うのは、insertPlan()のサブセット処理）

			if (!$model->Behaviors->hasMethod('insertEventData')) {
				$model->Behaviors->load('Reservations.ReservationInsertPlan');
			}

			//先頭のeventDataの１件登録
			$eventData = $model->insertEventData($planParams, $rruleData, $createdUserWhenUpd);
			if (!isset($eventData['ReservationEvent']['id'])) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$eventId = $eventData['ReservationEvent']['id'];

			if ($rruleData['ReservationRrule']['rrule'] !== '') {	//Rruleの登録
				if (!$model->Behaviors->hasMethod('insertRrule')) {
					$model->Behaviors->load('Reservations.ReservationRruleEntry');
				}
				$model->insertRrule($planParams, $rruleData, $eventData, $createdUserWhenUpd);
				////uddateRruleData()は、$isRepeatModがtrueの時だけ発行する関数なので、
				////ここではなく、前出の if（$isRepeatMod）｛...｝へ移動した。
				////$this->updateRruleData($model, $planParams, $rruleData);//FUJI
			}

		} else {
			//時間・繰返し系が変更されていない(vreservation的なキーが変わらない)ので、eventのkeyはそのままに
			//現在の全eventDataの時間以外の値を書き換える。

			//選択されたデータを編集画面のデータ(planParams)をもとに書き換える
			//書き換え後のデータは、以下の全書き換えの雛形eventとする。
			//
			$this->setEventData($planParams, $rruleData, $eventData);
			$index = 0;
			foreach ($newPlan['ReservationEvent'] as $fields) {
				++$index;
				$event = array();
				$event['ReservationEvent'] = $fields;	//$eventは元のeventを指す。
				$eventDataForAllUpd = $this->__getEventDataForUpdateAllOrAfter($event,
					$eventData, $status);
				if ($eventId === null) {
					//繰返しの最初のeventIdを記録しておく。
					$eventId = $eventDataForAllUpd['ReservationEvent']['id'];
				}

				$isArrays = array($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom);
				$eventDataForAllUpd = $this->updateDtstartData(
					$model, $planParams, $rruleData, $eventDataForAllUpd,
					$isArrays, $index, $editRrule, $createdUserWhenUpd);
			}
		}

		return $eventId;
	}

/**
 * EventDataのデータ更新
 *
 * @param Model $model モデル
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleデータ
 * @param array $eventData eventデータ
 * @param array $isArrays isArrays (isOriginRepeat,isTimeMod,isRepeatMod,isMyPrivateRoom)を格納した配列
 * @param int $index index 何回目のupdateのindex(1はじまり)
 * @param string $editRrule editRrule
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return array $eventData 変更後の$eventDataを返す
 * @throws InternalErrorException
 */
	public function updateDtstartData(Model $model, $planParams, $rruleData, $eventData,
			$isArrays, $index, $editRrule, $createdUserWhenUpd = null) {
		//bool $isOriginRepeat isOriginRepeat
		//bool $isTimeMod isTimeMod
		//bool $isRepeatMod isRepeatMod
		list($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom) = $isArrays;

		if (!(isset($model->ReservationEvent) && is_callable($model->ReservationEvent->create))) {
			$model->loadModels([
				'ReservationEvent' => 'Reservations.ReservationEvent',
			]);
		}

		if ($editRrule === self::CALENDAR_PLAN_EDIT_ALL) {
			//「この予定ふくめ全て更新」

			//繰返し・時間系の変更がない場合のEDIT_ALLの場合、
			//単一の更新と同じ処理にながせばよい。

			//なお、「この予定のみ更新」ではないので、
			//recurrenceにはなにもしない

		} elseif ($editRrule === self::CALENDAR_PLAN_EDIT_AFTER) {
			//「この予定以降を更新」

			//繰返し・時間系の変更がない場合のEDIT_AFTERの場合、
			//単一の更新と同じ処理にながせばよい。

			//なお、「この予定のみ更新」ではないので、
			//recurrenceにはなにもしない

		} else {
			//「この予定のみ更新」
			if ($isOriginRepeat) {
				//元予定が繰返しあり
				//置換イベントidとして1を立てておく。
				$eventData['ReservationEvent']['recurrence_event_id'] = 1;	//暫定１
			}
		}

		$eventId = $eventData['ReservationEvent']['id'];	//update対象のststartendIdを退避

		//施設予約独自の例外追加１）
		//変更後の公開ルームidが、「元予定生成者の＊ルーム」から「編集者・承認者(＝ログイン者）の
		//プライベート」に変化していた場合、created_userを、元予定生成者「から」編集者・承認者(＝ログイン者）
		//「へ」に変更すること。＝＞これを考慮したcreatedUserWhenUpdを使えばよい。
		//尚、ここのsaveはUPDATなので、save前に、create_user項目へセットして問題なし。
		if ($createdUserWhenUpd !== null) {
			$eventData['ReservationEvent']['created_user'] = $createdUserWhenUpd;
		}

		$model->ReservationEvent->set($eventData);

		if (!$model->ReservationEvent->validates()) {		//eventDataをチェック
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->ReservationEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//copyEventData()のINSERTsaveでは、WFのbeforeSaveのis_active調整処理を抑止し、
		//代わりに、prepareLatestCreatedForInsを発行し、is_latest,created調整処理および
		//is_activeのoff暫定セットをした。
		//（WFのbeforeSaveはUPDATEsaveでは発動されないことが分かっているので）
		//よって、「ここ」UPDATEsaveで、prepareActiveForUpdを事前実行し、INSERTsaveでdelayさせた
		//is_active調整処理を行う。（eventDataの値が一部変更されます）
		$model->ReservationEvent->prepareActiveForUpd($eventData);

		if (!$model->ReservationEvent->save($eventData,
			array(
				'validate' => false,
				'callbacks' => true,
			))) {	//保存のみ
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->ReservationEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		if ($eventId !== $model->ReservationEvent->id) {
			//insertではなくupdateでなくてはならないのに、insertになってしまった。(つまりid値が新しくなってしまった）
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//採番されたidをeventDataにセットしておく
		$eventData['ReservationEvent']['id'] = $model->ReservationEvent->id;

		//施設予約共有ユーザ更新
		if (!$model->Behaviors->hasMethod('updateShareUsers')) {
			$model->Behaviors->load('Reservations.ReservationShareUserEntry');
		}
		$model->updateShareUsers(
			$planParams['share_users'],
			$eventId,
			Hash::get($eventData, 'ReservationEvent.ReservationEventShareUser'),
			$createdUserWhenUpd
		);

		////関連コンテンツ(reservation_event_contents)の更新
		////
		//if (!empty($eventData['ReservationEvent']['ReservationEventContent']['linked_model'])) {
		//	if (!(isset($model->ReservationEventContent))) {
		//		$model->loadModels(['ReservationEventContent' => 'Reservations.ReservationEventContent']);
		//	}
		//	//saveLinkedData()は、内部で
		//	//modelとcontent_key一致データなし=> insert
		//	//modelとcontent_key一致データあり=> update
		//	//と登録・変更を適宜区別して実行する関数である。
		//	$model->ReservationEventContent->saveLinkedData($eventData, $createdUserWhenUpd);
		//}

		return $eventData;
	}

/**
 * 指定eventデータ以降の予定の変更
 *
 * @param Model $model 実際のモデル名
 * @param array $planParams  予定パラメータ
 * @param array $rruleData rruleData
 * @param array $eventData eventData
 * @param array $newPlan 新世代予定データ
 * @param array $isArray ($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom)をまとめた配列
 * @param string $status status(Workflowステータス)
 * @param int $editRrule editRrule
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return int $eventIdを返す
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function updatePlanByAfter(Model $model, $planParams, $rruleData, $eventData,
		$newPlan, $isArray, $status, $editRrule, $createdUserWhenUpd) {
		$eventId = $newPlan['new_event_id'];
		////$rruleKey = $rruleData['ReservationRrule']['key'];

		$isOriginRepeat = $isArray[0];
		$isTimeMod = $isArray[1];
		$isRepeatMod = $isArray[2];
		$isMyPrivateRoom = $isArray[3];

		if (!(isset($model->ReservationRrule))) {
			$model->loadModels([
				'ReservationRrule' => 'Reservations.ReservationRrule',
			]);
		}

		$eventId = null;
		if ($isTimeMod || $isRepeatMod) {
			//時間・繰返し系が変更されたので、

			////////////////////////
			//(1)指定eventのdtstart以降の全eventDataを消す

			//まずnewPlanより、基準日時以後の消す対象eventのidをすべて抽出する。
			//注）ここに来る前に、setEventDataAndRruleData()で、
			//rruleData, eventDataには、newPlanより指定したものが抽出・セットされているので、
			//それを使う。
			//

			//CakeLog::debug("DBG: before setEventData()。eventData[" . print_r($eventData, true) . "]");

			//ここでは、指定された元予定の時刻をつかわないといけない。
			//誤って、$planParamsからsetEventData()実行でeventDataを上書きすると、
			//時間系が変更になっているための別の日時になってしまい、
			//つかえないdtstartになります。要注意。
			//

			//CakeLog::debug("DBG: after setEventData()。eventData[" . print_r($eventData, true) . "]");

			//画面より入力された開始の日付時刻を、$baseDtstartにする。
			$baseDtstart = $eventData['ReservationEvent']['dtstart']; //基準日時

			//eventのidではなく、keyで消さないといけない。（なぜなら同一キーをもつ過去世代が複数あり
			//１つのidをけしても、同一keyの他のidのデータが拾われて表示されてしまうため。
			////$eventIds = Hash::extract($newPlan['ReservationEvent'], '{n}[dtstart>=' . $baseDtstart . '].id');
			$eventKeys = Hash::extract(
				$newPlan['ReservationEvent'], '{n}[dtstart>=' . $baseDtstart . '].key'
			);
			$this->__deleteOrUpdateAllEvents($model, $status, $eventData, $eventKeys);

			//////////////////////////////
			//(2) eventsを消した後、rruleIdを親にもつeventDataの件数を調べる。
			//(2)-1. eventData件数==0、つまり、今の親rruleDataは、子を一切持たなくなった。
			// 自分の新しい親rruleDataをこの後つくるので）現在の親rruleDataは浮きリソースになるので消す。
			//(2)-2. eventData件数!=0、つまり、今の親rruleDataは自分(eventData)以外の子（時間軸では自分より前の時間）
			// を持っている。
			// なので、今の親rruleDataのrruleのUNTIL値を「自分の直前まで」に書き換える。
			// 自分を今の親rruleDataの管理下から切り離す。(自分の新しい親rruleDataはこのあと作る）
			//
			// ＝＞これらの(2)の一連処理を実行する関数 auditEventOrRewriteUntil() をcallする。
			//
			if (!$model->Behaviors->hasMethod('auditEventOrRewriteUntil')) {
				$model->Behaviors->load('Reservations.ReservationCrudPlanCommon');
			}
			$model->auditEventOrRewriteUntil($eventData, $rruleData, $baseDtstart);

			/////////////////
			//(3) 新たな時間・繰返し系情報をもとに、rruleDataと、eventData群を生成し直す。(keyはすべて新規)
			//＊rruleDataは新しく発行する。ireservation_uidに分割された元rruleDataのkeyの一部を保持する。
			//＊vreservationでは日付時刻がキーになっているので、繰返し系に変更がなくとも、
			// 時間系が変われば、vreservation的にはキーがかわるので、eventデータのkeyも取り直すこととする。
			//
			//（以下で行うのは、insertPlan()のサブセット処理）

			//あとで、２つのrruleDataが分割されたものであることが分かるよう、
			//新rruleDataのireservation_uidを、元のireservation_uid + 元keyにしておく。
			//
			$newIcalUid = ReservationRruleUtil::addKeyToIcalUid(
				$rruleData['ReservationRrule']['ireservation_uid'], $rruleData['ReservationRrule']['key']);

			//(以下は、insertPlan()のサブセット処理

			if (!$model->Behaviors->hasMethod('insertRruleData')) {
				$model->Behaviors->load('Reservations.ReservationInsertPlan');
			}

			//rruleDataの新規１件登録
			$rruleData = $model->insertRruleData($planParams, $newIcalUid, $createdUserWhenUpd);

			//先頭のeventDataの１件登録
			$eventData = $model->insertEventData($planParams, $rruleData, $createdUserWhenUpd);
			if (!isset($eventData['ReservationEvent']['id'])) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$eventId = $eventData['ReservationEvent']['id'];

			if ($rruleData['ReservationRrule']['rrule'] !== '') {	//Rruleの登録
				if (!$model->Behaviors->hasMethod('insertRrule')) {
					$model->Behaviors->load('Reservations.ReservationRruleEntry');
				}
				$model->insertRrule($planParams, $rruleData, $eventData, $createdUserWhenUpd);
			}

		} else {
			//時間・繰返し系が変更されていない(vreservation的なキーが変わらない)ので、eventのkeyはそのままに
			//指定されたeventIdより日付時刻が後になるeventDataすべての時間以外の値を書き換える。

			//選択されたデータを編集画面のデータ(planParams)をもとに書き換える
			//書き換え後のデータは、以下の全書き換えの雛形eventとする。
			//

			//$planParamsの値（画面の入力値）より、$eventDataを作り出す。
			//＊時間系が変更されていないことが保証されているので、
			//setEventData()を発行して、$eventDataを更新しても、
			//dtstartは元のままです。
			$this->setEventData($planParams, $rruleData, $eventData);

			//画面より入力された開始の日付時刻を、$baseDtstartにする。
			$baseDtstart = $eventData['ReservationEvent']['dtstart'];

			$eventsAfterBase = Hash::extract(
				$newPlan['ReservationEvent'], '{n}[dtstart>=' . $baseDtstart . ']');

			$index = 0;
			foreach ($eventsAfterBase as $fields) {
				++$index;
				$event = array();
				$event['ReservationEvent'] = $fields;	//$eventは元のeventを指す。
				$eventDataForAfterUpd = $this->__getEventDataForUpdateAllOrAfter($event,
					$eventData, $status);
				if ($eventId === null) {
					//繰返しの最初のeventIdを記録しておく。
					$eventId = $eventDataForAfterUpd['ReservationEvent']['id'];
				}

				$isArrays = array($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom);
				$eventDataForAfterUpd = $this->updateDtstartData(
					$model, $planParams, $rruleData, $eventDataForAfterUpd,
					$isArrays, $index, $editRrule, $createdUserWhenUpd);
			}
		}

		return $eventId;
	}

/**
 * resutlsよりeventDataとrruleDataに値セット
 *
 * @param Model $model モデル
 * @param array $newPlan 新世代予定
 * @return array array($eventData, $rruleData)を返す
 * @throws InternalErrorException
 */
	public function setEventDataAndRruleData(Model $model, $newPlan) {
		//この時点で、$newPlan['ReservationRrule']、$newPlan['ReservationEvent']のcreated_userは、
		//createdUserWhenUpd考慮済になっている。
		$rruleData['ReservationRrule'] = $newPlan['ReservationRrule'];
		$events = Hash::extract($newPlan, 'ReservationEvent.{n}[id=' . $newPlan['new_event_id'] . ']');
		$eventData['ReservationEvent'] = $events[0];
		return array($eventData, $rruleData);
	}

/**
 * getEditRruleForUpdate
 *
 * request->data情報より、editRruleモードを決定し返す。
 *
 * @param Model $model モデル
 * @param array $data data
 * @return string 成功時editRruleモード(0/1/2)を返す。失敗時 例外をthrowする
 * @throws InternalErrorException
 */
	public function getEditRruleForUpdate(Model $model, $data) {
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

/**
 * RruleDataのデータ更新
 *
 * @param Model $model モデル
 * @param array $planParams 予定パラメータ
 * @param array $rruleData 更新対象となるrruleData
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return array $rruleDataを返す
 * @throws InternalErrorException
 */
	public function updateRruleData(Model $model, $planParams, $rruleData,
		$createdUserWhenUpd = null) {
		if (!(isset($model->ReservationRrule) && is_callable($model->ReservationRrule->create))) {
			$model->loadModels([
				'ReservationRrule' => 'Reservations.ReservationRrule',
			]);
		}

		//現rruleDataにplanParamデータを詰め、それをモデルにセット
		$this->setRruleData($model, $planParams, $rruleData, self::CALENDAR_UPDATE_MODE,
			$rruleData['ReservationRrule']['key'], $rruleData['ReservationRrule']['id']);

		if (!$model->Behaviors->hasMethod('saveRruleData')) {
			$model->Behaviors->load('Reservations.ReservationCrudPlanCommon');
		}
		$rruleData = $model->saveRruleData($rruleData, $createdUserWhenUpd);

		return $rruleData;
	}

/**
 * __getEventDataForUpdateAllOrAfter
 *
 * 時間系・繰返し系に変更がない時の全変更・以後変更兼用イベントデータ生成
 *
 * @param array $event newPlanの各繰返しeventデータ。keyにReservationEventを持つように整形してある。
 * @param array $eventData 編集画面のデータに基づいて作成されたeventData
 * @param string $status status
 * @return array 全変更用に適宜編集された繰返しeventデータ
 */
	private function __getEventDataForUpdateAllOrAfter($event, $eventData, $status) {
		//id,key,rrule_idはnewPlanのまま
		//$event['ReservationEvent']['id'] = 108
		//$event['ReservationEvent']['reservation_rrule_id'] = 83
		//$event['ReservationEvent']['key'] = d5612115c24c86ea8987eddd021aff5b

		//room_idは編集画面の値を使う
		$event['ReservationEvent']['room_id'] = $eventData['ReservationEvent']['room_id'];

		//langauge_id,target_userは編集画面にないのでnewPlanのまま
		//$event['ReservationEvent']['language_id'] = 2
		//$event['ReservationEvent']['target_user'] = 1

		//タイトル、場所、連絡先、詳細は編集画面の値を使う
		$event['ReservationEvent']['title'] = $eventData['ReservationEvent']['title'];
		$event['ReservationEvent']['title_icon'] = $eventData['ReservationEvent']['title_icon'];
		$event['ReservationEvent']['location'] = $eventData['ReservationEvent']['location'];
		$event['ReservationEvent']['contact'] = $eventData['ReservationEvent']['contact'];
		$event['ReservationEvent']['description'] = $eventData['ReservationEvent']['description'];

		//終日指定、開始終了日時、TZは「全て変更」の場合、newPlanの値を使う
		//$event['ReservationEvent']['is_allday'] =
		//$event['ReservationEvent']['start_date'] = 20160616
		//$event['ReservationEvent']['start_time'] = 080000
		//$event['ReservationEvent']['dtstart'] = 20160616080000
		//$event['ReservationEvent']['end_date'] = 20160616
		//$event['ReservationEvent']['end_time'] = 090000
		//$event['ReservationEvent']['dtend'] = 20160616090000
		//$event['ReservationEvent']['timezone'] = 9.0

		//statusは、編集画面のsave_Nを元に施設予約拡張新statusになっているので、
		//それを代入する。
		$event['ReservationEvent']['status'] = $status;

		//is_active, is_latestは、statusの値変化の有無で、処理が変わるのでここではスルーする。
		//$event['ReservationEvent']['is_active'] = $eventData['ReservationEvent']['is_active'];
		//$event['ReservationEvent']['is_latest'] = $eventData['ReservationEvent']['is_latest'];

		//「この予定のみ」変更した記録（置換）は残しておく(newPlanの値のまま）
		//$event['ReservationEvent']['recurrence_event_id'] = 0

		//「除外」記録は残しておく(newPlanの値のまま）
		//$event['ReservationEvent']['exception_event_id'] = 0

		//メール通知関連は編集画面の値を使う
		$event['ReservationEvent']['is_enable_mail'] = $eventData['ReservationEvent']['is_enable_mail'];
		$event['ReservationEvent']['email_send_timing'] =
						$eventData['ReservationEvent']['email_send_timing'];

		//作成日、作成者情報はnewPlanの値のまま
		//$event['ReservationEvent']['created_user'] = 1
		//$event['ReservationEvent']['created'] = 2016-06-17 07:38:27

		//更新日、更新者情報は変更する
		$event['ReservationEvent']['modified_user'] = $eventData['ReservationEvent']['modified_user'];
		$event['ReservationEvent']['modified'] = $eventData['ReservationEvent']['modified'];

		//ReservationEventShareUserは、あとで、planParamsのShareUserを
		//つかって書き換えるので、元のままとしておく。
		//$event['ReservationEvent']['ReservationEventShareUser'] = Array
		//	(
		//	)

		//ReservationEventContentは、あとで、書き換えるので、
		//元のままとしておく。
		//$event['ReservationEvent']['ReservationEventContent'] = Array
		//	(
		//	)

		return $event;
	}

/**
 * __deleteOrUpdateAllEvents
 *
 * 指定した全イベントデータの削除または更新処理
 *
 * @param Model $model 実際のモデル名
 * @param string $status status
 * @param array $eventData 元となるeventData
 * @param array $eventKeys 対象とするeventデータ群のkey集合
 * @return void
 * @throws InternalErrorException
 */
	private function __deleteOrUpdateAllEvents(Model $model, $status, $eventData, $eventKeys) {
		if ($status == WorkflowComponent::STATUS_PUBLISHED) {
			// (1)-1 statusが発行済の場合、実際に削除する。
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
		} else {
			// (1)-2 statusが一時保存、承認待ち、差し戻しの場合、現在のrrule配下の全eventDataの
			// excepted（除去）を立てて、無効化しておく。
			// なお、表示に引っかからないよう、is_xxxxもoffしておくこと。

			$fields = array(
				$model->ReservationEvent->alias . '.exception_event_id' => 1,
				$model->ReservationEvent->alias . '.modified_user' =>
					$eventData['ReservationEvent']['modified_user'],
				$model->ReservationEvent->alias . '.modified' =>
					"'" . $eventData['ReservationEvent']['modified'] . "'",	//クオートに注意
				//update,updateAllの時はWFのbeforeSaveによるis_xxxx変更処理は動かない.
				//よってCAL自体でis_xxxxを変更(off)しておく。
				$model->ReservationEvent->alias . '.is_active' => false,	//aaaaaaaaa
				$model->ReservationEvent->alias . '.is_latest' => false,	//aaaaaaaaa
			);
			$conditions = array($model->ReservationEvent->alias . '.key' => $eventKeys);
			if (!$model->ReservationEvent->updateAll($fields, $conditions)) {
				$model->validationErrors = Hash::merge(
					$model->validationErrors, $model->ReservationEvent->validationErrors);
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}
	}

}
