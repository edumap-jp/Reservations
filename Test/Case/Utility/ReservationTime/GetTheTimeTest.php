<?php
/**
 * ReservationTime::getTheTime()のテスト
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
 * ReservationTime::getTheTime()のテスト
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Utility\ReservationTime
 */
class ReservationsUtilityReservationTimeGetTheTimeTest extends NetCommonsCakeTestCase {

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
 * getTheTime()のテスト
 *
 * @param string $ymdHis  "Y-m-d H:i:s"形式の指定日付時刻
 * @param string $expect 期待値
 * @dataProvider dataProviderGetTheTime
 *
 * @return void
 */
	public function testGetTheTime($ymdHis, $expect) {
		//テスト実施
		$result = ReservationTime::getTheTime($ymdHis);
		//チェック
		$this->assertEquals($result, $expect);
	}

/**
 * getTheTimeのDataProvider
 *
 * #### 戻り値
 *  - string 指定日付時刻
 *  - string 期待値
 *
 * @return array
 */
	public function dataProviderGetTheTime() {
		$expect1 = array(
			'0' => '2011-12-14',
			'1' => '2011-12-14 21:00',
			'2' => '2011-12-14 22:00',
		);

		$expect2 = array(
			'0' => '2011-12-14',
			'1' => '2011-12-14 23:00',
			'2' => '2011-12-15 00:00',
		);

		return array(
			array('2011-12-14 21:13:20', $expect1),
			array('2011-12-14 23:13:20', $expect2),
		);
	}

}
