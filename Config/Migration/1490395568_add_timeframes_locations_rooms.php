<?php
/**
 * Add TimeFrames LocationsRooms table
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('NetCommonsMigration', 'NetCommons.Config/Migration');

/**
 * Class AddTimeframesLocationsRooms
 */
class AddTimeframesLocationsRooms extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_timeframes_locations_rooms';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_table' => array(
				'reservation_locations_rooms' => array(
					'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
					'reservation_location_key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'room_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
					'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
						'room_id' => array('column' => 'room_id', 'unique' => 0),
					),
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
				),
				'reservation_timeframes' => array(
					'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
					'key' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'language_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
					'is_translation' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '翻訳したかどうか'),
					'is_origin' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => 'オリジナルかどうか'),
					'is_original_copy' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'オリジナルのコピー。言語を新たに追加したときに使用する'),
					'title' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'start_time' => array('type' => 'string', 'null' => false, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'end_time' => array('type' => 'string', 'null' => false, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'color' => array('type' => 'string', 'null' => false, 'length' => 16, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
					),
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
				),
			),
		),
		'down' => array(
			'drop_table' => array(
				'reservation_locations_rooms', 'reservation_timeframes'
			),
		),
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
		return true;
	}
}
