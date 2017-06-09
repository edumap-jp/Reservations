<?php
/**
 * Reservation::afterFrameSave()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('ReservationFixture', 'Reservations.Test/Fixture');
App::uses('ReservationFrameSettingFixture', 'Reservations.Test/Fixture');
App::uses('ReservationFrameSetting', 'Reservations.Model');

/**
 * Reservation::afterFrameSave()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Model\Reservation
 */
class ReservationAfterFrameSaveTest extends NetCommonsModelTestCase {

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
	protected $_modelName = 'Reservation';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'afterFrameSave';

/**
 * afterFrameSave()のテスト
 *
 * @param mix $data FrameSettingデータ
 * @param mix $expect 期待値
 * @param string $exception 例外
 * @dataProvider dataProviderAfterFrameSave
 * @return void
 */
	public function testAfterFrameSave($data, $expect, $exception = null) {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		if ($exception != null) {
			$this->setExpectedException($exception);
		}

		if (isset($data['FramesLanguage']['name'])) {
			if ($data['FramesLanguage']['name'] == 'testdata4') {
				$this->_mockForReturnFalse($model, 'Frames.Frame', 'save', 1);
			} elseif ($data['FramesLanguage']['name'] == 'testdata5') {
				$this->_mockForReturnTrue($model, 'Reservations.Reservation', '_saveFrameChangeAppearance', 1);
			} elseif ($data['FramesLanguage']['name'] == 'testdata6') {
				$this->_mockForReturnTrue($model, 'Reservations.Reservation', '_saveFrameChangeAppearance', 1);
			} elseif ($data['FramesLanguage']['name'] == 'testdata7') {
				//$this->_mockForReturnTrue($model, 'Reservations.Reservation', '_saveFrameChangeAppearance', 1);
				$this->_mockForReturnTrue($model, 'Reservations.ReservationFrameSetting', 'saveFrameSetting', 1);
				//$this->_mockForReturnFalse($model, 'Reservations.Reservation', '_saveReservation', 1);
				$mock = $this->getMockForModel('Reservations.Reservation', array('save'));
				$this->$model = $mock;
				$mock->expects($this->once())
				->method('save')
				->will($this->returnValue(array()));
			} elseif ($data['FramesLanguage']['name'] == 'testdata8') {
				//$this->_mockForReturnTrue($model, 'Reservations.ReservationFrameSetting', 'saveFrameSetting', 1);
				$mock = $this->getMockForModel('Blocks.Block', array('save'));
				$mock->expects($this->once())
				->method('save')
				->will($this->returnValue(array()));
			}
			unset($data['FramesLanguage']);
		}

		//テスト実施
		$this->$model->$methodName($data);

		//チェック
		$this->assertEquals($data, $expect);
	}

/**
 * AfterFrameSaveのDataProvider
 * ε(     v ﾟωﾟ) <data3以降 必要性もふくめて見直し
 * ### 戻り値
 *  - data 登録データ
 *
 * @return array
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function dataProviderAfterFrameSave() {
		//1.すでにブロックIDが存在
		$data1 = array();
		$expect1 = array();
		$data1['Frame']['block_id'] = 2;
		$expect1 = $data1;

		//2.Frameなし
		$data2 = array();
		$expect2 = array();

		////3.Frameあり/Blockあり(_saveFrameChangeAppearanceでfalse)
		//$data3 = array();
		//$expect3 = array();
		//$data3['Frame'] = array(
		//	'room_id' => '2',
		//	'plugin_key' => 'reservations',
		//	'key' => 'key_1'
		//);
		//$data3['FramesLanguage'] = array(
		//	'language_id' => 2,
		//);
		//
		////4.Frameあり/Blockなし
		//$data4 = array();
		//$expect4 = array();
		//$data4['Frame'] = array(
		//	'room_id' => '17',
		//	'plugin_key' => 'reservations',
		//	'key' => 'frame_3',
		//	//'bix_id' => 3,
		//	//'is_myroom' => 0,
		//	//'display_type' => 2,
		//	//'is_select_room' => 0,
		//	//'start_pos' => 0,
		//	//'display_count' => 3,
		//	//'timeline_base_time' => 8,
		//);
		//$data4['FramesLanguage'] = array(
		//	'language_id' => 2,
		//	'name' => 'testdata4'
		//);
		//
		////5.Frameあり/Blockあり(_saveFrameChangeAppearanceでtrue)
		//$data5 = array();
		//$expect5 = array();
		//$data5['Frame'] = array(
		//	'room_id' => '2',
		//	'plugin_key' => 'reservations',
		//	'key' => 'key_1',
		//);
		//$data5['FramesLanguage'] = array(
		//	'language_id' => 2,
		//	'name' => 'testdata5'
		//);
		//$expect5['Frame'] = $data5['Frame'];
		//
		////6.Frameあり/Blockあり(_saveReservationで施設予約を生成)
		//$data6 = array();
		//$expect6 = array();
		//$data6['Frame'] = array(
		//	'room_id' => '17',
		//	'plugin_key' => 'reservations',
		//	'key' => 'key_2',
		//);
		//$data6['FramesLanguage'] = array(
		//	'language_id' => 2,
		//	'name' => 'testdata6'
		//);
		//$expect6['Frame'] = $data6['Frame'];
		//
		////7.Frameあり/Blockあり(_saveReservationで施設予約生成失敗)
		//$data7 = array();
		//$expect7 = array();
		//$data7['Frame'] = array(
		//	'room_id' => '17',
		//	'plugin_key' => 'reservations',
		//	'key' => 'key_3',
		//);
		//$data7['FramesLanguage'] = array(
		//	'language_id' => 2,
		//	'name' => 'testdata7'
		//);
		//$expect7['Frame'] = $data7['Frame'];
		//
		////8 Blockのsaveでエラー
		//$data8 = array();
		//$expect8 = array();
		//$data8['Frame'] = array(
		//	'room_id' => 17,
		//	'plugin_key' => 'reservations',
		//	'key' => 'key_4',
		//);
		//$data8['FramesLanguage'] = array(
		//	'language_id' => 2,
		//	'name' => 'testdata8'
		//);
		//$expect8['Frame'] = $data8['Frame'];

		return array(
			array($data1, $expect1),
			array($data2, $expect2, 'BadRequestException'),
			//array($data3, $expect3, 'InternalErrorException'),
			//array($data4, $expect4, 'InternalErrorException'),
			//array($data5, $expect5),
			//array($data6, $expect6),
			//array($data7, $expect7, 'InternalErrorException'),
			//array($data8, $expect8, 'InternalErrorException'),
		);
	}

}
