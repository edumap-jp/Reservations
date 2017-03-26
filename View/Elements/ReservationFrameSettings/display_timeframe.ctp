<?php
/**
 * reservation frame timeline start pos view template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<div class="form-group">
	<?php
	/* 表時枠表示 */
	echo $this->NetCommonsForm->label(
		'ReservationFrameSetting.display_timeframe',
		__d('reservations', '時間枠表示')
	);
	echo $this->NetCommonsForm->checkbox(
		'ReservationFrameSetting.display_timeframe',
		[
			'label' =>
				__d('reservations', '時間枠を表示する')
		]
	);
	?>
</div>
