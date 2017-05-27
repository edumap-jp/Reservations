<?php
/**
 * ReservationWorkflow Helper
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppHelper', 'View/Helper');
App::uses('ReservationPermissiveRooms', 'Reservations.Utility');

/**
 * Reservation Workflow Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservation\View\Helper
 */
class ReservationWorkflowHelper extends AppHelper {

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
 * Check deletable permission
 *
 * @param array $data Model data
 * @return bool True is deletable data
 */
	public function canDelete($data) {
		$model = ClassRegistry::init('Reservations.ReservationEvent');
		$canDel = $model->canDeleteContent($data);
		return $canDel;
	}

/**
 * 予約データの詳細を閲覧する権限があるか
 *
 * @param array $data event data
 * @return bool
 */
	public function canRead($data) {
		// ε(　　　　 v ﾟωﾟ)　＜こういう判定はPolicy ReadableReservationPolicy classとかにした方がいいのかも
		// （DBアクセスあるのはイヤだけど）
		// ε(　　　　 v ﾟωﾟ)　＜ というかアクセスユーザのアクセス可能なルームぐらいSessionにあってもいい気もする
		// ε(　　　　 v ﾟωﾟ)　＜ locations 情報も 一度取得したらオンメモリかなぁ　getByKey
		// ε(　　　　 v ﾟωﾟ)　＜ ReservationLocation::getByKey($key)とかでキャッシュしとくのはありかなぁ

		$userId = Current::read('User.id');

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
			$this->ReservationLocationsApprovalUser = ClassRegistry::init(
				'Reservations.ReservationLocationsApprovalUser'
			);
			$conditions = [
				'ReservationLocation.key' => $locationKey,
			];
			$location = $this->ReservationLocation->find(
				'first',
				[
					'conditions' =>
						$conditions
				]
			);
			$location['approvalUserIds'] =
				$this->ReservationLocationsApprovalUser->getApprovalUserIdsByLocation($location);
			$this->_locations[$locationKey] = $location;

		}
		return $this->_locations[$locationKey];
	}
}
