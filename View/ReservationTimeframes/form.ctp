<?php
/**
 * 時間枠設定 > 時間枠登録
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationSettingsComponent', 'Reservations.Controller/Component');

echo $this->NetCommonsHtml->script(['/reservations/js/reservations.js']);

$dataJson = json_encode($this->request->data);
?>

<?php echo $this->BlockTabs->main(ReservationSettingsComponent::MAIN_TAB_TIMEFRAME_SETTING); ?>

<div class="reservationTimeFrames" ng-init="data=<?php echo h($dataJson)?>">
	<article class="panel panel-default">
		<?php echo $this->NetCommonsForm->create('ReservationTimeframe'); ?>
			<?php echo $this->NetCommonsForm->hidden('ReservationTimeframe.id'); ?>
			<?php echo $this->NetCommonsForm->hidden('ReservationTimeframe.key'); ?>
			<?php echo $this->NetCommonsForm->hidden('ReservationTimeframe.language_id'); ?>
			<?php echo $this->NetCommonsForm->hidden('Frame.id', array('value' => Current::read('Frame.id'))); ?>
			<?php echo $this->NetCommonsForm->hidden('Block.id', array('value' => Current::read('Block.id'))); ?>

			<div class="panel-body">
				<?php
					//時間枠名
					echo $this->NetCommonsForm->input(
						'ReservationTimeframe.title',
						array(
							'required' => 'required',
							'label' => __d('reservations', 'Time frame name'),
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
						<div class="input-group">
							<?php
								// 利用時間 時分〜　時分
								// デフォルトは9:00-18:00
								echo $this->NetCommonsForm->input(
									'ReservationTimeframe.start_time',
									[
										'type' => 'text',
										'datetimepicker',
										'datetimepicker-options' => json_encode(
											['format' => 'HH:mm']
										),
										//'class' => 'form-inline'
										'ng-model' => 'data.ReservationTimeframe.start_time',
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
									'ReservationTimeframe.end_time',
									[
										'type' => 'text',
										'datetimepicker',
										'datetimepicker-options' => json_encode(
											['format' => 'HH:mm']
										),
										'ng-model' => 'data.ReservationTimeframe.end_time',
										//'class' => 'form-inline'
										'div' => false,
										'error' => false,
										'default' => false,
									]
								);
							?>
						</div>

						<?php
							echo $this->NetCommonsForm->error('ReservationTimeframe.start_time');
							echo $this->NetCommonsForm->error('ReservationTimeframe.end_time');
						?>
					</div>
				</div>

				<div class="form-group">
					<?php
						$this->NetCommonsForm->unlockField('ReservationTimeframe.color');
						echo $this->NetCommonsForm->label(
							'ReservationTimeframe.color',
							__d('reservations', 'Time frame color'),
							['required' => 'required']
						);
						echo $this->element('NetCommons.color_palette_picker', array(
							'ngAttrName' => 'data[ReservationTimeframe][color]',
							'ngModel' => 'data.ReservationTimeframe.color',
							'colorValue' => '{{data.ReservationTimeframe.color}}',
						));
						echo $this->NetCommonsForm->error('ReservationTimeframe.color');
					?>

				</div>
			</div>

			<div class="panel-footer text-center">
				<?php
					$cancelUrl = NetCommonsUrl::actionUrlAsArray(
						array(
							'plugin' => 'reservations',
							'controller' => 'reservation_timeframes',
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
					echo $this->NetCommonsForm->create('ReservationTimeframe',
						array(
							'type' => 'delete',
							'url' => NetCommonsUrl::blockUrl(
								array(
									'controller' => 'reservation_timeframes',
									'action' => 'delete',
									'frame_id' => Current::read('Frame.id')
								)
							)
						)
					);

					echo $this->NetCommonsForm->hidden('ReservationTimeframe.key');
					echo $this->Button->delete(
						'',
						__d('net_commons', 'Deleting the %s. Are you sure to proceed?', __d('reservations', 'TimeFrame'))
					);

					echo $this->NetCommonsForm->end();
				?>
			</div>
		<?php endif; ?>
	</article>
</div>
