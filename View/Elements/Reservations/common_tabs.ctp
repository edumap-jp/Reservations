<?php
/**
 * カテゴリー別・施設別切替タブ template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsComponent', 'Reservations.Controller/Component');

$commonUrlArray = array(
	'controller' => 'reservations',
	'action' => 'index',
	'block_id' => '',
	'frame_id' => Current::read('Frame.id'),
);

$weeklyUrlByCategory = $this->ReservationUrl->getReservationUrl(
	Hash::merge(
		$commonUrlArray,
		array(
			'?' => array(
				'style' => ReservationsComponent::RESERVATION_STYLE_CATEGORY_WEEKLY,
				//'tab' => 'timeline',
				'year' => sprintf('%04d', $vars['year']),
				'month' => sprintf('%02d', $vars['month']),
				'day' => $vars['day'],
			)
		)
	)
);

$weeklyUrlByLocation = $this->ReservationUrl->getReservationUrl(
	Hash::merge(
		$commonUrlArray,
		array(
			'?' => array(
				'style' => ReservationsComponent::RESERVATION_STYLE_LACATION_WEEKLY,
				//'tab' => 'timeline',
				'year' => sprintf('%04d', $vars['year']),
				'month' => sprintf('%02d', $vars['month']),
				'day' => $vars['day'],
			)
		)
	)
);
?>

<div class="btn-group btn-group-justified reservation-common-tabs" role="group" aria-label="...">
	<div class="btn-group" role="group">
		<?php if (in_array($vars['style'], ReservationsComponent::$reservationStylesByCategory, true)): ?>
			<a class="btn btn-default active" href='' onclick='return false;'>
				<?php echo __d('reservations', 'By Category'); ?>
			</a>
		<?php else: ?>
			<a class='btn btn-default' href="<?php echo $weeklyUrlByCategory; ?>">
				<?php echo __d('reservations', 'By Category'); ?>
			</a>
		<?php endif; ?>
	</div>
	<div class="btn-group" role="group">
		<?php if (in_array($vars['style'], ReservationsComponent::$reservationStylesByLocation, true)): ?>
			<a class="btn btn-default active" href='' onclick='return false;'>
				<?php echo __d('reservations', 'By Location'); ?>
			</a>
		<?php else: ?>
			<a class='btn btn-default' href="<?php echo $weeklyUrlByLocation; ?>">
				<?php echo __d('reservations', 'By Location'); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
