<?php
/**
 * ReservationLocationReservableFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for ReservationLocationReservableFixture
 */
class ReservationLocationReservableFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'reservation_location_reservable';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'location_key' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => '施設キー', 'charset' => 'utf8'),
		'role_key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ロールキー', 'charset' => 'utf8'),
		'room_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'value' => array('type' => 'boolean', 'null' => true, 'default' => null),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'location_key' => array('column' => 'location_key', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = [
		[
			'location_key' => 'location_1',
			'role_key' => 'chief_editor',
			'room_id' => null,
			'value' => 1,
		],
		[
			'location_key' => 'location_1',
			'role_key' => 'general_user',
			'room_id' => null,
			'value' => 0,
		],

		[
			//'id' => 1,
			'location_key' => 'KEY_1',
			'role_key' => 'chief_editor',
			'room_id' => null,
			'value' => 1,
		],
		[
			//'id' => 2,
			'location_key' => 'KEY_1',
			'role_key' => 'general_user',
			'room_id' => null,
			'value' => 0,
		],

		// room 11
		[
			//'id' => 1,
			'location_key' => 'KEY_2',
			'role_key' => 'chief_editor',
			'room_id' => 11,
			'value' => 1,
		],
		[
			//'id' => 2,
			'location_key' => 'KEY_2',
			'role_key' => 'general_user',
			'room_id' => 11,
			'value' => 0,
		],
		// room 12
		[
			//'id' => 3,
			'location_key' => 'KEY_2',
			'role_key' => 'chief_editor',
			'room_id' => 12,
			'value' => 1,
		],
		[
			//'id' => 4,
			'location_key' => 'KEY_2',
			'role_key' => 'general_user',
			'room_id' => 12,
			'value' => 0,
		],

		[
			//'id' => 1,
			'location_key' => 'KEY_3',
			'role_key' => 'room_administrator',
			'room_id' => null,
			'value' => 1,
		],

	];

}
