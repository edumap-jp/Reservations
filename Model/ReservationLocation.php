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

/**
 * Summary for ReservationLocation Model
 */
class ReservationLocation extends ReservationsAppModel {

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'NetCommons.Trackable',
		'NetCommons.OriginalKey',
		'Wysiwyg.Wysiwyg' => array(
			'fields' => array('detail'),
		),
		//多言語
		'M17n.M17n' => array(
			'commonFields' => array( // 言語が異なっても同じにするフィールド
				'category_id',
			),
			'afterCallback' => false,
		),
		'Reservations.ReservationValidate',
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
		$this->validate = Hash::merge($this->validate,
			array(
				'language_id' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						//'message' => 'Your custom message here',
						//'allowEmpty' => false,
						//'required' => false,
						//'last' => false, // Stop validation after this rule
						//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
				),
				'category_id' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						//'message' => 'Your custom message here',
						'allowEmpty' => true,
						//'required' => false,
						//'last' => false, // Stop validation after this rule
						//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
				),
				'location_name' => array(
					'notBlank' => array(
						'rule' => array('notBlank'),
						'message' => __d('net_commons', 'Please input %s.', __d('reservations', 'Location name')),
						//'allowEmpty' => false,
						//'required' => false,
						//'last' => false, // Stop validation after this rule
						//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
				),
				'add_authority' => array(
					'boolean' => array(
						'rule' => array('boolean'),
						//'message' => 'Your custom message here',
						//'allowEmpty' => false,
						//'required' => false,
						//'last' => false, // Stop validation after this rule
						//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
				),
				'time_table' => array(
					'notBlank' => array(
						'rule' => array('notBlank'),
						'message' => __d('reservations', '曜日を選択してください。'),
						//'allowEmpty' => false,
						//'required' => false,
						//'last' => false, // Stop validation after this rule
						//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
				),
				'start_time' => array(
					'rule1' => array(
						'rule' => array('validateTime'),
						'message' => __d('reservations', 'Invalid value.'),
					),
					'rule2' => array(
						'rule' => array('validateTimeRange', 'end_time'),
						'message' => __d('reservations', 'Invalid value.'),
					),
				),
				'end_time' => array(
					'rule1' => array(
						'rule' => array('validateTime'),
						'message' => __d('reservations', 'Invalid value.'),
					),
				),
				'use_private' => array(
					'boolean' => array(
						'rule' => array('boolean'),
						//'message' => 'Your custom message here',
						//'allowEmpty' => false,
						//'required' => false,
						//'last' => false, // Stop validation after this rule
						//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
				),
				'use_auth_flag' => array(
					'boolean' => array(
						'rule' => array('boolean'),
						//'message' => 'Your custom message here',
						//'allowEmpty' => false,
						//'required' => false,
						//'last' => false, // Stop validation after this rule
						//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
				),
				'use_all_rooms' => array(
					'boolean' => array(
						'rule' => array('boolean'),
						//'message' => 'Your custom message here',
						//'allowEmpty' => false,
						//'required' => false,
						//'last' => false, // Stop validation after this rule
						//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
				),
				'display_sequence' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						//'message' => 'Your custom message here',
						//'allowEmpty' => false,
						//'required' => false,
						//'last' => false, // Stop validation after this rule
						//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
				),
		));
		//$this->_doMergeWorkflowParamValidate(); //Workflowパラメータ関連validation
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
 * 時刻バリデーション H:i形式か。
 *
 * @param array $check チェックする値の配列
 * @return bool
 */
	public function validateTime($check) {
		$values = array_values($check);
		$time = $values[0];

		if (!preg_match('/^[0-9]{2}:[0-9]{2}$/', $time)) {
			return false;
		}
		list($hour, $min) = explode(':', $time);
		if (intval($hour) < 0 || intval($hour) > 24) {
			return false;
		}
		if (intval($min) < 0 || intval($min) > 59) {
			return false;
		}
		return true;
	}

/**
 * 時刻範囲バリデーション
 *
 * @param array $check 開始の配列
 * @param string $endKey 終了値の入るキー名
 * @return bool
 */
	public function validateTimeRange($check, $endKey) {
		$values = array_values($check);
		$startTime = $values[0];

		$endTime = $this->data[$this->alias][$endKey];
		return ($startTime < $endTime);
	}

/**
 * 施設データの登録
 *
 * @param array $data 登録データ
 * @return bool
 * @throws InternalErrorException
 */
	public function saveLocation($data) {
		$data = $this->_prepareData($data);

		$this->begin();
		try {
			$this->create(); //
			// 先にvalidate 失敗したらfalse返す
			$this->set($data);
			if (!$this->validates($data)) {
				return false;
			}
			$savedData = $this->save($data, false);
			if (! $savedData) {
				//このsaveで失敗するならvalidate以外なので例外なげる
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			// ReservationLocationsRoom登録
			if (isset($savedData['ReservationLocationsRoom'])) {

				$this->loadModels([
					'ReservationLocationsRoom' => 'Reservations.ReservationLocationsRoom',
				]);
				$key = $savedData[$this->alias]['key'];
				if (!$this->ReservationLocationsRoom->saveReservationLocaitonsRoom($key, $savedData)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
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
 * 施設データを取得する
 *
 * TODO: 後で自分が閲覧可のみ取得するように条件を追加する
 * とりあえず、すべて取得する
 *
 * @return array
 */
	public function getLocations($categoryId = null) {
		$options = [
			'conditions' => [
				'language_id' => Current::read('Language.id'),
			],
			'order' => 'ReservationLocation.weight ASC'
		];
		if (isset($categoryId)) {
			$options['conditions']['category_id'] = $categoryId;
		}

		$locations = $this->find('all', $options);
		// openTextをセットする。
		foreach ($locations as &$location) {
			$location['ReservationLocation']['openText'] = $this->openText($location);
		}
		return $locations;
	}

/**
 * openTextを返す
 *
 * @param array $reservationLocation 施設データ
 * @return string
 */
	public function openText($reservationLocation) {
		$ret = '';
		$weekDaysOptions = [
			'Sun' => __d('holidays', 'Sunday'),
			'Mon' => __d('holidays', 'Monday'),
			'Tue' => __d('holidays', 'Tuesday'),
			'Wed' => __d('holidays', 'Wednesday'),
			'Thu' => __d('holidays', 'Thursday'),
			'Fri' => __d('holidays', 'Friday'),
			'Sat' => __d('holidays', 'Saturday'),
		];
		$timeTable = $reservationLocation['ReservationLocation']['time_table'];
		if ($timeTable === 'Sun|Mon|Tue|Wed|Thu|Fri|Sat') {
			//毎日
			$ret = __d('reservations', '毎日');
		} elseif ($timeTable === 'Mon|Tue|Wed|Thu|Fri') {
			// 平日
			$ret = __d('reservations', '平日');
		} else {
			$timeTable = explode('|', $timeTable);
			$weekList = [];
			foreach ($timeTable as $weekday) {
				if ($weekday) {
					$weekList[] = $weekDaysOptions[$weekday];
				}
			}
			$ret = implode(', ', $weekList);
		}

		//時間
		$ret = sprintf('%s %s-%s',
			$ret,
			$reservationLocation['ReservationLocation']['start_time'],
			$reservationLocation['ReservationLocation']['end_time']
		);
		return $ret;
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
		// category_id=0だったらnullにする。そうしないと空文字としてSQL発行される
		if (empty($data[$this->alias]['category_id'])) {
			$data[$this->alias]['category_id'] = null;
		}

		// 予約を受け付けるルームがひとつもえらばれてないとき
		if (!$data['ReservationLocationsRoom']['room_id']) {
			$data['ReservationLocationsRoom']['room_id'] = array();
		}

		return $data;
	}
}
