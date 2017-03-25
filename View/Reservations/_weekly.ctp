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

/**
 * @var $this View
 * @var ReservationWeeklyHelper
 */
echo $this->element('Reservations.scripts');

?>
<article ng-controller="ReservationsDetailEdit" class="block-setting-body">
	<?php
		echo $this->element('Reservations.Reservations/reservation_tabs', array('active' => 'weekly', 'frameId' => $frameId, 'languageId' => $languageId));
	?>

	<?php echo $this->ReservationTurnReservation->getTurnReservationOperationsWrap('week', 'top', $vars); ?>

    <div class="row" ng-controller="ReservationsTimeline"><!--全体枠-->
        <div class="col-xs-12 col-sm-12 text-center table-responsive">

            <div class="reservation-vertical-timeline" data-daily-start-time-idx="<?php echo $vars['ReservationFrameSetting']['timeline_base_time']; ?>"><?php /*-- overflow-yのdivの始まり --*/?>

                <table class='reservation-daily-timeline-table'><?php /*-- overflow-yのscroll分5%考慮 --*/ ?>
                    <thead>
					<?php
					echo $this->ReservationWeekly->makeWeeklyHeaderHtml($vars);
					?>
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
					?>
                    <?php for ($hour = 1; $hour < 24; $hour++): ?>
                        <?php
							$timeIndex = sprintf('%02d00', $hour);
							$timeString = sprintf('%02d:00', $hour);
							echo $this->element('Reservations.Reservations/weekly_timeline_element', array(
								'hour' => $hour,
								'timeIndex' => $timeIndex,
								'timeString' => $timeString,
								'needTimeSlit' => false
							));
						?>
                    <?php endfor; ?>
                    </tbody>
                </table>
            </div><?php /*-- overflow-yのdivの終わり --*/ ?>




	<?php /*-- 予定の内容 --*/ ?>
	<?php
		//echo $this->ReservationLegend->getReservationLegend($vars);
	?>
	<div class="row text-center reservation-backto-btn">
		<?php
			echo $this->ReservationUrl->getBackFirstButton($vars);
		?>
	</div>
</article>
