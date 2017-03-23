<?php
/**
 * ReservationTime::dateFormat()のテスト
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
 * ReservationTime::dateFormat()のテスト
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Utility\ReservationTime
 */
class ReservationsUtilityReservationTimeDateFormatTest extends NetCommonsCakeTestCase {

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
 * dateFormat()のテスト
 *
 * @param string $time time YmdHis形式の文字列. insertFlag=1の時ユーザー系.insertFlag=0の時サーバー系であることを想定している。
 * @param mixed $timezoneOffset 値(-12.0 - 12.0)が入っていればその時差を使う。nullならtimezoneOffsetはつかわず、insertFlagだけで処理
 * @param bool $insertFlag １はユーザー系、0はサーバー系
 * @param string $expect 期待値
 * @dataProvider dataProviderDateFormat
 *
 * @return void
 */
	public function testDateFormat($time, $timezoneOffset, $insertFlag, $expect) {
		//テスト実施
		$reservationTime = new ReservationTime();
		$result = $reservationTime->dateFormat($time, $timezoneOffset, $insertFlag);
		//チェック
		$this->assertEquals($result, $expect);
	}

/**
 * dateFormatのDataProvider
 *
 * #### 戻り値
 *  - string 年
 *  - string 月
 *  - string 期待値
 *
 * @return array
 */
	public function dataProviderDateFormat() {
		return array(
			array('20111212', '-12.5', 1, '20111212113000'),
			array('20111212', '0', 1, '20111212000000'),
			array('20111212122200', null, 1, '20111212032200'),
			//array('2011', '1',1, array('2010', '12')),
		);
	}

}
