<?php
/**
 * ReservationMailSettings Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MailSettingsController', 'Mails.Controller');
App::uses('Room', 'Rooms.Model');
App::uses('ReservationPermissiveRooms', 'Reservations.Utility');

/**
 * ReservationMailSettingsController
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Controller
 */

class ReservationMailSettingsController extends MailSettingsController {

/**
 * 使用コンポーネントの定義
 *
 * @var array
 */
	public $components = array(
		'Mails.MailSettings',
		'NetCommons.Permission' => array(
			'allow' => array(
				'edit' => 'mail_editable',
			),
		),
		'Pages.PageLayout',
		'Security',
	);

/**
 * 使用モデルの定義
 *
 * @var array
 */
	public $uses = array(
		'Blocks.Block',
		'Rooms.Room',
		'Rooms.RoomsLanguage',
		'Mails.MailSetting',
		'Mails.MailSettingFixedPhrase',
		'Reservations.ReservationEvent',
		'Reservations.ReservationPermission',
	);

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
		'Blocks.BlockRolePermissionForm',
		'Blocks.BlockTabs' => array(
			//画面上部のタブ設定
			'mainTabs' => array(
				'category_settings' => [
					'label' => ['reservations', 'Location category setting'],
					'url' => array('controller' => 'reservation_settings', 'action' => 'edit')
				],
				'location_settings' => array(
					'label' => ['reservations', 'Location setting'],
					'url' => array('controller' => 'reservation_locations', 'action' => 'index')
				),
				'timeframe_settings' => array(
					'label' => ['reservations', 'TimeFrame setting'],
					'url' => array('controller' => 'reservation_timeframes', 'action' => 'index')
				),
				'import_reservations' => array(
					'label' => ['reservations', 'Import Reservations'],
					'url' => array('controller' => 'reservation_import', 'action' => 'edit')
				),
				'frame_settings' => array(	//表示設定変更
					'url' => array('controller' => 'reservation_frame_settings')
				),
				//'role_permissions' => array(
				//	'url' => array('controller' => 'reservation_block_role_permissions'),
				//),
				'mail_settings' => array(
					'url' => array('controller' => 'reservation_mail_settings'),
				),
			),
			'mainTabsOrder' => [
				'frame_settings', 'location_settings', 'category_settings', 'timeframe_settings',
				'mail_settings', 'import_reservations'

			],
		),
		'Mails.MailForm',
	);

/**
 * beforeFilter
 *
 * @return void
 * @see NetCommonsAppController::beforeFilter()
 */
	public function beforeFilter() {
		parent::beforeFilter();

		$this->backUrl = NetCommonsUrl::backToPageUrl(true);

		$mailRooms = $this->_getMailRooms();
		$mailSelect = Hash::combine($mailRooms, '{n}.roomId', '{n}.name');
		$this->set('mailRooms', $mailSelect);

		$specifiedRoomId = Hash::get($this->request->query, 'room');
		if ($specifiedRoomId !== false && isset($mailRooms[$specifiedRoomId])) {
			// 問題なければ強制すり替え
			Current::$current['Room']['id'] = $specifiedRoomId;
			Current::$current['Block']['key'] = $mailRooms[$specifiedRoomId]['blockKey'];
		}
	}

/**
 * _getMailRooms
 *
 * メール設定できるルームの一覧を返す
 *
 * @return array
 */
	protected function _getMailRooms() {
		$retRoom = array();

		$this->ReservationEvent->initSetting($this->Workflow);

		$roomPermRoles = $this->ReservationEvent->prepareCalRoleAndPerm();
		ReservationPermissiveRooms::setRoomPermRoles($roomPermRoles);

		// メール設定ができるルームの一覧を取り出す
		$mailEditableRoomIds = ReservationPermissiveRooms::getMailEditableRoomIdList();

		foreach ($mailEditableRoomIds as $roomId) {
			$retRoom[$roomId] = array();
			$retRoom[$roomId]['roomId'] = $roomId;

			if ($roomId == Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID)) {
				$retRoom[$roomId]['name'] = __d('reservations', 'All the members');
			} else {
				// それぞれのルーム名を取りだして配列作成
				$roomLang = $this->RoomsLanguage->find('first', array(
					'conditions' => array(
						'room_id' => $roomId,
						'language_id' => Current::read('Language.id')
					)
				));
				$retRoom[$roomId]['name'] = $roomLang['RoomsLanguage']['name'];
			}

			// それぞれのルームにすでに施設予約ブロックがあるかチェック
			// ない場合はブロック作成
			$block = $this->ReservationPermission->saveBlock($roomId);
			// そのブロックキーも配列に加える
			$retRoom[$roomId]['blockKey'] = $block['Block']['key'];
		}
		return $retRoom;
	}

}
