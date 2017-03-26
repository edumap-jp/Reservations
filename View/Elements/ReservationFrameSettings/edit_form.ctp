<?php
/**
 * reservation frame setting form view template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>

<?php
	//
	//以下の項目は画面からの入力項目にないので、(値省略型)hiddenで指定する必要あり。
	//hidden指定しないと、BlackHole行きとなる。
	//逆に、画面からの入力項目化したら、ここのhiddenから外すこと。
	//
	echo $this->NetCommonsForm->hidden('ReservationFrameSetting.id');
	echo $this->NetCommonsForm->hidden('ReservationFrameSetting.frame_key');
	echo $this->NetCommonsForm->hidden('Frame.id');
	echo $this->NetCommonsForm->hidden('Frame.key');
	echo $this->NetCommonsForm->hidden('ReservationFrameSetting.room_id');
	echo $this->NetCommonsForm->hidden('ReservationFrameSetting.is_myroom');

	$displayType = $this->request->data['ReservationFrameSetting']['display_type'];
?>

<div class="form-group">
	<div class='row'>
		<?php echo $this->NetCommonsForm->label('ReservationFrameSetting.display_type',
		__d('reservations', 'Display type'), array('class' => 'col-xs-12')); ?>
		<div class='col-xs-12'>
		<?php
			echo $this->NetCommonsForm->input('ReservationFrameSetting.display_type', array(
				'type' => 'select',
				'label' => false,
				'div' => false,
				'options' => $displayTypeOptions,
				'selected' => $this->request->data['ReservationFrameSetting']['display_type'],
				'data-reservation-frame-id' => Current::read('Frame.id'),
				'ng-model' => 'data.reservationFrameSetting.displayType',
				'ng-change' => 'displayChange()',
			));
		?>
		</div>
		<div class="clearfix"></div>
	</div>
</div><!-- form-groupおわり-->

<?php
	/* ルーム選択 */
	//echo $this->element('Reservations.ReservationFrameSettings/room_select');
	echo $this->NetCommonsForm->hidden('ReservationFrameSetting.is_select_room');

	/* 開始位置 */
	echo $this->element('Reservations.ReservationFrameSettings/start_pos');

	/* 日数 */
	echo $this->element('Reservations.ReservationFrameSettings/display_count');

	/* タイムライン開始 */
	echo $this->element('Reservations.ReservationFrameSettings/timeline_start');

