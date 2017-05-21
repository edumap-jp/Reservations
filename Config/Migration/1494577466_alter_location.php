<?php
/**
 * AlterLocation
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Class AlterLocation
 */
class AlterLocation extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'alter_location';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'alter_field' => array(
				'reservation_locations' => array(
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
				),
			),
		),
		'down' => array(
			'alter_field' => array(
				'reservation_locations' => array(
					'modified' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
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
