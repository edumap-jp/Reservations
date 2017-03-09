<?php
/**
 * ReservationLocationHelper.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

class ReservationLocationHelper extends AppHelper {

	public function openText($reservationLocation){
		$ret = '';
		$weekDaysOptions = [
			'Sun' => __d('holidays', 'Sunday'),
			'Mon' => __d('holidays', 'Monday'),
			'Tue' => __d('holidays', 'Tuesday'),
			'Wed' => __d('holidays', 'Wednesday'),
			'Thu' => __d('holidays', 'Thursday'),
			'Fri' => __d('holidays', 'Friday'),
			'Sat' => __d('holidays', 'Saturday'),
		];
		$timeTable = $reservationLocation['ReservationLocation']['time_table'];
		if($timeTable === 'Sun|Mon|Tue|Wed|Thu|Fri|Sat'){
			//毎日
			$ret = __d('reservations', '毎日');
		}elseif ($timeTable === 'Mon|Tue|Wed|Thu|Fri') {
			// 平日
			$ret = __d('reservations', '平日');
		}else {
			$timeTable = explode('|', $timeTable);
			$weekList = [];
			foreach($timeTable as $weekday){
				$weekList[] = $weekDaysOptions[$weekday];
			}
			$ret = implode(', ', $weekList);
		}

		//時間
		$ret = sprintf('%s %s-%s',
			$ret,
			$reservationLocation['ReservationLocation']['start_time'],
			$reservationLocation['ReservationLocation']['end_time']
			);
		return $ret;
	}
}