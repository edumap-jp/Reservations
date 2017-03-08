<?php
/**
 * ReservationSettings edit template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>

<article class="block-setting-body">
	<?php echo $this->BlockTabs->main('block_settings'); ?>

	<div class="tab-content">
		<?php //echo $this->BlockTabs->block(BlockTabsHelper::BLOCK_TAB_SETTING); ?>

		<?php echo $this->element('Blocks.edit_form', array(
			'model' => 'Reservation',
			'callback' => 'Reservations.ReservationSettings/edit_form',
			'cancelUrl' => NetCommonsUrl::backToIndexUrl('default_setting_action'),
		)); ?>

		<?php //if ($this->request->params['action'] === 'edit') : ?>
		<!--	--><?php //echo $this->element('Blocks.delete_form', array(
		//		'model' => 'ReservationSetting',
		//		'action' => NetCommonsUrl::actionUrl(array(
		//			'controller' => $this->params['controller'],
		//			'action' => 'delete',
		//			'block_id' => Current::read('Block.id'),
		//			'frame_id' => Current::read('Frame.id')
		//		)),
		//		//'action' => 'delete/' . Current::read('Frame.id') . '/' . Current::read('Block.id'),
		//		'callback' => 'Reservations.ReservationSettings/delete_form'
		//	)); ?>
		<?php //endif; ?>
	</div>
</article>
