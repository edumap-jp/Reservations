<?php
/**
 * ReservationEvent::getSiblings()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('WorkflowGetTest', 'Workflow.TestSuite');
App::uses('ReservationEventFixture', 'Reservations.Test/Fixture');

/**
 * ReservationEvent::getSiblings()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationEvent
 */
class ReservationEventGetSiblingsTest extends WorkflowGetTest {

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
	protected $_modelName = 'ReservationEvent';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'getSiblings';

/**
 * getSiblings()のテスト
 *
 * @param int $rruleId  最新に限定するかどうか。0:最新に限定しない。1:最新に限定する
 * @param int $needLatest needLatest 最新に限定するかどうか。0:最新に限定しない。1:最新に限定する
 * @param int $languageId 言語ID
 * @param mix $expect 期待値
 * @dataProvider dataProviderGet
 * @return void
 */
	public function testGetSiblings($rruleId, $needLatest, $languageId, $expect) {
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
				'id' => 1,
				),
			'Permission' => array(
				),
			);
		Current::$current = Hash::merge(Current::$current, $testCurrentData);

		//テスト実施
		$eventSiblings = $this->$model->$methodName($rruleId, $needLatest, $languageId);

		//チェック
		if ($eventSiblings == array()) {
			$this->assertEqual($eventSiblings, $expect);
		} else {
			$expect['is_origin'] = true;
			$expect['is_translation'] = false;
			$expect['is_original_copy'] = false;
			$this->assertEqual($eventSiblings[0]['ReservationEvent'], $expect);
		}
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
		$expectNotExist = array();
		$expectExist = (new ReservationEventFixture())->records[0];

		return array(
			//array(1, true, 22, $expectNotExist), //存在しない
			array(99, true, null, $expectNotExist), //存在しない
			array(1, 0, 2, $expectExist), //存在する

		);
	}

}
