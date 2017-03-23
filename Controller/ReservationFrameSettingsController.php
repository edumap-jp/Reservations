<?php
/**
 * ReservationFrameSettings Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppController', 'Reservations.Controller');
App::uses('ReservationsComponent', 'Reservations.Controller/Component');

/**
 * ReservationFrameSettingsController
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Controller
 */

class ReservationFrameSettingsController extends ReservationsAppController {

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
 * use components
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
		'Paginator',
		'Rooms.RoomsForm',
	);

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
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
				'frame_settings', 'location_settings', 'category_settings', 'mail_settings',
			],
		),
		'NetCommons.NetCommonsForm',
		//'NetCommons.Date',
		'Reservations.ReservationRoomSelect',
	);

/**
 * uses model
 */
	public $uses = array(
		'Reservations.Reservation',
		'Reservations.ReservationFrameSetting',
		'Reservations.ReservationFrameSettingSelectRooms',
		'Rooms.Room'
	);

/**
 * frame display type options
 */
	protected $_displayTypeOptions;

/**
 * Constructor. Binds the model's database table to the object.
 *
 * @param bool|int|string|array $id Set this ID for this model on startup,
 * can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @see Model::__construct()
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->_displayTypeOptions = array(
			ReservationsComponent::CALENDAR_DISP_TYPE_SMALL_MONTHLY =>
				__d('reservations', 'Monthly Reservation (small)'),
			ReservationsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY =>
				__d('reservations', 'Monthly Reservation (large)'),
			ReservationsComponent::CALENDAR_DISP_TYPE_WEEKLY =>
				__d('reservations', 'Weekly Reservation'),
			ReservationsComponent::CALENDAR_DISP_TYPE_DAILY =>
				__d('reservations', 'Day View'),
			ReservationsComponent::CALENDAR_DISP_TYPE_TSCHEDULE =>
				__d('reservations', 'Schedule (ordered-by-time)'),
			ReservationsComponent::CALENDAR_DISP_TYPE_MSCHEDULE =>
				__d('reservations', 'Schedule (ordered-by-user)'),
		);
	}

/**
 * beforeFilter
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny('index');
		$this->Reservation->afterFrameSave(['Frame' => Current::read('Frame')]);
	}

/**
 * edit
 *
 * @return void
 */
	public function edit() {
		if ($this->request->is(['put', 'post'])) {
			//登録(PUT)処理
			$data = $this->request->data;
			$data['ReservationFrameSetting']['display_type'] =
				(int)$data['ReservationFrameSetting']['display_type'];
			if ($this->ReservationFrameSetting->saveFrameSetting($data)) {
				$this->redirect(NetCommonsUrl::backToPageUrl(true));
				return;
			}
			$this->NetCommons->handleValidationError($this->ReservationFrameSetting->validationErrors);
			//NC3用のvalidateErrorHandler.エラー時、非ajaxならSession->setFalsh()する.又は.(ajaxの時は)jsonを返す.
		}
		//指定したフレームキーのデータセット
		//
		//注）施設予約はplugin配置(=フレーム生成)直後に、ReservationモデルのafterFrameSave()が呼ばれ、その中で、
		//	該当フレームキーのReservationFrameSettingモデルデータが１件新規作成されています。
		//	なので、ここでは、読むだけでＯＫ．
		//
		// 設定情報取り出し
		$setting = $this->ReservationFrameSetting->getFrameSetting();
		$settingId = $setting['ReservationFrameSetting']['id'];
		$this->set('settingId', $settingId);

		if (! $this->request->is(['put', 'post'])) {
			$this->request->data['ReservationFrameSetting'] = $setting['ReservationFrameSetting'];
			$this->request->data['ReservationFrameSettingSelectRoom'] =
				$this->ReservationFrameSetting->getSelectRooms($settingId);
		}

		// 空間情報
		$spaces = $this->Room->getSpaces();
		// ルームツリー
		$spaceIds = array(Space::PUBLIC_SPACE_ID, Space::COMMUNITY_SPACE_ID);
		foreach ($spaceIds as $spaceId) {
			$rooms[$spaceId] = $this->_getRoom($spaceId);
			$roomTreeList[$spaceId] = $this->_getRoomTree($spaces[$spaceId]['Room']['id'], $rooms[$spaceId]);
		}
		$this->set('spaces', $spaces);
		$this->set('rooms', $rooms);
		$this->set('roomTreeList', $roomTreeList);
		// フレーム情報
		//施設予約ではsaveAssociated()はつかわないので外す。
		$this->request->data['Frame'] = Current::read('Frame');
		// 施設予約表示種別
		$this->set('displayTypeOptions', $this->_displayTypeOptions);
	}
/**
 * _getRoom
 *
 * @param int $spaceId space id
 * @return array
 */
	protected function _getRoom($spaceId) {
		//$rooms = $this->Room->find('threaded', $this->Room->getReadableRoomsConditions($spaceId));
		$rooms = $this->Room->find('all',
			$this->Room->getReadableRoomsConditions(array('Room.space_id' => $spaceId)));
		$rooms = Hash::combine($rooms, '{n}.Room.id', '{n}');
		return $rooms;
	}
/**
 * _getRoomTree
 *
 * @param int $spaceRoomId room id which is space's
 * @param array $rooms room information
 * @return array
 */
	protected function _getRoomTree($spaceRoomId, $rooms) {
		// ルームTreeリスト取得
		$roomTreeList = $this->Room->generateTreeList(
			array(
				'Room.id' => array_merge(
					array($spaceRoomId), array_keys($rooms))), null, null, Room::$treeParser);
		return $roomTreeList;
	}
}
