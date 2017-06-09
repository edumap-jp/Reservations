<?php
/**
 * Reservation::prepareBlockSave()のテスト
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
 * Reservation::prepareBlock()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Model\Reservation
 */
class ReservationPrepareBlockTest extends NetCommonsModelTestCase {

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
		'plugin.blocks.block',
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
	protected $_methodName = 'prepareBlock';

/**
 * prepareBlock()のテスト
 *
 * @param int $roomId ルームID
 * @param int $langId languageID
 * @param string $pluginKey プラグインキー
 * @param mix $expect 期待値
 * @param string $exception 例外
 * @dataProvider dataProviderPrepareBlock
 * @return void
 */
	public function testPrepareBlock($roomId, $langId, $pluginKey, $expect, $exception = null) {
		//　ε(　　　　 v ﾟωﾟ)　＜ブロック存在しちゃってる状態でテストしているのでこのテストは通らない
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		if ($exception != null) {
			$this->setExpectedException($exception);
		}

		if ($expect == 'blockSaveErr') {
			$this->_mockForReturnFalse($model, 'Blocks.Block', 'save', 1);
			$expect = null;
			//テスト実施
			$return = $this->$model->$methodName($roomId, $langId, $pluginKey);
		}
		if ($expect == 'saveErr') {
				//$this->_mockForReturnFalse($model, 'Blocks.Block', 'save', 1);
				$mock = $this->getMockForModel('Reservations.Reservation', array('_saveReservation'));
				$this->$model = $mock;
				$mock->expects($this->once())
				->method('_saveReservation')
				->will($this->returnValue(array()));
				$expect = array();
			//テスト実施
			$return = $this->$model->$methodName($roomId, $langId, $pluginKey);
			//チェック
			$this->assertEquals($return, $expect);
		}
	}

/**
 * prepareBlockのDataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return array
 */
	public function dataProviderPrepareBlock() {
		//
		$roomId = 16;
		$languageId = 2;
		$pluginKey = 'reservations';

		$expect1 = 'blockSaveErr';
		$expect2 = 'saveErr';

		return array(
			array($roomId, 0, $pluginKey, $expect1, 'InternalErrorException'), //Blockがない
			array(1, $languageId, $pluginKey, $expect2, 'InternalErrorException'),
		);
	}
}
