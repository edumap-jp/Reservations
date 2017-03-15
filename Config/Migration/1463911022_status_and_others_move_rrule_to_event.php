<?php
/**
 * 施設予約status他をrruleからeventへ移すMigration
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsMigration', 'NetCommons.Config/Migration');

/**
 * 施設予約status他をrruleからeventへ移すMigration
 *
 * @package NetCommons\Reservations\Config\Migration
 */
class StatusAndOthersMoveRruleToEvent extends NetCommonsMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'status_and_others_move_rrule_to_event';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_field' => array(
				'reservation_events' => array(
					'key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'イベントKey', 'charset' => 'utf8', 'after' => 'reservation_rrule_id'),
					'status' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false, 'comment' => '公開状況  1:公開中>、2:公 開申請中、3:下書き中、4:差し戻し', 'after' => 'timezone_offset'),
					'is_active' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'アクティブなコンテンツかどうか 0:アクティブでない 1:アクティブ', 'after' => 'status'),
					'is_latest' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '最新コンテンツかどうか 0:最新でない 1:最新', 'after' => 'is_active'),
					'recurrence_event_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => '1以上のとき、再発(置換）イベントidを指す。VCALENDERのRECURRENCE-ID機能実現のための項目', 'after' => 'is_latest'),
					'exception_event_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => '1以上のとき、例外（削除）イベントidを指す。vreservationの EXDATE機能実現のための項目', 'after' => 'recurrence_event_id'),
				),
			),
			'drop_field' => array(
				'reservation_rrules' => array('language_id', 'status', 'is_active', 'is_latest'),
			),
		),
		'down' => array(
			'drop_field' => array(
				'reservation_events' => array('key', 'status', 'is_active', 'is_latest', 'recurrence_event_id', 'exception_event_id'),
			),
			'create_field' => array(
				'reservation_rrules' => array(
					'language_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'comment' => '言語ID'),
					'status' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false, 'comment' => '公開状況  1:公開中、2:公開申請中、3:下書き中、4:差し戻し'),
					'is_active' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'Is active, 0:deactive 1:acive | アクティブなコンテンツかどうか 0:アクテ ィブでない 1:アクティブ | | '),
					'is_latest' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '最新コンテンツかどうか 0:最新でない 1:最新'),
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
		$this->ReservationRrule = $this->generateModel('ReservationRrule');
		$this->ReservationEvent = $this->generateModel('ReservationEvent');

		if ($direction == 'up') {
			$events = $this->ReservationEvent->find('all', array(
				'recursive' => -1,
				'callbacks' => false,
			));
			foreach ($events as &$event) {
				$event['key'] = 'init' . $event['id'];
				$event['status'] = 1;	//publish
				$event['is_active'] = 1;
				$event['is_latest'] = 1;
				$event['recurrence_event_id'] = 0;
				$event['exception_event_id'] = 0;

				$this->ReservationEvent->save($event, array(
					'validate' => false,	//Currentを期待したvalidateなのでoffする
					'callbacks' => false,
				));
			}
		} elseif ($direction == 'down') {
			$rrules = $this->ReservationRrule->find('all', array(
				'recursive' => -1,
				'callbacks' => false,
			));
			foreach ($rrules as &$rrule) {
				$rrule['language_id'] = 2;	//日本語
				$rrule['status'] = 1;	//publish
				$rrule['is_active'] = 1;
				$rrule['is_latest'] = 1;

				$this->ReservationRrule->save($rrule, array(
					'validate' => false,	//Currentを期待したvalidateなのでoffする
					'callbacks' => false,
				));
			}
		}
		return true;
	}
}
