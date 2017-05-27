<?php
/**
 * 施設別 - 月表示 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
echo $this->element('Reservations.scripts');
?>

<article ng-controller="ReservationsDetailEdit" class="block-setting-body">
	<?php
		//共通タブ(カテゴリー別、施設別)
		echo $this->element('Reservations.Reservations/common_tabs');
	?>
	<?php
		//施設別の表示方法タブ(月、週)
		echo $this->element('Reservations.Reservations/tabs_by_location');
	?>

	<div class="clearfix">
		<?php echo $this->ReservationTurnReservation->getTurnReservationOperations('month', 'top', $vars); ?>

		<div class="reservation-category-locaction-operations reservation-category-locaction-operations-top pull-left">
			<?php echo $this->element('Reservations.Reservations/dropdown_category'); ?>
			<?php echo $this->element('Reservations.Reservations/dropdown_location'); ?>
		</div>
	</div>

	<div class="reservation-monthly-table">
		<table class="reservation-monthly-table">
			<thead>
				<tr class="hidden-xs">
					<td class='reservation-col-week-head reservation-monthly-line-0'>&nbsp;</td>
					<td class='reservation-col-day-head reservation-monthly-line-1'>
						<span class='reservation-sunday h4'><?php echo __d('reservations', 'Sun'); ?></span>
					</td>
					<td class='reservation-col-day-head reservation-monthly-line-2'>
						<span class='h4'><?php echo __d('reservations', 'Mon'); ?></span>
					</td>
					<td class='reservation-col-day-head reservation-monthly-line-3'>
						<span class='h4'><?php echo __d('reservations', 'Tue'); ?></span>
					</td>
					<td class='reservation-col-day-head reservation-monthly-line-4'>
						<span class='h4'><?php echo __d('reservations', 'Wed'); ?></span>
					</td>
					<td class='reservation-col-day-head reservation-monthly-line-5'>
						<span class='h4'><?php echo __d('reservations', 'Thu'); ?></span>
					</td>
					<td class='reservation-col-day-head reservation-monthly-line-6'>
						<span class='h4'><?php echo __d('reservations', 'Fri'); ?></span>
					</td>
					<td class='reservation-col-day-head reservation-monthly-line-7'>
						<span class='reservation-saturday h4'><?php echo __d('reservations', 'Sat'); ?></span>
					</td>
				</tr>
			</thead>
			<tbody>
				<?php $vars['currentLocationKey'] = $vars['location_key']; ?>

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
</article>
