<?php
/**
 * ReservationPlanValidate Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');
App::uses('ReservationPermissiveRooms', 'Reservations.Utility');

/**
 * ReservationPlanValidate Behavior
 *
 * @package  Reservations\Reservations\Model\Befavior
 * @author Allcreator <info@allcreator.net>
 */
class ReservationPlanValidateBehavior extends ModelBehavior {

/**
 * allowedRoomId
 *
 * 許可されたルームIDかどうか
 *
 * @param Model $model モデル変数
 * @param array $check 入力配列（room_id）
 * @return bool 成功時true, 失敗時false
 */
	//public function allowedRoomId(Model $model, $check) {
	//	$value = array_values($check);
	//	$value = $value[0];
	//	//return (in_array($value, ReservationPermissiveRooms::getCreatableRoomIdList()));
	//}

/**
 * allowedEmailSendTiming
 *
 * 許可されたメール通知タイミングかどうか
 *
 * @param Model $model モデル変数
 * @param array $check 入力配列（email_send_timing）
 * @return bool 成功時true, 失敗時false
 */
	public function allowedEmailSendTiming(Model $model, $check) {
		$value = array_values($check);
		$value = $value[0];

		//メール通知タイミング一覧のoptions配列を取得
		$emailTimingOptions = $model->getNoticeEmailOption();
		return in_array($value, array_keys($emailTimingOptions));
	}
}
