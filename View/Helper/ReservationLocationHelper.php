<?php
/**
 * ReservationLocationHelper.php
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Reservation location Helper
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @package NetCommons\Reservations\View\Helper
 * @SuppressWarnings(PHPMD)
 */
class ReservationLocationHelper extends AppHelper {

/**
 * openTextを表示する
 *
 * @param array $reservationLocation 施設データ
 * @return string
 */
	public function openText($reservationLocation) {
		$ReservationLocation = ClassRegistry::init('Reservations.ReservationLocation');
		return $ReservationLocation->openText($reservationLocation);
	}
}
