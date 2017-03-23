<?php
/**
 * reservation_rrulesのireservation_uidを変更するMigration
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * reservation_eventsにemai_send_timing他を足すMigration
 *
 * @package NetCommons\Reservations\Config\Migration
 */
class ChangeIreservationUid extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'change_ireservation_uid';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'alter_field' => array(
				'reservation_rrules' => array(
					'ireservation_uid' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'iReservationUIDの元となる情報。rrule分割元と分割先の関連性を記録する。', 'charset' => 'utf8'),
				),
			),
		),
		'down' => array(
			'alter_field' => array(
				'reservation_rrules' => array(
					'ireservation_uid' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'iReservation specification UID. | iReservation仕様のUID', 'charset' => 'utf8'),
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
