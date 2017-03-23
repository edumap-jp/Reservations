<?php
/**
 * ReservationTime::addDashColonAndSp()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsCakeTestCase', 'NetCommons.TestSuite');
App::uses('ReservationTime', 'Reservations.Utility');

/**
 * ReservationTime::addDashColonAndSp()のテスト
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Utility\ReservationTime\
 */
class ReservationsUtilityReservationTimeAddDashColonAndSpTest extends NetCommonsCakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'reservations';

/**
 * addDashColonAndSp()のテスト
 *
 * @param string $data データ
 * @param string $expect 期待値
 * @dataProvider dataProviderAddDashColonAndSp
 *
 * @return void
 */
	public function testAddDashColonAndSp($data, $expect) {
		//YYYYMMDD
		//$data = '20161109';

		//テスト実施
		$result = ReservationTime::addDashColonAndSp($data);
		//チェック
		//$this->assertTrue(true);
		$this->assertEquals($result, $expect);
	}

/**
 * AddDashColonAndSpのDataProvider
 *
 * #### 戻り値
 *  - string データ値
 *  - string 期待値
 *
 * @return array
 */
	public function dataProviderAddDashColonAndSp() {
		return array(
			array('20161109', '2016-11-09'),
			array('121200', '12:12:00'),
			array('data', 'data'),

		);
	}

}
