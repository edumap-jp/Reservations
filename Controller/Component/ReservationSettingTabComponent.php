<?php
/**
 * ReservationSettingTabComponent.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('BlockTabsHelper', 'Blocks.View/Helper');

/**
 * Class ReservationSettingTabComponent
 */
class ReservationSettingTabComponent extends Component {

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
 * @var array BlockTabsHelper設定
 */
	public static $blockTabs = array(
		//画面上部のタブ設定
		'mainTabs' => array(
			self::MAIN_TAB_CATEGORY_SETTING => [ //施設カテゴリ設定
				'label' => ['reservations', 'Location category setting'],
				'url' => array('controller' => 'reservation_settings', 'action' => 'edit')
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
}
