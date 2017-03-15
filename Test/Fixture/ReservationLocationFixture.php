<?php
/**
 * ReservationLocationFixture
 *
* @author Noriko Arai <arai@nii.ac.jp>
* @author Your Name <yourname@domain.com>
* @link http://www.netcommons.org NetCommons Project
* @license http://www.netcommons.org/license.txt NetCommons License
* @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for ReservationLocationFixture
 */
class ReservationLocationFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary'),
		'key' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'language_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'category_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => true, 'key' => 'index'),
		'location_name' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'comment' => '施設名', 'charset' => 'utf8'),
		'add_authority' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'time_table' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'utf8_general_ci', 'comment' => '利用可能な曜日', 'charset' => 'utf8'),
		'start_time' => array('type' => 'string', 'null' => false, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'end_time' => array('type' => 'string', 'null' => false, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'use_private' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'use_auth_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'use_all_rooms' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'display_sequence' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => true),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'modified' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'category_id' => array('column' => array('category_id', 'display_sequence'), 'unique' => 0)
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
			'key' => 'Lorem ipsum dolor sit amet',
			'language_id' => 1,
			'category_id' => 1,
			'location_name' => 'Lorem ipsum dolor sit amet',
			'add_authority' => 1,
			'time_table' => 'Lorem ipsum dolor sit amet',
			'start_time' => 'Lorem ipsum ',
			'end_time' => 'Lorem ipsum ',
			'use_private' => 1,
			'use_auth_flag' => 1,
			'use_all_rooms' => 1,
			'display_sequence' => 1,
			'created_user' => 1,
			'created' => '2017-02-25 08:59:40',
			'modified_user' => 1,
			'modified' => 1
		),
	);

}
