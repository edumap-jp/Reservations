<?php
/**
 * ReservationPlansController Test Case
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationPlansController', 'Reservations.Controller');
App::uses('WorkflowControllerAddTest', 'Workflow.TestSuite');
App::uses('ReservationsComponent', 'Reservations.Controller/Component');	//constを使うため

/**
 * ReservationPlansController Test Case
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Controller\ReservationPlansController
 */
class ReservationPlansControllerAddTest extends WorkflowControllerAddTest {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.reservations.block_setting_for_reservation',
		'plugin.reservations.reservation',
		'plugin.reservations.reservation_event',
		//'plugin.reservations.reservation_event_content',,
		'plugin.reservations.reservation_event_share_user',
		'plugin.reservations.reservation_frame_setting',

		'plugin.reservations.reservation_rrule',
		'plugin.workflow.workflow_comment',
		'plugin.rooms.rooms_language4test',
		'plugin.holidays.holiday',
		'plugin.holidays.holiday_rrule',
		'plugin.reservations.roles_room4test', //add
		'plugin.reservations.roles_rooms_user4test', //add
		'plugin.user_attributes.user_attribute_layout',
		'plugin.reservations.room4test',
		//'plugin.groups.group4_users_test',
	);

/**
 * Plugin name
 *
 * @var array
 */
	public $plugin = 'reservations';

/**
 * Controller name
 *
 * @var string
 */
	protected $_controller = 'reservation_plans';

/**
 * テストDataの取得
 *
 * @param string $originEventId eventID
 * @return array
 */
	private function __getData($originEventId = '0') {
		$frameId = '6';
		$blockId = '2';
		$blockKey = 'block_1';

		$data = array(
			'save_' . WorkflowComponent::STATUS_PUBLISHED => null,
			//'delete' => null,
			'Frame' => array(
				'id' => $frameId
			),
			'Block' => array(
				'id' => $blockId,
				'key' => $blockKey,
			),
			'ReservationActionPlan' => array(
				'origin_event_id' => $originEventId,
				'origin_event_key' => 0,
				'origin_event_recurrence' => '0',
				'origin_event_exception' => '0',
				'origin_rrule_id' => '0',
				'origin_rrule_key' => '',
				'origin_num_of_event_siblings' => '0',
				'is_repeat' => '0',
				'first_sib_event_id' => '0',
				'is_recurrence' => '0',
				'edit_rrule' => '0',
				'first_sib_event_id' => 7,
				'first_sib_event_key' => 'reservationplanx',
				'first_sib_year' => '2016',
				'first_sib_month' => '9',
				'first_sib_day' => '4',
				'easy_start_date' => '',
				'easy_hour_minute_from' => '',
				'easy_hour_minute_to' => '',
				'is_detail' => 1,
				'title_icon' => '',
				'title' => 'add',
				'enable_time' => '0',
				'detail_start_datetime' => '2016-09-04',
				'detail_end_datetime' => '2016-09-04',
				'is_repeat' => 0,
				'repeat_freq' => 'DAILY',
				'rrule_interval' => array(
					'DAILY' => '1',
					'WEEKLY' => '1',
					'MONTHLY' => '1',
					'YEARLY' => '1',
					),
				'rrule_byday' => array(
					'WEEKLY' => array(
						'0' => 'SU',
					),
					'MONTHLY' => '',
					'YEARLY' => '',
				),
				'rrule_byday' => array(
					'WEEKLY' => array(
						'0' => 'SU'
					),
					'MONTHLY' => 0,
					'YEARLY' => 0,
				),
				'rrule_bymonthday' => array(
					'MONTHLY' => '',
						'rrule_bymonth' => array(
							'YEARLY' => array(
								'0' => 9,
							),
					),
				),
				'rrule_term' => 'COUNT',
				'rrule_count' => '3',
				'rrule_until' => '2016-09-04',
				'plan_room_id' => '2',
				'enable_email' => '',
				'email_send_timing' => '5',
				'location' => '',
				'contact' => '',
				'description' => '',
				'timezone' => 'Asia/Tokyo',
			),
			'WorkflowComment' => array(
				'comment' => 'WorkflowComment save test'
			),
		);

		return $data;
	}

/**
 * addアクションのGETテスト(ログインなし)用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderAddGet() {
		$data = $this->__getData();
		$results = array();

		//ログインなし
		$results[0] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id']),
			'assert' => null, 'exception' => 'ForbiddenException'
		);
		return $results;
	}

/**
 * addアクションのGETテスト(作成権限あり)用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderAddGetByCreatable() {
		$data = $this->__getData();
		$results = array();

		//作成権限あり
		$base = 0;
		$results[0] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], '?' => array('year' => '2016', 'month' => '9', 'day' => '7', 'hour' => '12')),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		array_push($results, Hash::merge($results[$base], array(
			'assert' => array('method' => 'assertInput', 'type' => 'input', 'name' => 'data[Frame][id]', 'value' => $data['Frame']['id']),
		)));
		array_push($results, Hash::merge($results[$base], array(
			'assert' => array('method' => 'assertInput', 'type' => 'input', 'name' => 'data[Block][id]', 'value' => $data['Block']['id']),
		)));
		array_push($results, Hash::merge($results[$base], array(
			'assert' => array('method' => 'assertInput', 'type' => 'button', 'name' => 'save_' . WorkflowComponent::STATUS_IN_DRAFT, 'value' => null),
		)));
		array_push($results, Hash::merge($results[$base], array(
			//'assert' => array('method' => 'assertInput', 'type' => 'button', 'name' => 'save_' . WorkflowComponent::STATUS_APPROVAL_WAITING, 'value' => null),
			'assert' => array('method' => 'assertInput', 'type' => 'button', 'name' => 'save_' . WorkflowComponent::STATUS_PUBLISHED, 'value' => null),
		)));

		//フレームID指定なしテスト
		array_push($results, Hash::merge($results[$base], array(
			'urlOptions' => array('frame_id' => null, 'block_id' => $data['Block']['id']),
			'assert' => array('method' => 'assertNotEmpty'),
		)));

		return $results;
	}

/**
 * addアクションのPOSTテスト用DataProvider
 *
 * ### 戻り値
 *  - data: 登録データ
 *  - role: ロール
 *  - urlOptions: URLオプション
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderAddPost() {
		$data = $this->__getData();

		//共有者あり
		$dataGroup = $data;
		$dataGroup['ReservationActionPlan']['plan_room_id'] = '8';
		$dataGroup['GroupsUser'][0]['user_id'] = 2;
		$dataGroup['GroupsUser'][1]['user_id'] = 1;

		$dataGroupN = $dataGroup;
		$dataGroupN['ReservationActionPlan']['plan_room_id'] = '5';

		//繰り返しあり
		$dataRep = $data;
		$dataRep['ReservationAction']['is_repeat'] = 1;
		$dataRep['ReservationAction']['rrule_term'] = 'UNTIL';

		$dataError1 = $data;
		unset($dataError1['save_' . WorkflowComponent::STATUS_PUBLISHED]);
		$dataError1['save_' . ''] = '';

		$dataError2 = $data;
		//unset($dataError2['Block']['key']);
		$dataError2['Block']['key'] = 'aaa';

		return array(
			//ログインなし
			array(
				'data' => $data, 'role' => null,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id']),
				'exception' => 'ForbiddenException'
			),
			//作成権限あり
			array(
				'data' => $data, 'role' => Role::ROOM_ROLE_KEY_GENERAL_USER,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id']),
			),
			//(共有者あり)
			array(
				'data' => $dataGroup, 'role' => Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id']),
			),
			//(共有者あり)(プライベートルームではないので無効になるパターン)
			array(
				'data' => $dataGroupN, 'role' => Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id']),
			),
			//(繰り返しあり（期限）)
			array(
				'data' => $dataRep, 'role' => Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id']),
			),
			//フレームID指定なしテスト
			array(
				'data' => $data, 'role' => Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR,
				'urlOptions' => array('frame_id' => null, 'block_id' => $data['Block']['id']),
			), //pending
			//save_不正
			array(
				'data' => $dataError1, 'role' => Role::ROOM_ROLE_KEY_GENERAL_USER,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id']),
				'exception' => 'InternalErrorException'

			),
			//blockKey不正
			array(
				'data' => $dataError2, 'role' => Role::ROOM_ROLE_KEY_GENERAL_USER,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id']),
				'exception' => 'InternalErrorException'
			),
		);
	}

/**
 * addアクションのValidationErrorテスト用DataProvider
 *
 * ### 戻り値
 *  - data: 登録データ
 *  - urlOptions: URLオプション
 *  - validationError: バリデーションエラー
 *
 * @return array
 */
	public function dataProviderAddValidationError() {
		$data = $this->__getData();
		//繰り返し回数不正
		$data['ReservationActionPlan']['is_repeat'] = 1;
		$data['ReservationActionPlan']['rrule_count'] = 1;
		$data['ReservationActionPlan']['timezone'] = 'Australia/Adelaide'; //timezoneが変わるルート
		$result = array(
			'data' => $data,
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id']),
		);

		return array(
			Hash::merge($result, array(
				'validationError' => array(
					'field' => 'ReservationActionPlan.title',
					'value' => '',
					'message' => __d('reservations', 'Invalid input. (plan title)'),
				)
			)),
			Hash::merge($result, array(
				'validationError' => array(
					'field' => 'ReservationActionPlan.rrule_count',
					'value' => '888',
					'message' => __d('reservations',
						'The number of repetition is %d or more and %d or less.', ReservationsComponent::CALENDAR_RRULE_COUNT_MIN, ReservationsComponent::CALENDAR_RRULE_COUNT_MAX),
				)
			)),
		);
	}

/**
 * addアクションのExceptionErrorテスト
 *
 * @param string $mockModel Mockのモデル
 * @param string $mockMethod Mockのメソッド
 * @param array $data POSTデータ
 * @param string $role ロール
 * @param array $urlOptions URLオプション
 * @param string $exception Exception
 * @param string $return testActionの実行後の結果
 * @dataProvider dataProviderAddExceptionError
 * @return void
 */
	public function testAddExceptionError($mockModel, $mockMethod, $data, $role, $urlOptions,
												$exception = null, $return = 'view') {
		//ログイン
		if (isset($role)) {
			TestAuthGeneral::login($this, $role);
		}

		//if ($mockMethod == 'maxtime') {
		//	$this->_mockForReturn($mockModel, 'saveReservationPlan', 1, 3);
		//} else {
		$this->_mockForReturn($mockModel, $mockMethod, false, 1);
		//}

		//テスト実施
		$this->_testPostAction(
			'post', $data, Hash::merge(array('action' => 'add'), $urlOptions), $exception, $return
		);
		//ログアウト
		if (isset($role)) {
			TestAuthGeneral::logout($this);
		}
	}

/**
 * addアクションのExceptionErrorテスト用DataProvider
 *
 * ### 戻り値
 *  - mockModel: Mockのモデル
 *  - mockMethod: Mockのメソッド
 *  - data: 登録データ
 *  - role: ロール
 *  - urlOptions: URLオプション
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderAddExceptionError() {
		$data = $this->__getData();

		//繰り返しあり
		$dataRep = $data;
		$dataRep['ReservationAction']['is_repeat'] = 1;
		$dataRep['ReservationAction']['rrule_count'] = 400;

		return array(
			array(
				'mockModel' => 'ReservationActionPlan', 'mockMethod' => 'saveReservationPlan', 'data' => $data,
				'role' => Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id']),
				//'exception' => 'BadRequestException' //pending save失敗
			),
		//	array(
		//		'mockModel' => 'ReservationActionPlan', 'mockMethod' => 'maxtime', 'data' => $dataRep,
		//		'role' => Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR,
		//		'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id']),
		//		'exception' => '' //pending save失敗
		//	),
		);
	}

}
