<?php
/**
 * ReservationSettingsComponent.php
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('Component', 'Controller');
App::uses('BlockTabsHelper', 'Blocks.View/Helper');

/**
 * Class ReservationSettingsComponent
 */
class ReservationSettingsComponent extends Component {

/**
 * タブの定数(表示方法変更)
 *
 * @var string
 */
	const MAIN_TAB_FRAME_SETTING = BlockTabsHelper::MAIN_TAB_FRAME_SETTING;

/**
 * タブの定数(施設設定)
 *
 * @var string
 */
	const MAIN_TAB_LOCATION_SETTING = 'location_settings';

/**
 * タブの定数(施設カテゴリ設定)
 *
 * @var string
 */
	const MAIN_TAB_CATEGORY_SETTING = 'category_settings';

/**
 * タブの定数(時間枠設定)
 *
 * @var string
 */
	const MAIN_TAB_TIMEFRAME_SETTING = 'timeframe_settings';

/**
 * タブの定数(予約のインポート)
 *
 * @var string
 */
	const MAIN_TAB_IMPORT_RESERVATIONS = 'import_reservations';

/**
 * タブの定数(メール設定)
 *
 * @var string
 */
	const MAIN_TAB_MAIL_SETTING = BlockTabsHelper::MAIN_TAB_MAIL_SETTING;

/**
 * パーミッション定数(サイト管理者)
 *
 * @var string
 */
	const PERMISSION_LOCATION_EDITABLE = 'location_editable';

/**
 * パーミッション定数(ページ設定できる権限)
 *
 * @var string
 */
	const PERMISSION_ROOM_EDITABLE = 'room_editable';

/**
 * パーミッション
 *
 *
 * @var string value=site_editable or page_editable
 */
	public $permission = self::PERMISSION_LOCATION_EDITABLE;

/**
 * BlockTabsHelper設定
 *
 * @var array
 */
	public $blockTabs = array();

/**
 * BlockTabsHelper設定
 *
 * @var array
 */
	protected $_adminBlockTabs = array(
		//画面上部のタブ設定
		'mainTabs' => array(
			self::MAIN_TAB_CATEGORY_SETTING => [ //施設カテゴリ設定
				'label' => ['reservations', 'Location category setting'],
				'url' => array('controller' => 'reservation_location_categories', 'action' => 'edit')
			],
			self::MAIN_TAB_LOCATION_SETTING => array( //施設設定
				'label' => ['reservations', 'Location setting'],
				'url' => array('controller' => 'reservation_locations', 'action' => 'index')
			),
			self::MAIN_TAB_TIMEFRAME_SETTING => array( //時間枠設定
				'label' => ['reservations', 'TimeFrame setting'],
				'url' => array('controller' => 'reservation_timeframes', 'action' => 'index')
			),
			self::MAIN_TAB_IMPORT_RESERVATIONS => array( //予約のインポート
				'label' => ['reservations', 'Import Reservations'],
				'url' => array('controller' => 'reservation_import', 'action' => 'edit')
			),
			self::MAIN_TAB_FRAME_SETTING => array( //表示設定変更
				'url' => array('controller' => 'reservation_frame_settings')
			),
			self::MAIN_TAB_MAIL_SETTING => array( //メール設定
				'url' => array('controller' => 'reservation_mail_settings'),
			),
		),
		'mainTabsOrder' => [
			self::MAIN_TAB_FRAME_SETTING,
			self::MAIN_TAB_LOCATION_SETTING,
			self::MAIN_TAB_CATEGORY_SETTING,
			self::MAIN_TAB_TIMEFRAME_SETTING,
			self::MAIN_TAB_MAIL_SETTING,
			self::MAIN_TAB_IMPORT_RESERVATIONS
		],
	);

/**
 * BlockTabsHelper設定
 *
 * @var array
 */
	protected $_generalBlockTabs = array(
		//画面上部のタブ設定
		'mainTabs' => array(
			self::MAIN_TAB_FRAME_SETTING => array( //表示設定変更
				'url' => array('controller' => 'reservation_frame_settings')
			),
		),
		'mainTabsOrder' => [
			self::MAIN_TAB_FRAME_SETTING,
		],
	);

/**
 * Called after the Controller::beforeFilter() and before the controller action
 *
 * @param Controller $controller Controller with components to startup
 * @return void
 * @throws ForbiddenException
 */
	public function startup(Controller $controller) {
		//サイト管理者のみ編集可＝ページ編集権限があり、サイト管理が使えるユーザ
		$isAdmin = Current::permission('page_editable') && Current::allowSystemPlugin('site_manager');
		$controller->set('isAdmin', $isAdmin);

		if ($isAdmin) {
			$this->blockTabs = $this->_adminBlockTabs;
		} else {
			$this->blockTabs = $this->_generalBlockTabs;
		}

		if ($this->permission === self::PERMISSION_LOCATION_EDITABLE && $controller->viewVars['isAdmin'] ||
			$this->permission === self::PERMISSION_ROOM_EDITABLE && Current::permission('page_editable')) {
			$controller->helpers['Blocks.BlockTabs'] = $this->blockTabs;
			return;
		}

		throw new ForbiddenException('Permission denied');
	}

}
