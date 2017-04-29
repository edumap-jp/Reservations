<?php
/**
 * 予約できる権限Element
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

echo $this->NetCommonsHtml->script('/reservations/js/reservation_locations.js');

//Camel形式に変換
$initializeParams = NetCommonsAppController::camelizeKeyRecursive(array('roles' => $roles));
?>

<div ng-controller="LocationRolePermissions"
	ng-init="initializeRoles(<?php echo h(json_encode($initializeParams, JSON_FORCE_OBJECT)); ?>)">

	<div class="form-group">
		<?php
			echo $this->NetCommonsForm->label(
				'ReservationLocationReservable.location_reservable',
				__d('reservations', 'Authority')
			);
			echo $this->ReservationLocation->checkboxReservablePermission('ReservationLocationReservable');
		?>

		<div class="form-inline reservation-use-private">
			<div class="checkbox checkbox-inline">
				<?php
					echo $this->NetCommonsForm->checkbox(
						'ReservationLocation.use_private',
						['label' => __d('reservations', 'Allow private use?')]
					);
				?>
			</div>
		</div>
	</div>
</div>
