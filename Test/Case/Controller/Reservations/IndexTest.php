<?php
/**
 * ReservationsController Test Case
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsController', 'Reservations.Controller');
App::uses('WorkflowControllerIndexTest', 'Workflow.TestSuite');
App::uses('NetCommonsControllerTestCase', 'NetCommons.TestSuite');
App::uses('ReservationsComponent', 'Reservations.Controller/Component');
App::uses('ReservationFrameSetting', 'Reservations.Model');
App::uses('ReservationFrameSettingSelectRoom', 'Reservations.Model');

/**
 * ReservationsController Test Case
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Controller\ReservationsController
 */
class ReservationsControllerIndexTest extends NetCommonsControllerTestCase {

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
		'plugin.reservations.room4test',
		'plugin.rooms.rooms_language4test',
		'plugin.rooms.room_role_permission4test', //test
		'plugin.reservations.roles_room4test', //add
		'plugin.reservations.roles_rooms_user4test', //add
		'plugin.reservations.plugins_role4test', //add1
		'plugin.holidays.holiday',
		'plugin.holidays.holiday_rrule',
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
	protected $_controller = 'reservations';

/**
 * テストDataの取得
 *
 * @return array
 */
	private function __getData() {
		$frameId = '6';
		$blockId = '2';
		$blockKey = 'block_1';

		$data = array(
			'Frame' => array(
				'id' => $frameId
			),
			'Block' => array(
				'id' => $blockId,
				'key' => $blockKey,
				'language_id' => '2',
				'room_id' => '1',
				'plugin_key' => $this->plugin,
			),
		);

		return $data;
	}

/**
 * indexアクションのテスト
 *
 * @param array $urlOptions URLオプション
 * @param array $assert テストの期待値
 * @param string $defStyle 表示設定（スタイル）
 * @param string $startPos 開始位置（昨日/今日）
 * @param string|null $exception Exception
 * @param string $return testActionの実行後の結果
 * @dataProvider dataProviderIndex
 * @return void
 */
	public function testIndex($urlOptions, $assert, $defStyle = '', $startPos = '', $exception = null, $return = 'view') {
		//スタイル Fixture書き換え
		//Current::$current['ReservationFrameSetting']['display_type'] = $style;
		$data['ReservationFrameSetting'] = (new ReservationFrameSettingFixture())->records[0];
		$data['ReservationFrameSetting']['display_type'] = $defStyle;

		if ($startPos == ReservationsComponent::CALENDAR_START_POS_WEEKLY_YESTERDAY) {
			$data['ReservationFrameSetting']['start_pos'] = ReservationsComponent::CALENDAR_START_POS_WEEKLY_YESTERDAY;
		} elseif ($startPos == ReservationsComponent::CALENDAR_START_POS_WEEKLY_TODAY) {
			$data['ReservationFrameSetting']['start_pos'] = ReservationsComponent::CALENDAR_START_POS_WEEKLY_TODAY;
		}

		$this->controller->ReservationFrameSetting->save($data);

		//テスト実施
		$url = Hash::merge(array(
			'plugin' => $this->plugin,
			'controller' => $this->_controller,
			'action' => 'index',
		), $urlOptions);
		$this->_testGetAction($url, $assert, $exception, $return);
	}

/**
 * indexアクションのテスト(ログインなし)用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderIndex() {
		$results = array();
		//ログインなし（月縮小）
		$results[0] = array(
			'urlOptions' => array('frame_id' => '6'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		//ログインなし（月拡大）
		$results[1] = array( //(年月日指定なし)
			'urlOptions' => array('frame_id' => '6'),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY,
		);
		$results[2] = array( //(年が不正 最小値（CALENDAR_RRULE_TERM_UNTIL_YEAR_MIN以下）2001)
			'urlOptions' => array('frame_id' => '6', '?' => array('year' => '2000', 'month' => '9', 'day' => '9')),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY,
		);
		$results[3] = array( //(年が不正 最大値（CALENDAR_RRULE_TERM_UNTIL_YEAR_MAX以上）2033)
			'urlOptions' => array('frame_id' => '6', '?' => array('year' => '2034', 'month' => '9', 'day' => '9')),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY,
		);
		//ログインなし（週表示）
		$results[4] = array( //(年月日指定あり)
			'urlOptions' => array('frame_id' => '6', '?' => array('year' => '2016', 'month' => '9', 'day' => '9')),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_WEEKLY,
		);
		$results[5] = array( // (week指定あり)
			'urlOptions' => array('frame_id' => '6', '?' => array('year' => '2016', 'month' => '9', 'week' => '1')),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_WEEKLY,
		);
		$results[6] = array( // (指定なし)
			'urlOptions' => array('frame_id' => '6'),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_WEEKLY,
		);
		$results[7] = array( // (初期表示は週表示、style(パラメータ)で日表示:daily、リスト表示)
			'urlOptions' => array('frame_id' => '6', '?' => array('year' => '2016', 'month' => '9', 'day' => '9', 'style' => 'daily', 'tab' => 'list')),
			//'assert' => array('method' => 'assertNotEmpty'),
			'assert' => array('method' => 'assertContains', 'expected' => 'reservationplan1'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_WEEKLY,
		);
		$results[8] = array( // (初期表示は週表示、style(パラメータ)で日表示:daily、タイムライン表示)
			'urlOptions' => array('frame_id' => '6', '?' => array('year' => '2016', 'month' => '9', 'day' => '9', 'style' => 'daily', 'tab' => 'timeline')),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_WEEKLY,
		);
		$results[9] = array( // (初期表示は週表示、style(パラメータ)不正（不正時は月縮小表示）)
			'urlOptions' => array('frame_id' => '6', '?' => array('year' => '2016', 'month' => '9', 'day' => '9', 'style' => 'aaaa')),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_WEEKLY,
		);
		$results[10] = array( //ログインなし（日表示）(タイムライン)
			'urlOptions' => array('frame_id' => '6', '?' => array('tab' => 'timeline', 'year' => '2016', 'month' => '9', 'day' => '1')),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_DAILY,
		);
		$results[11] = array( //ログインなし（スケジュール（時間順）表示）(今日から表示)
			'urlOptions' => array('frame_id' => '6', '?' => array('year' => '2016', 'month' => '9', 'day' => '7')),
			'assert' => array('method' => 'assertNotContains', 'expected' => __d('reservations', 'yesterday')),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_TSCHEDULE,
		);
		$results[12] = array( //ログインなし（スケジュール（時間順）表示）（昨日から表示）
			'urlOptions' => array('frame_id' => '6', '?' => array('year' => '2016', 'month' => '9', 'day' => '9')),
			'assert' => array('method' => 'assertContains', 'expected' => __d('reservations', 'yesterday')),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_TSCHEDULE,
			'startPos' => ReservationsComponent::CALENDAR_START_POS_WEEKLY_YESTERDAY,
		);
		$results[13] = array( //ログインなし（スケジュール（会員順）表示）(今日から表示)
			'urlOptions' => array('frame_id' => '6', '?' => array('year' => '2016', 'month' => '9', 'day' => '1')),
			'assert' => array('method' => 'assertNotContains', 'expected' => __d('reservations', 'yesterday')),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_MSCHEDULE,
			'startPos' => ReservationsComponent::CALENDAR_START_POS_WEEKLY_TODAY,
		);
		$results[14] = array( //ログインなし（スケジュール（会員順）表示）（昨日から表示）
			'urlOptions' => array('frame_id' => '6', '?' => array('year' => '2016', 'month' => '9', 'day' => '9')),
			'assert' => array('method' => 'assertContains', 'expected' => __d('reservations', 'yesterday')),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_MSCHEDULE,
			'startPos' => ReservationsComponent::CALENDAR_START_POS_WEEKLY_YESTERDAY,
		);
		$results[15] = array( //チェック--追加ボタンチェック(なし)
			'urlOptions' => array('frame_id' => '6', 'block_id' => '2'),
			'assert' => array('method' => 'assertActionLink', 'action' => 'add', 'linkExist' => false, 'url' => array()),
		);
		return $results;
	}

/**
 * indexアクションのテスト(編集権限あり)
 *
 * @param array $urlOptions URLオプション
 * @param array $assert テストの期待値
 * @param string $defStyle スタイル
 * @param string|null $exception Exception
 * @param string $return testActionの実行後の結果
 * @dataProvider dataProviderIndexByEditable
 * @return void
 */
	public function testIndexByEditable($urlOptions, $assert, $defStyle = '', $exception = null, $return = 'view') {
		//ログイン
		TestAuthGeneral::login($this, Role::ROOM_ROLE_KEY_EDITOR);

		//スタイル Fixture書き換え
		$data['ReservationFrameSetting'] = (new ReservationFrameSettingFixture())->records[0];
		$data['ReservationFrameSetting']['display_type'] = $defStyle;
		$this->controller->ReservationFrameSetting->save($data);

		//テスト実施
		$url = Hash::merge(array(
			'plugin' => $this->plugin,
			'controller' => $this->_controller,
			'action' => 'index',
		), $urlOptions);

		$this->_testGetAction($url, $assert, $exception, $return);

		//ログアウト
		TestAuthGeneral::logout($this);
	}

/**
 * indexアクションのテスト(編集権限あり)用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderIndexByEditable() {
		$results = array();

		//編集権限あり
		$base = 0;
		$results[0] = array(
			'urlOptions' => array('frame_id' => '6', 'block_id' => '2', '?' => array('year' => '2016', 'month' => '9', 'day' => '9')),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_DAILY,
		);
		$results[1] = array(
			'urlOptions' => array('frame_id' => '6', 'block_id' => '2', '?' => array('year' => '2016', 'month' => '9', 'day' => '9')),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY,
		);
		$results[2] = array( //祝日あり
			'urlOptions' => array('frame_id' => '6', 'block_id' => '2', '?' => array('year' => '2015', 'month' => '1', 'day' => '1')),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY,
		);
		//チェック
		//--追加ボタンチェック 日表示
		array_push($results, Hash::merge($results[$base], array(
			//'assert' => array('method' => 'assertActionLink', 'action' => 'add', 'linkExist' => true, 'url' => array('controller' => 'reservation_plans')),
			'assert' => array('method' => 'assertContains', 'expected' => '/reservations/reservation_plans/add?'),
		)));
		//フレームあり（ブロックなし）
		array_push($results, Hash::merge($results[$base], array(
			'urlOptions' => array('frame_id' => '14', 'block_id' => null),
			'assert' => array('method' => 'assertEquals', 'expected' => 'index'),
			'exception' => null, 'return' => 'viewFile'
		)));
		//フレームID指定なしテスト
		array_push($results, Hash::merge($results[$base], array(
			'urlOptions' => array('frame_id' => null, 'block_id' => '2'),
			'assert' => array('method' => 'assertContains', 'expected' => 'index'),
		)));
		//  pending 120行目の表示形式が不明の場合の最後のelse「月縮小とみなす」のルートは通せない？？
		//（スケジュール（defStyleが不正）
		//array_push($results, Hash::merge($results[$base], array(
		//	'urlOptions' => array('frame_id' => '6'),
		//	'assert' => array('method' => 'assertNotEmpty'), 'defStyle' => '99',
		//)));

		return $results;
	}

/**
 * indexアクションのテスト(作成権限あり)
 *
 * @param array $urlOptions URLオプション
 * @param array $assert テストの期待値
 * @param string $defStyle スタイル
 * @param string|null $exception Exception
 * @param string $return testActionの実行後の結果
 * @dataProvider dataProviderIndexByCreatable
 * @return void
 */
	public function testIndexByCreatable($urlOptions, $assert, $defStyle = '', $exception = null, $return = 'view') {
		//ログイン
		TestAuthGeneral::login($this, Role::ROOM_ROLE_KEY_GENERAL_USER);

		//スタイル Fixture書き換え
		$data['ReservationFrameSetting'] = (new ReservationFrameSettingFixture())->records[0];
		$data['ReservationFrameSetting']['display_type'] = $defStyle;
		$this->controller->ReservationFrameSetting->save($data);

		//テスト実施
		$url = Hash::merge(array(
			'plugin' => $this->plugin,
			'controller' => $this->_controller,
			'action' => 'index',
		), $urlOptions);

		$this->_testGetAction($url, $assert, $exception, $return);

		//ログアウト
		TestAuthGeneral::logout($this);
	}

/**
 * indexアクションのテスト(作成権限のみ)用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderIndexByCreatable() {
		$data = $this->__getData();
		$results = array();

		//作成権限あり(2015.1.1祝日設定あり)
		$base = 0;
		$results[0] = array(
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id']),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY,
		);
		$results[1] = array(
			'urlOptions' => array('frame_id' => '6', '?' => array('year' => '2016', 'month' => '9', 'day' => '9')),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY,
		);
		$results[2] = array(
			'urlOptions' => array('frame_id' => '6', 'block_id' => '2', '?' => array('year' => '2016', 'month' => '7', 'day' => '28')),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY,
		);
		//チェック
		//--追加ボタンチェック
		array_push($results, Hash::merge($results[$base], array(
			//'assert' => array('method' => 'assertActionLink', 'action' => 'add', 'linkExist' => true, 'url' => array()),
			'assert' => array('method' => 'assertContains', 'expected' => '/reservations/reservation_plans/add?'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY,
		)));
		//フレームID指定なしテスト
		array_push($results, Hash::merge($results[$base], array(
			'urlOptions' => array('frame_id' => null, 'block_id' => $data['Block']['id']),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY,
		)));

		return $results;
	}

/**
 * indexアクションのテスト(公開権限あり)
 *
 * @param array $urlOptions URLオプション
 * @param array $assert テストの期待値
 * @param string $defStyle スタイル
 * @param string|null $exception Exception
 * @param string $return testActionの実行後の結果
 * @dataProvider dataProviderIndexByPublishable
 * @return void
 */
	public function testIndexByPublishable($urlOptions, $assert, $defStyle = '', $exception = null, $return = 'view') {
		//ログイン
		TestAuthGeneral::login($this, Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR);
		//TestAuthGeneral::login($this, UserRole::USER_ROLE_KEY_SYSTEM_ADMINISTRATOR);

		//スタイル Fixture書き換え
		$data['ReservationFrameSetting'] = (new ReservationFrameSettingFixture())->records[0];
		$data['ReservationFrameSetting']['display_type'] = $defStyle;

		if ($defStyle == ReservationsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY) {
			$data['ReservationFrameSetting']['is_myroom'] = false; //プライベートルームを表示するかどうか（表示しない）
			//$data['ReservationFrameSetting']['is_myroom'] = true;
			$data['ReservationFrameSetting']['is_select_room'] = false; //指定したルームのみを表示するかどうか（指定なし）
		} else {
			$data['ReservationFrameSetting']['is_myroom'] = true; //プライベートルームを表示するかどうか（表示する）
			$data['ReservationFrameSetting']['is_select_room'] = false; //指定したルームのみを表示するかどうか（指定なし）
		}

		//テスト設定
		CakeSession::write('Auth.User.UserRoleSetting.use_private_room', true);

		$this->controller->ReservationFrameSetting->save($data);

		//テスト実施
		$url = Hash::merge(array(
			'plugin' => $this->plugin,
			'controller' => $this->_controller,
			'action' => 'index',
		), $urlOptions);

		$this->_testGetAction($url, $assert, $exception, $return);

		//ログアウト
		TestAuthGeneral::logout($this);
	}

/**
 * indexアクションのテスト(公開権限あり)用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderIndexByPublishable() {
		$results = array();

		//公開権限あり
		$results[0] = array(
			'urlOptions' => array('frame_id' => '6', '?' => array('year' => '2016', 'month' => '9', 'day' => '9')),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY,
		);
		//（週表示）
		// (年月日指定あり)
		$results[1] = array(
			'urlOptions' => array('frame_id' => '6', '?' => array('year' => '2016', 'month' => '9', 'day' => '9')),
			'assert' => array('method' => 'assertNotEmpty'),
			'defStyle' => ReservationsComponent::CALENDAR_DISP_TYPE_WEEKLY,
		);

		return $results;
	}

}
