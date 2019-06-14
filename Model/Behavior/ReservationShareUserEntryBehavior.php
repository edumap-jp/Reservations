<?php
/**
 * ReservationShareUserEntry Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationAppBehavior', 'Reservations.Model/Behavior');

/**
 * ReservationShareUserEntryBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model\Behavior
 */
class ReservationShareUserEntryBehavior extends ReservationAppBehavior {

/**
 * Default settings
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2015, NetCommons Project
 */
	protected $_defaults = array(
	);

/**
 * 共有ユーザ情報の登録
 *
 * @param Model $model 実際のモデル名
 * @param array $shareUsers shareUsers
 * @param int $eventId eventId
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return void
 */
	public function insertShareUsers(Model $model, $shareUsers, $eventId,
		$createdUserWhenUpd = null) {
		if (!(isset($model->ReservationEventShareUser))) {
			$model->ReservationEventShareUser = ClassRegistry::init(
				'Reservations.ReservationEventShareUser', true
			);
			//$model->loadModels(['ReservationEventShareUser' => 'Reservation.ReservationEventShareUser']);
		}
		if (empty($shareUsers)) {
			//CakeLog::debug("DBG: shareUsersは空です");
			return;
		}

		//無名関数（クロージャ）は変数を親のスコープから引き継ぐことができ、引き継ぐ変数は、use で渡す必要があり。
		$func = function ($value) use($eventId, $createdUserWhenUpd) {
			$elm = array(
				'reservation_event_id' => $eventId,
				'share_user' => intval($value),
			);

			//施設予約独自の例外追加１）
			//変更後の公開ルームidが、「元予定生成者の＊ルーム」から「編集者・承認者(＝ログイン者）の
			//プライベート」に変化していた場合、created_userを、元予定生成者「から」編集者・承認者(＝ログイン者）
			//「へ」に変更すること。＝＞これを考慮したcreatedUserWhenUpdを使えばよい。
			if ($createdUserWhenUpd !== null) {
				$elm['created_user'] = $createdUserWhenUpd;
			}
			//CakeLog::debug("DBG: elm[" . print_r($elm, true) . "]");
			return $elm;
		};

		$shareUserData = array();
		$shareUserData[$model->ReservationEventShareUser->alias] = array_map($func, $shareUsers);
		$model->ReservationEventShareUser->saveAll(
			$shareUserData[$model->ReservationEventShareUser->alias]
		);
	}

/**
 * 共有ユーザ情報の登録
 *
 * @param Model $model 実際のモデル名
 * @param array $shareUsers shareUsers
 * @param int $eventId eventId
 * @param array $oldShareUserDataAry oldShareUserDataAry
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return void
 */
	public function updateShareUsers(Model $model, $shareUsers, $eventId, $oldShareUserDataAry,
		$createdUserWhenUpd = null) {
		//CakeLog::debug("DBG: IN updateShareUsers(). shareUsers[" .
		//	print_r($shareUsers, true) . "] eventId[" . $eventId .
		//	"] oldShareUserDataAry[" . print_r($oldShareUserDataAry, true) . "]");

		if (!is_array($shareUsers)) {
			$shareUsers = array();
		}
		if (!is_array($oldShareUserDataAry)) {
			$oldShareUserDataAry = array();
		}

		//CakeLog::debug("DBG: oldShareUserDataAry1[" . print_r($oldShareUserDataAry, true) . "]");
		$oldShareUsers = Hash::extract($oldShareUserDataAry, '{n}.share_user');
		//CakeLog::debug("DBG: oldShareUserUsers[" . print_r($oldShareUsers, true) . "]");

		sort($shareUsers, SORT_NUMERIC);		//新しい共有ユーザ群
		sort($oldShareUsers, SORT_NUMERIC);	//古い共有ユーザ群

		//新しい共有ユーザ群より、追加すべきユーザ群を抽出
		$insShareUsers = array_diff($shareUsers, $oldShareUsers);
		//CakeLog::debug("DBG: insShareUsers[" . print_r($insShareUsers, true) .
		//	"] oldShareUsers[" . print_r($oldShareUsers, true) .
		//	"] shareUsers[" . print_r($shareUsers, true) . "]");
		$this->insertShareUsers($model, $insShareUsers, $eventId, $createdUserWhenUpd);

		//古い共有ユーザ群より、削除すべきユーザ群を抽出
		$delShareUsers = array_diff($oldShareUsers, $shareUsers);
		//CakeLog::debug("DBG: delShareUsers[" . print_r($delShareUsers, true) .
		//	"] oldlShareUsers[" . print_r($oldShareUsers, true) .
		//	"] shareUsers[" . print_r($shareUsers, true) . "]");
		$this->deleteShareUsers($model, $delShareUsers, $eventId);

		//新しい共有ユーザ群と、古い共有ユーザ群両方に存在するユーザは、そのままとしておく。
	}

/**
 * 共有ユーザ情報の削除
 *
 * @param Model $model 実際のモデル名
 * @param array $shareUsers shareUsers
 * @param int $eventId eventId
 * @return void
 * @throws InternalErrorException
 */
	public function deleteShareUsers(Model $model, $shareUsers, $eventId) {
		if (!(isset($model->ReservationEventShareUser))) {
			$model->loadModels(['ReservationEventShareUser' => 'Reservation.ReservationEventShareUser']);
		}
		$conditions = array(
			'ReservationEventShareUser.reservation_event_id' => $eventId,
			'ReservationEventShareUser.share_user' => $shareUsers,	//shareUsersは配列なのでIN指定
		);
		if (!$model->ReservationEventShareUser->deleteAll($conditions, false)) {
			//deleteAll失敗
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}
}
