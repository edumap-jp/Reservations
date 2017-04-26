<?php
/**
 * ChangeStartTimeEndTime
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Class ChangeStartTimeEndTime
 */
class ChangeStartTimeEndTime extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'change_start_time_end_time';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'alter_field' => array(
				'reservation_locations' => array(
					'start_time' => array('type' => 'datetime', 'null' => false, 'default' => null, 'length' => null),
					'end_time' => array('type' => 'datetime', 'null' => false, 'default' => null, 'length' => null),
				),
			),
		),
		'down' => array(
			'alter_field' => array(
				'reservation_locations' => array(
					'start_time' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'end_time' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
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
