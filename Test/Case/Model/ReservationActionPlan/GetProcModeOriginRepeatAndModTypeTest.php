<?php
/**
 * ReservationActionPlan::getProcModeOriginRepeatAndModType()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsGetTest', 'NetCommons.TestSuite');
App::uses('ReservationsComponent', 'Reservations.Controller/Component'); //constを使うため
//App::uses('ReservationEventFixture', 'Reservations.Test/Fixture');

/**
 * ReservationActionPlan::getProcModeOriginRepeatAndModType()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationActionPlan
 */
class ReservationActionPlanGetProcModeOriginRepeatAndModTypeTest extends NetCommonsGetTest {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
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
	protected $_modelName = 'ReservationActionPlan';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'getProcModeOriginRepeatAndModType';

/**
 * テストDataの取得
 *
 * @param string $key key
 * @return array
 */
	private function __getData($key = 'key_1') {
		$frameId = '6';
		$blockId = '2';
		$blockKey = 'block_1';

		$data = array(
			'save_1' => '',
			'Frame' => array(
				'id' => $frameId,
				'room_id' => '2', //?
				'language_id' => 2, //?
				'plugin_key' => 'reservations', //?
			),
			'Block' => array(
				'id' => $blockId,
				'key' => $blockKey,
				//'language_id' => '2',
				//'room_id' => '1',
				//'plugin_key' => $this->plugin,
			),
			'ReservationActionPlan' => array(
				//'key' => 'aaa',
				'status' => 2,
				'origin_event_id' => 0,
				'origin_event_key' => '',
				'origin_event_recurrence' => 0,
				'origin_event_exception' => 0,
				'origin_rrule_id' => 1,
				'origin_rrule_key' => 'aaa',
				'origin_num_of_event_siblings' => 0,
				'first_sib_event_id' => 0,
				'first_sib_year' => 2016,
				'first_sib_month' => 7,
				'first_sib_day' => 28,
				'easy_start_date' => '',
				'easy_hour_minute_from' => '',
				'easy_hour_minute_to' => '',
				'is_detail' => 1,
				'title_icon' => '',
				'title' => 'test3',
				'enable_time' => 0,
				'detail_start_datetime' => '2016-07-28',
				'detail_end_datetime' => '2016-07-28',
				'is_repeat' => 0,
				'repeat_freq' => 'DAILY',
				'rrule_interval' => array(
					'DAILY' => 1,
					'WEEKLY' => 1,
					'MONTHLY' => 1,
					'YEARLY' => 1,
				),
				'rrule_byday' => array(
					'WEEKLY' => array(
						'0' => 'TH',
					),
					'MONTHLY' => '',
					'YEARLY' => '',
				),
				'rrule_bymonthday' => array(
					'MONTHLY' => '',
				),
				'rrule_bymonth' => array(
					'YEARLY' => array(
						'0' => 7,
					),
				),
				'rrule_term' => 'COUNT',
				'rrule_count' => 3,
				'rrule_until' => '2016-07-28',
				'plan_room_id' => '2',
				'enable_email' => 0,
				'email_send_timing' => 5,
				'location' => '',
				'contact' => '',
				'description' => '',
				'timezone' => 'Asia/Tokyo',
			),
			'ReservationActionPlanForDisp' => array(
				'detail_start_datetime' => '2016-07-28 11:00',
				'detail_end_datetime' => '2016-07-28',
			),
			'WorkflowComment' => array(
				//'comment' => 'WorkflowComment save test'
				'comment' => '',
			),
		);

		return $data;
	}

/**
 * テストEventDataの取得
 *
 * @param string $key key
 * @return array
 */
	private function __getEventData($key = 'key_1') {
		$eventData = array(
				'ReservationEvent' => array(
				'id' => 49,
				'timezone' => 9.0,
				'enable_time' => 0,
				'dtstart' => '20160630150000',
				'dtend' => '20160701150000',
			),
				'ReservationRrule' => array(
				'id' => 1,
				'rrule' => 'FREQ=DAILY;INTERVAL=1;COUNT=3',
			),
		);
		return $eventData;
	}

/**
 * getProcModeOriginRepeatAndModType()のテスト
 *
 * @param array $data data
 * @param array $originEvent
 * @param int $userId ユーザーID
 * @param mix $expect 期待値
 * @dataProvider dataProviderGet
 * @return void
 */
	public function testGetProcModeOriginRepeatAndModType($data, $originEvent, $userId, $expect) {
		$model = $this->_modelName;
		$methodName = $this->_methodName;
		$testCurrentData = array(
			'Frame' => array(
				'key' => 'frame_3',
				'room_id' => '2',
				'language_id' => 2,
				'plugin_key' => 'reservations',
				),
			'Language' => array(
				'id' => 1,
				),
			'Room' => array(
				'id' => '2',
				),
			'User' => array(
				'id' => $userId,
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
					'content_publishable_value' => 0,
					'content_editable_value' => 0,
					'content_creatable_value' => 1,
				),
			),
		);
		ReservationPermissiveRooms::$roomPermRoles = Hash::merge(ReservationPermissiveRooms::$roomPermRoles, $testRoomInfos);

		//テスト実施
		$result = $this->$model->$methodName($data, $originEvent);

		//チェック
		$this->assertEqual($result, $expect);
	}

/**
 * GetのDataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return void
 */
	public function dataProviderGet() {
		$data1 = $this->__getData();
		$data2 = $this->__getData();

		$data3 = $this->__getData();
		$data3['ReservationActionPlan']['origin_num_of_event_siblings'] = 2;

		$data4 = $this->__getData();
		$data4['ReservationActionPlan']['origin_event_id'] = 1;

		$data5 = $this->__getData();
		$data5['ReservationActionPlan']['origin_event_id'] = 1;
		$data5['ReservationActionPlan']['timezone'] = 'Asia/Bangkok'; //timezoneの変更

		$data6 = $this->__getData();
		$data6['ReservationActionPlan']['origin_event_id'] = 1;
		$data6['ReservationActionPlan']['enable_time'] = 1; //期間・時間の指定(編集)
		$data6['ReservationActionPlan']['detail_start_datetime'] = '2016-07-01 00:00';
		$data6['ReservationActionPlan']['detail_end_datetime'] = '2016-07-12 00:00';

		$data7 = $this->__getData();
		$data7['ReservationActionPlan']['repeat_freq'] = 'WEEKYL';
		$data7['ReservationActionPlan']['is_repeat'] = 1;
		$data7['ReservationActionPlan']['origin_event_id'] = 10;
		$data7['ReservationActionPlan']['rrule_term'] = 'UNTIL';
		$data7['ReservationActionPlan']['rrule_until'] = '2018-01-01';

		$originEvent1 = array();
		$originEvent2 = $this->__getEventData();
		$originEvent3 = $this->__getEventData();
		$originEvent4 = $this->__getEventData();
		$originEvent5 = $this->__getEventData();
		$originEvent6 = $this->__getEventData();
		$originEvent7 = $this->__getEventData();

		//list($procMode, $isOriginRepeat, $isTimeMod, $isRepeatMod)
		$expect1 = array(0 => ReservationsComponent::PLAN_ADD, 1 => false, 2 => false, 3 => false);
		$expect2 = array(0 => ReservationsComponent::PLAN_ADD, 1 => false, 2 => true, 3 => true);
		$expect3 = array(0 => ReservationsComponent::PLAN_ADD, 1 => true, 2 => true, 3 => true);
		$expect4 = array(0 => ReservationsComponent::PLAN_EDIT, 1 => false, 2 => true, 3 => true);
		$expect5 = array(0 => ReservationsComponent::PLAN_EDIT, 1 => false, 2 => true, 3 => true);
		$expect6 = array(0 => ReservationsComponent::PLAN_EDIT, 1 => false, 2 => true, 3 => true);
		$expect7 = array(0 => ReservationsComponent::PLAN_EDIT, 1 => false, 2 => true, 3 => true);

		return array(
			array($data1, $originEvent1, 1, $expect1), //originEventがEmpty
			array($data2, $originEvent2, 1, $expect2), //時間変更あり、繰り返し変更あり
			array($data3, $originEvent3, 1, $expect3), //元データが繰り返し
			array($data4, $originEvent4, 1, $expect4), //EDIT
			array($data5, $originEvent5, 1, $expect5), //EDIT(time zoneが変更されている)
			array($data6, $originEvent6, 1, $expect6), //EDIT(時間の指定)
			array($data7, $originEvent7, 1, $expect7), //EDIT
		);
	}

}
