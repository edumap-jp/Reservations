<?php
/**
 * 時間枠設定 > 時間枠登録
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationSettingTabComponent', 'Reservations.Controller/Component');

echo $this->NetCommonsHtml->script(
	[
		'/reservations/js/reservations.js',
	]
);

$dataJson = json_encode($this->request->data);
?>

<?php echo $this->BlockTabs->main(ReservationSettingTabComponent::MAIN_TAB_TIMEFRAME_SETTING); ?>

<div class="reservationTimeFrames form" ng-init="data=<?php echo h($dataJson)?>" >
	<div class="reservationTimeFrames form">
		<article>
			<div class="panel panel-default">
				<div class="panel-heading">
					<?php echo __d('reservations', 'TimeFrame setting'); ?>
				</div>

				<?php echo $this->NetCommonsForm->create(
					'ReservationTimeframe',
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
							'title',
							array(
								'required' => 'required',
								'label' => __d('reservations', 'Time frame name'),
								//'childDiv' => ['class' => 'form-inline'],
							)
						);
						?>
						<div class="form-group">
							<?php
							echo $this->NetCommonsForm->label(
								null,
								__d('reservations', 'Time frame range'),
								['required' => 'required']
							);
							?>
							<div class="form-inline">
								<?php

								// 利用時間 時分〜　時分
								// デフォルトは9:00-18:00
								echo $this->NetCommonsForm->input(
									'ReservationTimeframe.start_time',
									[
										'datetimepicker',
										'datetimepicker-options' => json_encode(
											['format' => 'HH:mm']
										),
										//'class' => 'form-inline'
										'ng-model' => 'data.ReservationTimeframe.start_time',
									]
								);
								echo ' - ';
								echo $this->NetCommonsForm->input(
									'ReservationTimeframe.end_time',
									[
										'datetimepicker',
										'datetimepicker-options' => json_encode(
											['format' => 'HH:mm']
										),
										'ng-model' => 'data.ReservationTimeframe.end_time',
									]
								);

								?>

							</div>
							<div class="form-group">

								<?php
								$this->NetCommonsForm->unlockField('ReservationTimeframe.color');
								echo $this->NetCommonsForm->label('ReservationTimeframe.color',
									__d('reservations', 'Time frame color'));
								echo $this->element('NetCommons.color_palette_picker', array(
									'ngAttrName' => 'data[ReservationTimeframe][color]',
									'ngModel' => 'data.ReservationTimeframe.color',
									'colorValue' => '{{data.ReservationTimeframe.color}}',
								)); ?>
							</div>

						</div>


					</fieldset>
				</div>

				<?php //echo $this->Workflow->buttons('ReservationTimeframe.status'); ?>
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
							'ReservationTimeframe',
							array(
								'type' => 'delete',
								'url' => NetCommonsUrl::blockUrl(
									array(
										'controller' => 'blog_entries_edit',
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
								__d('blogs', 'ReservationTimeframe')
							)
						); ?>

						</span>
						<?php echo $this->NetCommonsForm->end() ?>
					</div>
				<?php endif ?>

			</div>


		</article>

	</div>




