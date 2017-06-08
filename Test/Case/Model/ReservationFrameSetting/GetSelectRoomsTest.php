<?php
/**
 * ReservationFrameSetting::getSelectRooms()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('WorkflowGetTest', 'Workflow.TestSuite');

/**
 * ReservationFrameSetting::getSelectRooms()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationFrameSetting
 */
class ReservationFrameSettingGetSelectRoomsTest extends WorkflowGetTest {

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
	protected $_modelName = 'ReservationFrameSetting';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'getSelectRooms';

/**
 * GetのDataProvider
 *
 * #### 戻り値
 *  - data 登録データ
 *
 * @return void
 */
	public function dataProviderGet() {
		$rooms = array(
			'2' => array(
				'reservation_frame_setting_id' => 1,
				'room_id' => '2',
			),
			//'3' => 2,
			//'4' => 3,
			'5' => array(
				'reservation_frame_setting_id' => 1,
				'room_id' => '5',
			),
			'6' => array(
				'reservation_frame_setting_id' => null,
				'room_id' => null,
			)
		);
		$errRooms = array(
			'2' => array(
				'reservation_frame_setting_id' => null,
				'room_id' => null,
			),
			//'3' => null,
			//'4' => null,
			'5' => array(
				'reservation_frame_setting_id' => null,
				'room_id' => null,
			),
			'6' => array(
				'reservation_frame_setting_id' => null,
				'room_id' => null,
			)
		);
		return array(
			array(1, 'frame_3', $rooms),
			array(100, 'frame_3', $errRooms),
			array(null, 'frame_3', $rooms),
			array(null, null, array()),
		);
	}

/**
 * getSelectRooms()のテスト
 *
 * @param int $settingId FrameSettingレコードのID
 * @param string $frameKey frame key
 * @param mix $expect 期待値
 * @dataProvider dataProviderGet
 * @return void
 */
	public function testGetSelectRooms($settingId, $frameKey, $expect) {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		Current::$current['Frame']['key'] = $frameKey;

		//テスト実施
		$result = $this->$model->$methodName($settingId);

		//チェック
		$this->assertEqual($result, $expect);
	}

}
