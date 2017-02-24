<?php
/**
 * ReservationsController
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('ReservationsAppController', 'Reservations.Controller');

App::uses('CalendarTime', 'Calendars.Utility');
/**
 * Reservations Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
class ReservationsController extends ReservationsAppController {

/**
 * @var array use models
 */
	public $uses = array(
		'Reservations.Reservation',
		//'Categories.Category',
		//'Workflow.WorkflowComment',
	);

/**
 * @var array helpers
 */
	public $helpers = [
		'Calendars.CalendarTurnCalendar',
		'Calendars.CalendarMonthly',

		'NetCommons.BackTo',
		'NetCommons.NetCommonsForm',
		'Workflow.Workflow',
		'NetCommons.NetCommonsTime',
		'NetCommons.TitleIcon',

	];

/**
 * @var array components
 */
	public $components = [
		'Calendars.Calendars',
		'NetCommons.Permission' => array(
			//アクセスの権限
			'allow' => array(
				'add,edit,delete' => 'content_creatable',
			),
		),
		'Workflow.Workflow',

		'Categories.Categories',
		'NetCommons.NetCommonsTime',
	];

/**
 * 月間カレンダ
 *
 * @return void
 */
	public function index() {
		$frameId = Current::read('Frame.id');
		$languageId = Current::read('Language.id');

		// TODO パラメータ設定
		$vars['mInfo'] = [
			'yearOfPrevMonth' => 2017,
			'prevMonth' => 1,
			'daysInPrevMonth' => 31,
			'yearOfNextMonth' => 2017,
			'nextMonth' =>  3,
			'daysInNextMonth' => 31,
			'year' =>  2017,
			'month' =>  2,
			'wdayOf1stDay' => 3,
			'daysInMonth' => 28,
			'wdayOfLastDay' => 2,
			'numOfWeek' => 5,
		];
		$vars['year'] = 2017;
		$vars['month'] = 2;
		$vars['day'] = 1;
		$vars['style'] = [];
		$vars['today'] = [
			'year' => 2017,
			'month' => 02,
			'day' => 01,
			'hour' => 17,
			'min' => 28,
			'sec' => 36,
		];
		$vars['plans'] = [];

		App::uses('CalendarPermissiveRooms', 'Calendars.Utility');
		$perm['roomInfos'] =[];
		CalendarPermissiveRooms::setRoomPermRoles($perm);

		$vars['holidays'] = [];
		$this->set('frameId', $frameId);
		$this->set('languageId', $languageId);
		$this->set('vars', $vars);
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		$this->set('isEdit', false);
		//$this->_prepare();

		$reservation = $this->Reservation->create();
		$this->set('reservation', $reservation);

		if ($this->request->is('post')) {
			$this->Reservation->create();
			$this->request->data['Reservation']['blog_key'] =
				$this->_blogSetting['BlogSetting']['blog_key'];

			// set status
			$status = $this->Workflow->parseStatus();
			$this->request->data['Reservation']['status'] = $status;

			// set block_id
			$this->request->data['Reservation']['block_id'] = Current::read('Block.id');
			// set language_id
			$this->request->data['Reservation']['language_id'] = Current::read('Language.id');
			if (($result = $this->Reservation->saveEntry($this->request->data))) {
				$url = NetCommonsUrl::actionUrl(
					array(
						'controller' => 'blog_entries',
						'action' => 'view',
						'block_id' => Current::read('Block.id'),
						'frame_id' => Current::read('Frame.id'),
						'key' => $result['Reservation']['key'])
				);
				return $this->redirect($url);
			}

			$this->NetCommons->handleValidationError($this->Reservation->validationErrors);

		} else {
			$this->request->data = $reservation;
		}

		$this->render('form');
	}
}
