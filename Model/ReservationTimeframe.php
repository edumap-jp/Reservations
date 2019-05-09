<?php
/**
 * ReservationTimeframe Model
 *
 * @property Language $Language
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppModel', 'Reservations.Model');

/**
 * Summary for ReservationTimeframe Model
 */
class ReservationTimeframe extends ReservationsAppModel {

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'NetCommons.OriginalKey',
		//多言語
		'M17n.M17n' => array(
			'commonFields' => array( // 言語が異なっても同じにするフィールド
				'color',
			),
			'afterCallback' => false,
		),
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array();

	//The Associations below have been created with all possible keys, those that are not needed can be removed

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
		)
	);

/**
 * beforeValidate
 *
 * @param array $options options
 * @return bool
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
				'title' => array(
					'notBlank' => array(
						'rule' => array('notBlank'),
						'message' => __d(
							'net_commons',
							'Please input %s.', __d('reservations', 'Time frame name')
						),
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
					'rule3' => [
						'rule' => ['validateTimeFrameNotExist'],
						'message' => __d('reservations', 'Duplicate time frames.')
					]
				),
				'end_time' => array(
					'rule1' => array(
						'rule' => array('validateTime'),
						'message' => __d('reservations', 'Invalid value.'),
					),
				),
				'color' => array(
					'notBlank' => array(
						'rule' => array('notBlank'),
						'message' => __d(
							'net_commons',
							'Please input %s.', __d('reservations', 'Time frame color')
						),
					),
				),
			)
		);
		return parent::beforeValidate($options);
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
		foreach ($results as $key => $value) {
			if (
				Hash::check($value, $this->alias . '.start_time') &&
				Hash::check($value, $this->alias . '.end_time')
			) {
				$results[$key][$this->alias]['openText'] = $this->_openText($value);
			}
			//if (array_key_exists('start_time', $results[$key][$this->alias]) &&
			//	array_key_exists('end_time', $results[$key][$this->alias])) {
			//	$results[$key][$this->alias]['openText'] = $this->_openText($value);
			//}
		}
		return $results;
	}

/**
 * 時間枠の登録
 *
 * @param array $data 登録データ
 * @return bool
 * @throws InternalErrorException
 */
	public function saveTimeframe($data) {
		$this->begin();

		try {
			// 先にvalidate 失敗したらfalse返す
			$this->set($data);
			if (!$this->validates($data)) {
				return false;
			}

			// start_time end_timeをUTCに変換
			$startDateTime = Date('Y-m-d') . $data[$this->alias]['start_time'] . ':00';
			$endDateTime = Date('Y-m-d') . $data[$this->alias]['end_time'] . ':00';
			$ncTime = new NetCommonsTime();
			$startDateTime4UTC = $ncTime->toServerDatetime($startDateTime,
				$data[$this->alias]['timezone']);
			$endDateTime4UTC = $ncTime->toServerDatetime($endDateTime,
				$data[$this->alias]['timezone']);
			$data[$this->alias]['start_time'] = $startDateTime4UTC;
			$data[$this->alias]['end_time'] = $endDateTime4UTC;

			$savedData = $this->save($data, false);
			if (! $savedData) {
				//このsaveで失敗するならvalidate以外なので例外なげる
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
 * 時間枠の削除
 *
 * @param array $data 登録データ
 * @return bool
 * @throws InternalErrorException
 */
	public function deleteTimeframe($data) {
		$this->begin();

		try {
			$conditions = array(
				$this->alias . '.key' => $data[$this->alias]['key']
			);
			if (! $this->deleteAll($conditions, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//0件になったら、ReservationFrameSetting.display_timeframeを0にする
			if ($this->find('count', ['recursive' => -1]) === 0) {
				$this->loadModels([
					'ReservationFrameSetting' => 'Reservations.ReservationFrameSetting',
				]);
				$update = array(
					$this->ReservationFrameSetting->alias . '.display_timeframe' => false
				);
				if (! $this->ReservationFrameSetting->updateAll($update, null)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}

			$this->commit();

		} catch (Exception $e) {
			$this->rollback($e);
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
 * 時間枠の重複防止バリデーション
 *
 * @param array $check チェック対象配列
 * @return bool
 */
	public function validateTimeFrameNotExist($check) {
		$startTime = $this->data[$this->alias]['start_time'];
		$endTime = $this->data[$this->alias]['end_time'];
		// UTCに変換してから比較する。
		$startDateTime = Date('Y-m-d') . $startTime . ':00';
		$endDateTime = Date('Y-m-d') . $endTime . ':00';
		$ncTime = new NetCommonsTime();
		$startDateTime4UTC = $ncTime->toServerDatetime($startDateTime,
			$this->data[$this->alias]['timezone']);
		$endDateTime4UTC = $ncTime->toServerDatetime($endDateTime,
			$this->data[$this->alias]['timezone']);

		$startTime = date('H:i:s', strtotime($startDateTime4UTC));
		$endTime = date('H:i:s', strtotime($endDateTime4UTC));

		// start > endになったら24:00またぎなのでstart-24:00 までと 0:00-end の重複チェックする
		if ($startTime > $endTime) {
			$inputRanges = [
				[
					'start' => $startTime,
					'end' => '24:00:00'
				],
				[
					'start' => '00:00:00',
					'end' => $endTime
				]

			];
		} else {
			$inputRanges = [
				[
					'start' => $startTime,
					'end' => $endTime
				]
			];
		}
		// 全時間枠取得
		$conditions = [];
		if ($id = Hash::get($this->data, 'ReservationTimeframe.id')) {
			$conditions = [
			'ReservationTimeframe.id !=' => $id,
			];
		}
		$timeframes = $this->find('all', [
			'conditions' => $conditions,
			'group' => 'ReservationTimeframe.key'
		]);
		// start > endなデータは24:00またぎなのでstart-24:00 と 0:00-end のデータとして扱う。
		$existRanges = [];
		foreach ($timeframes as $timeframe) {
			if ($timeframe[$this->alias]['start_time'] > $timeframe[$this->alias]['end_time']) {
				$existRanges[] = [
					'start' => $timeframe[$this->alias]['start_time'],
					'end' => '24:00:00'
				];
				$existRanges[] = [
					'start' => '00:00:00',
					'end' => $timeframe[$this->alias]['end_time']
				];
			} else {
				$existRanges[] = [
					'start' => $timeframe[$this->alias]['start_time'],
					'end' => $timeframe[$this->alias]['end_time']
				];
			}
		}
		foreach ($inputRanges as $inputRange) {
			foreach ($existRanges as $existRange) {
				// 既にある時間枠のStartが指定範囲のENDより前で既存時間枠のENDが指定STARTよりも後なら重複
				if ($existRange['start'] < $inputRange['end'] &&
					$existRange['end'] > $inputRange['start']
				) {
					return false;
				}
			}
		}
		return true;
	}

/**
 * 時間枠の範囲（表示用）を返す
 *
 * @param array $timeframe 施設データ
 * @return string
 */
	protected function _openText($timeframe) {
		$ret = '';

		//時間
		$startTime = $timeframe[$this->alias]['start_time'];
		$timeframeTimeZone = new DateTimeZone($timeframe[$this->alias]['timezone']);
		$startDate = new DateTime($startTime, new DateTimeZone('UTC'));

		$startDate->setTimezone($timeframeTimeZone);
		$timeframe[$this->alias]['start_time'] = $startDate->format('H:i');

		$endTime = $timeframe[$this->alias]['end_time'];
		$endDate = new DateTime($endTime, new DateTimeZone('UTC'));
		$endDate->setTimezone($timeframeTimeZone);
		$timeframe[$this->alias]['end_time'] = $endDate->format('H:i');

		$ret = sprintf('%s %s - %s',
			$ret,
			$timeframe[$this->alias]['start_time'],
			$timeframe[$this->alias]['end_time']
		);
		if (AuthComponent::user('timezone') != $timeframe[$this->alias]['timezone']) {
			$SiteSetting = new SiteSetting();
			$SiteSetting->prepare();
			$ret .= ' ';
			$ret .= $SiteSetting->defaultTimezones[$timeframe[$this->alias]['timezone']];
		}
		return $ret;
	}
}
