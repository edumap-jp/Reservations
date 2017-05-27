<?php
/**
 * Reservation DailyTimeline Helper
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
class ReservationWeeklyTimelineHelper extends ReservationMonthlyHelper {

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
		'Reservations.ReservationUrl',
		'Reservations.ReservationWorkflow',
		'Users.DisplayUser',
	);

/**
 * Timeline plan data
 *
 * @var array
 */
	protected $_timelineData = array();

/**
 * TimelineDataの取得
 *
 * @return array
 */
	public function getTimelineData() {
		return $this->_timelineData;
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
		$cnt = 0;
		foreach ($plans as $plan) {
			//仕様
			//予定が１件以上あるとき）
			//$html .= "<div class='row'><div class='col-xs-12'>"; //１プランの開始

			$html .= $this->getPlanTitleHtml($vars, $year, $month, $day, $fromTime, $toTime, $plan, $cnt);

			// 1プランの終了
			//$html .= "</div></div>";
			//$cnt++;
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
 * @param int &$cnt タイムライン表示対象数
 * @return string HTML
 */
	public function getPlanTitleHtml(&$vars, $year, $month, $day, $fromTime, $toTime, $plan, &$cnt) {
		$url = '';
		$html = '';
		//$vars['reservationTimelinePlan'] = array();
		$id = 'plan' . (string)$plan['ReservationEvent']['id'] . '_' . $day;
		//print_r($id);
		//if ($fromTime !== $plan['ReservationEvent']['fromTime'] || $toTime !==
		//	$plan['ReservationEvent']['toTime']) {
			$reservationPlanMark = $this->ReservationCommon->getPlanMarkClassName($vars, $plan);
			$url = $this->ReservationUrl->makePlanShowUrl($year, $month, $day, $plan);

			$html .=
				"<div class='reservation-daily-timeline-slit-deco {$reservationPlanMark}' id='" . $id . "'>";

			$html .= "<div class='reservation-common-margin-padding'>";

			if ($this->ReservationWorkflow->canRead($plan)) {
				$html .= "<div><p class='reservation-plan-clickable text-left reservation-plan-show' ";
				$html .= "data-url='" . $url . "'>";
			} else {
				$html .= "<div><p class='text-left reservation-plan-show' ";
				$html .= ">";
			}

			//$html .= "<small style='float:left'>";
			//$html .= h($plan['ReservationEvent']['fromTime']) . '-' . h($plan['ReservationEvent']['toTime']);
			//$html .= '</small>';

			// ワークフロー（一時保存/承認待ち、など）のマーク
			$html .= $this->ReservationCommon->makeWorkFlowLabel($plan['ReservationEvent']['status']);

			if ($this->ReservationWorkflow->canRead($plan)) {
				$html .= '<small>' . h($plan['ReservationEvent']['title']) . '</small>';
			} else {
				$html .= $this->_getNotRedablePlanHtml($plan);
			}

			$html .= '</div></div>';
			$html .= '</p></div>';

			$this->_timelineData[$cnt]['element_id'] = $id;
			$this->_timelineData[$cnt]['fromTime'] = $plan['ReservationEvent']['fromTime'];
			$this->_timelineData[$cnt]['toTime'] = $plan['ReservationEvent']['toTime'];
			$cnt++;
		//}

		return $html;
	}

/**
 * makeDailyBodyHtml
 *
 * (日表示)本体html生成
 *
 * @param array $vars コントローラーからの情報
 * @return string HTML
 */
	public function makeDailyBodyHtml($vars) {
		$html = '';
		$nctm = new NetCommonsTime();

		$html .= $this->_makePlanSummariesHtml($vars, $nctm, $vars['year'], $vars['month'], $vars['day']);
		return $html;
	}

/**
 * getTimelineTdsHtml
 *
 * タイムラインテーブルhtml取得
 *
 * @param array $vars 施設予約情報
 * @return string HTML
 */
	/* 座標がずれるので未使用
	public function getTimelineTdsHtml($vars) {

		$hour = "";
		$html = '';
		for ($i=2; $i < 22 ; $i++) { //2時から22時まで
			$html .= '<tr>';
			$html .= '<td class="reservation-vertical-timeline-periodtime reservation-tbl-td-pos">';
			$html .= '<div class="row">';
			$html .= '<div class="col-xs-12">';

			$hour = str_pad($i, 2, 0, STR_PAD_LEFT);

			$html .= "<p class='text-right'><span>" . $hour . ":00</span></p>";

			$html .= "</div>";
			$html .= "<div class='clearfix'></div>";
			$html .= "<div class='col-xs-12'>";
			$html .= "<p class='reservation-plan-clickable text-right'><small>";
			$html .= "<span class='glyphicon glyphicon-plus'></span></small></p>";
			$html .= "</div>";
			$html .= "<div class='clearfix'></div>";
			$html .= "</div>";
			$html .= "</td>";
			$html .= "<td class='reservation-daily-timeline-col-slit reservation-tbl-td-pos'>";
			$html .= "</td>";
			$html .= "</tr>";

		}
		return $html;
	}
 */

}
