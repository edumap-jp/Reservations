<?php
/**
 * ReservationMail Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationAppBehavior', 'Reservations.Model/Behavior');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');
App::uses('ReservationPermissiveRooms', 'Reservations.Utility');
App::uses('ReservationPlan', 'Reservations.Helper');
App::uses('ReservationPlanRrule', 'Reservations.Helper');

/**
 * ReservationMailBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model\Behavior
 */
class ReservationMailBehavior extends ReservationAppBehavior {

/**
 * sendWorkflowAndNoticeMail
 *
 * 承認依頼メールや公開通知メールを送る処理
 * 施設予約は「カレント」のルームIDじゃない情報を作ったりするのでカレントのすり替え処理が必要
 *
 * @param Model &$model モデル
 * @param int $eventId イベントID（繰り返しの場合は先頭のイベント）
 * @param bool $isMyPrivateRoom （プライベートルームの情報かどうか）
 * @return void
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function sendWorkflowAndNoticeMail(Model &$model, $eventId, $isMyPrivateRoom) {
		$model->loadModels([
			'Block' => 'Blocks.Block',
			'ReservationEvent' => 'Reservations.ReservationEvent'
		]);
		$model->ReservationEvent->Behaviors->load('Mails.MailQueue');

		// 指定されたイベント情報を取得
		$data = $model->ReservationEvent->getEventById($eventId);
		if (! $data) {
			return;
		}

		$model->ReservationEvent->set($data);

		$this->_setDateTags($model, $data);
		$this->_setRruleTags($model, $data);
		$this->_setUrlTags($model, $data);
		$this->_setRoomTags($model, $data);
		$model->ReservationEvent->setAddEmbedTagValue('X-SUBJECT', $data['ReservationEvent']['title']);
		$model->ReservationEvent->setAddEmbedTagValue('X-CONTACT', $data['ReservationEvent']['contact']);
		$model->ReservationEvent->setAddEmbedTagValue('X-LOCATION', $data['ReservationEvent']['location']);
		$model->ReservationEvent->setAddEmbedTagValue('X-BODY', $data['ReservationEvent']['description']);

		// すり替え前にオリジナルルームID,オリジナルのBlockID,オリジナルのBlockKeyを確保
		$originalRoomId = Current::read('Room.id');
		$originalBlockId = Current::read('Block.id');
		$originalBlockKey = Current::read('Block.key');

		// 予定のルームID
		$eventRoomId = $data['ReservationEvent']['room_id'];
		$eventBlockId = $originalBlockId;
		$eventBlockKey = $originalBlockKey;
		$block = $model->Block->find('first', array(
			'conditions' => array(
				'plugin_key' => 'reservations',
				'room_id' => $eventRoomId
			)
		));
		if ($block) {
			$eventBlockId = $block['Block']['id'];
			$eventBlockKey = $block['Block']['key'];
		}

		// パーミッション情報をターゲットルームのものにすり替え
		ReservationPermissiveRooms::setCurrentPermission($eventRoomId);
		// カレントのルームIDなどをすり替え
		Current::$current['Room']['id'] = $eventRoomId;
		Current::$current['Block']['id'] = $eventBlockId;
		Current::$current['Block']['key'] = $eventBlockKey;

		// プライベートのものの場合は自分と共有者に
		if ($isMyPrivateRoom) {
			$userIds = Hash::merge(
				array(
					Current::read('User.id'),
				),
				Hash::extract($data['ReservationEventShareUser'], '{n}.share_user')
			);
			$model->ReservationEvent->setSetting(MailQueueBehavior::MAIL_QUEUE_SETTING_USER_IDS, $userIds);
		}

		$model->ReservationEvent->Behaviors->load('Mails.IsMailSend',
			array(
				'keyField' => 'key',
				MailQueueBehavior::MAIL_QUEUE_SETTING_IS_MAIL_SEND_POST => true,
			));

		$isMailSend = $model->ReservationEvent->isMailSend(
			MailSettingFixedPhrase::DEFAULT_TYPE, $data['ReservationEvent']['key'], 'reservations');

		if ($isMailSend) {
			// メールキュー作成
			$model->ReservationEvent->saveQueue();
			// キューからメール送信
			MailSend::send();
		}

		$model->ReservationEvent->Behaviors->unload('Mails.IsMailSend');
		$model->ReservationEvent->Behaviors->unload('Mails.MailQueue');

		// すり替えものをリカバー
		Current::$current['Room']['id'] = $originalRoomId;
		Current::$current['Block']['id'] = $originalBlockId;
		Current::$current['Block']['key'] = $originalBlockKey;
		ReservationPermissiveRooms::recoverCurrentPermission();
	}

/**
 * _setDateTags
 *
 * @param Model &$model モデル
 * @param array $data 予定データ
 * @return void
 */
	protected function _setDateTags(Model &$model, $data) {
		$view = new View();
		$planHelper = $view->loadHelper('Reservations.ReservationPlan');

		$startDate = $planHelper->makeDatetimeWithUserSiteTz(
			$data['ReservationEvent']['dtstart'], $data['ReservationEvent']['is_allday']);
		$model->ReservationEvent->setAddEmbedTagValue('X-START_TIME', $startDate);

		if ($data['ReservationEvent']['is_allday']) {
			$endDate = $planHelper->makeDatetimeWithUserSiteTz(
				$data['ReservationEvent']['dtstart'], $data['ReservationEvent']['is_allday']);
		} else {
			$endDate = $planHelper->makeDatetimeWithUserSiteTz(
				$data['ReservationEvent']['dtend'], $data['ReservationEvent']['is_allday']);
		}
		$model->ReservationEvent->setAddEmbedTagValue('X-END_TIME', $endDate);
	}
/**
 * _setRruleTags
 *
 * @param Model &$model モデル
 * @param array $data 予定データ
 * @return void
 */
	protected function _setRruleTags(Model &$model, $data) {
		$view = new View();
		$rruleHelper = $view->loadHelper('Reservations.ReservationPlanRrule');

		$rrule = $rruleHelper->getStringRrule($data['ReservationRrule']['rrule']);

		if ($rrule != '') {
			$rrule = str_replace('&nbsp;', ' ', $rrule);
			$model->ReservationEvent->setAddEmbedTagValue('X-RRULE', htmlspecialchars_decode($rrule));
		} else {
			$model->ReservationEvent->setAddEmbedTagValue('X-RRULE', __d('reservations', 'nothing'));
		}
	}
/**
 * _setUrlTags
 *
 * @param Model &$model モデル
 * @param array $data 予定データ
 * @return void
 */
	protected function _setUrlTags(Model &$model, $data) {
		$url = NetCommonsUrl::actionUrl(array(
			'plugin' => Current::read('Plugin.key'),
			'controller' => 'reservation_plans',
			'action' => 'view',
			'block_id' => '',
			'frame_id' => Current::read('Frame.id'),
			'key' => $data['ReservationEvent']['key']
		));
		$url = NetCommonsUrl::url($url, true);
		$model->ReservationEvent->setAddEmbedTagValue('X-URL', $url);
	}

/**
 * _setRoomTags
 *
 * @param Model &$model モデル
 * @param array $data 予定データ
 * @return void
 */
	protected function _setRoomTags(Model &$model, $data) {
		if ($data['ReservationEvent']['room_id'] == Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID)) {
			$model->ReservationEvent->setAddEmbedTagValue('X-ROOM', __d('reservations', 'All the members'));
		}
	}
}
