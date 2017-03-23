<?php
/**
 * reservation frame display count view template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<div class="form-group" ng-show="isShowDisplayCount">
	<?php echo $this->NetCommonsForm->label('ReservationFrameSetting.display_count',
		__d('reservations', 'Display days'), array('class' => 'col-xs-12 col-sm-12')); ?>
	<div class="col-xs-12 col-sm-9">
		<?php
		$options = array();
		for ($idx = ReservationsComponent::CALENDAR_MIN_DISPLAY_DAY_COUNT; $idx <= ReservationsComponent::CALENDAR_MAX_DISPLAY_DAY_COUNT; ++$idx) {
			$options[$idx] = sprintf(__d('reservations', '%dday(s)'), $idx);
		}

		echo $this->NetCommonsForm->input('ReservationFrameSetting.display_count', array(
		'type' => 'select',
		'label' => false,
		'div' => false,
		'options' => $options,
		'selected' => $this->request->data['ReservationFrameSetting']['display_count'],
		'class' => 'form-control',
		));
	?>
	</div><!-- col-xs-10おわり -->
	<div class="clearfix"></div>
</div><!-- form-groupおわり-->
