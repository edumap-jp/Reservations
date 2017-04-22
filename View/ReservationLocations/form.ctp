<?php echo $this->NetCommonsHtml->script(
	[
		'/reservations/js/reservations.js',
		//'/blogs/js/blogs_entry_edit.js',
		//'/tags/js/tags.js',
	]
); ?>
<?php
//$dataJson = json_encode(
//	$this->NetCommonsTime->toUserDatetimeArray($this->request->data, array('ReservationLocation.publish_start'))
//);
$dataJson = json_encode($this->request->data);
?>

<?php echo $this->BlockTabs->main('location_settings'); ?>

<div class="reservationLocations form" ng-controller="ReservationLocation" ng-init="init(<?php
echo h($dataJson) ?>)">
	<div class="reservationLocations form">
		<article>
			<div class="panel panel-default">
				<div class="panel-heading">
					<?php echo __d('reservations', 'Register Location'); ?>
				</div>

				<?php echo $this->NetCommonsForm->create(
					'ReservationLocation',
					array(
						'inputDefaults' => array(
							'div' => 'form-group',
							'class' => 'form-control',
							'error' => false,
						),
						'div' => 'form-control',
						'novalidate' => true
					)
				);
				//$this->NetCommonsForm->unlockField('Tag');
				?>
				<?php echo $this->NetCommonsForm->hidden('id'); ?>
				<?php echo $this->NetCommonsForm->hidden('key'); ?>
				<?php echo $this->NetCommonsForm->hidden(
					'Frame.id',
					array(
						'value' => Current::read('Frame.id'),
					)
				); ?>
				<?php echo $this->NetCommonsForm->hidden(
					'Block.id',
					array(
						'value' => Current::read('Block.id'),
					)
				); ?>

				<div class="panel-body">

					<fieldset>

						<?php
						echo $this->NetCommonsForm->input(
							'location_name',
							array(
								'required' => 'required',
								'label' => __d('reservations', 'Location name'),
								//'childDiv' => ['class' => 'form-inline'],
							)
						);
						?>
						<div class="form-group">
							<?php
							echo $this->NetCommonsForm->label(
								null,
								__d('reservations', 'Available'),
								['required' => 'required']
							);
							?>
							<div class="form-inline">
								<?php

								// 利用時間 時分〜　時分
								// デフォルトは9:00-18:00
								echo $this->NetCommonsForm->input(
									'ReservationLocation.start_time',
									[
										'datetimepicker',
										'datetimepicker-options' => json_encode(
											['format' => 'HH:mm']
										),
										//'class' => 'form-inline'
										'ng-model' => 'data.ReservationLocation.start_time',
										'ng-readonly' => 'allDay',
									]
								);
								echo ' - ';
								echo $this->NetCommonsForm->input(
									'ReservationLocation.end_time',
									[
										'datetimepicker',
										'datetimepicker-options' => json_encode(
											['format' => 'HH:mm']
										),
										'ng-model' => 'data.ReservationLocation.end_time',
										'ng-readonly' => 'allDay',
										//'class' => 'form-inline'
									]
								);
								echo '&nbsp;';
								// 利用時間の制限無しチェックボックス
								// 特にカラムはなし。0:00-24:00まで利用可能とするだけ AngularJS制御
								//$this->NetCommonsForm->unlockField('ReservationLocation.allday_flag');
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
							</div>
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

						<?php
						// カテゴリ
						echo $this->Category->select(
							'ReservationLocation.category_id',
							array('empty' => true)
						);
						// 予約できる権限
						//echo $this->NetCommonsForm->label(null, __d('reservations', 'Authority'));
						echo $this->element('Blocks.block_permission_setting', array(
								//'panelLabel' => __d('reservations', 'Authority'),
							'settingPermissions' => array(
								'content_creatable' => __d('reservations', 'Authority'),
							),
						));
						?>
						<?php // 個人的な予約を受け付ける ?>
						<div class="form-group">
							<div class="form-inline">
								<div class="checkbox checkbox-inline">
									<?php
									echo $this->NetCommonsForm->checkbox('ReservationLocation.use_private', ['label' =>
										__d('reservations', 'Allow private use?')]);
									?>
								</div>
							</div>
						</div>

						<?php
						// 予約を受け付けるルーム
						echo $this->NetCommonsForm->label(null, __d('reservations', 'Select rooms'));
						echo $this->NetCommonsForm->checkbox(
							'ReservationLocation.use_all_rooms',
							[
								'label' => __d(
									'reservations',
									'Allow all the groups to use?'
								),
								'ng-model' => 'data.ReservationLocation.use_all_rooms'
							]
						);
						?>
						<div ng-hide="data.ReservationLocation.use_all_rooms">
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

						<?php

						echo $this->NetCommonsForm->label(null, __d('blocks', 'Approval settings'));
						// 予約に承認が必要
						echo $this->NetCommonsForm->checkbox('ReservationLocation.use_workflow', [
							'label' =>
									__d('reservations', 'Need approval reservations'),
							'ng-model' => 'data.ReservationLocation.use_workflow'
						]);
						?>
						<div ng-show="data.ReservationLocation.use_workflow">
							<div class="form-group"
									ng-controller="ReservationLocationsApprovalUser">
								<label class="control-label">
									<?php echo h(__d('reservations', '施設管理者')); ?>
								</label>

								<div class="form-group">
									<div>
										<?php
										$title = __d('reservations', '施設管理者');
										$pluginModel = 'ReservationLocationsApprovalUser';
										$roomId = Current::read('Room.id');
										$selectUsers = (isset($this->request->data['selectUsers'])) ? $this->request->data['selectUsers'] : null;
										echo $this->GroupUserList->select($title, $pluginModel, $roomId, $selectUsers);
										?>
									</div>
									<div class="has-error">
										<?php echo $this->NetCommonsForm->error('ReservationLocationApprovalUser.user_id', null, array('class' => 'help-block')); ?>
									</div>
								</div>
							</div>

							<?php
							// 施設管理者
							echo $this->NetCommonsForm->input(
								'ReservationLocation.contact',
								[
									'label' => __d('reservations', 'Contact')
								]
							);
							?>
						</div>
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
					</fieldset>
				</div>

				<?php //echo $this->Workflow->buttons('ReservationLocation.status'); ?>
				<?php
				$cancelUrl = NetCommonsUrl::actionUrlAsArray(
					array(
						'plugin' => 'reservations',
						'controller' => 'reservation_locations',
						'action' => 'index',
						'frame_id' => Current::read('Frame.id'),
					)
				);
				?>
				<div class="panel-footer text-center">
					<?php echo $this->Button->cancelAndSave(
						__d('net_commons', 'Cancel'),
						__d('net_commons', 'OK'),
						$cancelUrl
					); ?>
				</div>

				<?php echo $this->NetCommonsForm->end() ?>

				<?php if ($isEdit && $isDeletable) : ?>
					<div class="panel-footer" style="text-align: right;">
						<?php echo $this->NetCommonsForm->create(
							'ReservationLocation',
							array(
								'type' => 'delete',
								'url' => NetCommonsUrl::blockUrl(
									array(
										'controller' => 'reservation_locations',
										'action' => 'delete',
										'frame_id' => Current::read('Frame.id')
									)
								)
							)
						) ?>
						<?php echo $this->NetCommonsForm->input(
							'key',
							array('type' => 'hidden')
						); ?>

						<?php echo $this->Button->delete(
							'',
							__d(
								'net_commons',
								'Deleting the %s. Are you sure to proceed?',
								__d('blogs', 'ReservationLocation')
							)
						); ?>

						</span>
						<?php echo $this->NetCommonsForm->end() ?>
					</div>
				<?php endif ?>

			</div>

			<?php echo $this->Workflow->comments(); ?>

		</article>

	</div>


