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
<tr class="reservations-weekly-timeline-row">
	<td class="reservation-daily-timeline-col-periodtime reservation-tbl-td-pos reservation-daily-timeline-<?php echo $timeIndex; ?>">
		<div class="row">
			<div class="col-xs-12">
				<p class="text-right">
					<span><?php echo $timeString; ?></span>
				</p>
			</div>
			<div class="clearfix"></div>
			<div class="col-xs-12">
				<p class="reservation-plan-clickable text-right">
					<small>
						<?php echo $this->ReservationButton->makeGlyphiconPlusWithTimeUrl($vars['year'], $vars['month'], $vars['day'], $hour, $vars); ?>
					</small>
				</p>
			</div>
			<div class="clearfix"></div>
		</div>
	</td>
	<!-- timeline-slit -->

    <?php for ($weekday = 0; $weekday <= 6; $weekday++): ?>
        <td class="reservation-weekly-timeline-col-slit reservation-tbl-td-pos">
            <?php if ($needTimeSlit): ?>
            <div class="reservation-timeline-data-area"><?php /*-- 位置調整用 --*/ ?>
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
