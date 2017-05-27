<?php
/**
 * ReservationEventPermissionPolicy.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
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
 * ReservationEventPermissionPolicy constructor.
 *
 * @param array $event ReservationEvnet Data
 */
	public function __construct($event) {
		$this->_event = $event;
	}

/**
 * 編集できるか
 *
 * @param int $userId ユーザID
 * @return bool
 */
	public function canEdit($userId) {
		$data = $this->_event;
		if ($userId == $data['ReservationEvent']['created_user']) {
			// 自分の予約は無条件に編集可能
			return true;
		} else {
			// 他の人の予約
			$location = $this->_getLocationByKey($data['ReservationEvent']['location_key']);
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
		} else {
			// 他の人の予約

			$location = $this->_getLocationByKey($data['ReservationEvent']['location_key']);
			$approvalUserIds = $location['approvalUserIds'];

			if (in_array($userId, $approvalUserIds)) {
				// 承認者はどのルームでも見られる（でないと承認できない）
				return true;
			}

			// 以降承認者以外の場合のチェック

			$readableRooomIds = $this->_getReadableRoomIds($userId);
			$publishedRoomId = $data['ReservationEvent']['room_id'];

			if (!in_array($publishedRoomId, $readableRooomIds)) {
				// 承認者でないなら→予約の公開ルームにアクセス権無いと見られない
				return false;
			}

			$status = $data['ReservationEvent']['status'];
			if ($status == WorkflowComponent::STATUS_PUBLISHED) {
				//「公開」になってない予約は承認者でなくても見られる
				return true;
			}

			// まだ公開になってない予約は見られない
			return false;
		}
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
			$readableRooomIds = Hash::combine($readableRooms, '{n}.Room.id');

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
