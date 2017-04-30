<?php
/**
 * メール設定 Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MailSettingsController', 'Mails.Controller');
App::uses('Room', 'Rooms.Model');
App::uses('ReservationPermissiveRooms', 'Reservations.Utility');

/**
 * メール設定 Controller
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
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
		'Pages.PageLayout',
		'Security',
		'Reservations.ReservationSettings', //NetCommons.Permissionは使わず、独自でやる
	);

/**
 * 使用モデルの定義
 *
 * @var array
 */
	public $uses = array(
		'Mails.MailSetting',
		'Mails.MailSettingFixedPhrase',
		'Reservations.Reservation',
	);

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
		'Blocks.BlockRolePermissionForm',
		'Blocks.BlockTabs', // 設定内容はReservationSettingsComponentにまとめた
		'Mails.MailForm',
	);

/**
 * beforeRender
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();

		$this->backUrl = NetCommonsUrl::backToPageUrl(true);

		//NC3の標準のカテゴリーを利用するために、
		//roomId=パブリック、blockId=サイト全体(＝パブリック)でひとつ持つ
		//Current::read('Block')を唯一のBlockに置き換える
		$this->Reservation->prepareBlock();
	}

}
