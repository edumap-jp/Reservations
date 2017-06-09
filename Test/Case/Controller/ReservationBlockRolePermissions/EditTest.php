<?php
/**
 * ReservationBlockRolePermissionsController Test Case
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsControllerTestCase', 'NetCommons.TestSuite');
App::uses('ReservationsComponent', 'Reservations.Controller/Component');
App::uses('ReservationFrameSettingFixture', 'Reservations.Test/Fixture');
App::uses('ReservationPermissiveRooms', 'Reservations.Utility');
App::uses('BlockRolePermissionsControllerEditTest', 'Blocks.TestSuite');

/**
 * ReservationBlockRolePermissionsController Test Case
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Controller
 */
class ReservationBlockRolePermissionsControllerEditTest extends BlockRolePermissionsControllerEditTest {

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
		'plugin.reservations.roles_room4test', //add
		'plugin.reservations.roles_rooms_user4test', //add
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
	protected $_controller = 'reservation_block_role_permissions';

/**
 * テストDataの取得
 * @param bool $isPost POSTかどうか
 *
 * @return array
 */
	private function __getData($isPost) {
		if ($isPost == true) {
			$data = array(
				'save' =>	'',
				'Frame' => array(
						'id' => 6,
					),
				'Block' => array(
					'id' => 2,
					'key' => 'block_1',
					'language_id' => '',
					'room_id' => '',
					'plugin_key' => '',
				),
				'2' => array(
					'1' => array(
						'BlockRolePermission' => array(
							'content_creatable' => array(
								'general_user' => array(
								'id' => '1',
								'roles_room_id' => '4',
								'permission' => 'content_creatable',
								'value' => '1',
								),
							),
						),
						'reservation' => array(
							'block_key' => 'block_1',
							'id' => '',
							'use_workflow' => '1',
						),
					),
				));
		} else {
			$data = array(
				'ReservationSetting' => array(
					'use_workflow',
					'use_comment_approval',
					'approval_type',
				)
			);
		}

		return $data;
	}

/**
 * edit()アクションDataProvider
 *
 * ### 戻り値
 *  - approvalFields コンテンツ承認の利用有無のフィールド
 *  - exception Exception
 *  - return testActionの実行後の結果
 *
 * @return void
 */
	public function dataProviderEditGet() {
		return array(
			array('approvalFields' => $this->__getData(false))
		);
	}

/**
 * editアクションのGETテスト
 *
 * @param array $approvalFields コンテンツ承認の利用有無のフィールド
 * @param string|null $exception Exception
 * @param string $return testActionの実行後の結果
 * @dataProvider dataProviderEditGet
 * @return string Viewの内容
 */
	public function testEditGet($approvalFields, $exception = null, $return = 'view') {
		//ログイン
		TestAuthGeneral::login($this);

		$frameId = '6';
		$blockId = '4';

		//テスト実施
		$url = array(
			'plugin' => $this->plugin,
			'controller' => $this->_controller,
			'action' => 'edit',
			'frame_id' => $frameId,
			'block_id' => $blockId
		);
		$params = array(
			'method' => 'get',
			'return' => 'view',
		);
		$result = $this->_testNcAction($url, $params, $exception, $return);

		if (! $exception) {
			//チェック
			$assert = array(
				'method' => 'assertInput', 'type' => 'form', 'name' => null,
				'value' => NetCommonsUrl::actionUrl($url)
			);
			$this->asserts(array($assert), $result);

			$assert = array(
				'method' => 'assertInput', 'type' => 'input', 'name' => 'data[Block][id]',
				'value' => $blockId
			);
			$this->asserts(array($assert), $result);

		}

		//ログアウト
		TestAuthGeneral::logout($this);

		return $result;
	}

/**
 * editアクションのGETテスト(Exceptionエラー) (施設予約の場合は不要)
 *
 * @param array $approvalFields コンテンツ承認の利用有無のフィールド
 * @param string|null $exception Exception
 * @param string $return testActionの実行後の結果
 * @dataProvider dataProviderEditGet
 * @return void
 */
	/*
	public function testEditGetExceptionError($approvalFields, $exception = null, $return = 'view') {
		$this->_mockForReturnFalse('Reservations.ReservationPermission', 'getReservationRoomBlocks');

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

		$exception = 'BadRequestException';
		$this->testEditGet($approvalFields, $exception, $return);
	}
	*/

/**
 * edit()アクションDataProvider
 *
 * ### 戻り値
 *  - data POSTデータ
 *  - exception Exception
 *  - return testActionの実行後の結果
 *
 * @return void
 */
	public function dataProviderEditPost() {
		return array(
			array('data' => $this->__getData(true)),
			//array('data' => $this->__getData(true), 'exception' => 'InternalErrorException'),
		);
	}

/**
 * edit()アクションDataProvider
 *
 * ### 戻り値
 *  - data POSTデータ
 *  - exception Exception
 *  - return testActionの実行後の結果
 *
 * @return void
 */
	public function dataProviderEditPostSaveError() {
		return array(
			array('data' => $this->__getData(true), 'exception' => 'InternalErrorException'),
			array('data' => $this->__getData(true), 'exception' => 'validates'),
		);
	}

/**
 * editアクションのPOSTテスト(Saveエラー)
 *
 * @param array $data POSTデータ
 * @param string|null $exception Exception
 * @param string $return testActionの実行後の結果
 * @dataProvider dataProviderEditPostSaveError
 * @return void
 */
	public function testEditPostSaveError($data, $exception = null, $return = 'view') {
		$data['BlockRolePermission']['content_creatable'][Role::ROOM_ROLE_KEY_GENERAL_USER]['roles_room_id'] = 'aaaa';

		if ($exception == 'InternalErrorException') {
			$this->setExpectedException('InternalErrorException');
			$this->_mockForReturnFalse('Reservations.ReservationPermission', 'save');
			//$this->setExpectedException('InternalErrorException');
		} else {
			$this->_mockForReturnFalse('Reservations.ReservationPermission', 'validates');
		}

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

		//テスト実施
		$this->testEditPost($data, false, $return);
		$this->assertEquals(
			$this->controller->request->data['BlockRolePermission']['content_creatable']['general_user']['roles_room_id'],
				'aaaa');
		//$approvalFields = $this->__getData(false);
		//$this->_assertEditGetPermission($approvalFields, $result);
	}

/**
 * ロールチェックdataProvider
 *
 * ### 戻り値
 *  - method: リクエストメソッド（get or post or put）
 *  - expected: 期待するviewファイル
 *  - role: ロール名
 *  - exception: Exception
 *
 * @return array
 */
	public function dataProviderRoleAccess() {
		$data = array(

			array(Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR, null),
			array(Role::ROOM_ROLE_KEY_CHIEF_EDITOR, 'ForbiddenException'),
			array(Role::ROOM_ROLE_KEY_EDITOR, 'ForbiddenException'),
			array(Role::ROOM_ROLE_KEY_GENERAL_USER, 'ForbiddenException'),
			array(Role::ROOM_ROLE_KEY_VISITOR, 'ForbiddenException'),
			array(null, 'ForbiddenException'),
		);
		return $data;
	}

}
