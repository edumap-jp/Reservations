<?php
/**
 * 予定登録 タイトル編集 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<label><?php echo __d('reservations', 'Title') . $this->element('NetCommons.required'); ?></label>
<?php

	$options = array(
		'label' => false,
		//'ng-model' => 'reservations.plan.title',
		'div' => false,
	);
	if (isset($this->request->data['ReservationEvent']['title'])) {
		$options['value'] = $this->request->data['ReservationEvent']['title'];
	}
	if (isset($this->request->data['ReservationEvent']['title_icon'])) {
		$options['titleIcon'] = $this->request->data['ReservationEvent']['title_icon'];
	}

	//inputWithTitleIcon()の第１引数がfieldName, 第２引数が$titleIconFieldName
	echo $this->TitleIcon->inputWithTitleIcon('ReservationActionPlan.title', 'ReservationActionPlan.title_icon', $options);
