<?php
/**
 * 施設設定 Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppController', 'Reservations.Controller');
App::uses('ReservationLocationReservable', 'Reservations.Model');

/**
 * 施設設定 Controller
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @package NetCommons\Reservations\Controller
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
//		'NetCommons.Permission' => array(
//			//アクセスの権限
//			'allow' => array(
//				'edit' => 'page_editable',
//			),
//		),
		//'Workflow.Workflow',

		'Categories.Categories',
		//'Blogs.ReservationLocationPermission',
		'NetCommons.NetCommonsTime',
		'Paginator',
		'Rooms.RoomsForm',
		'Reservations.ReservationSettings', //NetCommons.Permissionは使わず、独自でやる
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

		'Blocks.BlockTabs', // 設定内容はReservationSettingsComponentにまとめた
		'Blocks.BlockRolePermissionForm', // 設定内容はReservationSettingsComponentにまとめた
		'Rooms.RoomsForm',
		'Reservations.ReservationLocation',
		'Groups.GroupUserList',
		'Users.UserSearch'
	);

/**
 * beforeFilter
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
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

		$this->_processPermission();

		// 施設管理者保持
		if ($this->request->is('post')) {
//			$this->ReservationLocation->create();

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

			$isMyUser = false;
		} else {
			$newLocation = $this->ReservationLocation->createLocation();
			$this->request->data['ReservationLocation'] = $newLocation['ReservationLocation'];
			$isMyUser = true;
		}

		//施設管理者のデータ取得
		$this->request->data = $this->ReservationLocationsApprovalUser->getSelectUsers(
			$this->request->data, $isMyUser
		);

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
			$reservables = Hash::combine(
				$reservables,
				'{n}.ReservationLocationReservable.role_key',
				'{n}.ReservationLocationReservable.value'
			);
		} else {
			// default
			$reservables = ReservationLocationReservable::$defaultReservables;
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
		$this->request->data =
			$this->ReservationLocationsApprovalUser->getSelectUsers($this->request->data, false);

		$this->_processPermission($key);

		if ($this->request->is(array('post', 'put'))) {

			$this->ReservationLocation->create();

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
			// start_time, end_timeを施設のタイムゾーンに変換してH:i形式へ
			$locationTimeZone = new DateTimeZone($reservationLocation['ReservationLocation']['timezone']);
			$startDate = new DateTime($reservationLocation['ReservationLocation']['start_time'], new DateTimeZone('UTC'));

			$startDate->setTimezone($locationTimeZone);
			$reservationLocation['ReservationLocation']['start_time'] = $startDate->format('H:i');

			$endDate = new DateTime($reservationLocation['ReservationLocation']['end_time'], new DateTimeZone('UTC'));
			$endDate->setTimezone($locationTimeZone);
			$reservationLocation['ReservationLocation']['end_time'] = $endDate->format('H:i');

			$this->request->data['ReservationLocation'] = $reservationLocation['ReservationLocation'];
			$approvalUsers = $this->ReservationLocationsApprovalUser->find('all', ['conditions' => [
				'location_key' => $key
			]]);
			foreach ($approvalUsers as $approvalUser) {
				$this->request->data['selectUsers'][] = ['User' => $approvalUser['User']];
			}

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
