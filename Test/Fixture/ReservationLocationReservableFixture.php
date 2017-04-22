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
	public $records = array(
		array(
			'id' => 1,
			'location_key' => 'Lorem ipsum dolor sit amet',
			'role_key' => 'Lorem ipsum dolor sit amet',
			'room_id' => 1,
			'value' => 1,
			'created_user' => 1,
			'created' => '2017-04-20 09:36:12',
			'modified_user' => 1,
			'modified' => '2017-04-20 09:36:12'
		),
	);

}
