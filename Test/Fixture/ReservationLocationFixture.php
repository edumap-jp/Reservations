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
		'key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'language_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'is_translation' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '翻訳したかどうか'),
		'is_origin' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => 'オリジナルかどうか'),
		'is_original_copy' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'オリジナルのコピー。言語を新たに追加したときに使用する'),
		'category_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => true, 'key' => 'index'),
		'location_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '施設名', 'charset' => 'utf8'),
		'detail' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'add_authority' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'time_table' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 32, 'collate' => 'utf8_general_ci', 'comment' => '利用可能な曜日', 'charset' => 'utf8'),
		'start_time' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'end_time' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'timezone' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'use_private' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'use_auth_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'use_all_rooms' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'use_workflow' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'weight' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => true),
		'contact' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'category_id' => array('column' => array('category_id', 'weight'), 'unique' => 0)
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
			'key' => 'location_1',
			'language_id' => 2,
			'category_id' => 1,
			'location_name' => 'LocationName_1',
			'add_authority' => 0,
			'time_table' => 'Sun|Mon|Tue|Wed|Thu|Fri|Sat',
			'start_time' => '2017-05-04 15:00:00',
			'end_time' => '2017-05-05 15:00:00',
			'timezone' => 'Asia/Tokyo',
			'use_private' => 1,
			'use_auth_flag' => 0,
			'use_all_rooms' => 1,
			'use_workflow' => 0,
			'weight' => 1,
			'created_user' => 1,
			'created' => '2017-02-25 08:59:40',
			'modified_user' => 1,
			'modified' => 1
		),
		array(
			'id' => 2,
			'key' => 'location_2',
			'language_id' => 2,
			'category_id' => 1,
			'location_name' => 'LocationName_2',
			'add_authority' => 0,
			'time_table' => 'Sun|Mon|Tue|Wed|Thu|Fri|Sat',
			'start_time' => '2017-05-04 15:00:00',
			'end_time' => '2017-05-05 15:00:00',
			'timezone' => 'Asia/Tokyo',
			'use_private' => 1,
			'use_auth_flag' => 0,
			'use_all_rooms' => 1,
			'use_workflow' => 0,
			'weight' => 1,
			'created_user' => 1,
			'created' => '2017-02-25 08:59:40',
			'modified_user' => 1,
			'modified' => 1
		),
		array(
			'id' => 3,
			'key' => 'location_3',
			'language_id' => 2,
			'category_id' => 1,
			'location_name' => 'LocationName_3',
			'add_authority' => 0,
			'time_table' => 'Sun|Mon|Tue|Wed|Thu|Fri|Sat',
			'start_time' => '2017-05-04 15:00:00',
			'end_time' => '2017-05-05 15:00:00',
			'timezone' => 'Asia/Tokyo',
			'use_private' => 1,
			'use_auth_flag' => 0,
			'use_all_rooms' => 1,
			'use_workflow' => 0,
			'weight' => 1,
			'created_user' => 1,
			'created' => '2017-02-25 08:59:40',
			'modified_user' => 1,
			'modified' => 1
		),
		array(
			'id' => 4,
			'key' => 'location_4',
			'language_id' => 2,
			'category_id' => 1,
			'location_name' => 'UseWorkflowLocation',
			'add_authority' => 0,
			'time_table' => 'Sun|Mon|Tue|Wed|Thu|Fri|Sat',
			'start_time' => '2017-05-04 15:00:00',
			'end_time' => '2017-05-05 15:00:00',
			'timezone' => 'Asia/Tokyo',
			'use_private' => 1,
			'use_auth_flag' => 0,
			'use_all_rooms' => 1,
			'use_workflow' => 1,
			'weight' => 1,
			'created_user' => 1,
			'created' => '2017-02-25 08:59:40',
			'modified_user' => 1,
			'modified' => 1
		),
		// 指定ルームのみ予約可能
		array(
			'id' => 5,
			'key' => 'location_5',
			'language_id' => 2,
			'category_id' => 1,
			'location_name' => '指定ルームのみ予約可能施設',
			'add_authority' => 0,
			'time_table' => 'Sun|Mon|Tue|Wed|Thu|Fri|Sat',
			'start_time' => '2017-05-04 15:00:00',
			'end_time' => '2017-05-05 15:00:00',
			'timezone' => 'Asia/Tokyo',
			'use_private' => 1,
			'use_auth_flag' => 0,
			'use_all_rooms' => 0,
			'use_workflow' => 0,
			'weight' => 1,
			'created_user' => 1,
			'created' => '2017-02-25 08:59:40',
			'modified_user' => 1,
			'modified' => 1
		),
	);

}
