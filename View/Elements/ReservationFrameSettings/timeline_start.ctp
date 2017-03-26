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
<div class="form-group" ng-show="isShowTimelineStart">
	<?php echo $this->NetCommonsForm->label('ReservationFrameSetting.timeline_base_time', __d('reservations', 'Timeline start time')); ?>

		<?php
		$options = array();
		for ($idx = ReservationsComponent::CALENDAR_TIMELINE_MIN_TIME; $idx <= ReservationsComponent::CALENDAR_TIMELINE_MAX_TIME; ++$idx) {
			$options[$idx] = sprintf("%02d:00", $idx);
		}

		echo $this->NetCommonsForm->input('ReservationFrameSetting.timeline_base_time', array(
		'type' => 'select',
		'label' => false,
		'div' => false,
		'options' => $options,
		'selected' => $this->request->data['ReservationFrameSetting']['timeline_base_time'],
		'class' => 'form-control',
		));
		?>
	<div class="clearfix"></div>
</div><!-- form-groupおわり-->
