<?php
/**
 * ReservationEventContent::validate()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsValidateTest', 'NetCommons.TestSuite');
App::uses('ReservationEventContentFixture', 'Reservations.Test/Fixture');

/**
 * ReservationEvent::validate()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationEvent
 */
class ReservationEventContentValidateTest extends NetCommonsValidateTest {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.reservations.block_setting_for_reservation',
		'plugin.reservations.reservation',
		'plugin.reservations.reservation_event',
		'plugin.reservations.reservation_event_content',
		'plugin.reservations.reservation_event_share_user',
		'plugin.reservations.reservation_frame_setting',

		'plugin.reservations.reservation_rrule',
		'plugin.workflow.workflow_comment',
		'plugin.rooms.rooms_language4test',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'reservations';

/**
 * Model name
 *
 * @var string
 */
	protected $_modelName = 'ReservationEventContent';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'validates';

/**
 * ValidationErrorのDataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *  - field フィールド名
 *  - value セットする値
 *  - message エラーメッセージ
 *  - overwrite 上書きするデータ(省略可)
 *
 * @return array テストデータ
 */
	public function dataProviderValidationError() {
		$data['ReservationEventContent'] = (new ReservationEventContentFixture())->records[0];

		return array(
			//beforeValidate
			array('data' => $data, 'field' => 'model', 'value' => '',
				'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('reservations', 'Model Name'))),
			array('data' => $data, 'field' => 'content_key', 'value' => '',
				'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('reservations', 'content key'))),
			array('data' => $data, 'field' => 'reservation_event_id', 'value' => '',
				'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('reservations', 'reservation_event id'))),

		);
	}

}
