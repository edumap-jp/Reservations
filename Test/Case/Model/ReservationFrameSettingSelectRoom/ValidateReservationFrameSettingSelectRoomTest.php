<?php
/**
 * ReservationFrameSettingSelectRoom::validateReservationFrameSettingSelectRoom()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <iinfo@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');

/**
 * ReservationFrameSettingSelectRoom::validateReservationFrameSettingSelectRoom()のテスト
 *
 * @author AllCreator <iinfo@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationFrameSettingSelectRoom
 */
class ReservationFrameSettingSelectRoomValidateReservationFrameSettingSelectRoomTest extends NetCommonsModelTestCase {

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
	protected $_modelName = 'ReservationFrameSettingSelectRoom';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'validateReservationFrameSettingSelectRoom';

/**
 * validateReservationFrameSettingSelectRoom()のテスト
 *
 * @param array $data 登録データ
 * @param mix $expect 期待値
 * @dataProvider dataProviderValidate
 * @return void
 */
	public function testValidateReservationFrameSettingSelectRoom($data, $expect) {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//テスト実施
		$result = $this->$model->$methodName($data);

		//チェック
		$this->assertEqual($result, $expect);
	}
/**
 * ValidateのDataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return void
 */
	public function dataProviderValidate() {
		$data = array(
			'ReservationFrameSettingSelectRoom' => array(
				array(
					'reservation_frame_setting_id' => 1,
					'room_id' => '2'
				),
				array(
					'reservation_frame_setting_id' => 1,
					'room_id' => '5'
				),
				array(
					'reservation_frame_setting_id' => 1,
					'room_id' => '6'
				),
			)
		);
		$data2 = array(
			'ReservationFrameSettingSelectRoom' => array(
				array(
					'reservation_frame_setting_id' => 1,
					'room_id' => ''
				),
				array(
					'reservation_frame_setting_id' => 1,
					'room_id' => '5'
				),
				array(
					'reservation_frame_setting_id' => 1,
					'room_id' => 5000
				),
			)
		);
		return array(
			array($data, true),
			array($data2, false),
		);
	}
}
