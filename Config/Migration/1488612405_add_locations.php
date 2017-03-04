<?php
class AddLocations extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_locations';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_field' => array(
				'reservation_events' => array(
					'location_key' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8', 'after' => 'timezone_offset'),
				),
			),
			'create_table' => array(
				'reservation_locations' => array(
					'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary'),
					'key' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'language_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
					'is_translation' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '翻訳したかどうか'),
					'is_origin' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => 'オリジナルかどうか'),
					'is_original_copy' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'オリジナルのコピー。言語を新たに追加したときに使用する'),
					'category_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => true, 'key' => 'index'),
					'location_name' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'comment' => '施設名', 'charset' => 'utf8'),
					'detail' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'add_authority' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
					'time_table' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'utf8_general_ci', 'comment' => '利用可能な曜日', 'charset' => 'utf8'),
					'start_time' => array('type' => 'string', 'null' => false, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'end_time' => array('type' => 'string', 'null' => false, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'use_private' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
					'use_auth_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
					'use_all_rooms' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
					'display_sequence' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => true),
					'contact' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
					'modified' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
						'category_id' => array('column' => array('category_id', 'display_sequence'), 'unique' => 0),
					),
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'reservation_events' => array('location_key'),
			),
			'drop_table' => array(
				'reservation_locations'
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
