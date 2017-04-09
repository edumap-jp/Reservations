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
	echo $this->element('Reservations.Reservations/reservation_tabs', array('active' =>
		'all_weekly', 'frameId' => $frameId, 'languageId' => $languageId));
	?>

	<?php echo $this->ReservationTurnReservation->getTurnReservationOperationsWrap('week',
		'top', $vars); ?>

    <div class="row" ng-controller="ReservationsTimeline"><!--全体枠-->
        <div class="col-xs-12 col-sm-12 text-center table-responsive">

            全施設週表示



			<?php /*-- 予定の内容 --*/ ?>
			<?php
			//echo $this->ReservationLegend->getReservationLegend($vars);
			?>
            <div class="row text-center reservation-backto-btn">
				<?php
				echo $this->ReservationUrl->getBackFirstButton($vars);
				?>
            </div>
        </div>
    </div>
</article>
