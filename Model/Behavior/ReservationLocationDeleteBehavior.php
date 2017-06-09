<?php
/**
 * ReservationLocationDeleteBehavior.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */
App::uses('ReservationAppBehavior', 'Reservations.Model/Behavior');

/**
 * Class ReservationLocationDeleteBehavior
 */
class ReservationLocationDeleteBehavior extends ReservationAppBehavior {

/**
 * 施設削除にともなう施設関係データの削除
 *
 * @param ReservationLocation $model ReservationLocation
 * @param string $locationKey 施設キー
 * @throws InternalErrorException
 * @return void
 */
	public function deleteLocationData(ReservationLocation $model, $locationKey) {
		// ReservationLocation 削除
		$conditions = [
			$model->alias . '.key' => $locationKey
		];
		if (!$model->deleteAll($conditions)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		// ReservationLocationsRoom 削除
		$conditions = [
			$model->ReservationLocationsRoom->alias . '.reservation_location_key' => $locationKey
		];
		if (!$model->ReservationLocationsRoom->deleteAll($conditions)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		// ReservationLocationReservable 削除
		$conditions = [
			$model->ReservationLocationReservable->alias . '.location_key' => $locationKey
		];
		if (!$model->ReservationLocationReservable->deleteAll($conditions)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		// ReservationLocationsApprovalUser 削除
		$conditions = [
			$model->ReservationLocationsApprovalUser->alias . '.location_key' => $locationKey
		];
		if (!$model->ReservationLocationsApprovalUser->deleteAll($conditions)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}

/**
 * 施設削除にともなう予約データの削除
 *
 * @param ReservationLocation $model ReservationLocation
 * @param string $locationKey 施設キー
 * @throws InternalErrorException
 * @return void
 */
	public function deleteEventData(ReservationLocation $model, $locationKey) {
		// 削除するReservationEvent.id 取得
		$conditions = [
			$model->ReservationEvent->alias . '.location_key' => $locationKey
		];
		$reserveIds = $model->ReservationEvent->find(
			'list',
			[
				'recursive' => -1,
				'conditions' => $conditions
			]
		);
		$reserveIds = array_values($reserveIds);

		//// ReservationEventContent 削除
		//$conditions = [
		//	$model->ReservationEventContent->alias . '.reservation_event_id' => $reserveIds
		//];
		//if (!$model->ReservationEventContent->deleteAll($conditions)) {
		//	throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		//}

		// ReservationEventShareUser 削除
		$conditions = [
			$model->ReservationEventShareUser->alias . '.reservation_event_id' => $reserveIds
		];
		if (!$model->ReservationEventShareUser->deleteAll($conditions)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		// ReservationEvent 削除
		$conditions = [
			$model->ReservationEvent->alias . '.location_key' => $locationKey
		];
		if (!$model->ReservationEvent->deleteAll($conditions)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}
}