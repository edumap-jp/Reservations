<?php
/**
 * ReservationFrameSetting::saveFrameSetting()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsSaveTest', 'NetCommons.TestSuite');
App::uses('ReservationFrameSettingFixture', 'Reservations.Test/Fixture');

/**
 * ReservationFrameSetting::saveFrameSetting()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationFrameSetting
 */
class ReservationFrameSettingSaveFrameSettingTest extends NetCommonsSaveTest {

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
	protected $_methodName = 'saveFrameSetting';

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
		$data['ReservationFrameSetting']['is_myroom'] = false;

		$data['ReservationFrameSettingSelectRoom'] = array();
		$selectRoomFixture = new ReservationFrameSettingSelectRoomFixture();
		// Modelの試験のときはパブリックデータしか操作できない....ログイン状態を作れない
		$data['ReservationFrameSettingSelectRoom'][1] = $selectRoomFixture->records[0];
		$data['ReservationFrameSettingSelectRoom'][4] = $selectRoomFixture->records[3];
		$data['ReservationFrameSettingSelectRoom'][5] = array(
			'reservation_frame_setting_id' => 1,
			'room_id' => '6'
		);

		$results = array();
		// * 編集の登録処理
		$results[0] = array($data);
		// * 新規の登録処理
		$results[1] = array($data);
		$results[1] = Hash::insert($results[1], '0.ReservationFrameSetting.id', null);
		$results[1] = Hash::remove($results[1], '0.ReservationFrameSetting.created');
		$results[1] = Hash::remove($results[1], '0.ReservationFrameSetting.created_user');

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
			array($data, 'Reservations.ReservationFrameSetting', 'save'),
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
			array($data, 'Reservations.ReservationFrameSetting'),
			array($data, 'Reservations.ReservationFrameSettingSelectRoom'),
		);
	}

}
