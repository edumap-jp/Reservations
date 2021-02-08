<?php
/**
 * 公開対象のルーム選択のデフォルト選択肢を取得するためのコンポーネント
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('Component', 'Controller');

/**
 * Class ReservationSelectRoomComponent
 */
class ReservationSelectRoomComponent extends Component {

/**
 * @var ReservationLocationsRoom
 */
	private $__reservationLocationsRoom;

/**
 * initialize
 *
 * @param Controller $controller コントローラ
 * @return void
 */
	public function initialize(Controller $controller) {
		parent::initialize($controller);
		$this->__reservationLocationsRoom = ClassRegistry::init('Reservations.ReservationLocationsRoom');
	}

/**
 * __getSelectedRoom
 *
 * @param array $defaultRooms デフォルトで表示するルームリスト
 * @param int $selectedRoomId 選択されたルームID
 * @return array
 */
	public function getSelectedRoom(array $defaultRooms, int $selectedRoomId) {
		$roomIds = array_column($defaultRooms, 'roomId');
		$arrayIndex = array_search($selectedRoomId, $roomIds);
		$selectedRoom = $defaultRooms[$arrayIndex];
		return $selectedRoom;
	}

/**
 * __getDefaultPublishableRooms
 *
 * @param array $locations 施設リスト
 * @param string|null $defaultLocationKey 選択された施設のキー
 * @param string|int $userId ユーザID
 * @return array
 */
	public function getDefaultPublishableRooms(
		array $locations,
		$defaultLocationKey,
		$userId
	) : array {
		if ($defaultLocationKey) {
			$locationKeys = array_column(array_column($locations, 'ReservationLocation'), 'key');
			$keyPosition = array_search($defaultLocationKey, $locationKeys);
			$defaultLocation = $locations[$keyPosition];
		} else {
			$defaultLocation = current($locations);
		}

		// デフォルト施設だけ公開対象ルーム情報を取得しておく
		$publishableRooms = $this->__reservationLocationsRoom->getReservableRoomsByLocationAndUserId(
			$defaultLocation,
			$userId
		);
		$defaultRooms = [];
		$notSpecified = [
			'roomId' => 0,
			'name' => __d('reservations', '-- not specified --')
		];
		$defaultRooms[] = $notSpecified;
		foreach ($publishableRooms as $room) {
			$defaultRooms[] = [
				'roomId' => $room['Room']['id'],
				'name' => $room['RoomsLanguage'][0]['name']
			];
		}
		return $defaultRooms;
	}
}