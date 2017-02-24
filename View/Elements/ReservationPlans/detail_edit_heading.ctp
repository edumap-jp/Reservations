<?php
/**
 * 予定登録 表題 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<div class='h3'>
<?php if ($planViewMode === ReservationsComponent::PLAN_EDIT) : ?>
	<?php echo __d('reservations', 'Form to edit schedule item'); ?>
<?php else: ?>
	<?php echo __d('reservations', 'Form to add schedule item'); ?>
<?php endif; ?>
</div>
