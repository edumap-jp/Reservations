<?php
/**
 * ReservationRepeatServiceTest.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('ReservationRepeatService', 'Reservations.Service');

/**
 * Class ReservationRepeatServiceTest
 *
 * @property ReservationRepeatService $ReservationRepeatService
 */
class ReservationRepeatServiceTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ReservationRepeatService = new ReservationRepeatService();
	}

/**
 * 日ごとの繰り返し日付の取得　回数指定の場合
 *
 * @return void
 */
	public function testDailyCount() {
		$startDate = '2017-05-01';

		$rrule = [
			'FREQ' => 'DAILY',
			'INTERVAL' => 1,
			'COUNT' => 3
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		$this->assertCount(3, $result);
		$lastDay = array_pop($result);
		$this->assertEquals('2017-05-03', $lastDay);

		$rrule = [
			'FREQ' => 'DAILY',
			'INTERVAL' => 2,
			'COUNT' => 3
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		$this->assertCount(3, $result);
		$lastDay = array_pop($result);
		$this->assertEquals('2017-05-05', $lastDay);
	}

/**
 * 日ごとの繰り返し日付の取得　回数指定の場合
 *
 * @return void
 */
	public function testDailyUntil() {
		$startDate = '2017-05-01';

		$rrule = [
			'FREQ' => 'DAILY',
			'INTERVAL' => 1,
			'UNTIL' => '2017-05-10'
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		$this->assertCount(10, $result);
		$lastDay = array_pop($result);
		$this->assertEquals('2017-05-10', $lastDay);

		$rrule = [
			'FREQ' => 'DAILY',
			'INTERVAL' => 2,
			'UNTIL' => '2017-05-10'
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		$this->assertCount(5, $result);
		$lastDay = array_pop($result);
		$this->assertEquals('2017-05-09', $lastDay);
	}

/**
 * strtotimeのテスト
 *
 * @return void
 */
	public function testStrtotimeWeekday() {
		// strtotimeで曜日を指定すると、現在時刻以降の曜日の日時を返す。
		// よって月曜に月曜を指定すると今日の日時。
		// 火曜に月曜を指定すると次の週の月曜
		// 土曜を求めると今週の土曜。
		debug(date('Y-m-d', strtotime('MON'))); // 次の月曜日
		debug(date('Y-m-d', strtotime('TUE')));
		debug(date('Y-m-d', strtotime('WED')));
		debug(date('Y-m-d', strtotime('+0 week', strtotime('2017-05-20'))));
		debug(date('Y-m-d', strtotime('first WED of 2017-05')));
		debug(date('Y-m-d', strtotime('last WED of 2017-05')));
	}

/**
 * 週繰り返しのテスト かいすうしてい
 *
 * @return void
 */
	public function testWeeklyCount() {
		$startDate = '2017-05-01';

		$rrule = [
			'FREQ' => 'WEEKLY',
			'INTERVAL' => 1,
			'BYDAY' => [
				'MO',
				'WE'
			],
			'COUNT' => 3
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		$this->assertCount(3, $result);
		$lastDay = array_pop($result);
		$this->assertEquals('2017-05-08', $lastDay);

		$rrule = [
			'FREQ' => 'WEEKLY',
			'INTERVAL' => 2,
			'BYDAY' => [
				'MO',
				'WE'
			],
			'COUNT' => 3
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		$this->assertCount(3, $result);
		$lastDay = array_pop($result);
		$this->assertEquals('2017-05-15', $lastDay);
	}

/**
 * 週繰り返しのテスト 日付指定
 *
 * @return void
 */
	public function testWeeklyUnitl() {
		$startDate = '2017-05-01';

		$rrule = [
			'FREQ' => 'WEEKLY',
			'INTERVAL' => 1,
			'BYDAY' => [
				'MO',
				'WE'
			],
			'UNTIL' => '2017-05-31'
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		$this->assertCount(10, $result);
		$lastDay = array_pop($result);
		$this->assertEquals('2017-05-31', $lastDay);

		$rrule = [
			'FREQ' => 'WEEKLY',
			'INTERVAL' => 2,
			'BYDAY' => [
				'MO',
				'WE'
			],
			'UNTIL' => '2017-05-31'
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		$this->assertCount(6, $result);
		$lastDay = array_pop($result);
		$this->assertEquals('2017-05-31', $lastDay);
	}

/**
 * 月繰り返しのテスト（回数指定）
 *
 * @return void
 */
	public function testMonthlyCount() {
		$startDate = '2017-05-01';

		$rrule = [
			'FREQ' => 'MONTHLY',
			'INTERVAL' => 2,
			'BYDAY' => '2SU', // 第2日曜
			'COUNT' => 3
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		debug($result);
		$this->assertCount(3, $result);
		$lastDay = array_pop($result);
		$this->assertEquals('2017-07-09', $lastDay);

		$startDate = '2017-05-30';

		$rrule = [
			'FREQ' => 'MONTHLY',
			'INTERVAL' => 2,
			'BYDAY' => '2SU', // 第2日曜
			'COUNT' => 3
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		debug($result);
		$this->assertCount(3, $result);
		$lastDay = array_pop($result);
		$this->assertEquals('2017-09-10', $lastDay);

		// 9/10

		$rrule = [
			'FREQ' => 'MONTHLY',
			'INTERVAL' => 2,
			'BYMONTHDAY' => 15, //毎月15日
			'COUNT' => 3
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		debug($result);
		$this->assertEquals(
			[
				'2017-05-30',
				'2017-07-15',
				'2017-09-15'
			],
			$result
		);
		$this->assertCount(3, $result);
	}

/**
 * 月繰り返しのテスト
 *
 * @return void
 */
	public function testMonthlyUntil() {
		$startDate = '2017-05-01';

		$rrule = [
			'FREQ' => 'MONTHLY',
			'INTERVAL' => 2,
			'BYDAY' => '2SU', // 第2日曜
			'UNTIL' => '2017-12-31'
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		$this->assertEquals(
			[
				'2017-05-01',
				'2017-05-14',
				'2017-07-09',
				'2017-09-10',
				'2017-11-12',

			],
			$result
		);

		$startDate = '2017-05-30';

		$rrule = [
			'FREQ' => 'MONTHLY',
			'INTERVAL' => 2,
			'BYDAY' => '2SU', // 第2日曜
			'UNTIL' => '2017-12-31'
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		$this->assertEquals(
			[
				'2017-05-30',
				'2017-07-09',
				'2017-09-10',
				'2017-11-12',

			],
			$result
		);

		// 9/10

		$rrule = [
			'FREQ' => 'MONTHLY',
			'INTERVAL' => 2,
			'BYMONTHDAY' => 15, //毎月15日
			'UNTIL' => '2017-12-31'
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		debug($result);
		$this->assertEquals(
			[
				'2017-05-30',
				'2017-07-15',
				'2017-09-15',
				'2017-11-15'
			],
			$result
		);
	}

/**
 * 年繰り返しのテスト
 *
 * @return void
 */
	public function testYearlyByDay() {
		$startDate = '2017-05-01';

		$rrule = [
			'FREQ' => 'YEARLY',
			'INTERVAL' => 2,
			'BYMONTH' => [
				4,
				8
			],
			'BYDAY' => '2SU', // 第2日曜
			'COUNT' => 5
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		debug($result);

		$this->assertEquals(
			[
				'2017-05-01',
				'2017-08-13',
				'2019-04-14',
				'2019-08-11',
				'2021-04-11'
			],
			$result
		);

		//  until
		$rrule = [
			'FREQ' => 'YEARLY',
			'INTERVAL' => 2,
			'BYMONTH' => [
				4,
				8
			],
			'BYDAY' => '2SU', // 第2日曜
			'UNTIL' => '2022-05-31'
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		debug($result);

		$this->assertEquals(
			[
				'2017-05-01',
				'2017-08-13',
				'2019-04-14',
				'2019-08-11',
				'2021-04-11',
				'2021-08-08'
			],
			$result
		);
	}

/**
 * 年繰り返しのテスト 開始日での繰り返し
 *
 * @return void
 */
	public function testYearlyByStartDay() {
		$startDate = '2017-05-01';

		$rrule = [
			'FREQ' => 'YEARLY',
			'INTERVAL' => 2,
			'BYMONTH' => [
				4,
				8
			],
			//'BYDAY' => '2SU', // 第2日曜
			'COUNT' => 5
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		$this->assertEquals(
			[
				'2017-05-01',
				'2017-08-01',
				'2019-04-01',
				'2019-08-01',
				'2021-04-01',
			],
			$result,
			debug($result)
		);

		// until
		$rrule = [
			'FREQ' => 'YEARLY',
			'INTERVAL' => 2,
			'BYMONTH' => [
				4,
				8
			],
			//'BYDAY' => '2SU', // 第2日曜
			'UNTIL' => '2022-05-31'
		];
		$result = $this->ReservationRepeatService->getRepeatDateSet($rrule, $startDate);
		$this->assertEquals(
			[
				'2017-05-01',
				'2017-08-01',
				'2019-04-01',
				'2019-08-01',
				'2021-04-01',
				'2021-08-01',
			],
			$result,
			debug($result)
		);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ReservationTimeframe);

		parent::tearDown();
	}

}