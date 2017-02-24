<?php
/**
 * reservations block permission setting form template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<uib-tabset type="pills">
<?php foreach ($spaces as $space): ?>
	<?php echo $this->ReservationPermission->getSpaceSelectTabStart($space); ?>
		<?php if ($space['Space']['id'] != Space::PRIVATE_SPACE_ID): ?>
			<?php echo $this->element('Reservations.ReservationBlockRolePermissions/permission', array(
				'spaceId' => $space['Space']['id']
			)); ?>
		<?php else: ?>
			<p class="well">
				<?php echo __d('reservations', 'Only you can create your private event.'); ?>
			</p>
		<?php endif; ?>
	<?php echo $this->ReservationPermission->getSpaceSelectTabEnd($space); ?>
<?php endforeach; ?>

<?php
	/* 全会員 */
	echo $this->ReservationPermission->getSpaceSelectTabStart();
	echo $this->element('Reservations.ReservationBlockRolePermissions/permission_all_members');
	echo $this->ReservationPermission->getSpaceSelectTabEnd();
?>
</uib-tabset>
