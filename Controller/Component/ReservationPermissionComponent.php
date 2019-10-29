<?php
/**
 * ReservationPermission Component
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('Component', 'Controller');
App::uses('NetCommonsTime', 'NetCommons.Utility');
App::uses('Block', 'Blocks.Model');
App::uses('ReservationPermissiveRooms', 'Reservations.Utility');
App::uses('ReservationEventPermissionPolicy', 'Reservations.Policy');

/**
 * ReservationPermission Component
 *
 * リクエストされた予約へのアクセス許可を、<br>
 * 指定された予約の対象空間、共有人物、ステータス、
 * および閲覧者の権限から判定します。<br>
 *
 * @author Allcreator <info@allcreator.net>
 * @package Reservations\Reservations\Controller\Component
 */
class ReservationPermissionComponent extends Component {

/**
 * Called after the Controller::beforeFilter() and before the controller action
 *
 * @param Controller $controller Controller with components to startup
 * @return void
 * @throws ForbiddenException
 */
	public function startup(Controller $controller) {
		$this->controller = $controller;

		$permissionPolicy = new ReservationEventPermissionPolicy($this->controller->eventData);
		// add -> どこか一つでもcreatableな空間を持っている人なら
		// view -> 対象の空間に参加しているなら
		//         ただし、対象空間がプライベートのときに限り、共有者となっているなら
		// edit -> 対象空間での編集権限を持っているか、対象予定の作成者なら
		// delete -> 対象空間での編集権限を持っているか、対象予定の作成者なら

		// add いずれかの施設に予約できれば
		// view 公開されてるルームのアクセス権あるか、その施設の承認者なら
		// edit 予約した本人か　承認者
		// delete 予約した本人か　承認者
		$userId = Current::read('User.id');
		switch ($controller->action) {
			case 'add':
				if ($this->_canCreate()) {
					return;
				}
				break;
			case 'edit':
			case 'delete':
				if ($permissionPolicy->canEdit($userId)) {
					return;
				}
				//if ($this->_canEditEvent()) {
				//	return;
				//}
				break;
			case 'view':
				if ($permissionPolicy->canRead($userId)) {
					return;
				}
				break;
		}
		// チェックで引っかかってしまったらForbidden
		throw new ForbiddenException();
	}

/**
 * 予約可能な施設がひとつでもあるか
 *
 * @return bool
 */
	protected function _canCreate() {
		$locations = $this->controller->ReservationLocation->getReservableLocations();
		if (count($locations)) {
			// 予約可能な施設がひとつでもあればadd権限あり
			return true;
		}
		return false;
		//$rooms = ReservationPermissiveRooms::getCreatableRoomIdList();
		//if (empty($rooms)) {
		//	return false;
		//}
		//return true;
	}
/**
 * 対象のイベントは存在するか
 *
 * @return bool
 */
	protected function _existEvent() {
		if (empty($this->controller->eventData)) {
			return false;
		}
		return true;
	}

}
