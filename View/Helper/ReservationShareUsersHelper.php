<?php
/**
 * Reservation ShareUser Reservation Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Reservation ShareUser Reservation Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\View\Helper
 */
class ReservationShareUsersHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'Reservations.ReservationCommon',
		'Users.DisplayUser'
	);

/**
 * getShareUserTitle
 *
 * 共有ユーザーHTML取得
 * この関数が呼ばれるときは共有予定であることが前提とします
 * プライベート、共有でないときには使ってはいけません
 *
 * @param array $vars カレンンダー情報
 * @param array $event 予約
 * @param array $shareUsers 共有者
 * @return string 共有ユーザーHTML
 */
	public function getReservationShareUserTitle($vars, $event, $shareUsers) {
		// 自分が差し込んだものか、もしくは共有された予定か
		if ($this->isMyShareEvent($event)) {
			$html = __d('reservations', 'People to share the schedule');
		} else {
			$html = __d('reservations', 'Person who made the schedule');
		}
		return $html;
	}
/**
 * getShareUser
 *
 * 共有ユーザーHTML取得
 * この関数が呼ばれるときは共有予定であることが前提とします
 * プライベート、共有でないときには使ってはいけません
 *
 * @param array $vars カレンンダー情報
 * @param array $event 予約
 * @param array $shareUsers 共有者
 * @return string 共有ユーザーHTML
 */
	public function getReservationShareUser($vars, $event, $shareUsers) {
		// 自分が差し込んだものか、もしくは共有された予定か
		// 自分の場合、共有者の名まえの羅列
		// 差し込まれた場合、XXさんによって共有された予定です
		if ($this->isMyShareEvent($event)) {
			// 自分が差し込んだ場合は共有者全員
			$html = '';
			foreach ($shareUsers as $shareUser) {
				$html .= $this->DisplayUser->handleLink($shareUser,
					array('avatar' => true), [], 'User');
				$html .= ',&nbsp;&nbsp;';
			}
			$html = trim($html, ',&nbsp;&nbsp;');
		} else {
			// ひとさまから差し込まれた場合は
			$html = $this->DisplayUser->handleLink($event, array('avatar' => true));
		}
		return $html;
	}

/**
 * isShareEvent
 * 共有予定か
 *
 * @param arary $event 予約
 * @return bool
 */
	public function isShareEvent($event) {
		$shareUser = Hash::get($event, 'ReservationEventShareUser');
		if (empty($shareUser)) {
			return false;
		}
		return true;
	}
/**
 * isMyShareEvent
 * 自分が差し込んだ共有予定か
 *
 * @param arary $event 予約
 * @return bool
 */
	public function isMyShareEvent($event) {
		if ($this->isShareEvent($event) &&
			$event['ReservationEvent']['created_user'] == Current::read('User.id')) {
			return true;
		} else {
			return false;
		}
	}
}
