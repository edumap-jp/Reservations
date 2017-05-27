<?php
/**
 * ReservationMailQueueBehavior.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('MailQueueBehavior', 'Mails.Model/Behavior');

class ReservationMailQueueBehavior extends MailQueueBehavior {

/**
 * キュー保存
 *
 * ε(　　　　 v ﾟωﾟ)　＜内部でCallしている___saveQueueNoticeMailがprivateメソッドで上書きできなかったので
 * このメソッドごとラップした。___saveQueueNoticeMailをprotectedにできれば、このメソッドは不要
 *
 * @param Model $model モデル
 * @param array $sendTimes メール送信日時 配列
 * @param string $typeKey メールの種類
 * @return void
 */
	public function saveQueue(Model $model, $sendTimes = null,
		$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		$model->Behaviors->load('Mails.IsMailSend', $this->settings[$model->alias]);

		$languageId = Current::read('Language.id');
		$workflowType = Hash::get($this->settings, $model->alias . '.' .
			self::MAIL_QUEUE_SETTING_WORKFLOW_TYPE);
		$userIds = $this->settings[$model->alias][self::MAIL_QUEUE_SETTING_USER_IDS];
		$toAddresses = $this->settings[$model->alias][self::MAIL_QUEUE_SETTING_TO_ADDRESSES];
		$roomId = Current::read('Room.id');
		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_GROUP_ONLY) {
			// グループ配信は、ルーム配信しない
			$roomId = null;
		}

		$workflowTypeCheck = array(
			self::MAIL_QUEUE_WORKFLOW_TYPE_WORKFLOW,
			self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT,
		);
		if (in_array($workflowType, $workflowTypeCheck, true)) {
			// --- ワークフローのstatusによって送信内容を変える
			// 各プラグインが承認機能=ONかどうかは、気にしなくてＯＫ。承認機能=OFFなら status=公開が飛んでくるため。

			// 承認依頼通知, 差戻し通知, 承認完了通知メール(即時)
			$this->_saveQueueNoticeMail($model, $languageId, $typeKey);

			$mailSettingPlugin = $this->__getMailSettingPlugin($model, $languageId, $typeKey);
			$isMailSend = Hash::get($mailSettingPlugin, 'MailSetting.is_mail_send');
			$contentKey = $this->__getContentKey($model);

			/** @see IsMailSendBehavior::isSendMailQueuePublish() */
			if (! $model->isSendMailQueuePublish($isMailSend, $contentKey)) {
				return;
			}

			// 施設予約は予約毎にメール通知するか決められる
			if (!$model->data['ReservationEvent']['is_enable_mail']) {
				// 予約で「メールで通知する」になってなかったら
				return;
			}

			// 投稿メール - ルーム配信
			$this->saveQueuePostMail($model, $languageId, $sendTimes, $userIds, $toAddresses,
				$roomId, $typeKey);

		} else {
			//$workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_NONE ||
			//$workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_ANSWER ||
			//$workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_GROUP_ONLY) {
			// ・承認機能なし - 「公開」記事の内容を投稿メールでルーム配信
			// ・回答メール配信(即時) - ユーザID、メールアドレス、ルームに即時配信
			// ・グループ送信のみ - ユーザIDに配信

			// メールキューSave
			$this->saveQueuePostMail($model, $languageId, $sendTimes, $userIds, $toAddresses,
				$roomId, $typeKey);
		}
	}

/**
 * 通知メール - 登録者に配信(即時) - メールキューSave
 * - 承認依頼通知, 差戻し通知, 承認完了通知メール
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param string $typeKey メールの種類
 * @return void
 * @throws InternalErrorException
 */
	protected function _saveQueueNoticeMail(Model $model, $languageId,
		$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		$mailSettingPlugin = $this->__getMailSettingPlugin($model, $languageId, $typeKey);

		$isMailSendApproval = Hash::get($mailSettingPlugin, 'MailSetting.is_mail_send_approval');
		//$modifiedUserId = Hash::get($model->data, $model->alias . '.modified_user');
		//$settingPluginKey = $this->__getSettingPluginKey($model);

		if (!$isMailSendApproval) {
			// 承認メール使わないなら何もしない
			return;
		}
		/** @see IsMailSendBehavior::isSendMailQueueNotice() */
		// ワークフローを使うかチェックいれてたけど不要なのでコメントアウト
		//if (! $model->isSendMailQueueNotice($isMailSendApproval, $modifiedUserId, $settingPluginKey)) {
		//	return;
		//}

		// 承認コメント
		$comment = Hash::get($model->data, 'WorkflowComment.comment');
		$contentKey = $this->__getContentKey($model);
		/** @see IsMailSendBehavior::isPublishableEdit() */
		$isPublishableEdit = $model->isPublishableEdit($contentKey);

		// 定型文の種類
		$mailAssignTag = new NetCommonsMailAssignTag();
		$status = Hash::get($model->data, $model->alias . '.status');
		$fixedPhraseType = $mailAssignTag->getFixedPhraseType($status, $comment, $isPublishableEdit);

		$mailQueue = $this->__createMailQueue($model, $languageId, $typeKey, $fixedPhraseType);
		$mailQueue['MailQueue']['send_time'] = $model->MailQueue->getSaveSendTime();

		/** @see MailQueue::saveMailQueue() */
		if (! $mailQueueResult = $model->MailQueue->saveMailQueue($mailQueue)) {
			throw new InternalErrorException('Failed ' . __METHOD__);
		}
		$mailQueueId = $mailQueueResult['MailQueue']['id'];

		// 登録者に配信
		$this->__addMailQueueUserInCreatedUser($model, $mailQueueId);

		// ルーム内の承認者達に配信
		//$this->__addMailQueueUserInRoomAuthorizers($model, $mailQueueId);
		$this->__addMailQueueUserInApprovalUsers($model, $mailQueueId);
	}

/**
 * 施設の承認者に配信 登録
 *
 * @param Model $model モデル
 * @param int $mailQueueId メールキューID
 * @return bool
 * @throws InternalErrorException
 */
	private function __addMailQueueUserInApprovalUsers(Model $model, $mailQueueId) {
		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->settings[$model->alias]['pluginKey'];
		//$permissionKey = $this->settings[$model->alias]['editablePermissionKey'];

		// 既に登録者に配信セット済みの人には送らない
		$notSendKey = self::MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS;
		$notSendRoomUserIds = $this->settings[$model->alias][$notSendKey];

		// 施設の承認者にメールする
		// 予約の施設取得
		$this->ReservationLocation = ClassRegistry::init('Reservations.ReservationLocation');
		$location = $this->ReservationLocation->getByKey($model->data['ReservationEvent']['location_key']);
		// 施設の承認者取得
		$approvalUserIds = $location['approvalUserIds'];

		$blockKey = Current::read('Block.key');
		// 承認者にのメールキュー登録
		$mailQueueUser = [
			'MailQueueUser' => [
				'plugin_key' => $pluginKey,
				'block_key' => $blockKey,
				'content_key' => $contentKey,
				'mail_queue_id' => $mailQueueId,
				'user_id' => null,
				'room_id' => null,
				'to_address' => null,
				'send_room_permission' => null,
				'not_send_room_user_ids' => null,
			],
		];
		foreach ($approvalUserIds as $userId) {
			// 送らないユーザIDにあれば、登録しない
			if (in_array($userId, $notSendRoomUserIds)) {
				continue;
			}
			$mailQueueUser['MailQueueUser']['user_id'] = $userId;
			// 新規登録
			$mailQueueUser = $model->MailQueueUser->create($mailQueueUser);
			if (! $model->MailQueueUser->saveMailQueueUser($mailQueueUser)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			// 承認完了時に2通（承認完了とルーム配信）を送らず1通にする対応
			// ルーム配信で送らないユーザID を返す
			$notSendRoomUserIds[] = $userId;
		}

		// 編集者達(編集許可ありユーザ)
		/** @see MailQueueUser::addMailQueueUserInRoomByPermission() */
		//$notSendRoomUserIds = $model->MailQueueUser->addMailQueueUserInRoomByPermission($mailQueueId,
		//	$contentKey, $pluginKey, $permissionKey, $notSendRoomUserIds);
		//
		//// 承認者達(公開許可ありユーザ)
		//$permissionKey = $this->settings[$model->alias]['publishablePermissionKey'];
		//$notSendRoomUserIds = $model->MailQueueUser->addMailQueueUserInRoomByPermission($mailQueueId,
		//	$contentKey, $pluginKey, $permissionKey, $notSendRoomUserIds);

		// 承認完了時に2通（承認完了とルーム配信）を送らず1通にする対応
		// ルーム配信で送らないユーザID セット
		$this->settings[$model->alias][$notSendKey] =
			Hash::merge($this->settings[$model->alias][$notSendKey], $notSendRoomUserIds);
	}

	// ============== 以下MailQueueBehaviorそのまま privateメソッドを呼べないのもってきただけ============

/**
 * コンテンツキー ゲット
 *
 * @param Model $model モデル
 * @return string コンテンツキー
 */
	private function __getContentKey(Model $model) {
		$keyField = $this->settings[$model->alias]['keyField'];
		return $model->data[$model->alias][$keyField];
	}

/**
 * プラグインのメール設定(定型文等) 取得
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param string $typeKey メールの種類
 * @return array メール設定データ配列
 */
	private function __getMailSettingPlugin(Model $model, $languageId,
		$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE) {
		if (!$this->_mailSettingPlugin) {
			$settingPluginKey = $this->__getSettingPluginKey($model);
			/** @see MailSetting::getMailSettingPlugin() */
			$this->_mailSettingPlugin = $model->MailSetting->getMailSettingPlugin($languageId, $typeKey,
				$settingPluginKey);
		}
		return $this->_mailSettingPlugin;
	}

/**
 * プラグイン設定を取得するためのプラグインキー ゲット
 *
 * @param Model $model モデル
 * @return string コンテンツキー
 */
	private function __getSettingPluginKey(Model $model) {
		$workflowType = Hash::get($this->settings, $model->alias . '.' .
			self::MAIL_QUEUE_SETTING_WORKFLOW_TYPE);
		if ($workflowType == self::MAIL_QUEUE_WORKFLOW_TYPE_COMMENT) {
			return $model->data[$model->alias]['plugin_key'];
		}
		// 通常
		return Current::read('Plugin.key');
	}
/**
 * メールキューデータ 新規作成
 *
 * @param Model $model モデル
 * @param int $languageId 言語ID
 * @param string $typeKey メールの種類
 * @param string $fixedPhraseType SiteSettingの定型文の種類
 * @param string $fixedPhraseBodyAfter 末尾定型文
 * @return array メールキューデータ
 */
	private function __createMailQueue(Model $model,
		$languageId,
		$typeKey = MailSettingFixedPhrase::DEFAULT_TYPE,
		$fixedPhraseType = null,
		$fixedPhraseBodyAfter = '') {
		$mailSettingPlugin = $this->__getMailSettingPlugin($model, $languageId, $typeKey);
		$replyTo = Hash::get($mailSettingPlugin, 'MailSetting.reply_to');
		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->settings[$model->alias]['pluginKey'];
		$pluginName = $this->settings[$model->alias][self::MAIL_QUEUE_SETTING_PLUGIN_NAME];
		$blockKey = Current::read('Block.key');

		// メール生文の作成
		$mailAssignTag = new NetCommonsMailAssignTag();
		$mailAssignTag->initPlugin($languageId, $pluginName);
		$mailAssignTag->setMailFixedPhrase($languageId, $fixedPhraseType, $mailSettingPlugin);

		// 埋め込みタグのウィジウィグ対象
		$mailAssignTag->embedTagsWysiwyg = $this->settings[$model->alias]['embedTagsWysiwyg'];

		// 末尾定型文
		$mailAssignTag->setFixedPhraseBody($mailAssignTag->fixedPhraseBody . $fixedPhraseBodyAfter);

		// --- 埋め込みタグ
		$embedTags = $this->settings[$model->alias]['embedTags'];
		$xUrl = Hash::get($embedTags, 'X-URL', array());
		$mailAssignTag->setXUrl($contentKey, $xUrl);
		if (is_array($xUrl)) {
			$embedTags = Hash::remove($embedTags, 'X-URL');
		}

		$createdUserId = Hash::get($model->data, $model->alias . '.created_user');
		$mailAssignTag->setXUser($createdUserId);

		// ワークフロー
		$useWorkflowBehavior = $model->Behaviors->loaded('Workflow.Workflow');
		$mailAssignTag->setXWorkflowComment($model->data, $fixedPhraseType, $useWorkflowBehavior);

		$workflowType = Hash::get($this->settings, $model->alias . '.' .
			self::MAIL_QUEUE_SETTING_WORKFLOW_TYPE);
		$useTagBehavior = $model->Behaviors->loaded('Tags.Tag');

		// タグプラグイン
		$mailAssignTag->setXTags($model->data, $workflowType, $useTagBehavior);

		// 定型文の埋め込みタグをセット
		$mailAssignTag->assignTagDatas($embedTags, $model->data);

		// - 追加の埋め込みタグ セット
		// 既にセットされているタグであっても、上書きされる
		$mailAssignTag->assignTags($this->settings[$model->alias]['addEmbedTagsValues']);

		// 埋め込みタグ変換：メール定型文の埋め込みタグを変換して、メール生文にする
		$mailAssignTag->assignTagReplace();

		// メール本文の共通ヘッダー文、署名追加
		$mailAssignTag->fixedPhraseBody =
			$mailAssignTag->addHeaderAndSignature($mailAssignTag->fixedPhraseBody);

		$mailQueue['MailQueue'] = array(
			'language_id' => $languageId,
			'plugin_key' => $pluginKey,
			'block_key' => $blockKey,
			'content_key' => $contentKey,
			'reply_to' => $replyTo,
			'mail_subject' => $mailAssignTag->fixedPhraseSubject,
			'mail_body' => $mailAssignTag->fixedPhraseBody,
			'send_time' => null,
		);

		// MailQueueは新規登録
		$mailQueue = $model->MailQueue->create($mailQueue);
		return $mailQueue;
	}

/**
 * 登録者に配信 登録
 *
 * @param Model $model モデル
 * @param int $mailQueueId メールキューID
 * @return void
 */
	private function __addMailQueueUserInCreatedUser(Model $model, $mailQueueId) {
		$createdUserId = Hash::get($model->data, $model->alias . '.created_user');

		// ルーム配信で送らないユーザID にセット済みであれば、既に登録者に配信セット済みのため、セットしない
		$notSendKey = self::MAIL_QUEUE_SETTING_NOT_SEND_ROOM_USER_IDS;
		$notSendRoomUserIds = $this->settings[$model->alias][$notSendKey];
		if (in_array($createdUserId, $notSendRoomUserIds)) {
			return;
		}

		$contentKey = $this->__getContentKey($model);
		$pluginKey = $this->settings[$model->alias]['pluginKey'];

		/** @see MailQueueUser::addMailQueueUserInCreatedUser() */
		$model->MailQueueUser->addMailQueueUserInCreatedUser($mailQueueId, $createdUserId, $contentKey,
			$pluginKey);

		// 承認完了時に2通（承認完了とルーム配信）を送らず1通にする対応
		// ルーム配信で送らないユーザID セット
		$this->settings[$model->alias][$notSendKey][] = $createdUserId;
	}
}