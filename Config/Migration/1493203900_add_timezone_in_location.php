<?php
/**
 * AddTimezoneInLocation
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Class AddTimezoneInLocation
 */
class AddTimezoneInLocation extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_timezone_in_location';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_field' => array(
				'reservation_locations' => array(
					'timezone' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8', 'after' => 'end_time'),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'reservation_locations' => array('timezone'),
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
