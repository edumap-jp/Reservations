<?php
/**
 * Reservation GetCategoryName Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Reservation GetCategoryName Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\View\Helper
 */
class ReservationCategoryHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'Reservations.ReservationCommon',
	);

/**
 * getCategory
 *
 * 公開対象取得
 *
 * @param array $vars カレンンダー情報
 * @param array $event 予約
 * @return string 公開対象ＨＴＭＬ
 */
	public function getCategoryName($vars, $event) {
		$pseudoPlan = $this->ReservationCommon->makePseudoPlanFromEvent($vars, $event);
		$planMarkClassName = $this->ReservationCommon->getPlanMarkClassName($vars, $pseudoPlan);

		if ($event['ReservationEvent']['room_id'] == Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID)) {
			$roomName = __d('reservations', 'All the members');
		} else {
			$roomLangs = Hash::extract(
				$vars['roomsLanguages'],
				'{n}.RoomsLanguages[room_id=' . $event['ReservationEvent']['room_id'] . ']');
			$roomLang = Hash::extract($roomLangs, '{n}[language_id=' . Current::read('Language.id') . ']');
			$roomName = $this->ReservationCommon->decideRoomName(
				Hash::get($roomLang, '0.name'), $planMarkClassName);
		}
		$html = '';
		$html .= '<span class="reservation-plan-mark ' . $planMarkClassName . '"></span>';
		$html .= '<span>' . h($roomName) . '</span>';
		return $html;
	}

}