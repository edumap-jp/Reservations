<?php
/**
 * 施設予約上部の月・週・日切替タブ template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsComponent', 'Reservations.Controller/Component');

$baseLinkArr = array(
	'controller' => 'reservations',
	'action' => 'index',
	'block_id' => '',
	'frame_id' => Current::read('Frame.id'),
	'?' => array(
		'year' => sprintf('%04d', $vars['year']),
		'month' => sprintf('%02d', $vars['month']),
		'day' => $vars['day'],
	)
);
$monthlyLinkArr = Hash::merge($baseLinkArr, array(
	'?' => array('style' => ReservationsComponent::RESERVATION_STYLE_LACATION_MONTHLY)
));
$weeklyLinkArr = Hash::merge($baseLinkArr, array(
	'?' => array('style' => ReservationsComponent::RESERVATION_STYLE_LACATION_WEEKLY)
));
?>

<ul role='tablist' class='nav nav-tabs reservation-date-move-tablist'>
	<?php if ($vars['style'] === ReservationsComponent::RESERVATION_STYLE_LACATION_MONTHLY) : ?>
		<li class='active'>
			<a href=""><?php echo __d('reservations', 'month'); ?></a>
		</li>
	<?php else: ?>
		<li>
			<?php echo $this->NetCommonsHtml->link(__d('reservations', 'month'), $monthlyLinkArr); ?>
		</li>
	<?php endif; ?>

	<?php if ($vars['style'] === ReservationsComponent::RESERVATION_STYLE_LACATION_WEEKLY) : ?>
		<li class='active'>
			<a href=""><?php echo __d('reservations', 'week'); ?></a>
		</li>
	<?php else: ?>
		<li>
			<?php echo $this->NetCommonsHtml->link(__d('reservations', 'week'), $weeklyLinkArr); ?>
		</li>
	<?php endif; ?>
</ul>
