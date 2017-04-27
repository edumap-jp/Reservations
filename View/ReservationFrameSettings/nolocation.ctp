<?php
/**
 * reservations frame setting view template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationSettingTabComponent', 'Reservations.Controller/Component');
?>

<article class="block-setting-body">
	<?php echo $this->BlockTabs->main(ReservationSettingTabComponent::MAIN_TAB_FRAME_SETTING); ?>

	<div class="tab-content">
		<?php echo __d('reservations', 'No institution yet registered.'); ?>
	</div>
</article>
