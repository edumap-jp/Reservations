<?php
/**
 * reservation_eventsにemai_send_timing他を足すMigration
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
class AddEmailSendTiming extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_email_send_timing';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_field' => array(
				'reservation_events' => array(
					'is_enable_mail' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'イベント前にメール通知するかどうか 0:通知しない 1:通知する', 'after' => 'exception_event_id'),
					'email_send_timing' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => 'イベントN分前メール通知の値N。単位は分。', 'after' => 'is_enable_mail'),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'reservation_events' => array('is_enable_mail', 'email_send_timing'),
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
