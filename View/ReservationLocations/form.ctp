<?php
/**
 * 施設設定 > 施設登録
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationSettingsComponent', 'Reservations.Controller/Component');

echo $this->NetCommonsHtml->script('/reservations/js/reservations.js');
echo $this->NetCommonsHtml->css('/reservations/css/reservations.css');

$dataJson = json_encode($this->request->data);
?>

<?php echo $this->BlockTabs->main(ReservationSettingsComponent::MAIN_TAB_LOCATION_SETTING); ?>

<div ng-controller="ReservationLocation" ng-init="init(<?php echo h($dataJson) ?>)">
	<article class="panel panel-default">
		<?php echo $this->NetCommonsForm->create('ReservationLocation'); ?>

		<?php echo $this->NetCommonsForm->hidden('ReservationLocation.id'); ?>
		<?php echo $this->NetCommonsForm->hidden('ReservationLocation.key'); ?>
		<?php echo $this->NetCommonsForm->hidden('Frame.id', array('value' => Current::read('Frame.id'))); ?>
		<?php echo $this->NetCommonsForm->hidden('Block.id', array('value' => Current::read('Block.id'))); ?>

		<div class="panel-body">
			<?php
				echo $this->NetCommonsForm->input('ReservationLocation.location_name', array(
					'required' => 'required',
					'label' => __d('reservations', 'Location name'),
				));
			?>

			<div class="form-group">
				<?php
					echo $this->NetCommonsForm->label(
						null, __d('reservations', 'Available'), ['required' => true]
					);
				?>

				<div class="form-inline reservation-reserve-time form-input-outer">
					<div class="input-group">
						<?php
							// 利用時間 時分〜　時分
							// デフォルトは9:00-18:00
							echo $this->NetCommonsForm->input(
								'ReservationLocation.start_time',
								[
									'type' => 'text',
									'datetimepicker',
									'datetimepicker-options' => json_encode(
										['format' => 'HH:mm']
									),
									//'class' => 'form-inline'
									'ng-model' => 'data.ReservationLocation.start_time',
									'ng-readonly' => 'allDay',
									'div' => false,
									'error' => false,
									'default' => false,
								]
							);
						?>

						<span class="input-group-addon">
							<span class="glyphicon glyphicon-minus"></span>
						</span>

						<?php
							echo $this->NetCommonsForm->input(
								'ReservationLocation.end_time',
								[
									'type' => 'text',
									'datetimepicker',
									'datetimepicker-options' => json_encode(
										['format' => 'HH:mm']
									),
									'ng-model' => 'data.ReservationLocation.end_time',
									'ng-readonly' => 'allDay',
									//'class' => 'form-inline'
									'div' => false,
									'error' => false,
									'default' => false,
								]
							);
						?>
					</div>

					<?php
						echo $this->NetCommonsForm->inlineCheckbox(
							'ReservationLocation.allday_flag',
							[
								//'type' => 'checkbox',
								'ng-model' => 'allDay',
								'ng-click' => 'checkAllDay()',
								//'hiddenField' => false,
								'label'
								=> __d('reservations', 'Not specify')
							]
						);
					?>

					<?php
						// タイムゾーン
						$locationTimezone = Hash::get($this->request->data, 'ReservationLocation.timezone');
						if ($locationTimezone != Current::read('User.timezone')) {
							// ユーザのタイムゾーンと異なっていたらタイムゾーン選択ドロップダウン表示
							$SiteSetting = new SiteSetting();
							$SiteSetting->prepare();
							echo $this->NetCommonsForm->input('ReservationLocation.timezone', [
								'label' => false,
								'options' => $SiteSetting->defaultTimezones,
								'type' => 'select'
							]);
						} else {
							// 新規登録ならタイムゾーンは現在のユーザのタイムゾーンにする
							echo $this->NetCommonsForm->hidden('ReservationLocation.timezone');
						}
					?>
				</div>

				<div class="reservation-reserve-time form-input-outer">
					<?php
						echo $this->NetCommonsForm->error('ReservationLocation.start_time');
						echo $this->NetCommonsForm->error('ReservationLocation.end_time');
					?>
				</div>

				<div class="reservation-form-time-table form-input-outer">
					<?php
						// 利用時間曜日チェックボックス
						$weekDaysOptions = [
							'Sun' => __d('holidays', 'Sunday'),
							'Mon' => __d('holidays', 'Monday'),
							'Tue' => __d('holidays', 'Tuesday'),
							'Wed' => __d('holidays', 'Wednesday'),
							'Thu' => __d('holidays', 'Thursday'),
							'Fri' => __d('holidays', 'Friday'),
							'Sat' => __d('holidays', 'Saturday'),
						];
						// \
						//echo $this->NetCommonsForm->checkbox('ReservationLocation.time_table', ['options' =>
						//    $weekDaysOptions]);
						echo $this->NetCommonsForm->input(
							'ReservationLocation.time_table',
							[
								'options' => $weekDaysOptions,
								'type' => 'select',
								'multiple' => 'checkbox',
								'div' => 'form-inline'
							]
						);
					?>
				</div>
			</div>

			<?php
				// カテゴリ
				echo $this->Category->select('ReservationLocation.category_id', array('empty' => true));
			?>

			<?php
				// 予約できる権限
				echo $this->element('Reservations.ReservationLocations/block_permission_setting', array(
					'settingPermissions' => array(
						'content_creatable' => __d('reservations', 'Authority'),
					),
				));
			?>

			<?php
				// 予約に承認が必要
				echo $this->NetCommonsForm->input('ReservationLocation.use_workflow', [
					'type' => 'radio',
					'label' => __d('blocks', 'Approval settings'),
					'options' => [
						'1' => __d('reservations', 'Need approval reservations'),
						'0' => __d('blocks', 'Not need approval'),
					],
					'default' => Hash::get($this->request->data, 'ReservationLocation.use_workflow'),
				]);
			?>

			<?php
				// 予約を受け付けるルーム
				echo $this->NetCommonsForm->label(null, __d('reservations', 'Select rooms'));
			?>
			<div class="panel panel-default reservation-select-rooms">
				<div class="panel-heading">
					<?php
						echo $this->NetCommonsForm->checkbox(
							'ReservationLocation.use_all_rooms',
							[
								'label' => __d('reservations', 'Allow all the groups to use?'),
								'ng-model' => 'data.ReservationLocation.use_all_rooms'
							]
						);
					?>
				</div>

				<div class="panel-body" ng-hide="data.ReservationLocation.use_all_rooms">
					<?php
						echo $this->RoomsForm->checkboxRooms(
							'ReservationLocationsRoom.room_id',
							array(
								'privateSpace' => false,
								'default' => Hash::get($this->request->data, 'ReservationLocationsRoom.room_id'),
							)
						);
					?>
				</div>
			</div>

			<?php
				//施設管理者
				echo $this->NetCommonsForm->label(null, __d('reservations', 'Contact'));
			?>
			<?php
				$title = false;
				$pluginModel = 'ReservationLocationsApprovalUser';
				$roomId = null;
				if (isset($this->request->data['selectUsers'])) {
					$selectUsers = $this->request->data['selectUsers'];
				} else {
					$selectUsers = null;
				}
				echo $this->GroupUserList->select($title, $pluginModel, $roomId, $selectUsers);
			?>

			<?php
				// 施設説明 WYSIWYG
				echo $this->NetCommonsForm->wysiwyg(
					'ReservationLocation.detail',
					array(
						'label' => __d('reservations', 'Description'),
						'required' => false,
						'rows' => 12,
						'ng-model' => 'data.ReservationLocation.detail'

					)
				);
			?>

			<div class="panel-footer text-center">
				<?php
					$cancelUrl = NetCommonsUrl::actionUrlAsArray(
						array(
							'plugin' => 'reservations',
							'controller' => 'reservation_locations',
							'action' => 'index',
							'frame_id' => Current::read('Frame.id'),
						)
					);
					echo $this->Button->cancelAndSave(
						__d('net_commons', 'Cancel'),
						__d('net_commons', 'OK'),
						$cancelUrl
					);
				?>
			</div>
		<?php echo $this->NetCommonsForm->end(); ?>

		<?php if ($isEdit && $isDeletable) : ?>
			<div class="panel-footer text-right">
				<?php
					echo $this->NetCommonsForm->create('ReservationLocation', array(
						'type' => 'delete',
						'url' => NetCommonsUrl::blockUrl(
							array(
								'controller' => 'reservation_locations',
								'action' => 'delete',
								'frame_id' => Current::read('Frame.id')
							)
						))
					);
				?>

				<?php echo $this->NetCommonsForm->hidden('ReservationLocation.key'); ?>

				<?php
					echo $this->Button->delete(
						'',
						__d('net_commons', 'Deleting the %s. Are you sure to proceed?', __d('resevations', 'Location'))
					);
				?>
				<?php echo $this->NetCommonsForm->end() ?>
			</div>
		<?php endif ?>
	</article>
</div>


