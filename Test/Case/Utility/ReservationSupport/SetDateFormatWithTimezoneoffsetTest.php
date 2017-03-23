<?php
/**
 * ReservationSupport::setDateFormatWithTimezoneoffset()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsCakeTestCase', 'NetCommons.TestSuite');
App::uses('ReservationSupport', 'Reservations.Utility');

/**
 * ReservationSupport::setDateFormatWithTimezoneoffset()のテスト
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Utility\ReservationSupport
 */
class ReservationsUtilityReservationSupportSetDateFormatWithTimezoneoffsetTest extends NetCommonsCakeTestCase {

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
 * setDateFormatWithTimezoneoffset()のテスト
 *
 * @param string $timezoneOffset timezoneOffset
 * @param string $insertFlag ユーザー系/サーバー系
 * @param string $time time
 * @param string $expect 期待値
 * @dataProvider dataProviderSetDateFormatWithTimezoneoffset
 *
 * @return void
 */
	public function testSetDateFormatWithTimezoneoffset($timezoneOffset, $insertFlag, $time, $expect) {
		//テスト実施
		$result = ReservationSupport::setDateFormatWithTimezoneoffset($timezoneOffset, $insertFlag, $time);
		//チェック
		$this->assertEquals($result, $expect);
	}

/**
 * SetDateFormatWithTimezoneoffsetのDataProvider
 *
 * #### 戻り値
 *  - string 年
 *  - string 月
 *  - string 期待値
 *
 * @return array
 */
	public function dataProviderSetDateFormatWithTimezoneoffset() {
		return array(
			array('12', 1, 20, '20191129123000'),
			//array('2011', '1', array('2010', '12')),
		);
	}

}
