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
		$ReservationLocation = ClassRegistry::init('Reservations.ReservationLocation');
		return $ReservationLocation->openText($reservationLocation);

	}
}