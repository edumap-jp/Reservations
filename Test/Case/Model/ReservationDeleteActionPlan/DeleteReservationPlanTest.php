<?php
/**
 * ReservationActionPlan::deleteReservationPlan()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');


/**
 * ReservationDeleteActionPlan::deleteReservationPlan()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationDeleteActionPlan
 */
class ReservationDeleteActionPlanDeleteReservationPlanTest extends NetCommonsModelTestCase {

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
	protected $_modelName = 'ReservationDeleteActionPlan';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'deleteReservationPlan';

/**
 * テストDataの取得
 *
 * @return array
 */
	private function __getData() {
		$data = array(
			'ReservationDeleteActionPlan' => array(
				'is_repeat' => 0,
				'first_sib_event_id' => 1,
				'origin_event_id' => 1,
				'is_recurrence' => 0,
				'edit_rrule' => 0,
			),
			'_NetCommonsTime' => array(
				'user_timezone' => 'Asia/Tokyo',
				'convert_fields' => '',
			),
		);

		return $data;
	}

/**
 * Delete用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return array テストデータ
 */
	public function dataProviderDelete() {
		$data1 = $this->__getData();

		$data2 = $data1;
		$data2['ReservationDeleteActionPlan']['edit_rrule'] = 1; //この日以降の予定を変更(削除)

		$data3 = $data1;
		$data3['ReservationDeleteActionPlan']['edit_rrule'] = 2; //この日を含むすべての予定を変更(削除)

		$data4 = $data1;
		$data4['ReservationDeleteActionPlan']['edit_rrule'] = 3; //エラー

		$results = array();
		// * 編集の登録処理
		$results[0] = array($data1, 1, 'reservationplan1', 1, 0, 'reservationplan1');
		$results[1] = array($data2, 1, 'reservationplan1', 1, 0, 'reservationplan1');
		$results[2] = array($data3, 1, 'reservationplan1', 1, 0, 'reservationplan1');
		$results[3] = array($data4, 1, 'reservationplan1', 1, 0, 'reservationplan1', 'InternalErrorException');

		return $results;
	}

/**
 * SaveのExceptionError用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *  - mockModel Mockのモデル
 *  - mockMethod Mockのメソッド
 *  - originEventId
 *  - originEventKye
 *  - originRruleId
 *  - isOriginRepeat
 *  - expect 期待値
 *
 * @return array テストデータ
 */
	public function dataProviderDeleteOnExceptionError() {
		$data = $this->__getData();

		$dataForAll = $data;
		$dataForAll['ReservationDeleteActionPlan']['edit_rrule'] = 2; //この日を含むすべての予定を変更(削除)

		$dataAfter = $data;
		$dataAfter['ReservationDeleteActionPlan']['edit_rrule'] = 1; //この日以降の予定を変更(削除)

		return array(
			array($data, 'Reservations.ReservationEvent', 'deleteAll', 1, 'reservationplan1', 1, 0),
			array($data, 'Reservations.ReservationRrule', 'delete', 1, 'reservationplan1', 1, 0),
			array($dataForAll, 'Reservations.ReservationEvent', 'deleteAll', 1, 'reservationplan1', 1, 0),
			array($dataForAll, 'Reservations.ReservationRrule', 'delete', 1, 'reservationplan1', 1, 0),
			array($dataAfter, 'Reservations.ReservationEvent', 'deleteAll', 1, 'reservationplan1', 1, 0),

			//array($dataForAll, 'Reservations.ReservationRrule', 'delete', 999, 'reservationplanx', 1,0, 1), //存在しない
			//↑pending  存在しないデータを渡すと、ReservationDeletePlanBehavior.phpの関数setCurEventDataAndRruleDataの中で処理が中断されるようです。

		);
	}

/**
 * Deleteのテスト
 *
 * @param array $data 登録データ
 * @param int $originEventId
 * @param string $originEventKey
 * @param int $originRruleId
 * @param bool $isOriginRepeat
 * @param int $expect
 * @param string $exception
 * @dataProvider dataProviderDelete
 * @return void
 */
	public function testDelete($data, $originEventId, $originEventKey, $originRruleId, $isOriginRepeat, $expect, $exception = '') {
		$model = $this->_modelName;
		$method = $this->_methodName;

		if ($exception != null) {
			$this->setExpectedException($exception);
		}

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
				'id' => 1, //システム管理者
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

		//テスト実行
		$result = $this->$model->$method($data, $originEventId, $originEventKey, $originRruleId, $isOriginRepeat);
		//print_r($this->$model->validationErrors);

		//$this->assertNotEmpty($result);
		$this->assertEquals($result, $expect);
	}

/**
 * SaveのExceptionErrorテスト
 *
 * @param array $data 登録データ
 * @param string $mockModel Mockのモデル
 * @param string $mockMethod Mockのメソッド
 * @param int $originEventId
 * @param string $originEventKey
 * @param int $originRruleId
 * @param bool $isOriginRepeat
 * @dataProvider dataProviderDeleteOnExceptionError
 * @return void
 */
	public function testDeleteOnExceptionError($data, $mockModel, $mockMethod, $originEventId, $originEventKey, $originRruleId, $isOriginRepeat) {
		$model = $this->_modelName;
		$method = $this->_methodName;

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

		$this->_mockForReturnFalse($model, $mockModel, $mockMethod);

		$this->setExpectedException('InternalErrorException');

		//テスト実行
		$result = $this->$model->$method($data, $originEventId, $originEventKey, $originRruleId, $isOriginRepeat);
		$this->assertFalse($result);
	}

}
