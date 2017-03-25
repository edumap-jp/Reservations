<?php
/**
 * 日表示施設予約上部の一覧・タイムライン切替タブ template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php
	$timelineLink = $this->ReservationUrl->getReservationUrl(array(
		'controller' => 'reservations',
		'action' => 'index',
		'block_id' => '',
		'frame_id' => Current::read('Frame.id'),
		'?' => array(
			'style' => 'daily',
			'tab' => 'timeline',
			'year' => sprintf("%04d", $vars['year']),
			'month' => sprintf("%02d", $vars['month']),
			'day' => $vars['day'],
		)
	));

	$dailyLink = $this->ReservationUrl->getReservationUrl(array(
		'controller' => 'reservations',
		'action' => 'index',
		'block_id' => '',
		'frame_id' => Current::read('Frame.id'),
		'?' => array(
			'style' => 'daily',
			'tab' => 'list',
			'year' => sprintf("%04d", $vars['year']),
			'month' => sprintf("%02d", $vars['month']),
			'day' => $vars['day'],
		)
	));
?>

<div class="btn-group btn-group-justified" role="group" aria-label="...">
	<div class="btn-group" role="group">
	<?php if ($active === 'list'): ?>
		<a class="btn btn-default active" href='#' onclick='return false;'>
	<?php else: ?>
		<a class='btn btn-default' href="<?php echo $dailyLink; ?>">
	<?php endif; ?>
			<?php echo __d('reservations', 'Plan list'); ?></a>
		</a>
	</div>
	<div class="btn-group" role="group">
	<?php if ($active === 'timeline'): ?>
		<a class="btn btn-default active" href='#' onclick='return false;'>
	<?php else: ?>
		<a class="btn btn-default" href="<?php echo $timelineLink; ?>">
	<?php endif; ?>
		<?php echo __d('reservations', 'Timeline'); ?></a>
		</a>
	</div>
</div>
<br>
