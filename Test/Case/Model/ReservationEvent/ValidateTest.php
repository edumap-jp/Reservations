<?php
/**
 * ReservationEvent::validate()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsValidateTest', 'NetCommons.TestSuite');
App::uses('ReservationEventFixture', 'Reservations.Test/Fixture');

/**
 * ReservationEvent::validate()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationEvent
 */
class ReservationEventValidateTest extends NetCommonsValidateTest {

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
		'plugin.reservations.reservation_frame_setting_select_room',
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
	protected $_modelName = 'ReservationEvent';

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
		$data['ReservationEvent'] = (new ReservationEventFixture())->records[0];

		return array(
			//beforeValidate
			array('data' => $data, 'field' => 'reservation_rrule_id', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
			//array('data' => $data, 'field' => 'room_id', 'value' => 'a', //pending エラーになりました⇒Undefined index: a /var/www/app/app/Plugin/Reservations/Utility/ReservationPermissiveRooms.php:144
			//	'message' => __d('net_commons', 'Invalid request.')),
			//array('data' => $data, 'field' => 'room_id', 'value' => 'a', //pending エラーになりました⇒Undefined index: a /var/www/app/app/Plugin/Reservations/Utility/ReservationPermissiveRooms.php:144
			//	'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'target_user', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'title', 'value' => '',
				'message' => __d('reservations', 'Please input title text.')),
			array('data' => $data, 'field' => 'is_allday', 'value' => '',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'start_date', 'value' => '1234',
				'message' => __d('reservations', 'Invalid value.')),
			array('data' => $data, 'field' => 'start_date', 'value' => '11110101',
				'message' => __d('reservations', 'Out of range value.')),
			array('data' => $data, 'field' => 'start_time', 'value' => '1',
				'message' => __d('reservations', 'Invalid value.')),
			array('data' => $data, 'field' => 'end_date', 'value' => '1',
				'message' => __d('reservations', 'Invalid value.')),
			array('data' => $data, 'field' => 'end_date', 'value' => '99990101',
				'message' => __d('reservations', 'Out of range value.')),
			array('data' => $data, 'field' => 'end_time', 'value' => 'a',
				'message' => __d('reservations', 'Invalid value.')),
			array('data' => $data, 'field' => 'timezone', 'value' => 'a',
				'message' => __d('reservations', 'Invalid value.')),
			array('data' => $data, 'field' => 'timezone', 'value' => '-13', //範囲外
				'message' => __d('reservations', 'Invalid value.')),
			array('data' => $data, 'field' => 'recurrence_event_id', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'exception_event_id', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),

			// Workflowパラメータ関連バリデーション（_doMergeWorkflowParamValidate）
			array('data' => $data, 'field' => 'language_id', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'status', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'is_active', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'is_latest', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
		);
	}

/**
 * Validatesのテスト
 *
 * @param array $data 登録データ
 * @param string $field フィールド名
 * @param string $value セットする値
 * @param string $message エラーメッセージ
 * @param array $overwrite 上書きするデータ
 * @dataProvider dataProviderValidationError
 * @return void
 */
	public function testValidationError($data, $field, $value, $message, $overwrite = array()) {
		$model = $this->_modelName;

		$testCurrentData = array(
			'Frame' => array(
				'key' => 'frame_3',
				'room_id' => '2',
				'language_id' => 2,
				'plugin_key' => 'reservations',
				),
			'Language' => array(
				'id' => 2,
				),
			'Room' => array(
				'id' => '2',
				),
			'User' => array(
				'id' => 1,
				),
			'Permission' => array(
				),
			);
		Current::$current = Hash::merge(Current::$current, $testCurrentData);

		// 施設予約権限設定情報確保
		$testRoomInfos = array(
			'roomInfos' => array(
				'2' => array(
					'role_key' => 'room_administrator',
					'use_workflow' => '',
					'content_publishable_value' => 1,
					'content_editable_value' => 1,
					'content_creatable_value' => 1,
				),
			),
		);
		ReservationPermissiveRooms::$roomPermRoles = Hash::merge(ReservationPermissiveRooms::$roomPermRoles, $testRoomInfos);

		if (is_null($value)) {
			unset($data[$model][$field]);
		} else {
			$data[$model][$field] = $value;
		}
		$data = Hash::merge($data, $overwrite);

		//validate処理実行
		$this->$model->set($data);
		$result = $this->$model->validates();
		$this->assertFalse($result);

		if ($message) {
			$this->assertEquals($this->$model->validationErrors[$field][0], $message);
		}
	}

}
