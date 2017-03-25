<?php
/**
 * 施設別 - 週表示 template
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
		<?php echo $this->ReservationTurnReservation->getTurnReservationOperations('week', 'top', $vars); ?>

		<div class="reservation-category-locaction-operations reservation-category-locaction-operations-top pull-left">
			<?php echo $this->element('Reservations.Reservations/dropdown_category'); ?>
			<?php echo $this->element('Reservations.Reservations/dropdown_location'); ?>
		</div>
	</div>

	<div ng-controller="ReservationsTimeline" class="text-center table-responsive">
		<?php /*-- overflow-yのdivの始まり --*/?>
		<div class="reservation-vertical-timeline"
				data-daily-start-time-idx="<?php echo $vars['ReservationFrameSetting']['timeline_base_time']; ?>">

			<?php /*-- overflow-yのscroll分5%考慮 --*/ ?>
			<table>
				<thead>
					<?php echo $this->ReservationWeekly->makeWeeklyHeaderHtml($vars); ?>
				</thead>

				<tbody>
					<?php
						echo $this->element('Reservations.Reservations/weekly_timeline_element',
							array(
								'hour' => 0,
								'timeIndex' => '0000',
								'timeString' => '00:00',
								'needTimeSlit' => true
							)
						);

						for ($hour = 1; $hour < 24; $hour++) {
							$timeIndex = sprintf('%02d00', $hour);
							$timeString = sprintf('%02d:00', $hour);
							echo $this->element('Reservations.Reservations/weekly_timeline_element', array(
								'hour' => $hour,
								'timeIndex' => $timeIndex,
								'timeString' => $timeString,
								'needTimeSlit' => false
							));
						}
					?>
				</tbody>
			</table>
		</div>
		<?php /*-- overflow-yのdivの終わり --*/ ?>
	</div>

	<?php echo $this->ReservationLegend->getReservationLegend($vars); ?>
</article>

