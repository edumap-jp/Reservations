<?php
/**
 * ReservationLocationReservable Model
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
 * Summary for ReservationLocationReservable Model
 */
class ReservationLocationReservable extends ReservationsAppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'reservation_location_reservable';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
	);

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
		$this->validate = Hash::merge($this->validate, array(
			'location_key' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'role_key' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
		));

		return parent::beforeValidate($options);
	}

/**
 * 予約可能な施設か？（権限判定のみ）
 *
 * @param array $location ReservationLocation data
 * @return bool
 */
	public function isReservableByLocation($location) {
		$this->loadModels([
			'RolesRoomsUser' => 'Rooms.RolesRoomsUser',
			//'RolesRoom' => 'Rooms.RolesRoom',
			//'Room' => 'Rooms.Room'
		]);

		$roomIds = $this->getReadableRoomIdsWithOutPrivate();
		$userId = Current::read('User.id');
		if (!$userId) {
			return [];
		}
		// 個人的な予約OKな施設
		if ($location['ReservationLocation']['use_private']) {
			// マイルームが使えるならOK
			$this->loadModels(['UserRoleSetting' => 'UserRoles.UserRoleSetting']);
			$userRole = Current::read('User.role_key');
			$userRoleSetting = $this->UserRoleSetting->find('first', [
				'conditions' => [
					'UserRoleSetting.role_key' => $userRole
				]
			]);
			$usePrivateRoom = $userRoleSetting['UserRoleSetting']['use_private_room'];
			return $usePrivateRoom;
		}

		if ($location['ReservationLocation']['use_all_rooms']) {
			// 全てのルームで予約Ok
			// アクセスできる全ルーム（プライベートのぞく）でのロール取得
			$rolesRoomsUsers = $this->RolesRoomsUser->find('all', array(
				'recursive' => 0,
				'conditions' => array(
					'RolesRoomsUser.user_id' => $userId,
					'RolesRoomsUser.room_id' => $roomIds,
				),
			));
			$roleKeys = Hash::combine($rolesRoomsUsers, '{n}.RolesRoom.role_key', '{n}.RolesRoom.role_key');

			$conditions = [
				'ReservationLocationReservable.location_key' => $location['ReservationLocation']['key'],
				'ReservationLocationReservable.role_key' => $roleKeys,
				'room_id' => null,
			];
			$reservables = $this->find('all', ['conditions' => $conditions]);

			foreach ($reservables as $reservable) {
				if (Hash::get($reservable, 'ReservationLocationReservable.value')) {
					// いずれかのロールで予約権限ついてれば予約OK
					return true;
				}
			}
			return false;
		} else {
			// 選択されたルームのみ予約OK
			$reservable = false;
			foreach ($roomIds as $roomId) {
				// ルームでのロール取得
				$rolesRoomsUsers = $this->RolesRoomsUser->find('first', array(
					'recursive' => 0,
					'conditions' => array(
						'RolesRoomsUser.user_id' => $userId,
						'RolesRoomsUser.room_id' => $roomId,
					),
				));
				$roleKeys = Hash::get($rolesRoomsUsers, 'RolesRoom.role_key');

				// ロールに対する予約権限取得
				$conditions = [
					'ReservationLocationReservable.location_key' => $location['ReservationLocation']['key'],
					'ReservationLocationReservable.role_key' => $roleKeys,
					'room_id' => $roomId,
				];
				$reservable = $this->find('first', ['conditions' => $conditions]);
				// ユーザがアクセス可能なルーム（プライベートのぞく）のいずれかで予約OKなら予約できる施設
				if (Hash::get($reservable, 'ReservationLocationReservable.value', false)) {
					$reservable = true;
				}
			}
			return $reservable;
		}
	}

/**
 * 施設に対する予約できる権限データを取得する
 *
 * @param string $locationKey 施設キー
 * @param int| $roomId ルームID
 * @return array
 */
	public function getPermissions($locationKey, $roomId = null) {
		// reseravableデータ取得
		if ($locationKey !== null) {
			if (! $roomId) {
				$result = $this->find('first', [
					'recursive' => -1,
					'fields' => ['location_key', 'room_id'],
					'conditions' => [
						'location_key' => $locationKey
					]
				]);
				$roomId = Hash::get($result, 'ReservationLocationReservable.room_id');
			}
			$result = $this->find('all', [
				'recursive' => -1,
				'conditions' => [
					'location_key' => $locationKey,
					'room_id' => $roomId
				]
			]);
			$reservables = Hash::combine(
				$result,
				'{n}.ReservationLocationReservable.role_key',
				'{n}.ReservationLocationReservable.value'
			);
		} else {
			// default
			$reservables = [];
		}

		//DefaultRolePermission取得
		$this->loadModels([
			'DefaultRolePermission' => 'Roles.DefaultRolePermission',
		]);

		$defaultPermissions = $this->DefaultRolePermission->find('all', array(
			'recursive' => -1,
			'fields' => array('DefaultRolePermission.*', 'DefaultRolePermission.value AS default'),
			'conditions' => array(
				'DefaultRolePermission.type' => 'location_role',
				'DefaultRolePermission.permission' => 'location_reservable',
			),
		));
		$defaultPermissions = Hash::remove($defaultPermissions, '{n}.DefaultRolePermission.id');
		$defaults = Hash::combine(
			$defaultPermissions, '{n}.DefaultRolePermission.role_key', '{n}.DefaultRolePermission'
		);

		foreach ($defaults as $roleKey => $default) {
			$default['value'] = Hash::get($reservables, $roleKey, $default['value']);
			$defaults[$roleKey] = $default;
		}

		return $defaults;
	}

/**
 * 予約できる権限の保存
 *
 * #### $data のサンプル
 *	$data => array(
 *		'ReservationLocationReservable' => array(
 *			'room_administrator' => array(
 *				'role_key' => 'room_administrator',
 *				'type' => 'location_role',
 *				'permission' => 'location_reservable',
 *				'value' => true,
 *				'fixed' => true,
 *				'default' => true,
 *			),
 *			'chief_editor' => array(
 *				'role_key' => 'chief_editor',
 *				'type' => 'location_role',
 *				'permission' => 'location_reservable',
 *				'value' => '1',
 *				'fixed' => false,
 *				'default' => true,
 *				'id' => '',
 *			),
 *			'editor' => array(
 *				'role_key' => 'editor',
 *				'type' => 'location_role',
 *				'permission' => 'location_reservable',
 *				'value' => '1',
 *				'fixed' => false,
 *				'default' => true,
 *				'id' => '',
 *			),
 *			'general_user' => array(
 *				'role_key' => 'general_user',
 *				'type' => 'location_role',
 *				'permission' => 'location_reservable',
 *				'value' => '0',
 *				'fixed' => false,
 *				'default' => false,
 *				'id' => '',
 *			),
 *			'visitor' => array(
 *				'role_key' => 'visitor',
 *				'type' => 'location_role',
 *				'permission' => 'location_reservable',
 *				'value' => false,
 *				'fixed' => true,
 *				'default' => false,
 *			),
 *		),
 *	)
 *
 * @param string $locationKey location_key
 * @param array $data save data
 * @throws InternalErrorException
 * @return bool
 */
	public function saveReservable($locationKey, $data) {
		$roles = $data[$this->alias];

		// 同じ施設のreservableデータをあらかじめ削除しておく
		if (! $this->deleteAll(['location_key' => $locationKey])) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		foreach ($roles as $roleKey => $role) {
			$value = $role['value'];
			// ルーム毎に保存
			if ($data['ReservationLocation']['use_all_rooms']) {
				// 全てのルームから予約を受けつける
				$this->create();
				$reservableData = [
					'location_key' => $locationKey,
					'role_key' => $roleKey,
					'room_id' => null,
					'value' => $value
				];
				if (! $this->save($reservableData)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				};
			} else {
				// 個別のルームから予約を受け付ける
				foreach ($data['ReservationLocationsRoom']['room_id'] as $roomId) {
					$this->create();
					$reservableData = [
						'location_key' => $locationKey,
						'role_key' => $roleKey,
						'room_id' => $roomId,
						'value' => $value
					];
					if (! $this->save($reservableData)) {
						throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
					}
				}
			}
		}

		return true;
	}

}
