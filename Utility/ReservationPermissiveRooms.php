<?php
/**
 * ReservationRruleUtil Utility
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * ReservationPermissiveRooms Utility
 * ε(　　　　 v ﾟωﾟ)　＜要整理対象　というか削除候補
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Utility
 */
class ReservationPermissiveRooms {

/**
 * $roomPermRoles
 *
 * 全空間における施設予約権限情報
 *
 * @var array
 */
	public static $roomPermRoles = array();

/**
 * $backupPermissions
 *
 * 共通処理ワークフロー系のものを通すためのカレントパーミッション
 * 一時すり替え時使用のバックアップ情報
 *
 * @var array
 */
	public static $backupPermissions = array();

/**
 * setRoomPermRoles
 *
 * ルームパーミッション情報を設定する
 *
 * @param array $roomPermRoles roomPermRoles
 * @return void
 */
	public static function setRoomPermRoles($roomPermRoles) {
		self::$roomPermRoles = $roomPermRoles;
	}
/**
 * getPublishableRoomIdList
 *
 * 発行権限を持つルームIDリストを返す
 *
 * @return array
 */
	public static function getPublishableRoomIdList() {
		$rooms = self::getAbleRoomIdList(array('content_publishable_value'));
		return $rooms;
	}
/**
 * getEditableRoomIdList
 *
 * 編集権限を持つルームIDリストを返す
 *
 * @return array
 */
	public static function getEditableRoomIdList() {
		$rooms = self::getAbleRoomIdList(array(
			'content_publishable_value',
			'content_editable_value'
		));
		return $rooms;
	}
/**
 * getCreatableRoomIdList
 *
 * 登録権限を持つルームIDリストを返す
 *
 * @return array
 */
	public static function getCreatableRoomIdList() {
		$rooms = self::getAbleRoomIdList(array(
			'content_publishable_value',
			'content_editable_value',
			'content_creatable_value'
		));
		return $rooms;
	}
/**
 * getAccessibleRoomIdList
 *
 * 参加権限を持つルームIDリストを返す
 *
 * @return array
 */
	public static function getAccessibleRoomIdList() {
		return self::$roomPermRoles['accessibleRoomIds'];
	}
/**
 * getMailEditableRoomIdList
 *
 * 発行権限を持つルームIDリストを返す
 *
 * @return array
 */
	public static function getMailEditableRoomIdList() {
		$rooms = self::getAbleRoomIdList(array('mail_editable_value'));
		return $rooms;
	}
/**
 * getAbleRoomIdList
 *
 * 指定された権限を持つルームのIDのリストを返す
 *
 * @param array $perms 見てほしい権限名
 * @return array
 */
	public static function getAbleRoomIdList($perms) {
		$rooms = array();
		foreach (self::$roomPermRoles['roomInfos'] as $roomId => $roomPerm) {
			foreach ($perms as $perm) {
				if (Hash::get($roomPerm, $perm)) {
					$rooms[$roomId] = $roomId;
				}
			}
		}
		return $rooms;
	}

/**
 * isPublishable
 *
 * 指定されたルームは閲覧者にとって公開権限のあるところか
 *
 * @param int $roomId ルームID
 * @return bool
 */
	public static function isPublishable($roomId) {
		// ルーム情報自体がない
		if (! isset(self::$roomPermRoles['roomInfos'][$roomId])) {
			return false;
		}
		$useWorkflow = Hash::get(self::$roomPermRoles, 'roomInfos.' . $roomId . '.use_workflow');

		if ($useWorkflow == false) {
			// ルームが承認不要の場合は、creatble権限があればよい
			$rooms = self::getCreatableRoomIdList();
		} else {
			// 承認式の場合は
			$rooms = self::getPublishableRoomIdList();
		}
		return isset($rooms[$roomId]);
	}
/**
 * isEditable
 *
 * 指定されたルームは閲覧者にとって編集権限のあるところか
 *
 * @param int $roomId ルームID
 * @return bool
 */
	public static function isEditable($roomId) {
		$rooms = self::getEditableRoomIdList();
		return isset($rooms[$roomId]);
	}
/**
 * isCreatable
 *
 * 指定されたルームは閲覧者にとって投稿権限のあるところか
 *
 * @param int $roomId ルームID
 * @return bool
 */
	public static function isCreatable($roomId) {
		$rooms = self::getCreatableRoomIdList();
		return isset($rooms[$roomId]);
	}

/**
 * setCurrentPermission
 *
 * カレントパーミッションすり変え
 * ワークフロービヘイビアとかを一瞬だますためにカレントのパミッションを
 * 指定されているルームのものにすり替え
 *
 * @param int $roomId ルームID
 * @return void
 */
	public static function setCurrentPermission($roomId) {
		return;
		//if (! empty(self::$backupPermissions)) {
		//	return;
		//}
		//// ここが呼ばれるってことは絶対にroomInfosが絶対あることが前提
		//$useWorkflow = self::$roomPermRoles['roomInfos'][$roomId]['use_workflow'];
		//
		//self::$backupPermissions['current'] = Hash::get(Current::$current, 'Permission');
		//self::$backupPermissions['permission'] = Current::$permission;
		//
		//// 承認不要のときは作成権限があれば発行できる
		//if ($useWorkflow == false) {
		//	Current::$current['Permission']['content_publishable']['value'] =
		//		self::$roomPermRoles['roomInfos'][$roomId]['content_creatable_value'];
		//} else {
		//	Current::$current['Permission']['content_publishable']['value'] =
		//		self::$roomPermRoles['roomInfos'][$roomId]['content_publishable_value'];
		//}
		//Current::$current['Permission']['content_editable']['value'] =
		//	self::$roomPermRoles['roomInfos'][$roomId]['content_editable_value'];
		//Current::$current['Permission']['content_creatable']['value'] =
		//	self::$roomPermRoles['roomInfos'][$roomId]['content_creatable_value'];
		//
		//$pathKey = $roomId . '.Permission.%s.value';
		//Current::$permission = Hash::insert(
		//	Current::$permission,
		//	sprintf($pathKey, 'content_publishable'),
		//	Current::$current['Permission']['content_publishable']['value']
		//);
		//Current::$permission = Hash::insert(
		//	Current::$permission,
		//	sprintf($pathKey, 'content_editable'),
		//	Current::$current['Permission']['content_editable']['value']
		//);
		//Current::$permission = Hash::insert(
		//	Current::$permission,
		//	sprintf($pathKey, 'content_creatable'),
		//	Current::$current['Permission']['content_creatable']['value']
		//);
	}
/**
 * recoverCurrentPermission
 *
 * カレントパーミッション元に戻す
 * ワークフロービヘイビアとかを一瞬だますために設定したカレントのパミッションを
 * 元に戻す
 *
 * @return void
 */
	public static function recoverCurrentPermission() {
		return;
		//Current::$current['Permission'] = Hash::get(self::$backupPermissions, 'current', array());
		//Current::$permission = Hash::get(self::$backupPermissions, 'permission', array());
		//
		//self::$backupPermissions = array();
	}
}
