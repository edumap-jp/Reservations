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
		$this->validate = Hash::merge($this->validate,
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
		$conditions = [
			'OR' => [
				[
					// 始点が指定した範囲にあったら時間枠重複
					'start_time >' => $startTime,
					'start_time <' => $endTime,
				],
				[
					// 終点が指定した範囲にあったら時間枠重複
					'end_time >' => $startTime,
					'end_time <' => $endTime,

				],
				[
					// 始点、終点ともそれぞれ指定範囲の前と後だったら時間枠重複
					'start_time <' => $startTime,
					'end_time >' => $endTime,
				],

			]
		];
		$exist = $this->find('count', ['conditions' => $conditions]);
		if ($exist) {
			return false;
		}
		return true;
	}

}
