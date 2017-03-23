<?php
/**
 * 一日の予定表示 template
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
		echo $this->element('Reservations.Reservations/reservation_tabs', array('active' => 'daily', 'frameId' => $frameId, 'languageId' => $languageId));
	?>
	<div class="row">
		<div class="col-xs-12 col-sm-2 col-sm-push-10">
			<div class="text-right">
				<?php echo $this->ReservationButton->getAddButton($vars); ?>
			</div>
		</div>
		<div class="col-xs-12  col-sm-10 col-sm-pull-2">
			<?php echo $this->ReservationTurnReservation->getTurnReservationOperations('day', 'top', $vars); ?>
		</div>
	</div>
	<?php
	echo $this->element('Reservations.Reservations/daily_tabs', array('active' => $vars['tab'], 'frameId' => $frameId, 'languageId' => $languageId));
	if ($vars['tab'] === 'timeline') {
		echo $this->element('Reservations.Reservations/daily_timeline', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars));
	} else {
		echo $this->element('Reservations.Reservations/daily_list', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars));
	}
	echo $this->ReservationLegend->getReservationLegend($vars);
	?>
	<div class="row text-center reservation-backto-btn">
		<?php echo $this->ReservationUrl->getBackFirstButton($vars); ?>
	</div>
</article>
