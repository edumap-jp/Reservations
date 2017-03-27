<?php
/**
 * BlogEntriesEdit
 */
App::uses('ReservationsAppController', 'Reservations.Controller');

/**
 * BlogEntriesEdit Controller
 *
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link     http://www.netcommons.org NetCommons Project
 * @license  http://www.netcommons.org/license.txt NetCommons License
 * @property NetCommonsWorkflow $NetCommonsWorkflow
 * @property PaginatorComponent $Paginator
 * @property ReservationLocation $ReservationLocation
 * @property BlogCategory $BlogCategory
 */
class ReservationImportController extends ReservationsAppController {

/**
 * layout
 *
 * @var array
 */
	public $layout = 'NetCommons.setting';	//PageLayoutHelperのafterRender()の中で利用。
	//
	//$layoutに'NetCommons.setting'があると
	//「Frame設定も含めたコンテンツElement」として
	//ng-controller='FrameSettingsController'属性
	//ng-init=initialize(Frame情報)属性が付与される。
	//
	//'NetCommons.setting'がないと、普通の
	//「コンテンツElement」として扱われる。
	//
	//ちなみに、使用されるLayoutは、Pages.default
	//

/**
 * @var array use models
 */
	public $uses = array(
		'Reservations.ReservationLocation',
		'Reservations.ReservationLocationsRoom',
		'Categories.Category',
		//'Workflow.WorkflowComment',
	);

/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'NetCommons.Permission' => array(
			//アクセスの権限
			'allow' => array(
				'edit' => 'page_editable',
			),
		),
		//'Workflow.Workflow',

		'Categories.Categories',
		//'Blogs.ReservationLocationPermission',
		'NetCommons.NetCommonsTime',
		'Paginator',
		'Rooms.RoomsForm',

	);

/**
 * @var array helpers
 */
	public $helpers = array(
		'NetCommons.BackTo',
		'NetCommons.NetCommonsForm',
		'Workflow.Workflow',
		'NetCommons.NetCommonsTime',
		'NetCommons.TitleIcon',
		//'Blocks.BlockForm',

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
		'Rooms.RoomsForm',
		'Reservations.ReservationLocation',
	);

/**
 * edit method
 *
 * @return void
 */
	public function edit() {
		$this->set('isEdit', true);
		if ($this->request->is(array('post', 'put'))) {

		} else {

		}
	}
}
