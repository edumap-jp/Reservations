<?php
/**
 * ReservationLocationsRoom Model
 *
 * @property Room $Room
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppModel', 'Reservations.Model');

/**
 * Summary for ReservationLocationsRoom Model
 */
class ReservationLocationsRoom extends ReservationsAppModel {

/**
 * Use database config
 *
 * @var string
 */
	public $useDbConfig = 'master';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'reservation_location_key' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'room_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Room' => array(
			'className' => 'Room',
			'foreignKey' => 'room_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * ReservationLocationsRoomの登録
 *
 * ReservationLocationSetting::saveReservationLocationSetting()から実行されるため、ここではトランザクションを開始しない
 *
 * @param string $locationKey ReservationLocation.key
 * @param array $data リクエストデータ
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	public function saveReservationLocaitonsRoom($locationKey, $data) {
		$roomIds = Hash::get($data, $this->alias . '.room_id', array());

		$saved = $this->find('list', array(
			'recursive' => -1,
			'fields' => array('id', 'room_id'),
			'conditions' => ['reservation_location_key' => $locationKey],
		));
		$saved = array_unique(array_values($saved));

		$delete = array_diff($saved, $roomIds);
		if (count($delete) > 0) {
			$conditions = array(
				'ReservationLocationsRoom' . '.reservation_location_key' => $locationKey,
				'ReservationLocationsRoom' . '.room_id' => $delete,
			);
			if (! $this->deleteAll($conditions, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		$new = array_diff($roomIds, $saved);
		if (count($new) > 0) {
			$saveDate = array();
			foreach ($new as $i => $roomId) {
				$saveDate[$i] = array(
					'id' => null,
					'room_id' => $roomId,
					'reservation_location_key' => $locationKey
				);
			}
			if (! $this->saveMany($saveDate)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		return true;
	}
}
