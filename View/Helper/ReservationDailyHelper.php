<?php
/**
 * Reservation Daily Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Reservation daily Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\View\Helper
 */
class ReservationDailyHelper extends ReservationMonthlyHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
		'NetCommons.TitleIcon',
		'Form',
		'Reservations.ReservationCommon',
		'Reservations.ReservationUrl',
	);

/**
 * getSpaceName
 *
 * スペース名取得
 *
 * @param array &$vars 施設予約情報
 * @param int $roomId ルームID
 * @param int $languageId language_id
 * @return string
 */
	public function getSpaceName(&$vars, $roomId, $languageId) {
		if ($roomId == Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID)) {
			return __d('reservations', 'All the members');
		}

		$roomsLanguages = $vars['roomsLanguages'];
		$roomName = '';
		foreach ($roomsLanguages as $room) {
			//print_r($room);
			if ($room['RoomsLanguages']['room_id'] == $roomId &&
				$room['RoomsLanguages']['language_id'] == $languageId) {
				$roomName = $room['RoomsLanguages']['name'];
			}
		}
		return $roomName;
	}

/**
 * getPlanSummariesHtml
 *
 * 予定概要群html取得
 *
 * @param array &$vars 施設予約情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param string $fromTime この日の１日のスタート時刻
 * @param string $toTime この日の１日のエンド時刻
 * @param array $plans この日の予定群
 * @return string HTML
 */
	public function getPlanSummariesHtml(&$vars, $year, $month, $day, $fromTime, $toTime, $plans) {
		$html = '';
		foreach ($plans as $plan) {
			//仕様
			//予定が１件以上あるとき）
			$html .= "<tr><td><div class='row'><div class='col-xs-12'>"; //１プランの開始
			//$html .= "<p class='reservation-plan-clickable text-left reservation-daily-nontimeline-plan'>";

			$html .= $this->getPlanTitleDailyListHtml($vars, $year, $month, $day, $fromTime, $toTime, $plan);

			// 1プランの終了
			$html .= "</p>";
			$html .= "</div></div></div></td></tr>";
		}
		return $html;
	}

/**
 * getPlanTitleDailyListHtml
 *
 * 予定（タイトル）html取得
 *
 * @param array &$vars 施設予約情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param string $fromTime この日の１日のスタート時刻
 * @param string $toTime この日の１日のエンド時刻
 * @param array $plan 予定
 * @return string HTML
 */
	public function getPlanTitleDailyListHtml(&$vars, $year, $month, $day, $fromTime, $toTime, $plan) {
		$reservationPlanMark = $this->ReservationCommon->getPlanMarkClassName($vars, $plan);

		$html = "<div class='reservation-plan-mark {$reservationPlanMark}'>";

		// ワークフロー（一時保存/承認待ち、など）のマーク
		$html .= '<div>';
		$html .= $this->ReservationCommon->makeWorkFlowLabel($plan['ReservationEvent']['status']);
		$html .= '</div>';
		$url = $this->ReservationUrl->makePlanShowUrl($year, $month, $day, $plan, true);
		if ($fromTime !== $plan['ReservationEvent']['fromTime'] || $toTime !==
			$plan['ReservationEvent']['toTime']) {
			$html .= '<p class="reservation-daily-nontimeline-plan reservation-plan-time small">';
			$html .= h($plan['ReservationEvent']['fromTime']) . ' - ' .
				h($plan['ReservationEvent']['toTime']) . '</p>';
		}
		$spaceName = $this->getSpaceName(
			$vars, $plan['ReservationEvent']['room_id'], $plan['ReservationEvent']['language_id']);
		$spaceName = $this->ReservationCommon->decideRoomName($spaceName, $reservationPlanMark);
		$html .= '<p class="reservation-plan-spacename small">' . h($spaceName) . '</p>';

		$html .= '<h3 class="reservation-plan-tittle">';
		$html .= $this->NetCommonsHtml->link(
			$this->TitleIcon->titleIcon($plan['ReservationEvent']['title_icon']) .
			h($plan['ReservationEvent']['title']),
			$url,
			array('escape' => false)
		);
		$html .= '</h3>';

		$html .= '</p>';
		if ($plan['ReservationEvent']['location'] != '') {
			$html .= '<p class="reservation-plan-place small">' . __d('reservations', 'Location details:');
			$html .= h($plan['ReservationEvent']['location']) . '</p>';
		}
		if ($plan['ReservationEvent']['contact']) {
			$html .= '<p class="reservation-plan-address small">' . __d('reservations', 'Contact');
			$html .= h($plan['ReservationEvent']['contact']) . '</p>';
		}
		return $html;
	}

/**
 * makeDailyListBodyHtml
 *
 * (日表示)本体html生成
 *
 * @param array $vars コントローラーからの情報
 * @return string HTML
 */
	public function makeDailyListBodyHtml($vars) {
		$html = '';
		$nctm = new NetCommonsTime();

		$html .= $this->_makePlanSummariesHtml($vars, $nctm, $vars['year'], $vars['month'], $vars['day']);

		return $html;
	}

}
