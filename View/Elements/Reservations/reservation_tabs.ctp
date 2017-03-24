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
?>
<?php
	$baseLinkArr = array(
		'controller' => 'reservations',
		'action' => 'index',
		'block_id' => '',
		'frame_id' => Current::read('Frame.id'),
		'?' => array(
			'year' => sprintf("%04d", $vars['year']),
			'month' => sprintf("%02d", $vars['month']),
			'day' => $vars['day'],
		)
	);
	$weeklyLinkArr = Hash::merge($baseLinkArr, array(
		'?' => array('style' => 'weekly')
	));
    $weeklyAllLinkArr = Hash::merge($baseLinkArr, array(
        '?' => array('style' => 'all_weekly')
    ));

	$lmonthlyLinkArr = Hash::merge($baseLinkArr, array(
		'?' => array('style' => 'largemonthly')
	));

	$dailyLinkArr = Hash::merge($baseLinkArr, array(
		'?' => array('style' => 'daily', 'tab' => 'list')
	));
?>
<?php
if (in_array($active, ['lmonthly', 'weekly'])) {
    // 施設別タブをアクティブ
	$oneLocation = 'active';
	$oneLocationLink = '#';
	$allLocation = '';
	$allLocationLink = $weeklyAllLinkArr;
} else {
    // 全施設タブをアクティブ
	$oneLocation = '';
	$oneLocationLink = $lmonthlyLinkArr;
	$allLocation = 'active';
	$allLocationLink = '#';
}
?>
<div class="reservation-location-tabs btn-group btn-group-justified" role="group" aria-label="">
    <div class="btn-group" role="group">
		<?php echo $this->NetCommonsHtml->link(__d('reservations', '全施設'), $allLocationLink, [
		        'class' => ['btn', 'btn-default', $allLocation]
        ]); ?>
    </div>
    <div class="btn-group" role="group">
		<?php echo $this->NetCommonsHtml->link(__d('reservations', '施設別'), $oneLocationLink, [
			'class' => ['btn', 'btn-default', $oneLocation]
		]); ?>
    </div>
</div>

<ul role='tablist' class='nav nav-tabs reservation-date-move-tablist'>

<?php if ($oneLocation == 'active'): ?>
    <?php if ($active === 'lmonthly'): ?>
		<li class='active'>
		<a href="#"><?php echo __d('reservations', 'month'); ?></a>
    <?php else: ?>
		<li>
		<?php echo $this->NetCommonsHtml->link(__d('reservations', 'month'), $lmonthlyLinkArr); ?>
    <?php endif; ?>
		</li>

    <?php if ($active === 'weekly'): ?>
		<li class='active'>
		<a href="#"><?php echo __d('reservations', 'week'); ?></a>
    <?php else: ?>
		<li>
		<?php echo $this->NetCommonsHtml->link(__d('reservations', 'week'), $weeklyLinkArr); ?>
    <?php endif; ?>
		</li>
<?php endif ?>

<?php if ($allLocation == 'active'): ?>

    <?php if ($active === 'all_weekly'): ?>
        <li class='active'>
        <a href="#"><?php echo __d('reservations', 'week'); ?></a>
    <?php else: ?>
        <li>
		<?php echo $this->NetCommonsHtml->link(__d('reservations', 'week'), $weeklyAllLinkArr); ?>
	<?php endif; ?>
    </li>

    <?php if ($active === 'daily'): ?>
		<li class='active'>
		<a href="#"><?php echo __d('reservations', 'day'); ?></a>
    <?php else: ?>
		<li>
		<?php echo $this->NetCommonsHtml->link(__d('reservations', 'day'), $dailyLinkArr); ?>
    <?php endif; ?>
		</li>
<?php endif; ?>
</ul>
<br>
