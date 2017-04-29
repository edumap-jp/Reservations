<?php
/**
 * 施設カテゴリー設定
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationSettingsComponent', 'Reservations.Controller/Component');
?>

<article class="block-setting-body">
	<?php echo $this->BlockTabs->main(ReservationSettingsComponent::MAIN_TAB_CATEGORY_SETTING); ?>

	<div class="tab-content">
		<?php echo $this->element('Blocks.edit_form', array(
			'model' => 'Reservation',
			'callback' => 'Reservations.ReservationLocationCategories/edit_form',
			'cancelUrl' => NetCommonsUrl::backToPageUrl(true),
		)); ?>
	</div>
</article>
