<?php
/**
 * 予定削除 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
echo $this->element('Reservations.scripts');
?>
<?php $this->start('title_for_modal'); ?>
<?php echo __d('reservations', 'delete'); ?>
<?php $this->end(); ?>

<?php
	// ここに div ng-controller=xxx を書かずに、
	// NetCommonsModalが使っているng-controller"NetCommons.base"の
	// をそのままつかうこと。それにより、$scope, $eventが共通になるので
	// キャンセルした時の[$scope.]cancel()が、以下でも利用できるようになる。

	// どうしてもnc-controller(ex.AAA)を定義必要が出た場合、$scope, $eventは、親子
	// 関係ができる(NetCommons.baseの$scopeが親、AAAの$scopeが子)ので、
	// それを考慮して改造すること。
?>
<article>
	<div class="panel panel-default">
		<?php
			echo $this->NetCommonsForm->create(
				'ReservationDeleteActionPlan',
				array(
					'inputDefaults' => array(
					'div' => 'form-group',
					'class' => 'form-control',
					'error' => false,
					),
					'div' => 'form-control',
					'novalidate' => true,
					'type' => 'delete',
					'url' => NetCommonsUrl::actionUrlAsArray(
						array(
							'action' => 'delete',
							'block_id' => '',
							'key' => $event['ReservationEvent']['key'],
							'frame_id' => Current::read('Frame.id'),
						)
					),
				)
			);
		?>
		<?php echo $this->NetCommonsForm->hidden('ReservationDeleteActionPlan.is_repeat'); ?>
		<?php echo $this->NetCommonsForm->hidden('ReservationDeleteActionPlan.first_sib_event_id'); ?>
		<?php echo $this->NetCommonsForm->hidden('ReservationDeleteActionPlan.origin_event_id'); ?>
		<?php echo $this->NetCommonsForm->hidden('ReservationDeleteActionPlan.is_recurrence'); ?>

		<div class="panel-body">
			<fieldset>
			<?php
				if ($isRepeat) {
					echo __d('reservations', 'This plan has been repeatedly set. Select the plan that you want to remove from the following items, please press the delete button.<br />It can not be undone once you delete.');
					echo "<div class='alert alert-danger'>";

					$options = array();
					$options['0'] = __d('reservations', 'only this one');
					if (!$isRecurrence) {
						$options['1'] = __d('reservations', 'all after this one');
						$options['2'] = __d('reservations', 'all');
					}
					echo $this->NetCommonsForm->radio('ReservationDeleteActionPlan.edit_rrule', $options,
						array(
							'div' => 'form-inline',
							'value' => '0',
						)
					);
					echo "</div>";
				} else {
					echo __d('reservations', 'Delete event.<br />It can not be undone once you delete.');
					echo $this->NetCommonsForm->hidden('ReservationDeleteActionPlan.edit_rrule', array(
						'value' => '0'
					));
				}
			?>
			</fieldset>
		</div><!--end panel-body-->

		<?php
			//エラー表示。 便宜的に、is_repeatを指定しておく。
			echo $this->NetCommonsForm->error('ReservationDeleteActionPlan.is_repeat');
		?>

		<div class="panel-footer text-center">
		<?php
			$cancelOptions = array(
				'icon' => 'glyphicon-remove',
				'class' => 'btn btn-default btn-workflow',
				//cancel()の後、angularJSイベントのprevendDefault()を実行しないと、
				//Formのsubmitが実行されるので注意すること。
				'ng-click' => "cancel(); \$event.preventDefault()",
				'type' => 'cancel',
			);
			echo $this->Button->button(__d('net_commons', 'Cancel'), $cancelOptions);

			$deleteOptions = array(
				'onclick' => false,
				'ng-click' => 'cancel()',
				'class' => 'btn btn-danger btn-workflow',
			);
			echo $this->Button->delete(__d('net_commons', 'Delete'), '', $deleteOptions);
		?>
		</div><!-- end panel footer -->
		<?php echo $this->NetCommonsForm->end() ?>
	</div><!-- end panel-->
</article>

