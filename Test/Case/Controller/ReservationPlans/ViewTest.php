<?php
/**
 * ReservationPlansController Test Case
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationPlansController', 'ReservationPlans.Controller');
//App::uses('NetCommonsControllerTestCase', 'NetCommons.TestSuite');
App::uses('WorkflowControllerViewTest', 'Workflow.TestSuite');
App::uses('UserAttributeLayoutFixture', 'UserAttributes.Test/Fixture');

/**
 * ReservationPlansController Test Case
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Controller\ReservationPlansController
 */
class ReservationPlansControllerViewTest extends WorkflowControllerViewTest {

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
		'plugin.holidays.holiday',
		'plugin.holidays.holiday_rrule',
		'plugin.reservations.roles_room4test', //add
		'plugin.reservations.roles_rooms_user4test', //add
		//'plugin.groups.user_attribute_layout4_groups_test', //add
		'plugin.user_attributes.user_attribute_layout',
		'plugin.rooms.room_role_permission4test', //test
		'plugin.reservations.plugins_role4test', //add
		'plugin.reservations.room4test',
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
 * viewアクションのテスト用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderView() {
		$results = array();

		//ログインなし
		//--コンテンツあり
		$results[0] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan1'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		$results[1] = Hash::merge($results[0], array( //編集ボタンなし
			'assert' => array('method' => 'assertActionLink', 'action' => 'edit', 'linkExist' => false, 'url' => array()),
		));
		//--コンテンツなし
		$results[2] = array(
			'urlOptions' => array('frame_id' => '14', 'key' => null),
			//'assert' => array('method' => 'assertEquals', 'expected' => 'emptyRender'),
			//'exception' => null, 'return' => 'viewFile'
			'assert' => null,
			'exception' => 'ForbiddenException',

		);

		return $results;
	}

/**
 * viewアクションのテスト(作成権限のみ)用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderViewByCreatable() {
		$results = array();
		//作成権限のみ(一般が書いた予定＆一度公開している)
		$results[0] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan3'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		$results[1] = Hash::merge($results[0], array( //編集ボタンあり
			//'assert' => array('method' => 'assertActionLink', 'action' => 'edit', 'linkExist' => true, 'url' => array()),
			'assert' => array('method' => 'assertContains', 'expected' => '/reservations/reservation_plans/edit/reservationplan3'),
		));
		//作成権限のみ(一般が書いた予定＆公開前)
		$results[2] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan2'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		$results[3] = Hash::merge($results[2], array( //編集ボタンあり
			//'assert' => array('method' => 'assertActionLink', 'action' => 'edit', 'linkExist' => true, 'url' => array()),
			'assert' => array('method' => 'assertContains', 'expected' => '/reservations/reservation_plans/edit/reservationplan2'),
		));
		//作成権限のみ(他人が書いた予定＆公開中)
		$results[4] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan5'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		$results[5] = Hash::merge($results[4], array( //編集ボタンなし
			//'assert' => array('method' => 'assertActionLink', 'action' => 'edit', 'linkExist' => false, 'url' => array()),
			'assert' => array('method' => 'assertNotContains', 'expected' => '/reservations/reservation_plans/edit/reservationplan5'),
		));
		//作成権限のみ(他人が書いた予定＆公開前)
		$results[6] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan4'),
			'assert' => null,
			'exception' => 'ForbiddenException',
		);
		//作成権限のみ（承認待ち）
		$results[7] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan25'),
			'assert' => null,
		);
		//作成権限のみ（差し戻し）
		$results[8] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan26'),
			'assert' => null,
		);
		//--コンテンツなし
		$results[9] = array(
			'urlOptions' => array('frame_id' => '14', 'key' => null),
			//'assert' => array('method' => 'assertEquals', 'expected' => 'emptyRender'),
			//'exception' => null, 'return' => 'viewFile'
			'assert' => null,
			'exception' => 'ForbiddenException',
		);
		//--パラメータ不正(keyに該当する予定が存在しない)
		$results[10] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan_99'),
			//'assert' => null,
			//'exception' => 'BadRequestException',
			'assert' => null,
			'exception' => 'ForbiddenException',
		);

		return $results;
	}

/**
 * viewアクションのテスト用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderViewByEditable() {
		$results = array();
		//編集権限あり
		//--コンテンツあり 繰り返し(日/1日ごと/2回)
		$results[0] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan7'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//--コンテンツあり 繰り返し(週/1週/2回)
		$results[1] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan10'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//--コンテンツあり 繰り返し(月/第1週日曜日/2回)
		$results[2] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan12'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//--コンテンツあり 繰り返し(年/2回)
		$results[3] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan14'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//--コンテンツあり 繰り返し(週/2週おき(木)/2016.9.2まで)
		$results[4] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan16'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//--コンテンツあり 繰り返し(月/2か月ごとに2日/2016.10.01まで)
		$results[5] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan17'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//--コンテンツあり 繰り返し(年/2年ごとに,9月 / 開始日と同日 / 2017年09月01日まで)
		$results[6] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan18'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//--コンテンツあり 繰り返し(日/2日ごと/2016.09.02まで)
		$results[7] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan19'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//--コンテンツあり 繰り返し(月/第2週月曜日/1回)
		$results[8] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan20'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//--コンテンツあり 繰り返し(月/第3週火曜日/1回)
		$results[9] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan21'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//--コンテンツあり 繰り返し(月/第4週水曜日/1回)
		$results[10] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan22'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//--コンテンツあり 繰り返し(月/最終週木曜日/1回)
		$results[11] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan23'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//--コンテンツあり 期間
		$results[12] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan9line'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		$results[13] = Hash::merge($results[0], array( //編集ボタンあり
			//'assert' => array('method' => 'assertActionLink', 'action' => 'edit', 'linkExist' => true, 'url' => array()),
			'assert' => array('method' => 'assertContains', 'expected' => '/reservations/reservation_plans/edit/reservationplan7'),
		));
		//--コンテンツなし
		$results[14] = array(
			'urlOptions' => array('frame_id' => '14', 'key' => null),
			//'assert' => array('method' => 'assertEquals', 'expected' => 'emptyRender'),FREQ=MONTHLY;INTERVAL=1;BYDAY=1SU;COUNT=2
			//'exception' => null, 'return' => 'viewFile'
			'assert' => null,
			'exception' => 'ForbiddenException',
		);
		//フレームID指定なしテスト
		$results[15] = array(
			'urlOptions' => array('frame_id' => null, 'key' => 'reservationplan6'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//adminが編集長(chief_editor)と共有(差し込まれた予定)
		$results[16] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan27'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//$results[17] = Hash::merge($results[3], array(
			//'assert' => array('method' => 'assertActionLink', 'action' => 'edit', 'linkExist' => true, 'url' => array()),
			//'assert' => array('method' => 'assertContains', 'expected' => '/reservations/reservation_plans/edit/reservationplan6'),
		//)); //frame_idを省略すると編集不可（３．０．０では編集をOFFにする動き）
		return $results;
	}

/**
 * viewアクションのテスト(編集権限、公開権限あり)
 *
 * @param array $urlOptions URLオプション
 * @param array $assert テストの期待値
 * @param string|null $exception Exception
 * @param string $return testActionの実行後の結果
 * @dataProvider dataProviderViewByPublishable
 * @return void
 */
	public function testViewByPublishable($urlOptions, $assert, $exception = null, $return = 'view') {
		//ログイン
		TestAuthGeneral::login($this, Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR);

		//テスト設定
		CakeSession::write('Auth.User.UserRoleSetting.use_private_room', true);

		//テスト実施
		$url = Hash::merge(array(
			'plugin' => $this->plugin,
			'controller' => $this->_controller,
			'action' => 'view',
		), $urlOptions);

		$this->_testGetAction($url, $assert, $exception, $return);

		//ログアウト
		TestAuthGeneral::logout($this);
	}

/**
 * viewアクションのテスト用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderViewByPublishable() {
		$results = array();

		//共有者あり
		//adminが編集長と共有した
		$results[0] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan27'),
			//'assert' => array('method' => 'assertNotEmpty'),
			'assert' => array('method' => 'assertContains', 'expected' => 'reservationplan27'),
		);
		//全会員
		$results[1] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'reservationplan24'),
			//'assert' => array('method' => 'assertNotEmpty'),
			'assert' => array('method' => 'assertContains', 'expected' => 'reservationplan24'),
		);

		return $results;
	}

}
