<?php
/**
 * 施設予約タイムライン要素 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>

<tr>
	<?php if (Current::read('ReservationFrameSetting.display_timeframe')): ?>
		<td style="width:50px;" >
			<?php if ($needTimeSlit): ?>

				<?php
				// 時間枠を出力
				$displayTimeframes = [];
				foreach ($timeframes as $timeframe) {
					// 時間枠情報をUTCからユーザタイムゾーンに変換。
					$ncTime = new NetCommonsTime();
					$start = $ncTime->toUserDatetime($timeframe['ReservationTimeframe']['start_time']);
					$end = $ncTime->toUserDatetime($timeframe['ReservationTimeframe']['end_time']);
					$start = date('H:i', strtotime($start));
					$end = date('H:i', strtotime($end));
					if ($start > $end) {
						// Start > Endなら24時またぎなので2つの時間枠にわける
						$displayTimeframes[] = [
							'start' => $start,
							'end' => '24:00',
							'color' => $timeframe['ReservationTimeframe']['color'],
							'title' => $timeframe['ReservationTimeframe']['title'],
						];
						$displayTimeframes[] = [
							'start' => '00:00',
							'end' => $end,
							'color' => $timeframe['ReservationTimeframe']['color'],
							'title' => $timeframe['ReservationTimeframe']['title'],
						];
					} else {
						$displayTimeframes[] = [
							'start' => $start,
							'end' => $end,
							'color' => $timeframe['ReservationTimeframe']['color'],
							'title' => $timeframe['ReservationTimeframe']['title'],
						];
					}
				}
				?>
				<div ng-controller="ReservationsVerticalTimeframe"
						ng-init="init(<?php echo h(json_encode(['timeframes' => $displayTimeframes]))
						?>)" style="position: relative">
					<div ng-repeat="timeframe in data.timeframes"
							ng-style="timeframe.style"

							class="reservation-vertical-timeframe"
					>
						{{timeframe.title}}
					</div>
				</div>
			<?php endif ?>
		</td>
	<?php endif ?>
	<td class="reservation-vertical-timeline-periodtime reservation-col-head reservation-daily-timeline-<?php echo $timeIndex; ?>">
		<p class="text-right">
			<span><?php echo $timeString; ?></span>
		</p>
		<p class="reservation-plan-clickable text-right">
			<small>
				<?php echo $this->ReservationButton->makeGlyphiconPlusWithTimeUrl($vars['year'], $vars['month'], $vars['day'], $hour, $vars); ?>
			</small>
		</p>
	</td>

    <?php for ($weekday = 0; $weekday <= 6; $weekday++): ?>
		<?php
			$currentDay = strtotime(sprintf('%d-%d-%d +%d day',
				$this->ReservationWeekly->weekFirst['firstYear'],
				$this->ReservationWeekly->weekFirst['firstMonth'],
				$this->ReservationWeekly->weekFirst['firstDay'],
				$weekday
			));
			$currentDayVars = $vars;
			$currentDayVars['year'] = date('Y', $currentDay);
			$currentDayVars['month'] = date('n', $currentDay);
			$currentDayVars['day'] = date('j', $currentDay);
			$day = $this->ReservationWeekly->weekFirst['firstDay'] + $weekday;
			if ($this->ReservationCommon->isToday($vars, $vars['year'], $vars['month'], $day)) {
				$todayClass = ' class="reservation-today"';
			} else {
				$todayClass = '';
			}
		?>
		<td<?php echo $todayClass; ?>>
            <?php if ($needTimeSlit): ?>
				<div class="reservation-timeline-data-area"><?php /*-- 位置調整用 --*/ ?>
					<?php
						// 予約データの出力
						$this->ReservationWeeklyTimeline->clearTimelineData();
						echo $this->ReservationWeeklyTimeline->makeDailyBodyHtml($currentDayVars);
						$reservationPlans = $this->ReservationWeeklyTimeline->getTimelineData();
					?>
					<div ng-controller="ReservationsWeeklyTimelinePlan"
						ng-init="initialize(<?php echo h(json_encode(array('reservationPlans' => $reservationPlans))) ?>)">
					</div>
				</div>
            <?php endif; ?>
        </td>
    <?php endfor; ?>

</tr>
