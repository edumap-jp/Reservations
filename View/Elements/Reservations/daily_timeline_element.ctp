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
	<?php for ($hour = 0; $hour < 24; $hour++) : ?>
		<?php
			$timeIndex = sprintf('%02d00', $hour);
			$timeString = sprintf('%02d:00', $hour);
			$vars['currentLocationKey'] = $location['ReservationLocation']['key'];
		?>

		<td>
			<?php if ($hour === 0) : ?>
				<?php /*-- 位置調整用 --*/ ?>
				<div class="reservation-timeline-data-area" data-location-key="<?php echo h($vars['currentLocationKey']); ?>">
					<?php
						echo $this->ReservationDailyTimeline->makeDailyBodyHtml($vars);
						$reservationPlans = $this->ReservationDailyTimeline->getTimelineData();
					?>
					<div ng-controller="ReservationsHorizonTimelinePlan"
						ng-init="initialize(<?php echo h(json_encode(array('reservationPlans' => $reservationPlans))) ?>)">
					</div>
				</div>

			<?php endif; ?>
		</td>
	<?php endfor; ?>
</tr>
