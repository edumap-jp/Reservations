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
	<td class="reservation-daily-timeline-col-slit reservation-tbl-td-pos">
		<?php if ($needTimeSlit): ?>
		<div class="reservation-timeline-data-area"><?php /*-- 位置調整用 --*/ ?>
			<?php
				echo $this->ReservationDailyTimeline->makeDailyBodyHtml($vars); // ここでもIDセット
				$reservationPlans = $this->ReservationDailyTimeline->getTimelineData(); //
            // ここでplanのIDいれたらいいんでは？
			?>
			<div ng-controller="ReservationsTimelinePlan" ng-init="initialize(<?php echo h(json_encode(array('reservationPlans' => $reservationPlans))) ?>)"></div>
		</div>
		<?php endif; ?>
	</td>
</tr>
