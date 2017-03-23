<?php
/**
 * reservation frame setting room select view template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>

<div class="form-group" name="dispTargetRooms">
	<div class='col-xs-11 col-xs-offset-1'>
		<div class="checkbox">
			<label>
				<?php
				echo $this->NetCommonsForm->input('ReservationFrameSetting.is_select_room', array(
				'type' => 'checkbox',
				'label' => false,
				'div' => "style='text-align:left'",
				'class' => 'text-left',
				'data-reservation-frame-id' => Current::read('Frame.id'),
				'ng-model' => 'data.reservationFrameSetting.isSelectRoom',
				));
				echo __d('reservations', 'Display only designated room');
				?>
			</label>

		</div>
		<div name="roomSelect" ng-show="data.reservationFrameSetting.isSelectRoom">
			<div class="panel-body">
				<?php echo __d('reservations', 'Select room for participation.'); ?>
				<div class='reservation-list-wrapper'>
					<?php echo $this->ReservationRoomSelect->spaceSelector($spaces); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="clearfix"></div><?php /* 幅広画面整えるため追加 */ ?>
</div>
