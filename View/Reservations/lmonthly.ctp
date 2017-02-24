<?php
/**
 * 月（大）の予定表示 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
echo $this->element('Reservations.scripts');
?>
<article ng-controller="ReservationsDetailEdit" class="block-setting-body">
	<?php
		echo $this->element('Reservations.Reservations/reservation_tabs', array('active' => 'lmonthly', 'frameId' => $frameId, 'languageId' => $languageId));
	?>
	<?php echo $this->ReservationTurnReservation->getTurnReservationOperationsWrap('month', 'top', $vars);	?>
	<div class="row"><!--全体枠-->
		<div class="col-xs-12 col-sm-12">
			<table class='reservation-monthly-table'>
				<tbody>
					<tr class="hidden-xs">
						<td class='reservation-col-week-head reservation-monthly-line-0'>&nbsp;</td>
						<td class='reservation-col-day-head reservation-monthly-line-1'><span class='reservation-sunday h4'><?php echo __d('reservations', 'Sun'); ?></span></td>
						<td class='reservation-col-day-head reservation-monthly-line-2'><span class='h4'><?php echo __d('reservations', 'Mon'); ?></span></td>
						<td class='reservation-col-day-head reservation-monthly-line-3'><span class='h4'><?php echo __d('reservations', 'Tue'); ?></span></td>
						<td class='reservation-col-day-head reservation-monthly-line-4'><span class='h4'><?php echo __d('reservations', 'Wed'); ?></span></td>
						<td class='reservation-col-day-head reservation-monthly-line-5'><span class='h4'><?php echo __d('reservations', 'Thu'); ?></span></td>
						<td class='reservation-col-day-head reservation-monthly-line-6'><span class='h4'><?php echo __d('reservations', 'Fri'); ?></span></td>
						<td class='reservation-col-day-head reservation-monthly-line-7'><span class='reservation-saturday h4'><?php echo __d('reservations', 'Sat'); ?></span></td>
					</tr>
					<?php echo $this->ReservationMonthly->makeLargeMonthyBodyHtml($vars); ?>
					<?php $reservationLinePlans = $this->ReservationMonthly->getLineData() ?>
				</tbody>
			</table>
			<div
					ng-controller="ReservationsMonthlyLinePlan"
					ng-style="initialize(<?php echo h(json_encode(array('reservationLinePlans' => $reservationLinePlans))) ?>)"
					resize>
			</div>
		</div>
	</div><!--全体枠END-->
	<?php echo $this->ReservationTurnReservation->getTurnReservationOperationsWrap('month', 'bottom', $vars);	?>
	<!-- 予定の内容 -->
	<?php
		echo $this->ReservationLegend->getReservationLegend($vars);
	?>
	<div class="row text-center reservation-backto-btn">
		<?php echo $this->ReservationUrl->getBackFirstButton($vars); ?>
	</div>
</article>
