<?php
/**
 * 施設設定 > 並び替え
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationSettingsComponent', 'Reservations.Controller/Component');

echo $this->NetCommonsHtml->script('/reservations/js/reservations.js');

$reservationLocations = NetCommonsAppController::camelizeKeyRecursive($this->data['ReservationLocations']);
//debug($reservationLocations);
$reservationLocationsMap = array_flip(
		array_keys(Hash::combine($reservationLocations, '{n}.reservationLocation.key')));
//debug($reservationLocationsMap);
?>
<article class="block-setting-body">
	<?php echo $this->BlockTabs->main(ReservationSettingsComponent::MAIN_TAB_LOCATION_SETTING); ?>

	<div class="tab-content">
		<div class="nc-content-list" ng-controller="ReservationLocationOrders" class="nc-content-list"
			 ng-init="initialize(<?php echo h(json_encode(['reservationLocations' => $reservationLocations, 'reservationLocationsMap' => $reservationLocationsMap])); ?>)"
			 ng-cloak>

			<article>
				<?php echo $this->NetCommonsForm->create('ReservationLocation'); ?>
					<?php foreach ($reservationLocationsMap as $key => $value) : ?>
						<?php echo $this->NetCommonsForm->hidden('ReservationLocations.' . $value . '.ReservationLocation.id'); ?>
						<?php echo $this->NetCommonsForm->hidden('ReservationLocations.' . $value . '.ReservationLocation.key'); ?>
						<?php $this->NetCommonsForm->unlockField('ReservationLocations.' . $value . '.ReservationLocation.weight'); ?>
					<?php endforeach; ?>

					<div ng-hide="reservationLocations.length">
						<p><?php echo __d('net_commons', 'Not found.'); ?></p>
					</div>

					<div class="table-responsive" ng-show="reservationLocations.length">
						<table class="table table-condensed">
							<thead>
								<tr>
									<th></th>
									<th>
										<?php echo $this->Paginator->sort('ReservationLocation.location_name', __d('reservations', 'Location name')); ?>
									</th>
									<th>
										<?php echo $this->Paginator->sort('CategoryOrder.weight', __d('categories', 'Category')); ?>
									</th>
									<th>
										<?php echo __d('reservations', 'Available datetime') ?>
									</th>
								</tr>
							</thead>
							<tbody>
								<tr ng-repeat="q in reservationLocations track by $index">
									<td>
										<div class="text-nowrap">
											<button type="button" class="btn btn-default btn-sm"
													ng-click="move('up', $index)" ng-disabled="$first">
												<span class="glyphicon glyphicon-arrow-up"></span>
											</button>

											<button type="button" class="btn btn-default btn-sm"
													ng-click="move('down', $index)" ng-disabled="$last">
												<span class="glyphicon glyphicon-arrow-down"></span>
											</button>

											<input type="hidden"
												name="data[ReservationLocations][{{getIndex(q.reservationLocation.key)}}][ReservationLocation][weight]"
												ng-value="{{$index + 1}}">
										</div>
									</td>
									<td>
										{{q.reservationLocation.locationName}}
									</td>
									<td>
										<div class="text-nowrap">
											{{q.categoriesLanguage.name}}
										</div>
									</td>
									<td>
										{{q.reservationLocation.openText}}
									</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="text-center">
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
			</article>
		</div>
	</div>
</article>
