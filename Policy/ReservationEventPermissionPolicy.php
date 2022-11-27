<?php
/**
 * ReservationEventPermissionPolicy.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Class ReservationEventPermissionPolicy
 */
class ReservationEventPermissionPolicy {

/**
 * @var array ReservationEvent data
 */
	protected $_event;

/**
 * 施設のキャッシュ
 *
 * @var array
 */
	protected $_locations = [];

/**
 * アクセス可能なルームIDリストのキャッシュ
 *
 * @var array
 */
	protected $_readableRoomIds = [];

/**
 * @var ReservationLocationReservable ReadableLocationReservable model
 */
	private $__reservationLocationReservable;

/**
 * ReservationEventPermissionPolicy constructor.
 *
 * @param array $event ReservationEvnet Data
 */
	public function __construct($event) {
		$this->_event = $event;
		$this->__reservationLocationReservable = ClassRegistry::init(
			'Reservations.ReservationLocationReservable'
		);
	}

/**
 * 編集できるか
 *
 * @param int $userId ユーザID
 * @return bool
 */
	public function canEdit($userId) {
		$data = $this->_event;
		$location = $this->_getLocationByKey($data['ReservationEvent']['location_key']);
		if ($userId == $data['ReservationEvent']['created_user']) {
			// 予約権限があれば編集可
			return $this->__reservationLocationReservable->isReservableByLocation($location);
		} else {
			// 他の人の予約
			// ルーム管理者なら編集可能にしていたが、システム管理者、サイト管理者のみ編集可能にする。
			// サイト管理を使えるユーザなら編集可能。
			if (Current::allowSystemPlugin('site_manager')) {
				return true;
			}
			//// block_permission_editable なら見られる
			//if (Current::read('Room.space_id') != Space::PRIVATE_SPACE_ID) {
			//	if (Current::read('Permission.block_permission_editable.value')) {
			//		return true;
			//	}
			//}

			$approvalUserIds = $location['approvalUserIds'];

			if (in_array($userId, $approvalUserIds)) {
				// 承認者はどのルームでも編集可能（でないと承認できない）
				return true;
			}
			// 承認者でなければ編集不可
			return false;
		}
	}

/**
 * 詳細を閲覧できるか
 *
 * @param int $userId ユーザID
 * @return bool
 */
	public function canRead($userId) {
		// ε(　　　　 v ﾟωﾟ)　＜こういう判定はPolicy ReadableReservationPolicy classとかにした方がいいのかも
		// （DBアクセスあるのはイヤだけど）
		// ε(　　　　 v ﾟωﾟ)　＜ というかアクセスユーザのアクセス可能なルームぐらいSessionにあってもいい気もする

		//$userId = Current::read('User.id');
		$data = $this->_event;
		if ($userId == $data['ReservationEvent']['created_user']) {
			// 自分の予約は無条件に見られる。
			return true;
		}
		// 他の人の予約

		// block_permission_editable なら見られる
		if (Current::read('Room.space_id') != Space::PRIVATE_SPACE_ID) {
			if (Current::read('Permission.block_permission_editable.value')) {
				return true;
			}
		}

		$location = $this->_getLocationByKey($data['ReservationEvent']['location_key']);
		$approvalUserIds = $location['approvalUserIds'];

		if (in_array($userId, $approvalUserIds)) {
			// 承認者はどのルームでも見られる（でないと承認できない）
			return true;
		}

		// 以降承認者以外の場合のチェック
		$publishedRoomId = $data['ReservationEvent']['room_id'];
		if (!$this->_isReadablePublishedRoomId($publishedRoomId, $userId, $location)) {
			return false;
		}

		$status = $data['ReservationEvent']['status'];
		if ($status == WorkflowComponent::STATUS_PUBLISHED) {
			//「公開」になってる予約は承認者でなくても見られる
			return true;
		}

		// まだ公開になってない予約は見られない
		return false;
	}

/**
 * 予約で公開先となってるRoomIdにたいしてアクセスできるか？
 *
 * @param int $publishedRoomId 予約の公開先ルーム　0だと指定無し
 * @param int $userId ユーザID
 * @param array $location 施設データ
 * @return bool
 */
	protected function _isReadablePublishedRoomId($publishedRoomId, $userId, $location) {
		$readableRooomIds = $this->_getReadableRoomIds($userId);
		//$publishedRoomId = $data['ReservationEvent']['room_id'];
		if ($publishedRoomId == 0) {
			// 指定無し
			if (!$location['ReservationLocation']['use_all_rooms']) {
				//　施設で利用可能なルームのいずれにもアクセスできないなら見られない
				$LocationsRoom = ClassRegistry::init('Reservations.ReservationLocationsRoom');
				$count = $LocationsRoom->find('count', [
					'conditions' => [
						'reservation_location_key' => $location['ReservationLocation']['key'],
						'room_id' => $readableRooomIds
					]
				]);
				if ($count == 0) {
					// ユーザがアクセスできるルームで利用可のグループ無し
					return false;
				}
			}
		} elseif (!in_array($publishedRoomId, $readableRooomIds)) {
			// 承認者でないなら→予約の公開ルームにアクセス権無いと見られない
			return false;
		}
		return true;
	}

/**
 * 閲覧可能なルームのIDリストを返す
 *
 * @param int $userId ユーザID
 * @return array ルームIDリスト
 */
	protected function _getReadableRoomIds($userId) {
		if (!isset($this->_readableRoomIds[$userId])) {
			$this->Room = ClassRegistry::init('Rooms.Room');
			$condition = $this->Room->getReadableRoomsConditions([], $userId);
			$readableRooms = $this->Room->find('all', $condition);
			$readableRooomIds = Hash::combine($readableRooms, '{n}.Room.id', '{n}.Room.id');

			$this->_readableRoomIds[$userId] = $readableRooomIds;
		}
		return $this->_readableRoomIds[$userId];
	}

/**
 * 施設情報を返す
 *
 * @param string $locationKey 施設キー
 * @return array ReservationLocation data
 */
	protected function _getLocationByKey($locationKey) {
		// 何度も同じ施設で確認だすからキャッシュしとく
		if (!isset($this->_locations[$locationKey])) {
			$this->ReservationLocation = ClassRegistry::init('Reservations.ReservationLocation');
			$this->_locations[$locationKey] = $this->ReservationLocation->getByKey($locationKey);
		}
		return $this->_locations[$locationKey];
	}

}
