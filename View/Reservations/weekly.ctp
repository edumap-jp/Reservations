<?php
/**
 * 週の予定表示 template
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
		echo $this->element('Reservations.Reservations/reservation_tabs', array('active' => 'weekly', 'frameId' => $frameId, 'languageId' => $languageId));
	?>

	<?php echo $this->ReservationTurnReservation->getTurnReservationOperationsWrap('week', 'top', $vars); ?>

	<div class="row"><!--全体枠-->
		<div class="col-xs-12 col-sm-12 text-center table-responsive">
			<table class='reservation-weekly-table'>
				<tbody>
					<?php /* -- 日付（見出し） -- */ ?>
						<?php
							echo $this->ReservationWeekly->makeWeeklyHeaderHtml($vars);
						?>
						<?php /*-- 予定の内容 --*/ ?>
						<?php echo $this->ReservationWeekly->makeWeeklyBodyHtml($vars); ?>
						<?php $reservationLinePlans = $this->ReservationWeekly->getLineData() ?>
				</tbody>
			</table>
			<div ng-controller="ReservationsMonthlyLinePlan" ng-style="initialize(<?php echo h(json_encode(array('reservationLinePlans' => $reservationLinePlans))) ?>)" resize>
			</div>
		</div>
	</div><!--全体枠END-->

	<?php /*-- 予定の内容 --*/ ?>
	<?php
		echo $this->ReservationLegend->getReservationLegend($vars);
	?>
	<div class="row text-center reservation-backto-btn">
		<?php
			echo $this->ReservationUrl->getBackFirstButton($vars);
		?>
	</div>
</article>
