<?php
/**
 * デフォルトロールパーミッションデータ作成 migration
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsMigration', 'NetCommons.Config/Migration');

/**
 * デフォルトロールパーミッションデータ作成 migration
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Reservations\Config\Migration
 */
class AddDefaultRolePermissions extends NetCommonsMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_default_role_permissions';

/**
 * recodes
 *
 * @var array $records
 */
	public $records = array(
		'DefaultRolePermission' => array(
			// 予約できる権限
			array(
				'role_key' => 'room_administrator',
				'type' => 'location_role',
				'permission' => 'location_reservable',
				'value' => '1',
				'fixed' => '1',
			),
			array(
				'role_key' => 'chief_editor',
				'type' => 'location_role',
				'permission' => 'location_reservable',
				'value' => '1',
				'fixed' => '0',
			),
			array(
				'role_key' => 'editor',
				'type' => 'location_role',
				'permission' => 'location_reservable',
				'value' => '1',
				'fixed' => '0',
			),
			array(
				'role_key' => 'general_user',
				'type' => 'location_role',
				'permission' => 'location_reservable',
				'value' => '0',
				'fixed' => '0',
			),
			array(
				'role_key' => 'visitor',
				'type' => 'location_role',
				'permission' => 'location_reservable',
				'value' => '0',
				'fixed' => '1',
			),
		)
	);

/**
 * Before migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function after($direction) {
		if ($direction === 'down') {
			return true;
		}

		foreach ($this->records as $model => $records) {
			if (!$this->updateRecords($model, $records)) {
				return false;
			}
		}
		return true;
	}

}
