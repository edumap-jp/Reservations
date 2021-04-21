<?php
/**
 * 予定編集（その他の詳細設定部分） template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<div uib-accordion close-others="oneAtATime">

	<div uib-accordion-group class="panel-default" is-open="status.open">
		<div uib-accordion-heading>
			<?php echo __d('reservations', 'detail information'); ?>
			<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
		</div>

		<?php /* 場所 */ ?>
		<div class="form-group" data-reservation-name="inputLocation" ng-cloak>
			<div class="col-xs-12">
				<?php echo $this->NetCommonsForm->input('ReservationActionPlan.location', array(
				'type' => 'hidden',
				'label' => __d('reservations', 'Location'),
				'div' => false,
				)); ?>
			</div>
		</div>
		<?php /* 連絡先 */ ?>
		<div class="form-group" data-reservation-name="inputContact" ng-cloak>
			<div class="col-xs-12">
				<?php echo $this->NetCommonsForm->input('ReservationActionPlan.contact', array(
				'type' => 'text',
				'label' => __d('reservations', 'Contact'),
				'div' => false,
				)); ?>
			</div>
		</div>
		<?php /* 詳細 */ ?>
		<div class="form-group" data-reservation-name="inputDescription" ng-controller="ReservationDetailEditWysiwyg">
			<div class="col-xs-12 reservation-detailedit-detail" ng-cloak>
				<?php
				echo $this->NetCommonsForm->wysiwyg('ReservationActionPlan.description', array(
					'label' => __d('reservations', 'Details'),
					'required' => false,
					'div' => false,
					'ng-init' => 'initDescription(' . json_encode($this->request->data['ReservationActionPlan']['description']) . ');',
				));
				?>
			</div>
		</div>

		<?php /* タイムゾーン */ ?>
		<div class="form-group" data-reservation-name="selectTimeZone" ng-cloak>
			<div class="col-xs-12">
				<?php
				// タイムゾーン
				$planTimeZone = Hash::get($this->request->data, 'ReservationActionPlan.timezone');
				if ($planTimeZone != Current::read('User.timezone')) {
					// ユーザのタイムゾーンと異なっていたらタイムゾーン選択ドロップダウン表示
					$SiteSetting = new SiteSetting();
					$SiteSetting->prepare();
					echo $this->NetCommonsForm->input('ReservationActionPlan.timezone', [
						'label' => __d('reservations', 'Time zone'),
						'options' => $SiteSetting->defaultTimezones,
						'type' => 'select',
						'div' => false
					]);
				} else {
					// 新規登録ならタイムゾーンは現在のユーザのタイムゾーンにする
					echo $this->NetCommonsForm->hidden('ReservationActionPlan.timezone');
				}
				?>
				<?php
				//$tzTbl = ReservationsComponent::getTzTbl();
				//$options = Hash::combine($tzTbl, '{s}.2', '{s}.0');
				//echo $this->NetCommonsForm->label('ReservationActionPlan.timezone' . Inflector::camelize('timezone'), __d('reservations', 'Time zone'));
				//echo $this->NetCommonsForm->select('ReservationActionPlan.timezone', $options, array(
				//'value' => $this->request->data['ReservationActionPlan']['timezone'],
				//'class' => 'form-control',
				//'empty' => false,
				//'required' => true,
				//));
				//echo $this->NetCommonsForm->error('ReservationActionPlan.timezone');
			?>
			</div>
		</div>
	</div>

</div>
