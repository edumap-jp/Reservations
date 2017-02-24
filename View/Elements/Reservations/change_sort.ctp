<?php
/**
 * スケジュール形式施設予約上部の時間順・会員順切替タブ template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php
	$timeLink = $this->ReservationUrl->getReservationUrl(array(
		'controller' => 'reservations',
		'action' => 'index',
		'frame_id' => Current::read('Frame.id'),
		'?' => array(
			'style' => 'schedule',
			'sort' => 'time',
		)
	));

	$memberLink = $this->ReservationUrl->getReservationUrl(array(
		'controller' => 'reservations',
		'action' => 'index',
		'frame_id' => Current::read('Frame.id'),
		'?' => array(
			'style' => 'schedule',
			'sort' => 'member',
		)
	));
?>
<div class="row">
	<div class="col-sm-12 text-center reservation-schedule-sort">
		<ul role='tablist' class='nav nav-tabs reservation-date-move-tablist'>

			<?php if ($currentSort === 'time'): ?>
					<li class='active'>
						<a href='#' onclidk='return false;'>
			<?php else: ?>
					<li>
						<a href="<?php echo $timeLink; ?>">
			<?php endif; ?>

							<?php echo __d('reservations', 'Times'); ?>
						</a>
					</li>

			<?php if ($currentSort === 'member'): ?>
					<li class='active'>
						<a href='#' onclidk='return false;'>
			<?php else: ?>
					<li>
						<a href="<?php echo $memberLink; ?>">
			<?php endif; ?>
							<?php echo __d('reservations', 'User'); ?>
						</a>
					</li>
		</ul>
	</div>
</div>


