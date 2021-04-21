<?php
/**
 * 予定登録 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
echo $this->element('Reservations.scripts');
//$frameId = isset($frameId) ? $frameId : null;
?>

<article ng-controller='ReservationsDetailEdit' class='block-setting-body'
	ng-init="initialize(<?php echo h(json_encode(array('frameId' => Current::read('Frame.id'), 'locations' => $locations, 'event' => $event, 'ReservationActionPlan' => $this->request->data['ReservationActionPlan'], 'userId' =>
			Current::read('User.id')))
	); ?>)">

	<?php /* 画面見出し */ ?>
	<?php echo $this->element('Reservations.ReservationPlans/detail_edit_heading'); ?>

	<div class='panel panel-default'>
		<?php echo $this->element('Reservations.ReservationPlans/edit_form_create'); ?>
		<?php echo $this->element('Reservations.ReservationPlans/required_hiddens'); ?>
		<?php
			echo $this->element('Reservations.ReservationPlans/detail_edit_hiddens', array(
				'event' => $event, 'eventSiblings' => $eventSiblings, 'firstSib' => $firstSib,
			));
		?>
		<div class='panel-body'>
			<?php $this->NetCommonsForm->unlockField('ReservationActionPlan.edit_rrule'); ?>

			<?php
				//変数の初期化を先頭に集める
				$editRrule = true;

				$firstSibYear = $firstSibMonth = $firstSibDay = $firstSibEventId = $firstSibEventKey = 0;
				if (!empty($this->request->data['ReservationActionPlan']['first_sib_event_id']) &&
					!empty($this->request->data['ReservationActionPlan']['first_sib_event_key']) &&
					!empty($this->request->data['ReservationActionPlan']['first_sib_year']) &&
					!empty($this->request->data['ReservationActionPlan']['first_sib_month']) &&
					!empty($this->request->data['ReservationActionPlan']['first_sib_day'])) {
					$firstSibEventId = $this->request->data['ReservationActionPlan']['first_sib_event_id'];
					$firstSibEventKey = $this->request->data['ReservationActionPlan']['first_sib_event_key'];
					$firstSibYear = $this->request->data['ReservationActionPlan']['first_sib_year'];
					$firstSibMonth = $this->request->data['ReservationActionPlan']['first_sib_month'];
					$firstSibDay = $this->request->data['ReservationActionPlan']['first_sib_day'];
				} else {
					if (!empty($firstSib)) {
						$firstSibEventId = $firstSib['ReservationActionPlan']['first_sib_event_id'];
						$firstSibEventKey = $firstSib['ReservationActionPlan']['first_sib_event_key'];
						$firstSibYear = $firstSib['ReservationActionPlan']['first_sib_year'];
						$firstSibMonth = $firstSib['ReservationActionPlan']['first_sib_month'];
						$firstSibDay = $firstSib['ReservationActionPlan']['first_sib_day'];
					}
				}

				$originEventId = 0;
				if (!empty($event)) {
					$originEventId = $event['ReservationEvent']['id'];
				} else {
					if (!empty($this->request->data['ReservationActionPlan']['origin_event_id'])) {
						$originEventId = $this->request->data['ReservationActionPlan']['origin_event_id'];
					}
				}

				$isRecurrence = false;
				if ((!empty($event) && !empty($event['ReservationEvent']['recurrence_event_id'])) ||
					!empty($this->request->data['ReservationActionPlan']['origin_event_recurrence'])) {
					$isRecurrence = true;
				}

				$useTime = 'useTime[' . $frameId . ']';
				//$useTime = 'useTime';
			?>

			<?php /* 繰り返しパターンの場合の繰り返し編集オプション */
			echo $this->ReservationPlanEditRepeatOption->makeEditRepeatOption(
				$eventSiblings,
				$firstSibEventKey, $firstSibYear, $firstSibMonth, $firstSibDay, $isRecurrence);
			?>

			<?php /* タイトル入力 */ ?>
			<div class='form-group' data-reservation-name='inputTitle'>
				<div class='col-xs-12'>
					<?php echo $this->element('Reservations.ReservationPlans/edit_title'); ?>
				</div>
			</div>

			<?php /* 期日指定ラベル＋期間・時間指定のチェックボックス */ ?>
			<div class="form-group" data-reservation-name="checkTime">
				<div class='form-inline col-xs-12'>
					<?php
					echo $this->NetCommonsForm->label('', __d('reservations', 'Setting the date'), array(
						'required' => true));
					?>
					&nbsp;
					<?php
						echo $this->NetCommonsForm->hidden('ReservationActionPlan.enable_time', array(
							'ng-model' => $useTime,
							'ng-init' => (($this->request->data['ReservationActionPlan']['enable_time']) ? ($useTime . ' = true') : ($useTime . ' = false')),
						));
						//echo $this->NetCommonsForm->checkbox('ReservationActionPlan.enable_time', array(
						//	'label' => __d('reservations', 'Setting the time'),
						//	'class' => 'reservation-specify-a-time_' . $frameId,
						//	'div' => false,
						//	'ng-model' => $useTime,
						//	'ng-change' => 'toggleEnableTime(' . $frameId . ')',
						//	'ng-false-value' => 'false',
						//	'ng-true-value' => 'true',
						//	'ng-init' => (($this->request->data['ReservationActionPlan']['enable_time']) ? ($useTime . ' = true') : ($useTime . ' = false')),
						//));
					?>
				</div>
 			</div>

			<?php /* 期日入力（終日／開始、終了）*/ ?>
			<div class='form-group' data-reservation-name='inputStartEndDateTime'>
				<div class='col-xs-12'>
					<?php echo $this->element('Reservations.ReservationPlans/detail_edit_datetime', array('useTime' => $useTime)); ?>
				</div>
			</div>

			<?php /* 繰り返し設定 （この予定のみ変更のときは出さない）*/ ?>
			<div class="form-group" data-reservation-name="inputRruleInfo" ng-hide="editRrule==0">
				<div class="col-xs-12">
					<?php echo $this->element('Reservations.ReservationPlans/detail_edit_repeat_items', array('useTime' => $useTime)); ?>
				</div>
			</div>

			<?php if (empty($event) ||
				$event['ReservationEvent']['created_user'] == Current::read('User.id')): ?>

            <div class='form-group' >
                <div class='col-xs-12'>
					<?php
					echo $this->NetCommonsForm->label('', __d('reservations', 'Location'), array(
						'required' => true));
					?>
                    <div class='col-xs-12'>

                        <?php
							//  カテゴリ絞り込み
							$locationCategories = Hash::combine($categories, '{n}.Category.id', '{n}.CategoriesLanguage.name');
							// 施設の絞り込み, カテゴリなし　を追加
							$locationCategories = Hash::merge(
								[
									'all' => __d('reservations', '-- search for institutions --'),
									'' => __d('reservations', 'no category')
								],
								$locationCategories
							);
							//$locationCategories = [
							//        0 => 'カテゴリ無し',
							//        1 => '会議室',
							//];
							$this->NetCommonsForm->unlockField('ReservationLocation.category_id');
							$this->NetCommonsForm->unlockField('ReservationActionPlan.location_key');
							echo $this->NetCommonsForm->input('ReservationLocation.category_id',
								[
									'label' => false,
									'options' => $locationCategories,
									'ng-change' => 'selectLocationCategory()',
									'ng-model' => 'locationCategory',
								]
							);
						?>

                        <?php $locationOptions = Hash::combine($locations, '{n}.ReservationLocation.key', '{n}.ReservationLocation.location_name'); ?>
						<?php
							echo $this->NetCommonsForm->input(
								'ReservationActionPlan.location_key',
								[
									'label' => false,
									'type' => 'select',
									//'ng-options' =>
											//'location.ReservationLocation.location_name for location in data.locations track by location.ReservationLocation.key',
									'ng-options' =>
											'location.ReservationLocation.location_name for location in locationOptions track by location.ReservationLocation.key',
									//'ng-init' => 'ReservationActionPlan.location_key = \'' .
									//	$this->request->data['ReservationActionPlan']['location_key']
									//	. '\'',
									'ng-init' => 'setLocationKey(\'' . $this->request->data['ReservationActionPlan']['location_key'] . '\')',
									//'ng-model' => 'ReservationActionPlan.location_key',
									'ng-model' => 'selectLocation',
									// optionsを指定しないとSecurityComponentでBlackHole送りになる
									'options' => $locationOptions,
									'ng-change' => 'changeLocation()',
								]
							);
						?>
                        <?php echo __d('reservations', '[Available]'); ?>
						{{selectLocation.ReservationLocation.openText}}
                        <a href="" id="reservation-location-detail-popover-link" data-toggle="popover" data-placement="bottom" title="" data-trigger="focus"
							data-original-title="{{selectLocation.ReservationLocation.location_name}}"><?php echo __d('reservations', '詳細'); ?></a>

						<?php // 施設詳細popover ?>
						<div id="reservation-location-popover" class="hide">
							<strong><?php echo __d('reservations', 'Available'); ?></strong> {{selectLocation.ReservationLocation.openText}}<br>
							<strong><?php echo __d('reservations', 'Approver'); ?></strong><br>
							<ul>
								<li ng-repeat="userName in selectLocation.approvalUserNames">{{userName}}</li>
							</ul>
							<div ng-bind-html="selectLocation.ReservationLocation.detail|ncHtmlContent"></div>
						</div>
						<script type="text/javascript">
                          $(function() {
                            $('#reservation-location-detail-popover-link').popover({
                              html:true,
                              content:function() {
                                return $('#reservation-location-popover').html();
                              }

                            })
                          })
						</script>

					</div>
                </div>
            </div>

			<?php //echo $this->element('Reservations.ReservationPlans/detail_edit_location') ?>

			<?php /* 予定の対象空間選択 */ ?>
			<div class="form-group" data-reservation-name="selectRoomForOpen">
				<div class="col-xs-12" ng-cloak="">
					<?php
					echo $this->NetCommonsForm->label(
						'ReservationActionPlan.plan_room_id' . Inflector::camelize('room_id'),
						__d('reservations', 'Category') . $this->element('NetCommons.required'));
					?>

					<?php
					echo $this->NetCommonsForm->select('ReservationActionPlan.plan_room_id',
						[], array(
							'ng-init' => sprintf(
								'initReservableRooms(%s, %s)',
								$defaultPublishableRooms,
								$selectedRoom
							),
							'ng-options' =>
								'room.name for room in roomList track by room.roomId',
							'class' => 'form-control select-expose-target',
							'empty' => false,
							'required' => true,
							//value値のoption要素がselectedになる。
							//'value' => $this->request->data['ReservationActionPlan']['plan_room_id'],
							'data-frame-id' => $frameId,
							'data-myself' => $myself, // プライベートルーム
							'escape' => false,
							'ng-model' => 'selectedRoom',
						));


					?>

					<?php
					$this->NetCommonsForm->unlockField('ReservationActionPlan.plan_room_id');
					?>
					<?php echo $this->NetCommonsForm->error('ReservationActionPlan.plan_room_id'); ?>
				</div>
			</div>

			<?php /* 予定の共有設定 */ ?>
			<?php
				//$dispValue = 'none';
				//if (!empty($myself)) {
				//	if ($this->request->data['ReservationActionPlan']['plan_room_id'] == $myself) {
				//		$dispValue = 'block';
				//	} else {
				//		$keys = array_keys($exposeRoomOptions);
				//		if (array_shift($keys) == $myself) {
				//			//ルーム選択肢が１つだけで、それがプライベートの時の、特例対応
				//			$dispValue = 'block';
				//		}
				//	}
				//}
			?>
			<div class="form-group reservation-plan-share_<?php echo $frameId; ?>" data-reservation-name="planShare"
				 style="display: <?php //echo $dispValue; ?>; margin-top:0.5em;">
				<div class="col-xs-12 col-sm-10 col-sm-offset-2">
					<?php //echo $this->element('Reservations.ReservationPlans/edit_plan_share', array('shareUsers', $shareUsers)); ?>
				</div>
			</div>


			<?php else: // 編集中のユーザと作成者が異なる（つまり承認者による編集）?>
				<div class="col-xs-12 col-sm-12">
					<?php
					echo $this->NetCommonsForm->hidden('ReservationActionPlan.plan_room_id');
					echo $this->NetCommonsForm->hidden('ReservationActionPlan.location_key');

					$ReservationLocation = ClassRegistry::init('Reservations.ReservationLocation');
					$locationKey = $this->request->data['ReservationActionPlan']['location_key'];
					$result = $ReservationLocation->getByKey($locationKey);
					$thisLocation = [
						$locationKey => $result['ReservationLocation']['location_name']
					];
					echo $this->NetCommonsForm->input('ReservationActionPlan.location_key', [
						'type' => 'select',
						'disabled',
						'options' => $thisLocation,
						'label' => __d('reservations', 'Location')
					]);

					$Room = ClassRegistry::init('Rooms.Room');
					$roomId = $this->request->data['ReservationActionPlan']['plan_room_id'];
					$result = $Room->findById($roomId);
					$thisRoom = [
						$roomId => $result['RoomsLanguage'][0]['name']
					];

					echo $this->NetCommonsForm->input('ReservationActionPlan.plan_room_id', [
						'type' => 'select',
						'disabled',
						'options' => $thisRoom,
						'label' => __d('reservations', 'Category')
					]);

					?>
				</div>
			<?php endif?>


			<?php /* メール通知設定 */ ?>
			<?php echo $this->element('Reservations.ReservationPlans/detail_edit_mail'); ?>

			<br />

			<?php /* その他詳細設定 */ ?>
			<div class="form-group">
				<div class="col-xs-12">
					<?php echo $this->element('Reservations.ReservationPlans/detail_edit_etc_details'); ?>
				</div>
			</div>

			<?php /* コメント入力 */ ?>
			<hr />
			<div data-reservation-name="inputCommentArea">
				<div class="col-xs-12">
					<?php echo $this->Workflow->inputComment('ReservationEvent.status'); ?>
				</div>
			</div>

		</div>

		<div class="panel-footer text-center" ng-cloak="">

			<?php echo $this->ReservationPlan->makeEditButtonHtml('ReservationActionPlan.status', $vars, $event); ?>

		</div>
	<?php echo $this->NetCommonsForm->end(); ?>

	<?php if (isset($event['ReservationEvent']) && ($this->request->params['action'] === 'edit' && $this->ReservationWorkflow->canDelete($event))) : ?>
		<div class="panel-footer text-right">
			<?php
			echo $this->element('Reservations.ReservationPlans/delete_form', array(
				'frameId' => $frameId,
				'event' => $event,
				'capForView' => $capForView,
				'eventSiblings' => $eventSiblings,
				'firstSib' => $firstSib,
				'firstSibYear' => $firstSibYear,
				'firstSibMonth' => $firstSibMonth,
				'firstSibDay' => $firstSibDay,
				'firstSibEventId' => $firstSibEventId,
				'originEventId' => $originEventId,
				'isRecurrence' => $isRecurrence,
			));
			?>
		</div>
	<?php endif; ?>

	</div>

	<?php /* コメント一覧 */
		echo $this->Workflow->comments();
	?>

</article>
