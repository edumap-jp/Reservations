<?php
/**
 * 月（小）の予定表示 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
echo $this->element('Reservations.scripts');
$url = $this->ReservationUrl->getReservationUrlAsArray(array(
	'plugin' => 'reservations',
	'controller' => 'reservations',
	'action' => 'index',
	'block_id' => '',
	'frame_id' => Current::read('Frame.id'),
	'?' => array(
	'style' => 'largemonthly',
)));
$title = '<div class="h2">' . sprintf(__d('reservations', '<small>%d/</small> %d'), $vars['mInfo']['year'], $vars['mInfo']['month']) . '</div>';
?>

<article ng-controller="ReservationsDetailEdit" class="block-setting-body">

	<div class="row">
		<div class="col-xs-12 text-center reservation-smonthly-div reservation-small-title">
			<?php echo $this->NetCommonsHtml->link($title, $url, array('escape' => false)); ?>
		</div>
	</div>

	<div class="reservation-smonthly-div">
		<table>
			<tbody>
			<tr>
				<td class='reservation-col-small-day-head'><span class='reservation-sunday h4'><?php echo __d('reservations', 'Sun'); ?></span></td>
				<td class='reservation-col-small-day-head'><span class='h4'><?php echo __d('reservations', 'Mon'); ?></span></td>
				<td class='reservation-col-small-day-head'><span class='h4'><?php echo __d('reservations', 'Tue'); ?></span></td>
				<td class='reservation-col-small-day-head'><span class='h4'><?php echo __d('reservations', 'Wed'); ?></span></td>
				<td class='reservation-col-small-day-head'><span class='h4'><?php echo __d('reservations', 'Thu'); ?></span></td>
				<td class='reservation-col-small-day-head'><span class='h4'><?php echo __d('reservations', 'Fri'); ?></span></td>
				<td class='reservation-col-small-day-head'><span class='reservation-saturday h4'><?php echo __d('reservations', 'Sat'); ?></span></td>
			</tr>
			<?php
				echo $this->ReservationMonthly->makeSmallMonthyBodyHtml($vars);
			?>
			</tbody>
		</table>
	  </div>
</article>
