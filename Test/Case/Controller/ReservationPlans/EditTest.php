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
App::uses('WorkflowControllerEditTest', 'Workflow.TestSuite');
App::uses('ReservationsComponent', 'Reservations.Controller/Component');	//constを使うため

/**
 * ReservationPlansController Test Case
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Controller\ReservationPlansController
 */
class ReservationPlansControllerEditTest extends WorkflowControllerEditTest {

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
		'plugin.holidays.holiday',
		'plugin.holidays.holiday_rrule',
		'plugin.reservations.roles_room4test',
		'plugin.reservations.roles_rooms_user4test',
		'plugin.user_attributes.user_attribute_layout',
		//'plugin.reservations.room4test',
		'plugin.reservations.room4test',
		//'plugin.rooms.room_role', //add 2016.09.30
		//'plugin.rooms.room_role_permission4test', //add 2016.09.30

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
 * @param string $originEventId オリジナルEventId
 * @return array
 */
	private function __getData($originEventId = '0') {
		$blockKey = 'block_1';
		$originalRruleId = $originEventId;
		if ($originEventId == 1) {
			$originEventKey = 'reservationplan1';
		} elseif ($originEventId == 2) {
			$originEventKey = 'reservationplan2';
		} elseif ($originEventId == 4) {
			$originEventKey = 'reservationplan4';
		} elseif ($originEventId == 6) {
			$originEventKey = 'reservationplan6';
		} elseif ($originEventId == 8) {
			$originEventKey = 'reservationplan7';
			$originalRruleId = 8;
		} elseif ($originEventId == 27) {
			$originEventKey = 'reservationplan27';
		}
		$data = array(
			'save_' . WorkflowComponent::STATUS_PUBLISHED => null,
			'Frame' => array(
				'id' => '6',
			),
			'Block' => array(
				'id' => '2',
				'key' => $blockKey,
			),
			'ReservationActionPlan' => array(
				'origin_event_id' => $originEventId,
				'origin_event_key' => $originEventKey,
				'origin_event_recurrence' => '0',
				'origin_event_exception' => '0',
				'origin_rrule_id' => $originalRruleId,
				'origin_rrule_key' => $originEventKey,
				'origin_num_of_event_siblings' => '0',
				'is_repeat' => '0',
				'first_sib_event_id' => '0',
				'is_recurrence' => '0',
				'edit_rrule' => '0', //null,
				'first_sib_event_id' => 0,
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
				), ), ),
				'rrule_bymonth' => array(
					'YEARLY' => array(
						'0' => 7,
				), ),
				'rrule_term' => 'COUNT',
				'rrule_count' => '3',
				'rrule_until' => '2016-09-04',
				'plan_room_id' => '2',
				'enable_email' => '',
				'email_send_timing' => '5',
				'location' => 'locationText',
				'contact' => '',
				'description' => '',
				'timezone' => 'Asia/Tokyo',
			),
			'WorkflowComment' => array(
				'comment' => 'WorkflowComment save test'),
		);
		return $data;
	}

/**
 * editアクションのGETテスト(ログインなし)用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderEditGet() {
		$data = $this->__getData(1);
		$results = array();

		//ログインなし
		$results[0] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => $data['ReservationActionPlan']['origin_event_key']),
			'assert' => null, 'exception' => 'ForbiddenException'
		);
		return $results;
	}

/**
 * editアクションのGETテスト(作成権限のみ)用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderEditGetByCreatable() {
		$data = $this->__getData(1);
		$results = array();

		//作成権限のみ
		//--他人の記事
		$results[0] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan1'),
			'assert' => null,
			//'exception' => 'BadRequestException',
			'exception' => 'ForbiddenException',
		);
		$results[1] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan4'),
			'assert' => null,
			//'exception' => 'BadRequestException',
			'exception' => 'ForbiddenException',
		);
		//--自分の記事
		$results[2] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan2'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		$results[3] = Hash::merge($results[2], array(
			'assert' => array('method' => 'assertInput', 'type' => 'input', 'name' => 'data[Frame][id]', 'value' => $data['Frame']['id']),
		));
		$results[4] = Hash::merge($results[2], array(
			'assert' => array('method' => 'assertInput', 'type' => 'input', 'name' => 'data[Block][id]', 'value' => $data['Block']['id']),
		));
		$results[5] = Hash::merge($results[2], array(
			//'assert' => array('method' => 'assertInput', 'type' => 'input', 'name' => '_method', 'value' => 'DELETE'),
			'assert' => array('method' => 'assertInput', 'type' => 'button', 'name' => 'delete', 'value' => null),
		));
		//--自分の記事(一度公開済み)
		$results[6] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan3'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//フレームID指定なしテスト
		$results[7] = Hash::merge($results[2], array(
			//'urlOptions' => array('frame_id' => null, 'block_id' => $data['Block']['id'], 'key' => 'reservationplan2'),
			//'urlOptions' => array('frame_id' => null, 'block_id' => $data['Block']['id']),
			'urlOptions' => array('frame_id' => null, 'block_id' => 0),
			'assert' => array('method' => 'assertNotEmpty'),
		));
		return $results;
	}

/**
 * editアクションのGETテスト(編集権限、公開権限なし)用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderEditGetByEditable() {
		$data = $this->__getData(4);
		$results = array();

		//編集権限あり
		//--コンテンツあり
		$base = 0;
		$results[0] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan4'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		array_push($results, Hash::merge($results[$base], array(
			'assert' => array('method' => 'assertActionLink', 'action' => 'delete', 'linkExist' => false, 'url' => array()),
		)));
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
		//--コンテンツなし
		$results[count($results)] = array(
			'urlOptions' => array('frame_id' => '14', 'block_id' => null, 'key' => null),
			'assert' => array('method' => 'assertEquals', 'expected' => 'emptyRender'),
			'exception' => 'ForbiddenException', 'return' => 'viewFile'
		);

		return $results;
	}

/**
 * editアクションのGETテスト(公開権限あり)用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderEditGetByPublishable() {
		$data = $this->__getData(6);
		$results = array();

		//--コンテンツあり
		$base = 0;
		$results[0] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan6'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//繰り返しあり(この予定のみ変更)
		$results[1] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan7'),
			//'assert' => array('method' => 'assertNotEmpty'),
			'assert' => array('method' => 'assertContains', 'expected' => __d('reservations', 'only this one')),
		);
		//繰り返しあり(WEEKLY)
		$results[2] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan10'),
			'assert' => array('method' => 'assertContains', 'expected' => __d('reservations', 'only this one')), //あとで変更pending
		);
		//繰り返しあり(MONTHLY曜日指定)
		$results[3] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan12'),
			'assert' => array('method' => 'assertContains', 'expected' => __d('reservations', 'only this one')),
		);
		//繰り返しあり(MONTHLY日指定)
		$results[4] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan17'),
			//'assert' => array('method' => 'assertContains', 'expected' => __d('reservations', 'only this one')),
			'assert' => array('method' => 'assertNotContains', 'expected' => __d('reservations', 'only this one')), //ReservationEventが1件の場合は、繰り返し編集の設定なし
		);
		//繰り返しあり(YEARLY)(COUNT)
		$results[5] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan14'),
			'assert' => array('method' => 'assertContains', 'expected' => __d('reservations', 'only this one')),
		);
		//繰り返しあり(YEARLY)(UNTIL)
		$results[6] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan18'),
			'assert' => array('method' => 'assertNotContains', 'expected' => __d('reservations', 'only this one')), //ReservationEventが1件の場合は、繰り返し編集の設定なし
		);
		array_push($results, Hash::merge($results[$base], array(
			'assert' => array('method' => 'assertActionLink', 'action' => 'delete', 'linkExist' => false, 'url' => array()),
		)));
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
		array_push($results, Hash::merge($results[0], array(
			'assert' => array('method' => 'assertInput', 'type' => 'button', 'name' => 'delete', 'value' => null),
		)));
		//フレームID指定なしテスト
		array_push($results, Hash::merge($results[$base], array(
			'urlOptions' => array('frame_id' => null, 'block_id' => $data['Block']['id'], 'key' => 'reservationplan6'),
			'assert' => array('method' => 'assertNotEmpty'),
		)));

		return $results;
	}

/**
 * editアクションのPOSTテスト
 *
 * @param array $data POSTデータ
 * @param string $role ロール
 * @param array $urlOptions URLオプション
 * @param string|null $exception Exception
 * @param string $return testActionの実行後の結果
 * @dataProvider dataProviderEditPost
 * @return void
 */
	public function testEditPost($data, $role, $urlOptions, $exception = null, $return = 'view') {
		//ログイン
		if (isset($role)) {
			TestAuthGeneral::login($this, $role);
		}

		//テスト設定
		CakeSession::write('Auth.User.UserRoleSetting.use_private_room', true);

		//テスト実施
		$this->_testPostAction(
			'post', $data, Hash::merge(array('action' => 'edit'), $urlOptions), $exception, $return
		);

		//正常の場合、リダイレクト
		if (! $exception) {
			$header = $this->controller->response->header();
			$this->assertNotEmpty($header['Location']);
		}

		//ログアウト
		if (isset($role)) {
			TestAuthGeneral::logout($this);
		}
	}

/**
 * editアクションのPOSTテスト用DataProvider
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
	public function dataProviderEditPost() {
		$data = $this->__getData(1);
		$data2 = $this->__getData(2);
		//unset($data2['ReservationActionPlan']['timezone']); //timezoneの設定が無いケース（ReservationActionPlan.php:926行目でおちる）

		$data4 = $this->__getData(4);

		//$data5 = $data4;

		$data6 = $this->__getData(2);
		$data6['ReservationActionPlan']['plan_room_id'] = '8'; //privateroom

		$data7 = $this->__getData(27);
		$data7['ReservationActionPlan']['plan_room_id'] = '8'; //privateroom

		//$data8 = $this->__getData(8); //この予定のみ変更
		//$data8['ReservationActionPlan']['is_repeat'] = 1; //※この予定のみで繰り返し変更は指定できない（ReservationPlansController.php:472でエラー）
		//$data8['ReservationActionPlan']['edit_rrule'] = 1; //この日以降の予定を変更

		//$data5['ReservationActionPlan']['rrule_term'] = 'UNTIL';
		//$data5['ReservationActionPlan']['rrule_until'] = '2018-01-01';

		return array(
			//ログインなし
			array(
				'data' => $data, 'role' => null,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan1'),
				'exception' => 'ForbiddenException',
			),
			//作成権限のみ
			//--他人の記事
			array(
				'data' => $data, 'role' => Role::ROOM_ROLE_KEY_GENERAL_USER,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan1'),
				'exception' => 'ForbiddenException',
			),
			//--自分の記事
			array(
				'data' => $data2, 'role' => Role::ROOM_ROLE_KEY_GENERAL_USER,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan2'),
			),
			//編集権限あり
			//--コンテンツあり
			array(
				'data' => $data4, 'role' => Role::ROOM_ROLE_KEY_EDITOR,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan4'),
			),
			//--共有者ありの予定変更
			array(
				'data' => $data7, 'role' => Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan27'),
			),

			//--コンテンツあり(この予定のみ変更)
			//array(
			//	'data' => $data8, 'role' => Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR,
			//	'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan7'),
			//),
			//array(
			//	'data' => $data5, 'role' => Role::ROOM_ROLE_KEY_EDITOR,
			//	'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan4'),
			//),
			//フレームID指定なしテスト
			array(
				'data' => $data2, 'role' => Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR,
				'urlOptions' => array('frame_id' => null, 'block_id' => $data['Block']['id'], 'key' => 'reservationplan2'),
			), //pending ReservationPlansController.php 304行目のルート($frameIdが0)は通らない？
			//--自分のプライベート記事に変更
			array(
				'data' => $data6, 'role' => Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'reservationplan2'),
			),

		);
	}

/**
 * editアクションのValidationErrorテスト用DataProvider
 *
 * ### 戻り値
 *  - data: 登録データ
 *  - urlOptions: URLオプション
 *  - validationError: バリデーションエラー
 *
 * @return array
 */
	public function dataProviderEditValidationError() {
		$data1 = $this->__getData(2);
		$result1 = array(
			'data' => $data1,
			'urlOptions' => array('frame_id' => $data1['Frame']['id'], 'block_id' => $data1['Block']['id'], 'key' => 'reservationplan2'),
		);

		$data2 = $this->__getData(4);
		$data2['ReservationActionPlan']['is_repeat'] = 1;
		$data2['ReservationActionPlan']['rrule_term'] = 'UNTIL';
		$data2['ReservationActionPlan']['rrule_until'] = '2018-01-01';

		$result2 = array(
			'data' => $data2,
			'urlOptions' => array('frame_id' => $data2['Frame']['id'], 'block_id' => $data2['Block']['id'], 'key' => 'reservationplan4'),
		);

		return array(
			Hash::merge($result1, array(
				'validationError' => array(
					'field' => 'ReservationActionPlan.title',
					'value' => '',
					'message' => __d('reservations', 'Invalid input. (plan title)'),
				)
			)),
			Hash::merge($result2, array(
				'validationError' => array(
					'field' => 'ReservationActionPlan.rrule_until',
					'value' => '2018-01-01',
					'message' => __d('reservations',
						'Cyclic rules using deadline specified exceeds the maximum number of %d',
						intval(ReservationsComponent::CALENDAR_RRULE_COUNT_MAX)),
				)
			)),
		);
	}

/**
 * editアクションのValidateionErrorテスト
 *
 * @param array $data POSTデータ
 * @param array $urlOptions URLオプション
 * @param string|null $validationError ValidationError
 * @dataProvider dataProviderEditValidationError
 * @return void
 */
	public function testEditValidationError($data, $urlOptions, $validationError = null) {
		//ログイン
		TestAuthGeneral::login($this);

		//テスト実施
		$this->_testActionOnValidationError(
			'post', $data, Hash::merge(array('action' => 'edit'), $urlOptions), $validationError
		);

		//ログアウト
		TestAuthGeneral::logout($this);
	}

}
