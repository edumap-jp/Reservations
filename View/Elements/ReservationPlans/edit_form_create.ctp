<?php
/**
 * 予定登録 Form create template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php
	$options = array(
		'inputDefaults' => array(
			'label' => false,	//以降のinput要素のlabelをデフォルト抑止。必要なら各inputで明示指定する。
			'div' => false,	//以降のinput要素のdivをデフォルト抑止。必要なら各inputで明示指定する。
		),
		'class' => 'form-horizontal',
	);
	echo $this->NetCommonsForm->create('ReservationActionPlan', $options);	//<!-- <form class="form-horizontal"> --> <!-- これで<div class-"form-group row"のrowを省略できる -->
