<?php
/**
 * Reservation Weekly Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('ReservationsComponent', 'Reservations.Controller/Component');

/**
 * Reservation weekly Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\View\Helper
 */
class ReservationWeeklyHelper extends ReservationMonthlyHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommonsForm',
		'NetCommonsHtml',
		'Form',
		'Reservations.ReservationCommon',
		'Reservations.ReservationButton',
		'Reservations.ReservationUrl',
		'Reservations.ReservationDaily',
		'Reservations.ReservationMonthly',
		'Reservations.ReservationPlan',
		'NetCommons.TitleIcon',
	);

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

		if ($this->_lineProcess == true) {

			$html .= $this->getPlanSummariesLineHtml(
				$vars, $year, $month, $day, $fromTime, $toTime, $plans, $vars['currentLocationKey']
			);
			return $html;
		}

		$id = 'divline' . (string)$this->_week . '_' . (string)$this->_celCnt;
		$html .= "<div style='z-index:1;' id='" . $id . "'></div>"; //縦位置調整用

		foreach ($plans as $plan) {
			//仕様
			//予定が１件以上あるとき）
			//※roomIdが一致するデータ

//			if ($vars['currentRoomId'] != $plan['ReservationEvent']['room_id']) {
//				if ($vars['currentRoomId'] == ReservationsComponent::FRIEND_PLAN_VIRTUAL_ROOM_ID
//					&& !empty($plan['ReservationEvent']['pseudo_friend_share_plan'])) {
//					//このルームは「仲間の予定」仮想ルームで、かつ、
//					//予定($plan['ReservationEvent])の擬似項目pseudo_friend_share_planに値(1)がセットされている「仲間の予定」
//					//データである。よって、room_idが一致しなくても、表示する例外ケース。
//				} else {
//					//ルームIDが予定のルームIDと一致もしないし、
//					//「仲間の予定」仮想ルーム時の「仲間の予定」データでもないので、
//					//次の予定に進む。
//					continue;
//				}
//			}

			$isLine = $this->ReservationPlan->isLinePlan($plan);
			if ($isLine == true) {
				continue;
			}

			$html .= $this->getPlanTitleHtml($vars, $year, $month, $day, $fromTime, $toTime, $plan);
		}
		return $html;
	}

/**
 * getPlanTitleHtml
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
	public function getPlanTitleHtml(&$vars, $year, $month, $day, $fromTime, $toTime, $plan) {
		$html = '';
		// 大枠
		//$html .= '<div class="row">';
		//$html .= '<div class="col-xs-12">';
		// スペースごとの枠
		$html .= $this->getPlanTitle($vars, $year, $month, $day, $fromTime, $toTime, $plan);
		//$html .= '</div></div>';

		return $html;
	}

/**
 * makeWeeklyHeaderHtml
 *
 * (週表示)ヘッダ部分html生成
 *
 * @param array &$vars コントローラーからの情報
 * @return string HTML
 */
	public function makeWeeklyHeaderHtml(&$vars) {
		if ($vars['week'] == 0) {
			//日付から第n週を求めて設定
			$nWeek = ceil(($vars['mInfo']['wdayOf1stDay'] + $vars['day']) / 7);
			//第n週の日曜日の日付に更新
		} else {
			$nWeek = $vars['week'];
		}

		//n週の日曜日の日付をセットする(n日前にする)
		$firstSunDay = (1 - $vars['mInfo']['wdayOf1stDay']) + (7 * ($nWeek - 1));
		$firsttimestamp = mktime(0, 0, 0, $vars['month'], $firstSunDay, $vars['year']);
		$firstYear = date('Y', $firsttimestamp);
		$firstMonth = date('m', $firsttimestamp);
		$firstDay = date('d', $firsttimestamp);

		$vars['weekFirst']['firstYear'] = $firstYear;
		$vars['weekFirst']['firstMonth'] = $firstMonth;
		$vars['weekFirst']['firstDay'] = $firstDay;

		$this->weekFirst = $vars['weekFirst'];

		/* 日（曜日）(指定日を開始日) */
		$days = array();
		$wDay = array();

		/* 曜日 */
		$html = '<tr><td rowspan="2" class="reservation-col-head"></td>';
		for ($i = 0; $i < 7; $i++) {
			$timestamp = mktime(0, 0, 0, $firstMonth, ($firstDay + $i ), $firstYear);
			$years[$i] = date('Y', $timestamp);
			$months[$i] = date('m', $timestamp);
			$days[$i] = (int)date('d', $timestamp);
			$wDay[$i] = date('w', $timestamp);
			$url = $this->ReservationUrl->getReservationDailyUrl(
				$years[$i], $months[$i], $days[$i], $vars
			);
			$tdColor[$i] = '';
			if ($this->ReservationCommon->isToday($vars, $years[$i], $months[$i], $days[$i])) {
				$tdColor[$i] = 'reservation-today';
			}
			$textColor = $this->ReservationCommon->makeTextColor(
				$years[$i], $months[$i], $days[$i], $vars['holidays'], $wDay[$i]);
			$holidayTitle = $this->ReservationCommon->getHolidayTitle(
				$years[$i], $months[$i], $days[$i], $vars['holidays'], $i);

			$html .= '<td class="reservation-monthly-line-' . ($i + 1) .
				' reservation-weekly-col-day-head ' . $tdColor[$i] . '">';
			$html .= '<span class=';
			$html .= '"reservation-day reservation-daily-disp ';
			$html .= $textColor . '" data-url="' . $url . '">';
			$html .= $days[$i] . '<small>(' . $this->ReservationCommon->getWeekName($i) . ')</small>';
			$html .= '</span>';
			$html .= '<small class="reservation-weekly-holiday ' . $textColor . '">';
			$html .= $holidayTitle . '</small>';
			$html .= '</td>';
		}
		$html .= '</tr>';
		$html .= '<tr>';
		for ($i = 0; $i < 7; $i++) {
			$html .= '<td class="reservation-weekly-col-day-head-bottom ' . $tdColor[$i] . '">';
			$html .= $this->ReservationButton->makeGlyphiconPlusWithUrl(
				$years[$i], $months[$i], $days[$i], $vars);
			$html .= '</td>';
		}
		$html .= '</tr>';
		return $html;
	}

/**
 * makeWeeklyBodyHtml
 *
 * (週表示)本体html生成
 *
 * @param array $vars コントローラーからの情報
 * @return string HTML
 */
	public function makeWeeklyBodyHtml($vars) {
		$html = '';
		$locations = $this->_View->viewVars['locations'];

		//ルーム数分繰り返し
		$cnt = 0;
		$year = $vars['year'];
		$month = $vars['month'];
		$day = $vars['day'];
		$nctm = new NetCommonsTime();
		$roomMaxNum = count($locations);

		foreach ($locations as $location) {
			$cnt++;
			$locationKye = $location['ReservationLocation']['key'];
			$vars['currentLocationKey'] = $locationKye;//$cnt;

			$html .= '<tr>'; //1行の開始

			/**Line**/
			$this->_week = $cnt - 1;
			$this->_lineData[$this->_week] = array();
			$this->_celCnt = 0; //左から何セル目か
			$this->_linePlanCnt = 0; // この週の連続する予定数
			/**Line**/

			//施設名
			$html .= '<td class="reservation-col-head">' .
						'<div>' .
							$location['ReservationLocation']['location_name'] .
						'</div>' .
						//$this->ReservationButton->getAddButton($vars) .
					'</td>';

			//予定（7日分繰り返し）
			for ($nDay = 0; $nDay < 7; $nDay++) {
				$tdColor = '';
				if ($nDay === 0) { //前日+1日
					$year = $vars['weekFirst']['firstYear'];
					$month = $vars['weekFirst']['firstMonth'];
					$day = $vars['weekFirst']['firstDay'];
				} else {
					list($year, $month, $day) = ReservationTime::getNextDay($year, $month, $day);
				}
				if ($tdColor = $this->ReservationCommon->isToday($vars, $year, $month, $day) == true) {
					$tdClass = ' class="reservation-today"';
				} else {
					$tdClass = '';
				}

				$html .= '<td' . $tdClass . '><div>';
				//施設ID($cnt)が一致するの当日の予定を取得 pending
				//line----start
				$html .= "<div class=
					'reservation-col-day-line reservation-period_" . $this->_week . $this->_celCnt . "'>";
				$this->_lineProcess = true; //line予定の追加
				$html .= $this->_makePlanSummariesHtml($vars, $nctm, $year, $month, $day);
				$html .= "</div>";

				$this->_lineProcess = false; //line予定の追加
				$html .= $this->_makePlanSummariesHtml($vars, $nctm, $year, $month, $day);

				$this->_celCnt++;
				//line test------end
				$html .= "</div></td>";
			}

			$html .= '</tr>'; // 1行の終了
		}
		return $html;
	}

}
