<?php
/**
 * 予約のインポート > 初期画面
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationSettingsComponent', 'Reservations.Controller/Component');
echo $this->element('Reservations.scripts');
?>

<article class="block-setting-body">
	<?php echo $this->BlockTabs->main(
		ReservationSettingsComponent::MAIN_TAB_IMPORT_RESERVATIONS
	); ?>

	<div class="tab-content">

		<article ng-controller='ReservationsDetailEdit' class='block-setting-body'
				ng-init="initialize(<?php echo h(
					json_encode(
						array(
							'frameId' => Current::read('Frame.id'),
							'locations' => $locations,
							'event' => $event,
							'ReservationActionPlan' => $this->request->data['ReservationActionPlan'],
							'userId' =>
								Current::read('User.id')
						)
					)
				); ?>)">

			<div class='panel panel-default'>
				<?php
				$options = array(
					'inputDefaults' => array(
						'label' => false, //以降のinput要素のlabelをデフォルト抑止。必要なら各inputで明示指定する。
						'div' => false, //以降のinput要素のdivをデフォルト抑止。必要なら各inputで明示指定する。
					),
					'class' => 'form-horizontal',
					'type' => 'file',
				);
				echo $this->NetCommonsForm->create(
					'ReservationActionPlan',
					$options
				);

				?>
				<?php echo $this->element('Reservations.ReservationPlans/required_hiddens'); ?>
				<?php
				echo $this->element(
					'Reservations.ReservationPlans/detail_edit_hiddens',
					array(
						'event' => $event,
						'eventSiblings' => $eventSiblings,
						'firstSib' => $firstSib,
					)
				);
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
						!empty($this->request->data['ReservationActionPlan']['first_sib_day'])
					) {
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
						!empty($this->request->data['ReservationActionPlan']['origin_event_recurrence'])
					) {
						$isRecurrence = true;
					}

					$useTime = 'useTime[' . $frameId . ']';
					//$useTime = 'useTime';
					?>


					<div class="reservation-select-location">
						<div class='form-group'>
							<div class='col-xs-12'>
								<?php
								echo $this->NetCommonsForm->label(
									'',
									__d('reservations', 'Location'),
									array(
										'required' => true
									)
								);
								?>
								<div class='col-xs-12'>

									<?php
									//  カテゴリ絞り込み
									$locationCategories = Hash::combine(
										$categories,
										'{n}.Category.id',
										'{n}.CategoriesLanguage.name'
									);
									// 施設の絞り込み, カテゴリなし　を追加
									$locationCategories = Hash::merge(
										[
											'all' => __d(
												'reservations',
												'-- search for institutions --'
											),
											'' => __d('reservations', 'no category')
										],
										$locationCategories
									);
									//$locationCategories = [
									//        0 => 'カテゴリ無し',
									//        1 => '会議室',
									//];
									$this->NetCommonsForm->unlockField(
										'ReservationLocation.category_id'
									);
									$this->NetCommonsForm->unlockField(
										'ReservationActionPlan.location_key'
									);
									echo $this->NetCommonsForm->input(
										'ReservationLocation.category_id',
										[
											'label' => false,
											'options' => $locationCategories,
											'ng-change' => 'selectLocationCategory()',
											'ng-model' => 'locationCategory',
										]
									);
									?>
									<!--施設選択-->
									<?php $locationOptions = Hash::combine(
										$locations,
										'{n}.ReservationLocation.key',
										'{n}.ReservationLocation.location_name'
									); ?>
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
									<a href="" data-toggle="popover" data-placement="bottom"
											title="" data-trigger="focus" data-content="
                        <dl>
                        <dt><?php echo __d('reservations', 'Available'); ?></dt><dd>{{selectLocation.ReservationLocation.openText}}</dd>
                        <dt><?php echo __d('reservations', 'Approver'); ?></dt><dd>{{selectLocation.ReservationLocation.contact}}</dd>
                        </dl>
                        <p>{{selectLocation.ReservationLocation.description}}</p>
						"
											data-original-title="{{selectLocation.ReservationLocation.location_name}}"><?php echo __d(
											'reservations',
											'詳細'
										); ?></a>
									<?php
									$html = '<script type="text/javascript">' .
										'$(function () { $(\'[data-toggle="popover"]\').popover({html: true}) });</script>';
									echo $html;
									?>
								</div>
							</div>
						</div><!-- form-group name="inputStartEndDateTime"おわり -->

						<?php //echo $this->element('Reservations.ReservationPlans/detail_edit_location') ?>

						<?php /* 予定の対象空間選択 */ ?>
						<div class="form-group" data-reservation-name="selectRoomForOpen">
							<div class="col-xs-12" ng-cloak="">
								<?php
								//echo $this->ReservationExposeTarget->makeSelectExposeTargetHtml($event, $frameId, $vars, $exposeRoomOptions, $myself);
								echo $this->NetCommonsForm->label(
									'ReservationActionPlan.plan_room_id' . Inflector::camelize(
										'room_id'
									),
									__d('reservations', 'Category') . $this->element(
										'NetCommons.required'
									)
								);
								//debug($exposeRoomOptions);
								?>
								<?php
								foreach ($locations as $location) {
									$options = Hash::combine(
										$location['ReservableRoom'],
										'{n}.Room.id',
										'{n}.RoomsLanguage.0.name'
									);
									echo $this->NetCommonsForm->select(
										'ReservationActionPlan.plan_room_id',
										$options,
										array(
											'class' => 'form-control select-expose-target',
											'empty' => false,
											'required' => true,
											//value値のoption要素がselectedになる。
											'value' => $this->request->data['ReservationActionPlan']['plan_room_id'],
											'data-frame-id' => $frameId,
											'data-myself' => $myself, // プライベートルーム
											'escape' => false,
											'ng-model' => 'data.ReservationActionPlan.plan_room_id',
											//'ng-change' => 'debugShow()',
											'ng-show' => 'selectLocation.ReservationLocation.id == ' .
												$location['ReservationLocation']['id']
										)
									);
								}
								$this->NetCommonsForm->unlockField(
									'ReservationActionPlan.plan_room_id'
								);
								echo $this->NetCommonsForm->hidden(
									'ReservationActionPlan.plan_room_id',
									['ng-value' => 'data.ReservationActionPlan.plan_room_id']
								);
								?>
								<?php echo $this->NetCommonsForm->error(
									'ReservationActionPlan.plan_room_id'
								); ?>
							</div>
						</div><!-- end form-group-->

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
						<div class="form-group reservation-plan-share_<?php echo $frameId; ?>"
								data-reservation-name="planShare"
								style="display: <?php //echo $dispValue; ?>; margin-top:0.5em;">
							<div class="col-xs-12 col-sm-10 col-sm-offset-2">
								<?php //echo $this->element('Reservations.ReservationPlans/edit_plan_share', array('shareUsers', $shareUsers)); ?>
							</div><!-- col-sm-10おわり -->
						</div>
						<!-- form-groupおわり-->
					</div>


					<div class="reservation-import-rules col-xs-12 col-sm-12">
						<?php
						echo $this->NetCommonsForm->inlineCheckbox(
							'ReservationActionPlan.delete_room_events',
							[
								'label' => __d(
									'reservations',
									'Delete all reservation items and import.'
								)
							]
						);
						echo $this->NetCommonsForm->inlineCheckbox(
							'ReservationActionPlan.skip_duplicate_events',
							[
								'label' => __d(
									'reservations',
									'The duplicate subject name and reservation time are disregarded.'
								)
							]
						);
						?>
					</div>

					<div class="reservation-import-file  col-xs-12 col-sm-12">
						<?php
						echo $this->NetCommonsForm->input(
							'ReservationActionPlan.csv_file',
							[
								'label' => __d(
									'reservations',
									'Please designate CSV file for import.'
								),
								'type' => 'file',
								'error' => false,
							]
						);
						?>
						<?php echo $this->NetCommonsForm->error(
							'ReservationActionPlan.csv_file',
							null,
							['escape' => false]
						); ?>

					</div>

					<div class="text-right">
						<?php echo $this->NetCommonsHtml->link(
							__d('reservations', 'Download format'),
							__d('reservations', '/reservations/locale/eng/reservation_import.csv'),
							[
								'class' =>
									'btn 
						btn-default 
						btn-sm'
							]
						); ?>
						<button type="button" class="btn btn-default btn-sm" data-toggle="modal"
								data-target="#reservationsExplanationsOfItems">
							<?php echo __d('reservations', 'Explanations of items'); ?>
						</button>
					</div>

				</div><!-- panel-bodyを閉じる -->

				<div class="panel-footer text-center" ng-cloak="">

					<?php echo $this->Button->cancelAndSave(
						__d('net_commons', 'Cancel'),
						__d('net_commons', 'OK')

					); ?>

				</div><!--panel-footerの閉じるタグ-->
				<?php echo $this->NetCommonsForm->end(); ?>

			</div><!--end panel-->

			<?php /* コメント一覧 */
			//echo $this->Workflow->comments();
			?>

		</article>


	</div><!--end tab-content-->
</article>


<!-- Button trigger modal -->


<!-- Modal -->
<div class="modal fade" id="reservationsExplanationsOfItems" tabindex="-1" role="dialog"
		aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo __d(
					'net_commons',
					'Close'
				) ?>"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><?php echo __d(
						'reservations',
						'Explanations of items'
					); ?></h4>
			</div>
			<div class="modal-body">
				<p>
					<?php
					echo __d(
						'reservations',
						'To see details of import file item settings, please see below.<br />' .
						'<strong>Further, for itesm that have no settings, enter \'null\' instead of blank spaces.</strong><br />' .
						'Also, in the case of no setting (setting of \'null\') or outside range setting, <span class="text-primary">it is changed to</span> \'no setting\'.'
					); ?>
				</p>
				<table class="table">

					<thead>
					<tr>
						<th><?php echo __d('reservations', 'Item')?></th>
						<th><?php echo __d('reservations', 'Description')?></th>
					</tr>

					</thead>
					<tbody>
					<tr>
						<td><?php echo __d('reservations', 'Title'); ?></td>
						<td>
							<div class="text-danger"><?php echo __d(
									'reservations',
									'* Mandatory item(s)'
								); ?></div>
						</td>
					</tr>
					<tr>
						<td><?php echo __d('reservations', 'All-day event'); ?></td>
						<td>
							<div>
								<?php echo __d('reservations',
									'&lt;Options&gt;<br>0 : Set event time(default)<br>1 : All-day event');?>
							</div>
						</td>
					</tr>
					<tr>
						<td><?php echo __d('reservations', 'Day'); ?></td>
						<td>
							<div class="text-danger"><?php echo __d(
									'reservations',
									'* Mandatory item(s)'
								); ?></div>
							<?php echo __d(
								'reservations',
								'Enter in the form of yyyymmdd.<br>ex）20170607'
							); ?>

						</td>
					</tr>
					<tr>
						<td><?php echo __d('reservations', 'Start time'); ?></td>
						<td><?php echo __d(
								'reservations',
								'Enter in the form of hhnnss.<br>' .
								'ex）090000<br>* When setting all-day event, enter \'null\' instead.'
							); ?>
						</td>
					</tr>
					<tr>
						<td><?php echo __d('reservations', 'End time'); ?></td>
						<td><?php echo __d(
								'reservations',
								'Enter in the form of hhnnss.<br>ex）180000<br>* When' .
							'setting all-day event, enter \'null\' instead.'
							); ?>
						</td>
					</tr>
					<tr>
						<td><?php echo __d('reservations', 'Contact'); ?></td>
						<td><?php echo __d('reservations', '-'); ?></td>
					</tr>
					<tr>
						<td><?php echo __d('reservations', 'Details'); ?></td>
						<td><?php echo __d('reservations', '-'); ?></td>
					</tr>
					</tbody>
				</table>

			</div>
		</div>
	</div>
</div>