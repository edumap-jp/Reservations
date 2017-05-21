<?php
/**
 * ReservationRepeatService.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Class ReservationRepeatService
 */
class ReservationRepeatService {

/**
 * @var array rruleでつかってる曜日文字
 */
	protected $_weekday = [
		0 => 'SU',
		1 => 'MO',
		2 => 'TU',
		3 => 'WE',
		4 => 'TH',
		5 => 'FR',
		6 => 'SA'
	];

/**
 * rruleと開始日から繰り返しの日付リストを返す
 *
 * @param array $rrule rrule
 * @param string $startDate Y-m-d日付
 * @return array Y-m-d日付のリスト
 */
	public function getRepeatDateSet($rrule, $startDate) {
		switch ($rrule['FREQ']) {
			case 'DAILY':
				$dateSet = $this->_getRepeatDateSetByDaily($rrule, $startDate);
				break;
			case 'WEEKLY':
				$dateSet = $this->_getRepeatDateSetByWeekly($rrule, $startDate);
				break;
			case 'MONTHLY':
				$dateSet = $this->_getRepeatDateSetByMonthly($rrule, $startDate);
				break;
			case 'YEARLY':
				$dateSet = $this->_getRepeatDateSetByYearly($rrule, $startDate);
				break;
			default:
				// $rruleに該当する繰り返しがなければ初日だけ
				$dateSet = [
					$startDate
				];
				break;
		}
		//debug($dateSet);
		return $dateSet;
	}

/**
 * 日繰り返しの日付を返す
 *
 * @param array $rrule rrule
 * @param string $startDate Y-m-d日付
 * @return array Y-m-d日付のリスト
 */
	protected function _getRepeatDateSetByDaily($rrule, $startDate) {
		// 間隔
		$interval = $rrule['INTERVAL'];
		// 回数 COUNT か期日 UNTILまでか
		$currentDate = $startDate;
		$repeatDateSet = [];

		// 回数指定 最初を1回目として数える
		$count = 1;
		$continue = true;

		while ($continue) {
			$repeatDateSet[] = $currentDate;
			$currentDate = date(
				'Y-m-d',
				strtotime('+' . $interval . ' day', strtotime($currentDate))
			);

			$count++;
			$continue = $this->_isContinue($rrule, $count, $currentDate);
		}

		return $repeatDateSet;
	}

/**
 * 週繰り返しの日付を返す
 *
 * @param array $rrule rrule
 * @param string $startDate Y-m-d日付
 * @return array Y-m-d日付のリスト
 */
	protected function _getRepeatDateSetByWeekly($rrule, $startDate) {
		// 間隔
		$interval = $rrule['INTERVAL'];
		// 回数 COUNT か期日 UNTILまでか
		$currentDate = $startDate;
		$repeatDateSet = [];
		$bydayNumbers = [];
		foreach ($rrule['BYDAY'] as $weekday) {
			$bydayNumbers[] = array_search($weekday, $this->_weekday);
		}
		// 回数指定 最初を1回目として数える

		$currentWeekEndDate = date('Y-m-d', strtotime('Saturday', strtotime($currentDate)));//
		// 土曜日の日付取得
		$continue = true;
		$count = 1;
		while ($continue) {
			$repeatDateSet[] = $currentDate;
			$next = false;

			while ($next == false) {
				$currentDate = date(
					'Y-m-d',
					strtotime('+1 day', strtotime($currentDate))
				);
				$currentWeekDayNumber = date('w', strtotime($currentDate));
				//$next = true;
				if (in_array($currentWeekDayNumber, $bydayNumbers)) {
					// 繰り返し曜日に該当したら繰り返し日に保存
					$next = true;
				}
				if ($currentDate >= $currentWeekEndDate) {
					// 土曜になったら翌週へ。interval 1なら1週
					$currentWeekEndDate = date(
						'Y-m-d',
						strtotime(
							'+' . $interval . ' week 
					Saturday',
							strtotime($currentWeekEndDate)
						)
					);
					$currentDate = date(
						'Y-m-d',
						strtotime(
							'+' . $interval - 1 . ' week',
							strtotime($currentDate)
						)
					);
				}
			}
			$count++;
			$continue = $this->_isContinue($rrule, $count, $currentDate);

		}
		return $repeatDateSet;
	}

/**
 * 月繰り返しの日付を返す
 *
 * @param array $rrule rrule
 * @param string $startDate Y-m-d日付
 * @return array Y-m-d日付のリスト
 */
	protected function _getRepeatDateSetByMonthly($rrule, $startDate) {
		// 間隔
		$interval = $rrule['INTERVAL'];
		// 回数 COUNT か期日 UNTILまでか
		$currentDate = $startDate;
		$repeatDateSet = [];
		//$bydayNumbers = [];
		//foreach($rrule['BYDAY'] as $weekday){
		//	$bydayNumbers[] = array_search($weekday, $this->_weekday);
		//}
		// 回数指定 最初を1回目として数える

		//$currentWeekEndDate = date('Y-m-d', strtotime('Saturday', strtotime($currentDate)));//
		// 土曜日の日付取得

		$currentMonth = date('Y-m', strtotime($currentDate));
		$continue = true;
		$count = 1;

		while ($continue) {
			$repeatDateSet[] = $currentDate;

			$nthString = [
				'-1' => 'last',
				'1' => 'First',
				'2' => 'Second',
				'3' => 'Third',
				'4' => 'Fourth',
			];
			$weekDayString = [
				'SU' => 'Sunday',
				'MO' => 'Monday',
				'TU' => 'Tuesday',
				'WE' => 'Wednesday',
				'TH' => 'Thursday',
				'FR' => 'Friday',
				'SA' => 'Saturday',
			];

			$continue2 = true;
			while ($continue2) {
				if (isset($rrule['BYDAY'])) {
					// 第n曜日繰り返し
					$weekDay = substr($rrule['BYDAY'], -2);// SU, MO, ...SA
					$nth = str_replace($weekDay, '', $rrule['BYDAY']); // -1 〜4

					$nextString = sprintf(
						'%s %s of %s',
						$nthString[$nth],
						$weekDayString[$weekDay],
						$currentMonth
					);
					$nextDate = date('Y-m-d', strtotime($nextString));
				} else {
					//指定日繰り返し
					$nextDate = $currentMonth . '-' . $rrule['BYMONTHDAY'];
				}

				if ($nextDate > $currentDate) {
					// 1回目は第n $weekDay曜日が$cuurentDateより小さくなる可能性あるのでチェックする。
					$currentDate = $nextDate;
					//$repeatDateSet[] = $currentDate;
					$continue2 = false;
				}

				// 次の繰り返しの年月
				$currentMonth = date(
					'Y-m',
					strtotime('+' . $interval . ' month', strtotime($currentMonth))
				);
			}

			$count++;
			$continue = $this->_isContinue($rrule, $count, $currentDate);

		}
		return $repeatDateSet;
	}

/**
 * 年繰り返しの日付を返す
 *
 * @param array $rrule rrule
 * @param string $startDate Y-m-d日付
 * @return array Y-m-d日付のリスト
 */
	protected function _getRepeatDateSetByYearly($rrule, $startDate) {
		// 間隔
		$interval = $rrule['INTERVAL'];
		// 回数 COUNT か期日 UNTILまでか
		$currentDate = $startDate;
		$repeatDateSet = [];

		// 回数指定 最初を1回目として数える
		$count = 1; //次のカウント
		// スタート年の繰り返し月日を得る
		// 日付指定くりかえし
		$currentYear = date('Y', strtotime($startDate));
		$repeatDay = date('d', strtotime($startDate));

		$continue = true;
		$byMonth = $rrule['BYMONTH'];
		$byMonthIndex = 0;
		$byMonthLength = count($byMonth);
		//$monthCycle = new ReservationCycle($rrule['BYMONTH']);
		while ($continue) {
			$repeatDateSet[] = $currentDate;

			$date = '1900-01-01';
			while ($date < $currentDate) {
				$month = $byMonth[$byMonthIndex];

				if (isset($rrule['BYDAY'])) {
					//第nX曜日指定
					$nthString = [
						'-1' => 'last',
						'1' => 'First',
						'2' => 'Second',
						'3' => 'Third',
						'4' => 'Fourth',
					];
					$weekDayString = [
						'SU' => 'Sunday',
						'MO' => 'Monday',
						'TU' => 'Tuesday',
						'WE' => 'Wednesday',
						'TH' => 'Thursday',
						'FR' => 'Friday',
						'SA' => 'Saturday',
					];

					$weekDay = substr($rrule['BYDAY'], -2);// SU, MO, ...SA
					$nth = str_replace($weekDay, '', $rrule['BYDAY']); // -1 〜4

					$nextString = sprintf(
						'%s %s of %04d-%02d',
						$nthString[$nth],
						$weekDayString[$weekDay],
						$currentYear,
						$month
					);
					$date = date('Y-m-d', strtotime($nextString));

				} else {
					// 開始日で繰り返し
					$date = sprintf('%04d-%02d-%02d', $currentYear, $month, $repeatDay);
				}

				if (date('m', strtotime($date)) != $month) {
					// 2017-02-30とかって日時をstrtotimeしてdate('m')すると3月あつかいになる
					// 翌月になってしまうなら月の最終日とする
					$date('Y-m-d', strtotime('Last day of ' . $currentYear . ' ' . $month));
				}
				$byMonthIndex++;
				if ($byMonthIndex >= $byMonthLength) {
					$byMonthIndex = 0;
					$currentYear = $currentYear + $interval;
				}
			}
			$currentDate = $date;

			$count++;
			$continue = $this->_isContinue($rrule, $count, $currentDate);
		}
		return $repeatDateSet;
	}

/**
 * 繰り返し終了判定
 *
 * @param array $rrule rrule
 * @param int $count 現在の繰返し回数
 * @param string $currentDate Y-m-d 繰り返しで生成中の日付
 * @return bool false:繰り返し終了 true : 繰り返し
 */
	protected function _isContinue($rrule, $count, $currentDate) {
		$continue = true;
		if (isset($rrule['COUNT'])) {
			if ($count > $rrule['COUNT']) {
				$continue = false;
			}
		} else {
			if ($currentDate > $rrule['UNTIL']) {
				$continue = false;
			}
		}
		return $continue;
	}

}

