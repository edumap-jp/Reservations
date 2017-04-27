<?php
/**
 * ReservationSettingTabComponent.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Class ReservationSettingTabComponent
 */
class ReservationSettingTabComponent extends Component {

/**
 * @var array BlockTabsHelper設定
 */
	public static $blockTabs = array(
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
	);
}
