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
class ReservationLocationsController extends ReservationsAppController {

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
		//'Reservations.ReservationPermission',
		'Roles.DefaultRolePermission',
		'Reservations.ReservationLocationReservable',
		'Reservations.ReservationLocationsApprovalUser',
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
		'Reservations.ReservationSettingTab',
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

		'Blocks.BlockTabs', // 設定内容はReservationSettingTabComponentにまとめた
		'Blocks.BlockRolePermissionForm', // 設定内容はReservationSettingTabComponentにまとめた
		'Rooms.RoomsForm',
		'Reservations.ReservationLocation',
		'Groups.GroupUserList',
	);

/**
 * beforeFilter
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->helpers['Blocks.BlockTabs'] = ReservationSettingTabComponent::$blockTabs;
	}

/**
 * index
 *
 * @return void
 */
	public function index() {
		$query = array();

		//条件
		$conditions = array(
			'ReservationLocation.language_id' => Current::read('Language.id'),
		);
		if (isset($this->params['named']['category_id'])) {
			$conditions['ReservationLocation.category_id'] = $this->params['named']['category_id'];
		}
		$query = [
			'conditions' => $conditions,
			'recursive' => 0,
			'order' => 'ReservationLocation.weight ASC'
		];

		$this->Paginator->settings = [
			'ReservationLocation' => $query
		];
		$reservationLocations = $this->Paginator->paginate('ReservationLocation');
		$this->set('reservationLocations', $reservationLocations);
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		$this->set('isEdit', false);
		//$this->_prepare();

		//$blogEntry = $this->ReservationLocation->getNew();
		//$this->set('blogEntry', $blogEntry);
		$this->_processPermission();

		// 施設管理者保持
		$this->request->data = $this->ReservationLocationsApprovalUser->getSelectUsers($this->request->data, false);

		if ($this->request->is('post')) {
			$this->ReservationLocation->create();
			//$this->request->data['ReservationLocation']['blog_key'] =
			//	$this->_blogSetting['BlogSetting']['blog_key'];

			// set status
			//$status = $this->Workflow->parseStatus();
			//$this->request->data['ReservationLocation']['status'] = $status;

			// set block_id
			//$this->request->data['ReservationLocation']['block_id'] = Current::read('Block.id');
			// set language_id
			$this->request->data['ReservationLocation']['language_id'] = Current::read('Language.id');
			$result = $this->ReservationLocation->saveLocation($this->request->data);
			if ($result) {
				$url = NetCommonsUrl::actionUrl(
					array(
						'controller' => 'reservation_locations',
						'action' => 'index',
						//'block_id' => Current::read('Block.id'),
						'frame_id' => Current::read('Frame.id'),
						//'key' => $result['ReservationLocation']['key']
						)
				);
				return $this->redirect($url);
			}

			$this->NetCommons->handleValidationError($this->ReservationLocation->validationErrors);

		} else {
			$newLocation = $this->ReservationLocation->create();
			$newLocation['ReservationLocation'] = [
				'start_time' => '09:00',
				'end_time' => '18:00',
				'time_table' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
				'use_all_rooms' => '1',
			];
			$this->request->data = $newLocation;
		}
		// プライベートルームは除外する
		$roomConditions = [
			//'Room.space_id !=' => Space::PRIVATE_SPACE_ID,
		];
		$this->RoomsForm->setRoomsForCheckbox($roomConditions);

		$this->render('form');
	}

/**
 * 予約できる権限の処理
 *
 * @param null $key locationKey
 * @return void
 */
	protected function _processPermission($key = null) {
		// ε(　　　　 v ﾟωﾟ)　＜ きれいにしたいところ
		$permissions = $this->Workflow->getBlockRolePermissions(
			array(
				'content_creatable',
			)
		);
		// reseravableデータ取得
		if ($key !== null) {
			$reservables = $this->ReservationLocationReservable->find('all', ['conditions' => [
				'location_key' => $key
			]]);
			$reservables = Hash::combine($reservables,
				'{n}.ReservationLocationReservable.role_key',
				'{n}.ReservationLocationReservable.value');

		} else {
			// default
			$reservables = [
				'room_administrator' => 1,
				'chief_editor' => 1,
				'editor' => 1,
				'general_user' => 1,
				'visitor' => 0,
			];
		}

		$default = array(
			'content_creatable' => array(
				'room_administrator' => array(
					'role_key' => 'room_administrator',
					'type' => 'room_role',
					'permission' => 'content_creatable',
					'value' => $reservables['room_administrator'],
					'fixed' => true,
					'default' => true,
					'roles_room_id' => '1',
					//'block_key' => '0955dd34f66ac731ab5a548afcbfeb82'
				),
				'chief_editor' => array(
					'role_key' => 'chief_editor',
					'type' => 'room_role',
					'permission' => 'content_creatable',
					'value' => $reservables['chief_editor'],
					'fixed' => true,
					'default' => true,
					'roles_room_id' => '2',
					//'block_key' => '0955dd34f66ac731ab5a548afcbfeb82'
				),
				'editor' => array(
					'role_key' => 'editor',
					'type' => 'room_role',
					'permission' => 'content_creatable',
					'value' => $reservables['editor'],
					'fixed' => true,
					'default' => true,
					'roles_room_id' => '3',
					//'block_key' => '0955dd34f66ac731ab5a548afcbfeb82'
				),
				'general_user' => array(
					'role_key' => 'general_user',
					'type' => 'room_role',
					'permission' => 'content_creatable',
					'value' => $reservables['general_user'],
					'fixed' => false,
					'default' => true,
					'roles_room_id' => '4',
					//'block_key' => '0955dd34f66ac731ab5a548afcbfeb82'
				),
				'visitor' => array(
					'role_key' => 'visitor',
					'type' => 'room_role',
					'permission' => 'content_creatable',
					'value' => $reservables['visitor'],
					'fixed' => true,
					'default' => false,
					'roles_room_id' => '5',
					//'block_key' => '0955dd34f66ac731ab5a548afcbfeb82'
				)
			)
		);
		$this->request->data['BlockRolePermission'] = Hash::merge($default,
			Hash::get($this->request->data, 'BlockRolePermission'));
		$this->set('roles', $permissions['Roles']);
	}

/**
 * edit method
 *
 * @return void
 */
	public function edit() {
		$this->set('isEdit', true);
		//$key = $this->request->params['named']['key'];
		$key = $this->params['key'];

		//  keyのis_latstを元に編集を開始
		$this->ReservationLocation->recursive = 0;
		$options = [
			'conditions' => [
				'ReservationLocation.key' => $key,
				'ReservationLocation.language_id' => Current::read('Language.id')
			]
		];

		$reservationLocation = $this->ReservationLocation->find('first', $options);
		$timeTable = explode('|', $reservationLocation['ReservationLocation']['time_table']);
		$reservationLocation['ReservationLocation']['time_table'] = $timeTable;

		if (empty($reservationLocation)) {
			return $this->throwBadRequest();
		}

		// 施設管理者保持
		$this->request->data = $this->ReservationLocationsApprovalUser->getSelectUsers($this->request->data, false);

		//if ($this->ReservationLocation->canEditWorkflowContent($blogEntry) === false) {
		//	return $this->throwBadRequest();
		//}
		//$this->_prepare();
		$this->_processPermission($key);

		if ($this->request->is(array('post', 'put'))) {

			$this->ReservationLocation->create();
			//$this->request->data['ReservationLocation']['blog_key'] =
			//	$this->_blogSetting['BlogSetting']['blog_key'];

			// set status
			//$status = $this->Workflow->parseStatus();
			//$this->request->data['ReservationLocation']['status'] = $status;

			// set block_id
			//$this->request->data['ReservationLocation']['block_id'] = Current::read('Block.id');
			// set language_id
			$this->request->data['ReservationLocation']['language_id'] = Current::read('Language.id');

			$data = $this->request->data;

			//unset($data['ReservationLocation']['id']); // 常に新規保存

			if ($this->ReservationLocation->saveLocation($data)) {
				$url = NetCommonsUrl::actionUrl(
					array(
						'controller' => 'reservation_locations',
						'action' => 'index',
						'frame_id' => Current::read('Frame.id'),
						//'block_id' => Current::read('Block.id'),
						//'key' => $data['ReservationLocation']['key']
					)
				);

				return $this->redirect($url);
			}

			$this->NetCommons->handleValidationError($this->ReservationLocation->validationErrors);

		} else {

			$this->request->data['ReservationLocation'] = $reservationLocation['ReservationLocation'];

			//予約を受け付けるルームを取得
			$result = $this->ReservationLocationsRoom->find('list', array(
				'recursive' => -1,
				'fields' => array('id', 'room_id'),
				'conditions' => ['reservation_location_key' =>
					$this->request->data['ReservationLocation']['key']],
			));
			$this->request->data['ReservationLocationsRoom']['room_id'] =
				array_unique(array_values($result));
		}

		$this->set('reservationLocation', $reservationLocation);
		//$this->set('isDeletable', $this->ReservationLocation->canDeleteWorkflowContent($blogEntry));
		$this->set('isDeletable', true);

		//$comments = $this->ReservationLocation->getCommentsByContentKey($blogEntry['ReservationLocation']['key']);
		//$this->set('comments', $comments);

		// プライベートルームは除外する
		$roomConditions = [
			//'Room.space_id !=' => Space::PRIVATE_SPACE_ID,
		];
		$this->RoomsForm->setRoomsForCheckbox($roomConditions);

		$this->render('form');
	}

/**
 * delete method
 *
 * @throws InternalErrorException
 * @return void
 */
	public function delete() {
		$this->request->allowMethod('post', 'delete');

		$key = $this->request->data['ReservationLocation']['key'];
		//$blogEntry = $this->ReservationLocation->getWorkflowContents('first', array(
			//'recursive' => 0,
		//	'conditions' => array(
		//		'ReservationLocation.key' => $key
		//	)
		//));

		// 権限チェック
		//if ($this->ReservationLocation->canDeleteWorkflowContent($blogEntry) === false) {
		//	return $this->throwBadRequest();
		//}
		$conditions = [
			'ReservationLocation.key' => $key
		];
		if ($this->ReservationLocation->deleteAll($conditions) === false) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		return $this->redirect(
			NetCommonsUrl::actionUrl(
				array(
					'controller' => 'reservation_locations',
					'action' => 'index',
					'frame_id' => Current::read('Frame.id'),
					//'block_id' => Current::read('Block.id')
				)
			)
		);
	}

/**
 * 施設並び替え
 *
 * @return void
 */
	public function sort() {
		if ($this->request->is('post')) {
			if ($this->ReservationLocation->saveWeights($this->data)) {
				$this->redirect(['action' => 'index', 'frame_id' => Current::read('Frame.id')]);
				return;
			}
			$this->NetCommons->handleValidationError($this->ReservationLocation->validationErrors);

		} else {

			//条件
			$conditions = array(
				'ReservationLocation.language_id' => Current::read('Language.id'),
			);
			if (isset($this->params['named']['category_id'])) {
				$conditions['ReservationLocation.category_id'] = $this->params['named']['category_id'];
			}
			$query = [
				'conditions' => $conditions,
				'recursive' => 0,
				'limit' => PHP_INT_MAX,
				'maxLimit' => PHP_INT_MAX
			];

			$this->Paginator->settings = [
				'ReservationLocation' => $query
			];
			$reservationLocations = $this->Paginator->paginate('ReservationLocation');
			$this->set('reservationLocations', $reservationLocations);

			$this->request->data['ReservationLocations'] = $reservationLocations;
			$this->request->data['Frame'] = Current::read('Frame');
		}
	}
}
