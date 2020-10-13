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
		'Room' => array(
			'className' => 'Rooms.Room',
			'foreignKey' => 'room_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * Called during validation operations, before validation. Please note that custom
 * validation rules can be defined in $validate.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = array()) {
		$this->validate = ValidateMerge::merge($this->validate, array(
			'reservation_location_key' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'room_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
		));

		return parent::beforeValidate($options);
	}

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

/**
 * getReservableRoomsByLocationKey
 *
 * @param string $locationKey 施設キー
 * @param int|string $userId User.id
 * @return array Room data
 */
	public function getReservableRoomsByLocationKey($locationKey, $userId) : array {
		$this->loadModels([
			'ReservationLocation' => 'Reservations.ReservationLocation'
		]);
		// location取得
		$location = $this->ReservationLocation->getByKey($locationKey);

		return $this->getReservableRoomsByLocationAndUserId($location, $userId);
	}

/**
 * getReservableRoomsByLocationAndUserId
 *
 * @param array $location 施設
 * @param int|string $userId User.id
 * @return array
 */
	public function getReservableRoomsByLocationAndUserId(array $location, $userId) : array {
		// roomBase取得
		$this->loadModels([
			'Room' => 'Rooms.Room',
		]);
		$condition = $this->Room->getReadableRoomsConditions([], $userId);
		$roomBase = $this->Room->find('all', $condition);

		return $this->getReservableRoomsByLocation($location, $roomBase);
	}

/**
 * 施設で予約を受け付けるルームを返す
 *
 * @param array $location ReservationLocation data
 * @param array $roomBase アクセスユーザがアクセス可能なルーム情報
 * @return array アクセス可能なルームのうち引数で指定された施設で予約可能なルーム
 */
	public function getReservableRoomsByLocation($location, $roomBase) {
		// 予約を受け付けるルームを制限するなら受け付けるルームIDを取得する
		if (!$location['ReservationLocation']['use_all_rooms']) {
			$locationRooms = $this->find(
				'all',
				[
					'conditions' => [
						'ReservationLocationsRoom.reservation_location_key' => $location['ReservationLocation']['key']
					]
				]
			);
			$reservableRoomIds = array_column(array_column($locationRooms, 'ReservationLocationsRoom'), 'room_id');
		}

		$thisLocationRooms = [];
		foreach ($roomBase as $room) {
			if ($room['Room']['space_id'] == Space::PRIVATE_SPACE_ID) {
				if ($location['ReservationLocation']['use_private']) {
					// 個人的な予約OK ならプライベートルームを選択肢に追加
					$thisLocationRooms[] = $room;
				}
			} else {
				if ($location['ReservationLocation']['use_all_rooms']) {
					// 全てのルームから予約受付なら選択肢にルームを追加
					$thisLocationRooms[] = $room;
				} else {
					// 予約を受け付けるルームなら選択肢に追加
					if (in_array($room['Room']['id'], $reservableRoomIds)) {
						$thisLocationRooms[] = $room;
					}
				}
			}
		}
		return $thisLocationRooms;
	}
}

