<?php
/**
 * ReservationLocation Model
 *
 * @property Language $Language
 * @property Category $Category
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Your Name <yourname@domain.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppModel', 'Reservations.Model');
App::uses('ReservationLocationOpenText', 'Reservations.Lib');

/**
 * Summary for ReservationLocation Model
 *
 * @property ReservationLocationsRoom $ReservationLocationsRoom
 * @property ReservationLocationsApprovalUser $ReservationLocationsApprovalUser
 * @property ReservationLocationReservable $ReservationLocationReservable
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ReservationLocation extends ReservationsAppModel {

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'NetCommons.OriginalKey',
		'Wysiwyg.Wysiwyg' => array(
			'fields' => array('detail'),
		),
		//多言語
		'M17n.M17n' => array(
			'commonFields' => array( // 言語が異なっても同じにするフィールド
				'category_id',
				'weight'
			),
			'afterCallback' => false,
		),
		'Reservations.ReservationLocationDelete',
		'Reservations.ReservationValidate',
		'Reservations.ReservationLocationValidate',
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Language' => array(
			'className' => 'M17n.Language',
			'foreignKey' => 'language_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Category' => array(
			'className' => 'Categories.Category',
			'foreignKey' => 'category_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

/**
 * 施設キャッシュ用
 *
 * @var array
 */
	protected $_locations = [];

/**
 * Constructor. Binds the model's database table to the object.
 *
 * @param bool|int|string|array $id Set this ID for this model on startup,
 * can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @see Model::__construct()
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->loadModels([
			'Block' => 'Blocks.Block',
		]);
	}

/**
 * Called during validation operations, before validation. Please note that custom
 * validation rules can be defined in $validate.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
 * @see Model::save()
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function beforeValidate($options = array()) {
		$this->validate = ValidateMerge::merge($this->validate,
			array(
				'language_id' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						'message' => __d('net_commons', 'Invalid request.'),
					),
				),
				'category_id' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						'message' => __d('net_commons', 'Invalid request.'),
						'allowEmpty' => true,
					),
				),
				'location_name' => array(
					'notBlank' => array(
						'rule' => array('notBlank'),
						'message' => __d('net_commons', 'Please input %s.', __d('reservations', 'Location name')),
					),
				),
				'time_table' => array(
					'notBlank' => array(
						'rule' => array('notBlank'),
						'message' => __d('reservations', 'Invalid input. Please enter week day.'),
					),
				),
				'start_time' => array(
					'rule1' => array(
						'rule' => array('validateTime'),
						'message' => __d('reservations', 'Invalid input. (time)'),
					),
					'rule2' => array(
						'rule' => array('validateTimeRange', 'end_time'),
						'message' => __d('reservations', 'Invalid input. Please enter the correct time.'),
					),
				),
				'end_time' => array(
					'rule1' => array(
						'rule' => array('validateTime'),
						'message' => __d('reservations', 'Invalid input. (time)'),
					),
				),
				'use_private' => array(
					'boolean' => array(
						'rule' => array('boolean'),
						'message' => __d('net_commons', 'Invalid request.'),
					),
				),
				'use_all_rooms' => array(
					'boolean' => array(
						'rule' => array('boolean'),
						'message' => __d('net_commons', 'Invalid request.'),
					),
				),
				'weight' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						'message' => __d('net_commons', 'Invalid request.'),
					),
				),
			));

		return parent::beforeValidate($options);
	}

/**
 * Called before each find operation. Return false if you want to halt the find
 * call, otherwise return the (modified) query data.
 *
 * @param array $query Data used to execute this query, i.e. conditions, order, etc.
 * @return mixed true if the operation should continue, false if it should abort; or, modified
 *  $query to continue with new $query
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforefind
 */
	public function beforeFind($query) {
		if (Hash::get($query, 'recursive') > -1 && ! $this->id) {
			$belongsTo = $this->Category->bindModelCategoryLang('ReservationLocation.category_id');
			$this->bindModel($belongsTo, true);
		}
		return true;
	}

/**
 * Called after each find operation. Can be used to modify any results returned by find().
 * Return value should be the (modified) results.
 *
 * @param mixed $results The results of the find operation
 * @param bool $primary Whether this model is being queried directly (vs. being queried as an association)
 * @return mixed Result of the find operation
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#afterfind
 */
	public function afterFind($results, $primary = false) {
		if (!isset($results[0][$this->alias])) {
			return $results;
		}
		foreach ($results as $key => $value) {
			if (array_key_exists('time_table', $results[$key][$this->alias]) &&
					array_key_exists('start_time', $results[$key][$this->alias]) &&
					array_key_exists('end_time', $results[$key][$this->alias]) &&
					array_key_exists('timezone', $results[$key][$this->alias])) {
				$openText = new ReservationLocationOpenText();
				$results[$key][$this->alias]['openText'] = $openText->openText($value);
			}
		}
		return $results;
	}

/**
 * Called before each save operation, after validation. Return a non-true result
 * to halt the save.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if the operation should continue, false if it should abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforesave
 * @see Model::save()
 */
	public function beforeSave($options = array()) {
		$content = isset($this->data['ReservationLocation']['detail'])
			? $this->data['ReservationLocation']['detail']
			: null;
		if (empty($content)) {
			return true;
		}

		// Wysiwygでアップロードされた画像やファイルの持ち主は、各ルームではなく、
		// パブリックルームであるべき
		// 各ルームが削除された場合でも、画像やファイルは残しておくため
		$roomId = Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID);
		$newDetail = $this->consistentContent($content, $roomId);
		if ($content != $newDetail) {
			$this->data['ReservationLocation']['detail'] = $newDetail;
		}

		return true;
	}

/**
 * Called after each successful save operation.
 *
 * @param bool $created True if this save created a new record
 * @param array $options Options passed from Model::save().
 * @return void
 * @throws InternalErrorException
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#aftersave
 * @see Model::save()
 */
	public function afterSave($created, $options = array()) {
		$content = isset($this->data['ReservationLocation']['detail'])
			? $this->data['ReservationLocation']['detail']
			: null;
		// Wysiwygでアップロードされた画像やファイルの持ち主は、各ルームではなく、
		// パブリックルームであるべき
		// 各ルームが削除された場合でも、画像やファイルは残しておくため
		// （このroom_idは、Wysiwygでアップロードした画像やファイルの持ち主を決めるための値であり、
		// block_keyの検索条件には使用しない）
		$roomId = Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID);
		// block_keyを取得
		// 施設予約のBlockは「パブリックルームに固定で１つ」なので、room_idの検索条件はパブリックルーム固定にしておく
		$blockKey = $this->Block->findByRoomIdAndPluginKey(
			Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID),
			'reservations',
			['key'],
			null,
			-1
		);
		$updateDetail = [
			'content_key' => isset($this->data['ReservationLocation']['key'])
				? $this->data['ReservationLocation']['key']
				: null,
			'block_key' => isset($blockKey['Block']['key'])
				? $blockKey['Block']['key']
				: null,
			'room_id' => $roomId
		];

		$this->updateUploadFile($content, $updateDetail);
	}

/**
 * 施設生成処理
 *
 * @return array
 */
	public function createLocation() {
		$newLocation = $this->create();
		$newLocation['ReservationLocation'] = Hash::merge(
			$newLocation['ReservationLocation'],
			[
				'start_time' => '09:00',
				'end_time' => '18:00',
				'time_table' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
				'use_all_rooms' => '0',
				'use_workflow' => Current::read('Room.need_approval', '1'),
				'timezone' => Current::read('User.timezone'),
			]
		);
		$newLocation['ReservationLocationsRoom']['room_id'] = [
			Current::read('Room.id'),
		];

		return $newLocation;
	}

/**
 * 施設データの登録
 *
 * @param array $data 登録データ
 * @return bool
 * @throws InternalErrorException
 */
	public function saveLocation($data) {
		$this->loadModels([
			'ReservationLocationsRoom' => 'Reservations.ReservationLocationsRoom',
			'ReservationLocationReservable' => 'Reservations.ReservationLocationReservable',
			'ReservationLocationsApprovalUser' => 'Reservations.ReservationLocationsApprovalUser',
		]);

		$data = $this->_prepareData($data);

		$this->begin();
		try {
			$this->create();

			// 先にvalidate 失敗したらfalse返す
			$this->set($data);
			$this->validate['use_workflow']['seleceUserRule'] = [
				'rule' => ['validateSelectUser'],
				'message' => __d('net_commons', 'Please input %s.', __d('reservations', 'Approver')),
			];
			if (!$this->validates($data)) {
				return false;
			}

			$savedData = $this->save(null, false);
			if (! $savedData) {
				//このsaveで失敗するならvalidate以外なので例外なげる
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			// ReservationLocationsRoom登録
			if (isset($savedData['ReservationLocationsRoom'])) {
				$key = $savedData[$this->alias]['key'];
				if (! $this->ReservationLocationsRoom->saveReservationLocaitonsRoom($key, $savedData)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}
			// ReservationLoationReservable登録
			if (! $this->ReservationLocationReservable->saveReservable($key, $savedData)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			// ReservationLocationsApprovalUser登録
			if (! $this->ReservationLocationsApprovalUser->saveApprovalUser($key, $savedData)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//多言語化の処理
			$this->set($savedData);
			$this->saveM17nData();

			$this->commit();

		} catch (Exception $e) {
			$this->rollback($e);
		}
		return $savedData;
	}

/**
 * 並び替えの保存
 *
 * @param array $data 並び替えデータ
 * @throws InternalErrorException 例外エラー
 * @return bool
 */
	public function saveWeights($data) {
		//トランザクションBegin
		$this->begin();

		//バリデーション
		if (! $this->validateMany($data['ReservationLocations'])) {
			return false;
		}

		try {
			//登録処理
			if (! $this->saveMany($data['ReservationLocations'], ['validate' => false])) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
		}

		return true;
	}

/**
 * 施設データの削除
 *
 * @param string $locationKey 施設キー
 * @return bool
 * @throws InternalErrorException
 */
	public function deleteLocation($locationKey) {
		$this->loadModels([
			'ReservationLocationsRoom' => 'Reservations.ReservationLocationsRoom',
			'ReservationLocationReservable' => 'Reservations.ReservationLocationReservable',
			'ReservationLocationsApprovalUser' => 'Reservations.ReservationLocationsApprovalUser',
			'ReservationEvent' => 'Reservations.ReservationEvent',
			'ReservationEventShareUser' => 'Reservations.ReservationEventShareUser',
			'ReservationFrameSetting' => 'Reservations.ReservationFrameSetting',
		]);

		$this->begin();
		try {
			$this->deleteLocationData($locationKey);

			$this->deleteEventData($locationKey);

			//ReservationFrameSetting のlocation_keyを変更する
			$updateKey = $this->_getLocationKeyByMinWeight();
			$update = [
				$this->ReservationFrameSetting->alias . '.location_key' => '\'' . $updateKey . '\''
			];
			$conditions = [
				$this->ReservationFrameSetting->alias . '.location_key' => $locationKey
			];
			if (! $this->ReservationFrameSetting->updateAll($update, $conditions)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			$this->commit();

		} catch (Exception $e) {
			$this->rollback($e);
		}

		return true;
	}

/**
 * weightの最大値取得
 *
 * @return int
 */
	protected function _getMaxWeight() {
		$order = $this->find('first', array(
				'recursive' => -1,
				'fields' => array('weight'),
				'order' => array('weight' => 'DESC')
			));

		if (isset($order[$this->alias]['weight'])) {
			$weight = (int)$order[$this->alias]['weight'];
		} else {
			$weight = 0;
		}
		return $weight;
	}

/**
 * weightの最小値の施設キー取得
 *
 * @return int
 */
	protected function _getLocationKeyByMinWeight() {
		$location = $this->find('first', array(
			'recursive' => -1,
			'fields' => array('key', 'weight'),
			'order' => array('weight' => 'ASC')
		));

		if (! $location) {
			$locationKey = null;
		} else {
			$locationKey = $location[$this->alias]['key'];
		}
		return $locationKey;
	}

/**
 * 施設データを取得する
 *
 * アクセス可能なルームから予約を受け付けてる施設だけに絞り込んで返す
 *
 * @param int $categoryId カテゴリID 0を指定したときは例外的にカテゴリ未指定を返す
 * @return array
 */
	public function getLocations($categoryId = null) {
		// ログインユーザが参加してるルームを取得
		$accessibleRoomIds = $this->getReadableRoomIds();
		$this->loadModels([
			'ReservationLocationsRoom' => 'Reservations.ReservationLocationsRoom',
			//'ReservationLocationReservable' => 'Reservations.ReservationLocationReservable'
		]);
		$locationsRooms = $this->ReservationLocationsRoom->find('all', ['conditions' => [
			'room_id' => $accessibleRoomIds,
		]]);
		$locationKeys = Hash::combine($locationsRooms,
			'{n}.ReservationLocationsRoom.reservation_location_key',
			'{n}.ReservationLocationsRoom.reservation_location_key'
			);
		// そのルームからの予約を受け付ける施設を取得
		$options = [
			'conditions' => [
				'language_id' => Current::read('Language.id'),
				'OR' => [
					'use_all_rooms' => 1, // 全てのルームから予約を受け付ける施設
					'ReservationLocation.key' => $locationKeys
				]
			],
			'order' => 'ReservationLocation.weight ASC'
		];
		if (isset($categoryId)) {
			if ($categoryId != 0) {
				$options['conditions']['category_id'] = $categoryId;
			} else {
				$options['conditions']['category_id'] = null;
			}
		}

		$locations = $this->find('all', $options);
		return $locations;
	}

/**
 * 予約可能な施設を返す
 *
 * @param int $categoryId カテゴリID
 * @param int $userId ユーザID
 * @return array
 */
	public function getReservableLocations($categoryId = null, $userId = null) {
		if (! $userId) {
			$userId = Current::read('User.id');
		}
		$this->loadModels(
			[
				'ReservationLocationsRoom' => 'Reservations.ReservationLocationsRoom',
				'ReservationLocationReservable' => 'Reservations.ReservationLocationReservable',
				'ReservationLocationsApprovalUser' => 'Reservations.ReservationLocationsApprovalUser',
				//'ReservationLocationRoom' => 'Reservations.ReservationLocationRoom'
			]
		);
		$locations = $this->getLocations($categoryId);

		// あらかじめ承認ユーザ取得する
		$approvalUsers = $this->ReservationLocationsApprovalUser->findApprovalUserIdsByLocations(
			$locations
		);
		foreach ($locations as $key => $location) {
			$locationKey = $location['ReservationLocation']['key'];

			// 承認ユーザを配列にセット
			$locations[$key]['approvalUserIds'] = $approvalUsers[$locationKey] ?? [];
		}
		// プライベートルームを除外したルームIDで予約可能かチェック
		$roomIds = $this->getReadableRoomIdsWithOutPrivate();
		$reservableLocations = [];

		// 予め予約可能なロール情報をロードしておく
		$this->ReservationLocationReservable->loadAll($roomIds);

		foreach ($locations as $location) {
			// いずれかのルームで予約できるなら予約可能な施設とする
			if ($this->ReservationLocationReservable->isReservableByLocation($location)) {
				$reservableLocations[] = $location;
			}
		}
		return $reservableLocations;
	}

/**
 * 施設キーを元に施設情報を返す（内部キャッシュ有り）
 *
 * @param string $locationKey 施設キー
 * @return mixed
 */
	public function getByKey($locationKey) {
		// 何度も同じ施設で確認だすからキャッシュしとく
		if (!isset($this->_locations[$locationKey])) {
			//$this->ReservationLocation = ClassRegistry::init('Reservations.ReservationLocation');
			$this->loadModels([
				'ReservationLocationsApprovalUser' => 'Reservations.ReservationLocationsApprovalUser'
			]);
			//$this->ReservationLocationsApprovalUser = ClassRegistry::init(
			//	'Reservations.ReservationLocationsApprovalUser'
			//);
			$conditions = [
				'ReservationLocation.key' => $locationKey,
			];
			$location = $this->find(
				'first',
				[
					'conditions' =>
						$conditions
				]
			);
			if (!$location) {
				return [];
			}
			$location['approvalUserIds'] =
				$this->ReservationLocationsApprovalUser->getApprovalUserIdsByLocation($location);
			$this->_locations[$locationKey] = $location;

		}
		return $this->_locations[$locationKey];
	}

/**
 * 保存前にpostされたdataを保存用に加工する
 *
 * @param array $data POSTされたdata
 * @return array 保存用に加工されたdata
 */
	protected function _prepareData($data) {
		if (is_array(Hash::get($data, 'ReservationLocation.time_table'))) {
			$timeTable = implode('|', $data['ReservationLocation']['time_table']);
			$data['ReservationLocation']['time_table'] = $timeTable;
		}

		// 全日フラグあったら00:00-24:00あつかいにする
		if ($data['ReservationLocation']['allday_flag']) {
			$data['ReservationLocation']['start_time'] = '00:00';
			$data['ReservationLocation']['end_time'] = '24:00';
		}
		// start_time end_timeをUTCに変換
		$startDateTime = Date('Y-m-d') . $data['ReservationLocation']['start_time'] . ':00';
		$endDateTime = Date('Y-m-d') . $data['ReservationLocation']['end_time'] . ':00';
		$ncTime = new NetCommonsTime();
		$startDateTime4UTC = $ncTime->toServerDatetime($startDateTime,
			$data['ReservationLocation']['timezone']);
		$endDateTime4UTC = $ncTime->toServerDatetime($endDateTime,
			$data['ReservationLocation']['timezone']);
		$data['ReservationLocation']['start_time'] = $startDateTime4UTC;
		$data['ReservationLocation']['end_time'] = $endDateTime4UTC;

		// category_id=0だったらnullにする。そうしないと空文字としてSQL発行される
		if (empty($data[$this->alias]['category_id'])) {
			$data[$this->alias]['category_id'] = null;
		}

		//新規の場合、順番を最大値＋１にする
		if (empty($data['ReservationLocation']['id'])) {
			$data[$this->alias]['weight'] = $this->_getMaxWeight() + 1;
		}

		// 予約を受け付けるルームがひとつもえらばれてないとき
		if (!$data['ReservationLocationsRoom']['room_id']) {
			$data['ReservationLocationsRoom']['room_id'] = array();
		}

		return $data;
	}

/**
 * getAliveCondition
 * 現在使用中状態であるか判断する。CleanUpプラグインで使用
 *
 * @param array $key 判断対象のデータのキー
 * @return array
 */
	public function getAliveCondition($key) {
		return array(
			'conditions' => array(
				'ReservationLocation.key' => $key,
			),
		);
	}
}
