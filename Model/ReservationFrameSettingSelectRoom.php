<?php
/**
 * ReservationFrameSettingSelectRoom Model
 *
 * @property Room $Room
 * @property ReservationFrameSetting $ReservationFrameSetting
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppModel', 'Reservations.Model');

/**
 * ReservationFrameSettingSelectRoom Model
 *
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\Model
 */
class ReservationFrameSettingSelectRoom extends ReservationsAppModel {

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'NetCommons.OriginalKey',
		'NetCommons.Trackable',
		//'Workflow.WorkflowComment',
		//'Workflow.Workflow',
		'Reservations.ReservationValidate',
		'Reservations.ReservationApp',	//baseビヘイビア
		'Reservations.ReservationInsertPlan', //Insert用
		'Reservations.ReservationUpdatePlan', //Update用
		'Reservations.ReservationDeletePlan', //Delete用
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'ReservationFrameSetting' => array(
			'className' => 'Reservations.ReservationFrameSetting',
			'foreignKey' => 'reservation_frame_setting_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Room' => array(
			'className' => 'Rooms.Room',
			'foreignKey' => 'room_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
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
		$roomIds = $this->getReadableRoomIds();
		$this->validate = Hash::merge($this->validate, array(
			'reservation_frame_setting_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'room_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __d('net_commons', 'Invalid request.'),
				),
				'rule2' => array(
					'rule' => array('inList', $roomIds),
					'message' => __d('net_commons', 'Invalid request.'),
				)
			),
		));

		return parent::beforeValidate($options);
	}
/**
 * getSelectRooms
 *
 * @param int $settingId frame setting id
 * @return array select Rooms
 */
	public function getSelectRooms($settingId) {
		$this->Room = ClassRegistry::init('Rooms.Room', true);
		$roomIds = $this->getReadableRoomIds();
		$selectRoom = $this->Room->find('all', array(
			'fields' => array(
				'Room.id',
				'ReservationFrameSettingSelectRoom.room_id',
				'ReservationFrameSettingSelectRoom.reservation_frame_setting_id'
			),
			'recursive' => -1,
			'joins' => array(
				array('table' => 'reservation_frame_setting_select_rooms',
					'alias' => 'ReservationFrameSettingSelectRoom',
					'type' => 'LEFT',
					'conditions' => array(
						'Room.id = ReservationFrameSettingSelectRoom.room_id',
						'reservation_frame_setting_id' => $settingId,
					)
				)
			),
			'conditions' => array(
				'Room.id' => $roomIds
			),
			'order' => array('Room.id ASC')
		));
		if (! $selectRoom) {
			return array();
		}
		$selectRoom = Hash::combine($selectRoom, '{n}.Room.id', '{n}.ReservationFrameSettingSelectRoom');
		return $selectRoom;
	}

/**
 * validateFrameSettingSelectRoom
 *
 * @param array $data validate data
 * @return bool
 */
	public function validateReservationFrameSettingSelectRoom($data) {
		foreach ($data['ReservationFrameSettingSelectRoom'] as $selectRoom) {
			if ($selectRoom['room_id'] == '') {
				continue;
			}
			$this->create();
			$this->set($selectRoom);
			if (! $this->validates()) {
				return false;
			}
		}
		return true;
	}

/**
 * saveFrameSettingSelectRoom
 *
 * @param array $data save data
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	public function saveReservationFrameSettingSelectRoom($data) {
		$settingId = $data['ReservationFrameSetting']['id'];
		$ret = array();
		//トランザクションBegin
		$this->begin();
		try {
			foreach ($data['ReservationFrameSettingSelectRoom'] as $roomId => $selectRoom) {
				$condition = array(
					'ReservationFrameSettingSelectRoom.reservation_frame_setting_id' => $settingId,
					'ReservationFrameSettingSelectRoom.room_id' => $roomId,
				);
				$orgData = $this->find('first', array(
					'conditions' => $condition
				));
				if (empty($selectRoom['room_id'])) {
					$this->deleteAll($condition, false);
				} else {
					if (! $orgData) {
						$this->create();
						$this->set($selectRoom);
						if (! $this->validates()) {
							return false;
						}
						$saveData = $this->save($selectRoom, false);
						if (! $saveData) {
							throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
						}
						$ret[] = $saveData;
					} else {
						$ret[] = $orgData;
					}
				}
			}
			$this->commit();
		} catch (Exception $ex) {
			CakeLog::error($ex);

			$this->rollback();
			throw $ex;
		}
		return $ret;
	}
}
