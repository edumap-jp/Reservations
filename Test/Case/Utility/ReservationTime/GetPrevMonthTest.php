<?php
/**
 * ReservationTime::getPrevMonth()のテスト
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
 * ReservationTime::getPrevMonth()のテスト
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Utility\ReservationTime
 */
class ReservationsUtilityReservationTimeGetPrevMonthTest extends NetCommonsCakeTestCase {

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
 * getPrevMonth()のテスト
 *
 * @param string $year 年
 * @param string $month 月
 * @param string $expect 期待値
 * @dataProvider dataProviderGetPrevMonth
 *
 * @return void
 */
	public function testGetPrevMonth($year, $month, $expect) {
		//テスト実施
		$result = ReservationTime::getPrevMonth($year, $month);
		//チェック
		$this->assertEquals($result, $expect);
	}

/**
 * GetPrevMonthのDataProvider
 *
 * #### 戻り値
 *  - string 年
 *  - string 月
 *  - string 期待値
 *
 * @return array
 */
	public function dataProviderGetPrevMonth() {
		return array(
			array('2011', '12', array('2011', '11')),
			array('2011', '1', array('2010', '12')),
		);
	}

}
