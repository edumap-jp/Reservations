<?php
/**
 * ReservationFrameSettingSelectRoom::saveReservationFrameSettingSelectRoom()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <iinfo@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsSaveTest', 'NetCommons.TestSuite');
App::uses('ReservationFrameSettingFixture', 'Reservations.Test/Fixture');
App::uses('ReservationFrameSettingSelectRoomFixture', 'Reservations.Test/Fixture');

/**
 * ReservationFrameSettingSelectRoom::saveReservationFrameSettingSelectRoom()のテスト
 *
 * @author AllCreator <iinfo@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationFrameSettingSelectRoom
 */
class ReservationFrameSettingSelectRoomSaveReservationFrameSettingSelectRoomTest extends NetCommonsSaveTest {

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
	protected $_methodName = 'saveReservationFrameSettingSelectRoom';

/**
 * Saveのテスト
 *
 * @param array $data 登録データ
 * @dataProvider dataProviderSave
 * @return void
 */
	public function testSave($data) {
		$model = $this->_modelName;
		$method = $this->_methodName;

		// 登録したい選択ルーム
		$registRooms = array();
		foreach ($data[$this->$model->alias] as $selectRoom) {
			if (empty($selectRoom['room_id'])) {
				continue;
			}
			$registRooms[$selectRoom['room_id']] = $selectRoom['room_id'];
		}

		//テスト実行
		$this->$model->$method($data);

		//チェック用データ取得
		$after = $this->$model->find('all', array(
			'recursive' => -1,
			'conditions' => array('reservation_frame_setting_id' => $data['ReservationFrameSetting']['id']),
		));
		$after = Hash::combine($after, '{n}.ReservationFrameSettingSelectRoom.room_id', '{n}.ReservationFrameSettingSelectRoom.room_id');
		// 確認
		$this->assertEqual($after, $registRooms);
	}

/**
 * Save用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return array テストデータ
 */
	public function dataProviderSave() {
		$data['ReservationFrameSetting'] = (new ReservationFrameSettingFixture())->records[0];
		$data['ReservationFrameSetting']['is_select_room'] = '1';

		$data['ReservationFrameSettingSelectRoom'] = array();
		$selectRoomFixture = new ReservationFrameSettingSelectRoomFixture();
		$data['ReservationFrameSettingSelectRoom']['2'] = $selectRoomFixture->records[0];
		$data['ReservationFrameSettingSelectRoom']['2']['room_id'] = '';
		$data['ReservationFrameSettingSelectRoom']['3'] = $selectRoomFixture->records[1];
		$data['ReservationFrameSettingSelectRoom']['4'] = $selectRoomFixture->records[2];
		$data['ReservationFrameSettingSelectRoom']['5'] = $selectRoomFixture->records[3];
		$data['ReservationFrameSettingSelectRoom']['6'] = array(
			'reservation_frame_setting_id' => 1,
			'room_id' => '6'
		);
		$results = array();
		// * 削除の登録処理
		$results[0] = array($data);
		// * 登録処理
		$data['ReservationFrameSettingSelectRoom']['2']['room_id'] = '2';
		$results[1] = array($data);
		$results[1] = Hash::remove($results[1], '2.ReservationFrameSettingSelectRoom.created');
		$results[1] = Hash::remove($results[1], '2.ReservationFrameSettingSelectRoom.created_user');

		return $results;
	}

/**
 * SaveのExceptionError用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *  - mockModel Mockのモデル
 *  - mockMethod Mockのメソッド
 *
 * @return array テストデータ
 */
	public function dataProviderSaveOnExceptionError() {
		$data = $this->dataProviderSave()[0][0];

		return array(
			array($data, 'Reservations.ReservationFrameSettingSelectRoom', 'save'),
		);
	}

/**
 * SaveのValidationError用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *  - mockModel Mockのモデル
 *  - mockMethod Mockのメソッド(省略可：デフォルト validates)
 *
 * @return array テストデータ
 */
	public function dataProviderSaveOnValidationError() {
		$data = $this->dataProviderSave()[0][0];

		return array(
			array($data, 'Reservations.ReservationFrameSettingSelectRoom'),
		);
	}

}
