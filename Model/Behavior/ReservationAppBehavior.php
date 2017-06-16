<?php
/**
 * ReservationApp Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');
//App::uses('ReservationTime', 'Reservations.Utility');
App::uses('ReservationTime', 'Reservations.Utility');
App::uses('ReservationSupport', 'Reservations.Utility');
App::uses('ReservationRruleUtil', 'Reservations.Utility');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');
App::uses('ReservationPermissiveRooms', 'Reservations.Utility');

/**
 * ReservationAppBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model\Behavior
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class ReservationAppBehavior extends ModelBehavior {
	const CALENDAR_PLAN_EDIT_THIS = '0';	//この日の予定のみ変更(削除)
	const CALENDAR_PLAN_EDIT_AFTER = '1';	//この日以降の予定を変更(削除)
	const CALENDAR_PLAN_EDIT_ALL = '2';		//この日を含むすべての予定を変更(削除)

	const CALENDAR_PLUGIN_NAME = 'reservation';
	const TASK_PLUGIN_NAME = 'task';	//ＴＯＤＯプラグインに相当
	const RESERVATION_PLUGIN_NAME = 'reservation';

	const CALENDAR_LINK_UPDATE = 'update';
	const CALENDAR_LINK_CLEAR = 'clear';

	const CALENDAR_INSERT_MODE = 'insert';
	const CALENDAR_UPDATE_MODE = 'update';


	//以下は暫定定義
	const _ON = 1;
	const _OFF = 0;

	const ROOM_ZERO = 0;

/**
 * reservationWdayArray
 *
 * @var array
 */
	public static $reservationWdayArray = array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA');

/**
 * edit_rrrule_list
 *
 * @var array
 */
	public static $editRrules = array(
		self::CALENDAR_PLAN_EDIT_THIS,
		self::CALENDAR_PLAN_EDIT_AFTER,
		self::CALENDAR_PLAN_EDIT_ALL
	);

/**
 * beforeValidate is called before a model is validated, you can use this callback to
 * add behavior validation rules into a models validate array. Returning false
 * will allow you to make the validation fail.
 *
 * @param Model $model Model using this behavior
 * @param array $options Options passed from Model::save().
 * @return mixed False or null will abort the operation. Any other result will continue.
 * @see Model::save()
 */
	public function beforeValidate(Model $model, $options = array()) {
		//ReservationEventに移動
		//if ($model->alias == 'ReservationEvent') {
		//	ReservationPermissiveRooms::setCurrentPermission($model->data['ReservationEvent']['room_id']);
		//}
	}
/**
 * Called after data has been checked for errors
 *
 * @param Model $model Model using this behavior
 * @return void
 */
	public function afterValidate(Model $model) {
		if ($model->alias == 'ReservationEvent') {
			//ReservationPermissiveRooms::recoverCurrentPermission();
		}
	}

/**
 * 繰返し専用event登録処理(*event初回登録に使ってはいけません）
 *
 * 毎回 keyをclearしてから登録します。(初回登録から踏襲するのは、status, is_active, is_latestとします)
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $eventData eventデータ(ReservationEventのモデルデータ)
 * @param string $startTime startTime 開始日付時刻文字列
 * @param string $endTime endTime 開始日付時刻文字列
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return array $rEventData
 * @throws InternalErrorException
 */
	public function insert(Model &$model, $planParams, $rruleData, $eventData, $startTime, $endTime,
		$createdUserWhenUpd = null) {
		$this->loadEventAndRruleModels($model);
		$params = array(
			'conditions' => array(
				'ReservationRrule.id' => $eventData['ReservationEvent']['reservation_rrule_id']
			),
			'recursive' => (-1),
			'fields' => array('ReservationRrule.*'),
			'callbacks' => false
		);
		if (empty($this->rruleData)) {
			//ReservationRruleのデータがないので初回アクセス
			//
			$rruleData = $model->ReservationRrule->find('first', $params);
			if (!is_array($rruleData) || !isset($rruleData['ReservationRrule'])) {
				$model->validationErrors =
					Hash::merge($model->validationErrors, $model->ReservationRrule->validationErrors);
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			//ReservationRruleのデータを記録し、２度目以降に備える。
			$this->rruleData = $rruleData;
		}

		//NC3では内部はサーバー系日付時刻になっているのでtimezoneDateはつかわない.
		//また、引数$starTime, $endTimeはすでに、YmdHis形式で渡されることになっているので、
		//$insertStartTime, $insertEndTimeにそのまま代入する。
		$insertStartTime = $startTime;
		$insertEndTime = $endTime;

		//eventDataをもとにrEventDataをつくり、モデルにセット
		$rEventData = $this->setReventData(
			$eventData, $insertStartTime, $insertEndTime, $planParams);

		//eventのkeyを新しく採番するため、nullクリアします。
		$rEventData['ReservationEvent']['key'] = null;

		//バリデーションエラー含め、モデルの状態リセット
		$model->ReservationEvent->clear();

		$model->ReservationEvent->set($rEventData);
		/* FIXME: なぜか子Modelのcontent_key不正がでる。要調査。
		if (!$model->ReservationEvent->validates()) {	//rEventDataをチェック
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->ReservationEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		*/
		//eventの保存。
		//なお、追加情報(workflowcomment)は WFCのafterSave()で自動セットされる。
		//
		if (!$model->ReservationEvent->save($rEventData, false)) { //保存のみ
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
			$rEventData['ReservationEvent']['created_user'] = $createdUserWhenUpd;
		}

		//採番されたidをeventDataにセット
		$rEventData['ReservationEvent']['id'] = $model->ReservationEvent->id;

		$this->_insertChidren($model, $planParams, $rEventData, $createdUserWhenUpd);

		return $rEventData;
	}

/**
 * rEventDataへのデータ設定
 *
 * @param array $eventData 元になるeventData配列
 * @param string $insertStartTime insertStartTime 登録用開始日付時刻文字列
 * @param string $insertEndTime insertEndTime 登録用終了日付時刻文字列
 * @param array $planParams planParamsが渡ってくる。追加拡張を取り出す為に必要。
 * @return array 実際に登録する$rEventData配列を返す
 */
	public function setReventData($eventData, $insertStartTime, $insertEndTime, $planParams) {
		$rEventData = $eventData;

		$rEventData['ReservationEvent']['id'] = null;		//新規登録用にidにnullセット

		$rEventData['ReservationEvent']['start_date'] = substr($insertStartTime, 0, 8);
		$rEventData['ReservationEvent']['start_time'] = substr($insertStartTime, 8);
		$rEventData['ReservationEvent']['dtstart'] = $insertStartTime;
		$rEventData['ReservationEvent']['end_date'] = substr($insertEndTime, 0, 8);
		$rEventData['ReservationEvent']['end_time'] = substr($insertEndTime, 8);
		$rEventData['ReservationEvent']['dtend'] = $insertEndTime;

		if (isset($eventData['ReservationEvent']['created_user'])) {
			$rEventData['ReservationEvent']['created_user'] = $eventData['ReservationEvent']['created_user'];
		}

		if (isset($eventData['ReservationEvent']['created'])) {
			$rEventData['ReservationEvent']['created'] = $eventData['ReservationEvent']['created'];
		}

		if (isset($eventData['ReservationEvent']['modified_user'])) {
			$rEventData['ReservationEvent']['modified_user'] =
											$eventData['ReservationEvent']['modified_user'];
		}

		if (isset($eventData['ReservationEvent']['modified'])) {
			$rEventData['ReservationEvent']['modified'] = $eventData['ReservationEvent']['modified'];
		}

		//workflowcommentなどの追加拡張データはここで追加する。
		//
		$addInfo = Hash::get($planParams, ReservationsComponent::ADDITIONAL);
		if (! empty($addInfo)) {
			foreach ($addInfo as $modelName => $vals) {
				$rEventData[$modelName] = $vals;
			}
		}

		return $rEventData;
	}

/**
 * startDate,startTime,endDate,endTime生成
 *
 * @param string $sTime サーバー系sTime文字列(YmdHis)
 * @param string $eTime サーバー系eTime文字列(YmdHis)
 * @param string $byday byday サーバー系byday日文字列(YmdHis)
 * @param string $userTz userTz ユーザー系タイムゾーンID (Asia/Tokyoなど)
 * @param string &$startDate 生成したサーバー系startDate文字列
 * @param string &$startTime 生成したサーバー系startTime文字列
 * @param string &$endDate 生成したサーバー系endDate文字列
 * @param string &$endTime 生成したサーバー系endTime文字列
 * @return void
 */
	public function setStartDateTiemAndEndDateTime($sTime, $eTime, $byday, $userTz,
		&$startDate, &$startTime, &$endDate, &$endTime) {
		//INPUT引数のsTime, eTime, bydayはサーバー系なので、
		//まずは、それぞれをTZのユーザー系のYmdHisに変換する。
		$userStartTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($sTime));
		$userStartTime = ReservationTime::dt2calDt($userStartTime);
		$userEndTime = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($eTime));
		$userEndTime = ReservationTime::dt2calDt($userEndTime);
		$userByday = (new NetCommonsTime())->toUserDatetime(ReservationTime::calDt2dt($byday));
		$userByday = ReservationTime::dt2calDt($userByday);

		//施設予約上（＝ユーザー系）の開始日の00:00:00のtimestamp取得
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザーTZ系のDateTimeObj生成
		$date->setDate(substr($userStartTime, 0, 4),
			substr($userStartTime, 4, 2), substr($userStartTime, 6, 2));
		$date->setTime(0, 0, 0);
		$startTimestamp = $date->getTimestamp();

		//施設予約上（＝ユーザー系）の終了日の00:00:00のtimestamp取得
		$date->setDate(substr($userEndTime, 0, 4),
			substr($userEndTime, 4, 2), substr($userEndTime, 6, 2));
		$date->setTime(0, 0, 0);
		$endTimestamp = $date->getTimestamp();

		//開始日と終了日の差分の日数(a)を算出
		$diffNum = ($endTimestamp - $startTimestamp) / 86400;

		//日付がbyday日で時刻が開始日時刻のタイムスタンプの、「サーバー系」のYmdとHisを取得する
		//
		$sdate = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザーTZ系のDateTimeObj生成
		$sdate->setDate(substr($userByday, 0, 4),
			substr($userByday, 4, 2), substr($userByday, 6, 2));
		$sdate->setTime(substr($userStartTime, 8, 2),
			substr($userStartTime, 10, 2), substr($userStartTime, 12, 2));
		$sdate->setTimezone(new DateTimeZone('UTC'));	//サーバーTZに切り替える
		$startDate = $sdate->format('Ymd');	//サーバー系の開始日付時刻のYmd
		$startTime = $sdate->format('His');	//サーバー系の開始日付時刻のHis

		//月がbyday月、日がbyday日+差分日数(a)で時刻が終了日時刻のタイムスタンプの、「サーバー系」のYmdとHisを取得する
		//
		$edate = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザーTZ系のDateTimeObj生成
		$edate->setDate(substr($userByday, 0, 4),
			substr($userByday, 4, 2), substr($userByday, 6, 2) + $diffNum);
		$edate->setTime(substr($userEndTime, 8, 2),
			substr($userEndTime, 10, 2), substr($userEndTime, 12, 2));
		$edate->setTimezone(new DateTimeZone('UTC'));	//サーバーTZに切り替える
		$endDate = $edate->format('Ymd');	//サーバー系の終了日付時刻のYmd
		$endTime = $edate->format('His');	//サーバー系の終了日付時刻のHis
	}

/**
 * RruleDataへのデータ設定
 *
 * @param Model &$model model
 * @param array $planParams 予定パラメータ
 * @param array &$rruleData rruleデータ
 * @param string $mode mode insert時:self::CALENDAR_INSERT_MODE(デフォルト値) update時:self::CALENDAR_UPDATE_MODE
 * @param string $rruleKey rruleKey 未指定時はnull. null以外の文字列の時はこのkeyを使う。
 * @param int $rruleId rruleId updateの時、このrruleIdをidに使う
 * @return void
 */
	public function setRruleData(&$model, $planParams, &$rruleData,
		$mode = self::CALENDAR_INSERT_MODE, $rruleKey = null, $rruleId = 0) {
		if (!(isset($model->Reservation))) {
			$model->loadModels(['Reservation' => 'Reservations.Reservation']);
		}
		$blockKey = Current::read('Block.key');
		$reservation = $model->Reservation->findByBlockKey($blockKey);	//find('first'形式で返る
		$reservationId = 1;	//暫定初期値
		if (!empty($reservation['Reservation']['id'])) {
			$reservationId = $reservation['Reservation']['id'];
		}
		//CakeLog::debug("DBG: blockKey[" . $blockKey . "] reservation[" . print_r($reservation, true) .
		//	"] reservationId[" . $reservationId . "]");

		$params = array(
			'reservation_id' => $reservationId,
			'name' => '',
			'rrule' => '',
			'ireservation_uid' => ReservationRruleUtil::generateIcalUid(
				$planParams['start_date'], $planParams['start_time']),
			'ireservation_comp_name' => self::CALENDAR_PLUGIN_NAME,
			'room_id' => Current::read('Room.id'),	//FIXME: eventのroom_idと合わせるべきでは？
			//'language_id' => Current::read('Language.id'),
			//'status' => WorkflowComponent::STATUS_IN_DRAFT,
		);

		foreach ($planParams as $key => $val) {
			if (isset($params[$key])) {
				$params[$key] = $val;
			}
		}

		//レコード $rrule_data  の初期化と'ReservationRrule'キーセットはおわっているので省略
		//rruleDataに詰める。

		//INSERT_MODEの時はidは自動採番されるので、セット不要
		if ($mode === self::CALENDAR_UPDATE_MODE && $rruleId !== 0) {
			//UPDATE_MODEの時は、更新対象のrruleIdを指定
			$rruleData['ReservationRrule']['id'] = $rruleId;
		}

		$rruleData['ReservationRrule']['reservation_id'] = $params['reservation_id'];
		$rruleData['ReservationRrule']['name'] = $params['name'];
		if ($rruleKey !== null) {
			$rruleData['ReservationRrule']['key'] = $rruleKey;
		}
		$rruleData['ReservationRrule']['rrule'] = $params['rrule'];
		if ($mode === self::CALENDAR_INSERT_MODE) {
			$rruleData['ReservationRrule']['ireservation_uid'] = $params['ireservation_uid'];
			$rruleData['ReservationRrule']['ireservation_comp_name'] = $params['ireservation_comp_name'];
		}
		$rruleData['ReservationRrule']['room_id'] = $params['room_id'];
		////$rruleData['ReservationRrule']['language_id'] = $params['language_id'];
		////$rruleData['ReservationRrule']['status'] = $params['status'];
		//is_active,is_latestは、Workflowが自動セット ->　is_xxxは項目削除した。
		//create_user, created, modified_user, modifiedは、Trackableが自動セット
	}

/**
 * setPlanParams2Params
 *
 * planParamsからparamsへの設定
 *
 * @param array &$planParams 予定パラメータ
 * @param array &$params paramsパラメータ
 * @return void
 */
	public function setPlanParams2Params(&$planParams, &$params) {
		$keys = array(
			'title',
			'title_icon',
			'location',
			'contact',
			'description',
			'is_allday',
			'timezone',
			'linked_model',
			'linked_content_key',
			'enable_email',
			'email_send_timing',
			'use_calendar'
		);
		foreach ($keys as $key) {
			if (isset($planParams[$key])) {
				$params[$key] = $planParams[$key];
			}
		}
	}

/**
 * eventDataへのデータ設定
 *
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleDataパラメータ
 * @param array &$eventData eventデータ
 * @return void
 */
	public function setEventData($planParams, $rruleData, &$eventData) {
		//
		//補足）ここでは、idとkeyは一切セットしていない。なので、$eventDataに値がなければ
		//新規生成されるし、あればその値がそのまま利用されます。
		//
		//初期化
		$params = array(
			'reservation_rrule_id' => $rruleData['ReservationRrule']['id'],	//外部キーをセット
			//keyは、Workflowが自動セット
			////'room_id' => $rruleData['ReservationRrule']['room_id'],	//rruleDataにroom_idがあるのはおかしい。
			'room_id' => $planParams['room_id'],	//画面で指定したroom_idとなるように修正.
			'language_id' => Current::read('Language.id'),
			'target_user' => Current::read('User.id'),
			'title' => '',
			'title_icon' => '',
			'location' => '',
			'contact' => '',
			'description' => '',
			'is_allday' => self::_OFF,
			'start_date' => $planParams['start_date'],
			'start_time' => $planParams['start_time'],
			'dtstart' => $planParams['start_date'] . $planParams['start_time'],
			'end_date' => $planParams['end_date'],
			'end_time' => $planParams['end_time'],
			'dtend' => $planParams['end_date'] . $planParams['end_time'],
			'timezone' => $planParams['timezone'],
			//'timezone' => Hash::get($planParams, 'timezone'),
			'status' => $planParams['status'],
			'enable_email' => $planParams['enable_email'],
			'email_send_timing' => $planParams['email_send_timing'],

			'linked_model' => '',
			'linked_content_key' => '',
			'location_key' => $planParams['location_key'],
		);

		$this->setPlanParams2Params($planParams, $params);

		//レコード $event_data  の初期化と'ReservationEvent'キーセットはおわっているので省略
		//$eventData = array();
		//$eventData['ReservationEvent'] = array();

		//eventを詰める。
		//$eventData['ReservationEvent']['id'] = null;		//create()の後なので、不要。
		$eventData['ReservationEvent']['reservation_rrule_id'] = $params['reservation_rrule_id'];
		$eventData['ReservationEvent']['room_id'] = $params['room_id'];
		$eventData['ReservationEvent']['language_id'] = $params['language_id'];
		$eventData['ReservationEvent']['target_user'] = $params['target_user'];
		$eventData['ReservationEvent']['title'] = $params['title'];
		$eventData['ReservationEvent']['title_icon'] = $params['title_icon'];
		$eventData['ReservationEvent']['is_allday'] = $params['is_allday'];
		$eventData['ReservationEvent']['start_date'] = $params['start_date'];
		$eventData['ReservationEvent']['start_time'] = $params['start_time'];
		$eventData['ReservationEvent']['dtstart'] = $params['dtstart'];
		$eventData['ReservationEvent']['end_date'] = $params['end_date'];
		$eventData['ReservationEvent']['end_time'] = $params['end_time'];
		$eventData['ReservationEvent']['dtend'] = $params['dtend'];
		$eventData['ReservationEvent']['timezone'] = $params['timezone'];
		$eventData['ReservationEvent']['status'] = $params['status'];

		$eventData['ReservationEvent']['location'] = $params['location'];
		$eventData['ReservationEvent']['contact'] = $params['contact'];
		$eventData['ReservationEvent']['description'] = $params['description'];

		$eventData['ReservationEvent']['is_enable_mail'] = $params['enable_email']; //名違いに注意
		$eventData['ReservationEvent']['email_send_timing'] = $params['email_send_timing'];

		$eventData['ReservationEvent']['location_key'] = $params['location_key'];

		$eventData['ReservationEvent']['use_calendar'] = $params['use_calendar'];

		////保存するモデルをここで替える
		//$eventData['ReservationEventContent']['linked_model'] = $params['linked_model'];
		//$eventData['ReservationEventContent']['linked_content_key'] = $params['linked_content_key'];

		//workflowcommentなどの追加拡張データはここで追加する。
		//
		$addInfo = Hash::get($planParams, ReservationsComponent::ADDITIONAL);
		if (! empty($addInfo)) {
			foreach ($addInfo as $modelName => $vals) {
				$eventData[$modelName] = $vals;
			}
		}
	}

/**
 * eventとrruleの両モデルをロードする。
 *
 * @param Model &$model モデル
 * @return void
 */
	public function loadEventAndRruleModels(Model &$model) {
		if (!isset($model->ReservationEvent)) {
			$model->loadModels([
				'ReservationEvent' => 'Reservations.ReservationEvent'
			]);
		}
		if (!isset($model->ReservationRrule)) {
			$model->loadModels([
				'ReservationRrule' => 'Reservations.ReservationRrule'
			]);
		}
	}

/**
 * _insertChidren
 *
 * 関連する(hasMany関係にある）子レコードを登録する
 *
 * @param Model &$model モデル
 * @param array $planParams planParams
 * @param array $eventData eventData
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return void
 */
	protected function _insertChidren(&$model, $planParams, $eventData, $createdUserWhenUpd = null) {
		//カレンダ共有ユーザ登録
		if (!$model->Behaviors->hasMethod('insertShareUsers')) {
			$model->Behaviors->load('Reservations.ReservationShareUserEntry');
		}
		$model->insertShareUsers($planParams['share_users'], $eventData['ReservationEvent']['id'],
			$createdUserWhenUpd);
		//注: 他のモデルの組み込みBehaviorをcallする場合、第一引数に$modelの指定はいらない。

		//関連コンテンツの登録
		//if (isset($eventData['ReservationEventContent']) &&
		//	$eventData['ReservationEventContent']['linked_model'] !== '') {
		//	if (!(isset($model->ReservationEventContent))) {
		//		$model->loadModels(['ReservationEventContent' => 'Reservation.ReservationEventContent']);
		//	}
		//	$model->ReservationEventContent->saveLinkedData($eventData, $createdUserWhenUpd);
		//}
	}

/**
 * shareUser変数を整える
 *
 * @param array &$planParams planParamsパラメータ
 * @return void
 * @throws InternalErrorException
 */
	protected function _arrangeShareUsers(&$planParams) {
		if (!isset($planParams['share_users'])) {
			$planParams['share_users'] = null;
			return;
		}
		if (!is_null($planParams['share_users']) && !is_string($planParams['share_users']) &&
			!is_array($planParams['share_users'])) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$planParams['share_users'] = is_string($planParams['share_users']) ?
			array($planParams['share_users']) : $planParams['share_users'];
	}
}
