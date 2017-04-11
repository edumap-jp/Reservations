<?php
/**
 * AddFramesettingCategoryId
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Class AddFramesettingCategoryId
 */
class AddFramesettingCategoryId extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_framesetting_category_id';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_field' => array(
				'reservation_frame_settings' => array(
					'category_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 10, 'unsigned' => true, 'comment' => '最初に表示する施設カテゴリID', 'after' => 'location_key'),
				),
			),
			'alter_field' => array(
				'reservation_locations' => array(
					'key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'location_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '施設名', 'charset' => 'utf8'),
					'time_table' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 32, 'collate' => 'utf8_general_ci', 'comment' => '利用可能な曜日', 'charset' => 'utf8'),
					'start_time' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'end_time' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
				),
				'reservation_timeframes' => array(
					'key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'title' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'start_time' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'end_time' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'color' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'reservation_frame_settings' => array('category_id'),
			),
			'alter_field' => array(
				'reservation_locations' => array(
					'key' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'location_name' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'comment' => '施設名', 'charset' => 'utf8'),
					'time_table' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'utf8_general_ci', 'comment' => '利用可能な曜日', 'charset' => 'utf8'),
					'start_time' => array('type' => 'string', 'null' => false, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'end_time' => array('type' => 'string', 'null' => false, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
				),
				'reservation_timeframes' => array(
					'key' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'title' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'start_time' => array('type' => 'string', 'null' => false, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'end_time' => array('type' => 'string', 'null' => false, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'color' => array('type' => 'string', 'null' => false, 'length' => 16, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
				),
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
