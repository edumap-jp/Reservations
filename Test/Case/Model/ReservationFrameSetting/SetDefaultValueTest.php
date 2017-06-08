<?php
/**
 * ReservationFrameSetting::setDefaultValue()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('ReservationsComponent', 'Reservations.Controller/Component'); //constを使うため

/**
 * ReservationFrameSetting::setDefaultValue()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationFrameSetting
 */
class ReservationFrameSettingSetDefaultValueTest extends NetCommonsModelTestCase {

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
	protected $_methodName = 'setDefaultValue';

/**
 * setDefaultValue()のテスト
 *
 * @param mix $data FrameSettingデータ
 * @param mix $expect 期待値
 * @dataProvider dataProviderSetDefaultValue
 * @return void
 */
	public function testSetDefaultValue($data, $expect) {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//Current::$current['Frame']['key'] = $frameKey;

		//テスト実施
		$this->$model->$methodName($data);

		//チェック
		$this->assertEqual($data, $expect);
	}

/**
 * SetDefaultValueのDataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return void
 */
	public function dataProviderSetDefaultValue() {
		$data = array();
		$expect = array();

		$expect = array(
			'ReservationFrameSetting' => array(
				'display_type' => ReservationsComponent::CALENDAR_DISP_TYPE_SMALL_MONTHLY,
				'display_count' => ReservationsComponent::CALENDAR_STANDARD_DISPLAY_DAY_COUNT,
				'timeline_base_time' => ReservationsComponent::CALENDAR_TIMELINE_DEFAULT_BASE_TIME,
				'is_select_room' => false,
				'is_myroom' => true,
				'start_pos' => '0',
				'frame_key' => '',
				'room_id' => '',
				'created_user' => null,
				'created' => null,
				'modified_user' => null,
				'modified' => null,
				'id' => null,
			)
		);
		return array(
			array($data, $expect),
		);
	}

}
