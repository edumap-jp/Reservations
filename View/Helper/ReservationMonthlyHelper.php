<?php
/**
 * Reservation Monthly Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');

/**
 * Reservation monthy Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\View\Helper
 * @SuppressWarnings(PHPMD)
 */
class ReservationMonthlyHelper extends AppHelper {

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
		'Reservations.ReservationPlan',
		'Reservations.ReservationWorkflow',
		'NetCommons.TitleIcon',
		'Users.DisplayUser',
	);

/**
 * line plan data
 *
 * @var array
 */
	protected $_lineData = array();

/**
 * line week data
 * 処理中の週数
 * @var array
 */
	protected $_week = 0;

/**
 * line plan week data
 * 処理中のセル数（左から何セル目か）
 * @var array
 */
	protected $_celCnt = 0; //処理中セル（左から何セル目か）

/**
 * line plan count data
 * 処理中の週の日跨ぎプラン数
 * @var array
 */
	protected $_linePlanCnt = 0; //この週の連続プランの数

/**
 * line plan proceess data
 * 処理中の予定
 * (true:連続プランである/false連続プランではない）
 *
 * @var array
 */
	protected $_lineProcess = false;

/**
 * TimelineDataの取得
 *
 * @return array
 */
	public function getLineData() {
		return $this->_lineData;
	}

/**
 * _makePlanSummariesHtml
 *
 * 予定概要群html生成
 *
 * @param array &$vars 施設予約情報
 * @param object &$nctm NetCommonsTimeオブジェクトへの参照
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return string HTML
 */
	protected function _makePlanSummariesHtml(&$vars, &$nctm, $year, $month, $day) {
		//指定日の開始時間、終了時間および指定日で表示すべき予定群の配列を取得
		list ($fromTimeOfDay, $toTimeOfDay, $plansOfDay) =
			$this->ReservationCommon->preparePlanSummaries($vars, $nctm, $year, $month, $day);
		return $this->getPlanSummariesHtml($vars, $year, $month, $day, $fromTimeOfDay, $toTimeOfDay,
			$plansOfDay);
	}

/**
 * isExistLinePlan
 *
 * 日跨ぎ(日跨ぎLine)存在判定
 *
 * @param array $plan 予定
 * @return bool
 */
	public function isExistLinePlan($plan) {
		$idx = 0;

		foreach ($this->_lineData[$this->_week] as $linePlan) {
			if ($linePlan['id'] == $plan['ReservationEvent']['id']) {
				$this->_lineData[$this->_week][$idx]['toCell'] = $this->_celCnt;
				return true;
			}
			$idx++;
		}
		return false;
	}

/**
 * addLinePlanHtml
 *
 * 日跨ぎ(日跨ぎLine)HTML取得
 *
 * @param array $plan 予定
 * @param array $reservationLinePlanMark cssクラス名
 * @param array $url リンクURL
 * @return string HTML
 */
	public function addLinePlanHtml($plan, $reservationLinePlanMark, $url) {
		$html = '';
		$id = 'planline' . (string)$plan['ReservationEvent']['id']; //位置制御用id

		$html .= "<div class='hidden-xs reservation-plan-line " . $reservationLinePlanMark .
						"'  id='" . $id . '_' . $this->_week . "'>";
		$label = $this->ReservationCommon->makeWorkFlowLabel($plan['ReservationEvent']['status']);
		if (! empty($label)) {
			$html .= '<small>' . $label . '</small>&nbsp;';
		}

		if ($this->ReservationWorkflow->canRead($plan)) {
			$title = $this->TitleIcon->titleIcon($plan['ReservationEvent']['title_icon']) .
				h(mb_strimwidth($plan['ReservationEvent']['title'], 0, 20, '...'));
			$html .= $this->NetCommonsHtml->link($title, $url, array(
				'class' => 'reservation-line-link',
				'escape' => false
			));
		} else {
			$html .= $this->_getNotRedablePlanHtml($plan);
		}

		$html .= '</div>';
		$this->_lineData[$this->_week][$this->_linePlanCnt]['id'] = $plan['ReservationEvent']['id'];
		$this->_lineData[$this->_week][$this->_linePlanCnt]['fromCell'] = $this->_celCnt;
		$this->_lineData[$this->_week][$this->_linePlanCnt]['toCell'] = $this->_celCnt;
		$this->_linePlanCnt++;//連続するplanの数（この週内）

		return $html;
	}

/**
 * getPlanSummariesLineHtml
 *
 * 予定概要群(日跨ぎLine)html取得
 *
 * @param array &$vars 施設予約情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param string $fromTime この日の１日のスタート時刻
 * @param string $toTime この日の１日のエンド時刻
 * @param array $plans この日の予定群
 * @param int $roomId ルームIDによる絞り込み（週表示用）
 * @return string HTML
 */
	public function getPlanSummariesLineHtml(&$vars, $year, $month, $day, $fromTime, $toTime,
		$plans, $roomId = -1) {
		$html = '';
		$nctm = new NetCommonsTime();
		//$id = '';

		foreach ($plans as $plan) {
			if ($vars['currentLocationKey'] !== $plan['ReservationEvent']['location_key']) {
				continue;
			}
			////※roomIdが一致するデータ
			//if (!$this->_isTargetPlan($roomId, $vars, $plan)) {
			//	continue;
			//}

			$url = $this->ReservationUrl->makePlanShowUrl($year, $month, $day, $plan, true);
			$checkStartDate = $nctm->toUserDatetime($plan['ReservationEvent']['dtstart']);

			//予定クラス名取得関数に予定(日跨ぎライン)マーククラス名取得を統合した
			$roomId = null;
			$reservationLinePlanMark = $this->ReservationCommon->getPlanMarkClassName(
				$vars, $plan, $roomId, 'reservation-lineplan-');

			$tmaStart = ReservationTime::transFromYmdHisToArray($checkStartDate);
			//期間（日跨ぎの場合）
			$isLine = $this->ReservationPlan->isLinePlan($plan);
			if ($isLine == true) {
				if ($year == $tmaStart['year'] && $month == $tmaStart['month'] &&
					$day == $tmaStart['day']) { // 日跨ぎの初日である
					/* HTML追加 */
					$html .= $this->addLinePlanHtml($plan, $reservationLinePlanMark, $url);

				} else { // 日跨ぎの初日ではない
					$find = false;
					$find = $this->isExistLinePlan($plan);
					if ($find == false) { //この週では最初
						/* HTML追加 */
						$html .= $this->addLinePlanHtml($plan, $reservationLinePlanMark, $url);

					}
				}
				continue;
			}
		}
		return $html;
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
		//$nctm = new NetCommonsTime();

		if ($this->_lineProcess == true) {

			$html = $this->getPlanSummariesLineHtml($vars, $year, $month, $day, $fromTime, $toTime,
			$plans);
			return $html;
		}

		$id = 'divline' . (string)$this->_week . '_' . (string)$this->_celCnt;
		$html .= "<div class='hidden-xs' style='z-index:1;' id='" . $id . "'></div>"; //縦位置調整用
		//$linePlanCnt = 0;
		foreach ($plans as $plan) {
			//期間（日跨ぎの場合）
			$isLine = $this->ReservationPlan->isLinePlan($plan);
			if ($isLine === true) {
				//continue;
				// 大枠
				$html .= '<div class="row reservation-plan-noline visible-xs"><div class="col-xs-12">';
				//$html .= '<div class="row"><div class="col-xs-12">';
			} else {
				$html .= '<div class="row reservation-plan-noline"><div class="col-xs-12">';
				//$html .= '<div class="row"><div class="col-xs-12">';
				//print_r($plan['ReservationEvent']['title']);
			}

			// スペースごとの枠
			$html .= $this->getPlanTitle(
				$vars, $year, $month, $day, $fromTime, $toTime, $plan, array('short_title' => true));
			$html .= '</div></div>';
		}
		return $html;
	}

/**
 * getPlanTitle
 *
 * 予定タイトルhtml取得
 *
 * @param array $vars 施設予約情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param string $fromTime この日の１日のスタート時刻
 * @param string $toTime この日の１日のエンド時刻
 * @param array $plan この日の予定
 * @param array $options オプション指定
 * @return string
 */
	public function getPlanTitle(
		$vars, $year, $month, $day, $fromTime, $toTime, $plan, $options = array()) {
		$reservationPlanMark = $this->ReservationCommon->getPlanMarkClassName($vars, $plan);
		$url = $this->ReservationUrl->makePlanShowUrl($year, $month, $day, $plan, true);
		$html = '<div class="reservation-plan-mark ' . $reservationPlanMark . '">';
		$html .= '<div>';
		$html .= $this->ReservationCommon->makeWorkFlowLabel($plan['ReservationEvent']['status']);
		$html .= '</div>';
		// 時間
		if ($fromTime !== $plan['ReservationEvent']['fromTime'] ||
			$toTime !== $plan['ReservationEvent']['toTime']) {
			$html .= '<p class="reservation-plan-time small">';
			$html .= h($plan['ReservationEvent']['fromTime']) .
				' - ' .
				// 上の両側のブランクに「折り返しはここで」の
				//意味があるから削除しちゃダメよ
				h($plan['ReservationEvent']['toTime']);
			$html .= '</p>';
		}
		$html .= '<h3 class="reservation-plan-title">';

		if ($this->ReservationWorkflow->canRead($plan)) {
			if (isset($options['short_title'])) {
				$title = h(mb_strimwidth($plan['ReservationEvent']['title'], 0, 20, '...'));
			} else {
				$title = h($plan['ReservationEvent']['title']);
			}
			$html .= $this->NetCommonsHtml->link(
				$this->TitleIcon->titleIcon($plan['ReservationEvent']['title_icon']) . $title,
				$url,
				array('escape' => false)
			);

		} else {
			$html .= $this->_getNotRedablePlanHtml($plan);
		}

		$html .= '</h3></div>';
		return $html;
	}

/**
 * makePlansHtml
 *
 * 予定群html生成
 *
 * @param array &$vars 施設予約情報
 * @param object &$nctm NetCommonsTimeオブジェクトへの参照
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return string HTML
 */
	public function makePlansHtml(&$vars, &$nctm, $year, $month, $day) {
		//list ($fromTimeOfDay, $toTimeOfDay, $plansOfDay) = $this->ReservationCommon->preparePlanSummaries($vars, $nctm, $year, $month, $day);
		$plansOfDay = $this->ReservationCommon->preparePlanSummaries($vars, $nctm, $year, $month, $day);
		$planNum = count($plansOfDay[2]);
		if ($planNum === 0) {
			$html = '<div>&nbsp</div>'; //0件
		} else {
			$html = '<div><span class="badge">' . $planNum . '</span></div>'; //1件以上
		}
		return $html;
	}

/**
 * _makeStartTr
 *
 * 条件付TR開始タグ挿入
 *
 * @param int $cnt 月施設予約開始日からの累積日数(0オリジン)
 * @param array $vars 施設予約情報
 * @param int &$week 週数 (0オリジン)
 * @return string HTML
 */
	protected function _makeStartTr($cnt, $vars, &$week) {
		$html = '';
		if ($cnt % 7 === 0) {
			//週の先頭
			++$week;	//週数をカウントupして、現在の週数にする。

			if ($vars['style'] === 'smallmonthly') {
				$html .= '<tr>';
			} else {	//largemonthly
				$url = $this->ReservationUrl->getReservationUrl(array(
					'plugin' => 'reservations',
					'controller' => 'reservations',
					'action' => 'index',
					'block_id' => '',
					'frame_id' => Current::read('Frame.id'),
					'?' => array(
						'style' => 'weekly',
						'year' => sprintf("%04d", $vars['mInfo']['year']),
						'month' => sprintf("%02d", $vars['mInfo']['month']),
						'week' => $week,
					)
				));
				//$html .= "<tr><th rowspan='2' class='reservation-col-week hidden-xs' data-url='" . $url . "'>";
				$html .= "<tr><th class='reservation-col-week hidden-xs' data-url='" . $url . "'>";
				$html .= $week . __d('reservations', 'week') . '</th>';

				/**Line**/
				$this->_week = $week - 1;
				$this->_lineData[$this->_week] = array();
				$this->_celCnt = 0; //左から何セル目か
				$this->_linePlanCnt = 0; // この週の連続する予定数
				/**Line**/
			}
		}
		return $html;
	}

/**
 * _makeEndTr
 *
 * 条件付TR終了タグ挿入
 *
 * @param int $cnt 月施設予約開始日からの累積日数(0オリジン)
 * @return string HTML
 */
	protected function _makeEndTr($cnt) {
		return ($cnt % 7 === 6) ? '</tr>' : '';
	}

/**
 * makeSmallMonthyBodyHtml
 *
 * 月(縮小)本体html生成
 *
 * @param array $vars コントローラーからの情報
 * @return string HTML
 */
	public function makeSmallMonthyBodyHtml($vars) {
		$html = '';
		$cnt = 0;
		$week = 0;
		$tdColor = '';
		$nctm = new NetCommonsTime();
		//初週の前月部 処理
		for ($idx = 0; $idx < $vars['mInfo']['wdayOf1stDay']; ++$idx) {

			$html .= $this->_makeStartTr($cnt, $vars, $week);

			$day = $vars['mInfo']['daysInPrevMonth'] - $vars['mInfo']['wdayOf1stDay'] + ($idx + 1);
			//$url = $this->ReservationUrl->getPlanListUrl('prevMonth', $vars['mInfo']['yearOfPrevMonth'], $vars['mInfo']['prevMonth'], $day, $vars);
			$url = $this->ReservationUrl->getReservationDailyUrl(
				$vars['mInfo']['yearOfPrevMonth'], $vars['mInfo']['prevMonth'], $day, $vars
			);
			$html .= '<td class=';
			$html .= "'reservation-col-small-day reservation-out-of-range reservation-plan-list' ";
			$html .= "data-url='" . $url . "'>";
			$html .= "<div><span class='text-center text-muted'>";
			$html .= $day;
			$html .= '</span></div>';
			$html .= $this->makePlansHtml($vars, $nctm, $vars['mInfo']['yearOfPrevMonth'],
				$vars['mInfo']['prevMonth'], $day);
			$html .= '</td>';

			$html .= $this->_makeEndTr($cnt);

			++$cnt;
		}

		for ($day = 1; $day <= $vars['mInfo']['daysInMonth']; ++$day) {
			$tdColor = '';
			$html .= $this->_makeStartTr($cnt, $vars, $week);
			if ($this->ReservationCommon->isToday(
				$vars, $vars['mInfo']['year'], $vars['mInfo']['month'], $day) == true) {
				$tdColor = 'reservation-tbl-td-today'; //本日のセル色
			}
			$textColor = $this->ReservationCommon->makeTextColor(
				$vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars['holidays'], $cnt);

			//$url = $this->ReservationUrl->getPlanListUrl('thisMonth', $vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars);
			$url = $this->ReservationUrl->getReservationDailyUrl(
				$vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars
			);
			$html .= "<td class='reservation-col-small-day reservation-plan-list {$tdColor}' ";
			$html .= "data-url='" . $url . "'><div><span class='text-center {$textColor}'>";
			$html .= $day;
			$html .= '</span></div>';
			$html .= $this->makePlansHtml($vars, $nctm, $vars['mInfo']['year'],
				$vars['mInfo']['month'], $day);
			$html .= '</td>';

			$html .= $this->_makeEndTr($cnt);

			++$cnt;
		}

		//最終週の次月部 処理
		for ($idx = $vars['mInfo']['wdayOfLastDay'], $day = 1; $idx < 6; ++$idx, ++$day) {

			$html .= $this->_makeStartTr($cnt, $vars, $week);

			//$url = $this->ReservationUrl->getPlanListUrl(
			//'nextMonth', $vars['mInfo']['yearOfNextMonth'], $vars['mInfo']['nextMonth'], $day, $vars);
			$url = $this->ReservationUrl->getReservationDailyUrl(
				$vars['mInfo']['yearOfNextMonth'], $vars['mInfo']['nextMonth'], $day, $vars
			);
			$html .= "<td class='reservation-col-small-day reservation-out-of-range reservation-plan-list' ";
			$html .= "data-url='" . $url . "'><div><span class='text-center text-muted'>";
			$html .= $day;
			$html .= '</span></div>';
			$html .= $this->makePlansHtml(
				$vars, $nctm, $vars['mInfo']['yearOfNextMonth'], $vars['mInfo']['nextMonth'], $day);
			$html .= '</td>';

			$html .= $this->_makeEndTr($cnt);

			++$cnt;
		}

		return $html;
	}

/**
 * _doPrevNextMonthPart
 *
 * 初週前月部または最終週次月部の生成
 *
 * @param object &$nctm  NetCommonsTimeオブジェクトの参照
 * @param array $type  'prev' or 'next'
 * @param array &$vars  施設予約情報
 * @param string &$html  html
 * @param int &$cnt  cnt
 * @param int &$week  week
 * @param int &$idx index
 * @param int &$day day
 * @param string &$holidayTitle  holidayTitle
 * @return void
 */
	protected function _doPrevNextMonthPart(&$nctm, $type, &$vars, &$html, &$cnt, &$week, &$idx,
		&$day, &$holidayTitle) {
		if ($type === 'prev') {
			$year = $vars['mInfo']['yearOfPrevMonth'];
			$month = $vars['mInfo']['prevMonth'];
		} else {
			$year = $vars['mInfo']['yearOfNextMonth'];
			$month = $vars['mInfo']['nextMonth'];
		}
		$url = $this->ReservationUrl->getReservationDailyUrl($year, $month, $day, $vars);
		//<!-- 1row --> 日付と予定追加glyph
		$html .= "<div class='row'>";
		$html .= "<div class='col-xs-3 col-sm-12'>";
		$html .= "<div class='row reservation-day-num'>";
		$html .= "<div class='col-xs-12'>";
		$html .= "<span class='text-muted reservation-day reservation-daily-disp' ";
		$html .= "data-url='" . $url . "'>" . $day . '</span>';
		$html .= "<span class='text-muted visible-xs-inline'><small>(";
		$html .= $this->ReservationCommon->getWeekName($cnt) . ')</small></span>';
		$html .= '</div>';
		//<!-- 2row --> 祝日タイトル
		$html .= "<div class='col-xs-12'>";
		//$html .= "<span class='reservation-sunday'><small>";
		//$html .= (($holidayTitle === '') ? '&nbsp;' : h($holidayTitle)) . '</small></span>';

		$html .= "<small class='reservation-sunday'>";
		$html .= (($holidayTitle === '') ? '&nbsp;' : h($holidayTitle)) . '</small>';

		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		/* forLINE add */
		$html .= "<div class='col-xs-9 col-sm-12'>";

		$htmlClass = 'reservation-col-day-line reservation-period_' . $week . $this->_celCnt;
		$html .= '<div class="' . $htmlClass . '">';

		$this->_lineProcess = true; //line予定の追加
		//予定概要群
		$html .= $this->_makePlanSummariesHtml($vars, $nctm, $year, $month, $day);
		$html .= '</div>';

		$this->_lineProcess = false; //line以外の予定の追加
		//予定概要群
		$html .= $this->_makePlanSummariesHtml($vars, $nctm, $year, $month, $day);
		/* forline add */

		$this->_celCnt++;
		$html .= '</td>';
	}

/**
 * makeLargeMonthyBodyHtml
 *
 * 月(拡大)本体html生成
 *
 * @param array $vars コントローラーからの情報
 * @return string HTML
 */
	public function makeLargeMonthyBodyHtml($vars) {
		$html = '';
		$cnt = 0;
		$week = 0;
		$nctm = new NetCommonsTime();

		//初週の前月部
		for ($idx = 0; $idx < $vars['mInfo']['wdayOf1stDay']; ++$idx) {
			$html .= $this->_makeStartTr($cnt, $vars, $week);
			$html .= "<td class='reservation-col-day reservation-tbl-td-pos reservation-out-of-range'>";
			$day = $vars['mInfo']['daysInPrevMonth'] - $vars['mInfo']['wdayOf1stDay'] + ($idx + 1);
			$holidayTitle = $this->ReservationCommon->getHolidayTitle(
				$vars['mInfo']['yearOfPrevMonth'], $vars['mInfo']['prevMonth'], $day,
				$vars['holidays'], $cnt);
			$this->_doPrevNextMonthPart( //生成結果等は、参照で返す
				$nctm, 'prev', $vars, $html, $cnt, $week, $idx, $day, $holidayTitle);

			$html .= $this->_makeEndTr($cnt);

			++$cnt;
		}

		//当月部
		for ($day = 1; $day <= $vars['mInfo']['daysInMonth']; ++$day) {
			$tdColor = '';
			$url = $this->ReservationUrl->getReservationDailyUrl(
				$vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars
			);
			$isToday = $this->ReservationCommon->isToday(
				$vars, $vars['mInfo']['year'], $vars['mInfo']['month'], $day);
			$holidayTitle = $this->ReservationCommon->getHolidayTitle(
				$vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars['holidays'], $cnt);
			$textColor = $this->ReservationCommon->makeTextColor(
				$vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars['holidays'], $cnt);

			$html .= $this->_makeStartTr($cnt, $vars, $week);
			if ($isToday == true) {
				$tdColor = 'reservation-tbl-td-today'; //本日のセル色
			}
			$html .= '<td class="reservation-col-day reservation-tbl-td-pos ' . $tdColor . '"><div>';
			//<!-- 1row --> 日付と予定追加glyph
			$html .= $this->ReservationButton->makeGlyphiconPlusWithUrl(
				$vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars);
			$html .= '<div class="row">';
			$html .= '<div class="col-xs-3 col-sm-12">';
			$html .= '<div class="row reservation-day-num">';
			$html .= '<div class="col-xs-12">';
			$html .= '<span class="reservation-day reservation-daily-disp ';
			$html .= $textColor . '" data-url="' . $url . '">' . $day . '</span>';
			$html .= '<span class="' . $textColor . ' visible-xs-inline">';
			$html .= '<small>(' . $this->ReservationCommon->getWeekName($cnt) . ')</small>';
			$html .= '</span>';
			$html .= '</div>';
			//<!-- 2row --> 祝日タイトル
			$html .= '<div class="col-xs-12">';
			$html .= '<small class="reservation-sunday">';
			$html .= (($holidayTitle === '') ? '&nbsp;' : h($holidayTitle));
			$html .= '</small>';
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';
			//予定概要群
			$html .= '<div class="col-xs-9 col-sm-12">';
			//line start
			$tdColor = '';
			$htmlClass = 'reservation-col-day-line reservation-period_' . $week . $this->_celCnt;
			$html .= '<div class="' . $htmlClass . '">';
			$this->_lineProcess = true; //line予定の追加
			//予定概要群
			$html .= $this->_makePlanSummariesHtml(
				$vars, $nctm, $vars['mInfo']['year'], $vars['mInfo']['month'], $day
			);
			$html .= '</div>';
			$this->_lineProcess = false; //line以外の予定の追加
			//予定概要群
			$html .= $this->_makePlanSummariesHtml(
				$vars, $nctm, $vars['mInfo']['year'], $vars['mInfo']['month'], $day
			);
			$this->_celCnt++;
			$html .= $this->_makeEndTr($cnt);
			// line end
			++$cnt;
		}

		//最終週の次月部
		for ($idx = $vars['mInfo']['wdayOfLastDay'], $day = 1; $idx < 6; ++$idx, ++$day) {

			$html .= $this->_makeStartTr($cnt, $vars, $week);
			$html .= "<td class='reservation-col-day reservation-tbl-td-pos reservation-out-of-range'>";
			$holidayTitle = $this->ReservationCommon->getHolidayTitle(
				$vars['mInfo']['yearOfNextMonth'], $vars['mInfo']['nextMonth'], $day,
				$vars['holidays'], $cnt);
			$this->_doPrevNextMonthPart( //生成結果等は、参照で返す.
				$nctm, 'next', $vars, $html, $cnt, $week, $idx, $day, $holidayTitle);
			$html .= $this->_makeEndTr($cnt);

			++$cnt;
		}

		return $html;
	}

/**
 * _isTargetPlan
 *
 * 対象となる予定かどうかの判断
 *
 * @param int &$roomId roomId
 * @param array &$vars vars
 * @param array &$plan plan
 * @return bool 対象となる場合true。そうでない場合false。
 */
	protected function _isTargetPlan(&$roomId, &$vars, &$plan) {
		if ($roomId != -1) {	// roomId == -1はMonthly, roomId != -1はWeeklyを想定

			//Monthlyの時は、roomId == currentRoomId == emptyの時のみ
			//
			//Weeklyの時は、roomId==empty && currentRoomId >= 1 (含む high-value)の時と、
			// roomId == currentRoomId >= 1 (含む higt-value)の時の２つある。
			//

			if (!empty($vars['currentRoomId'])) {
				//Weeklyで roomIdが有るとき、ない時それぞれある。

				if (!($vars['currentRoomId'] == $plan['ReservationEvent']['room_id'])) {
					//Weeklyで、currentRoomIdとroom_idが一致した時、続行

					if ($vars['currentRoomId'] == ReservationsComponent::FRIEND_PLAN_VIRTUAL_ROOM_ID
						&& !empty($plan['ReservationEvent']['pseudo_friend_share_plan'])) {
						//このルームは「仲間の予定」仮想ルームで、かつ、
						//予定($plan['ReservationEvent'])の擬似項目pseudo_friend_share_planに値(1)がセットされている「仲間の予定」
						//データである。よって、room_idが一致しなくても、表示する例外ケース。
						//続行
					} else {
						//次の予定へ
						return false; //continue;
					}
				}
			}
		}
		return true;
	}

/**
 * 詳細閲覧不可の予約のHTML
 *
 * @param array $plan ReservationEvent data
 * @return string html
 */
	protected function _getNotRedablePlanHtml($plan) {
		$html = '<span class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span> ';
		$html .= $this->DisplayUser->handleLink($plan);
		return $html;
	}
}
