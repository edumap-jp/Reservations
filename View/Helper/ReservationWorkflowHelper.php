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
App::uses('ReservationEventPermissionPolicy', 'Reservations.Policy');

/**
 * Reservation Workflow Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservation\View\Helper
 */
class ReservationWorkflowHelper extends AppHelper {

/**
 * Check deletable permission
 *
 * @param array $data Model data
 * @return bool True is deletable data
 */
	public function canDelete($data) {
		return $this->canEdit($data);
	}

/**
 * 予約を編集できるか
 *
 * @param array $data ReservationEvent data
 * @return bool
 */
	public function canEdit($data) {
		$userId = Current::read('User.id');

		$permissionPolicy = new ReservationEventPermissionPolicy($data);
		return $permissionPolicy->canEdit($userId);
	}

/**
 * 予約データの詳細を閲覧する権限があるか
 *
 * @param array $data event data
 * @return bool
 */
	public function canRead($data) {
		// ε(　　　　 v ﾟωﾟ)　＜ というかアクセスユーザのアクセス可能なルームぐらいSessionにあってもいい気もする
		$userId = Current::read('User.id');
		$permissionPolicy = new ReservationEventPermissionPolicy($data);
		return $permissionPolicy->canRead($userId);
	}
}
