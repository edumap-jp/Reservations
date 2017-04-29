<?php
/**
 * 表示方法変更テンプレート
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationSettingsComponent', 'Reservations.Controller/Component');

echo $this->element('Reservations.scripts');

if (isset($this->data['ReservationFrameSetting'])) {
	$camelizeData = NetCommonsAppController::camelizeKeyRecursive(array(
		'frameId' => $this->request->data['Frame']['id'],
		'reservationFrameSetting' => $this->request->data['ReservationFrameSetting'],
		'displayTypeOptions' => $displayTypeOptions
	));

} else {
	$camelizeData = NetCommonsAppController::camelizeKeyRecursive(array(
		'frameId' => $this->request->data['Frame']['id'],
		'reservationFrameSetting' => array(),
		'displayTypeOptions' => $displayTypeOptions
	));
}
?>

<article class="block-setting-body"
	ng-controller="ReservationFrameSettings"
	ng-init="initialize(<?php echo h(json_encode($camelizeData, JSON_FORCE_OBJECT)); ?>)">

	<?php echo $this->BlockTabs->main(ReservationSettingsComponent::MAIN_TAB_FRAME_SETTING); ?>

	<div class="tab-content">
		<?php echo $this->element('Blocks.edit_form', array(
			'model' => 'ReservationFrameSetting',
			'callback' => 'Reservations.ReservationFrameSettings/edit_form',
			'cancelUrl' => NetCommonsUrl::backToPageUrl(true),
		)); ?>
	</div>
</article>
