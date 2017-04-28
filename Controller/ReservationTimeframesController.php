<?php
/**
 * 時間枠設定 Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppController', 'Reservations.Controller');

/**
 * 時間枠設定 Controller
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @package NetCommons\Reservations\Controller
 */
class ReservationTimeframesController extends ReservationsAppController {

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
		'Reservations.ReservationTimeframe',
	);

/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'Paginator',
		'Reservations.ReservationSettings', //NetCommons.Permissionは使わず、独自でやる
	);

/**
 * @var array helpers
 */
	public $helpers = array(
		'Blocks.BlockTabs', // 設定内容はReservationSettingsComponentにまとめた
	);

/**
 * index
 *
 * @return void
 */
	public function index() {
		$query = array();

		//条件
		$conditions = array(
			'ReservationTimeframe.language_id' => Current::read('Language.id'),
		);
		$query['conditions'] = $conditions;
		$query['order'] = 'ReservationTimeframe.start_time ASC';
		$query['recursive'] = 0;

		$this->Paginator->settings = $query;
		$timeframes = $this->Paginator->paginate('ReservationTimeframe');
		$this->set('reservationTimeframes', $timeframes);
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		$this->set('isEdit', false);

		if ($this->request->is('post')) {
			if ($this->ReservationTimeframe->saveTimeframe($this->request->data)) {
				$url = NetCommonsUrl::actionUrl(
					array(
						'controller' => 'reservation_timeframes',
						'action' => 'index',
						'frame_id' => Current::read('Frame.id'),
					)
				);
				return $this->redirect($url);
			} else {
				$this->NetCommons->handleValidationError($this->ReservationTimeframe->validationErrors);
			}

		} else {
			$newTimeframe = $this->ReservationTimeframe->create([
				'start_time' => '09:00',
				'end_time' => '18:00',
			]);
			$this->request->data = $newTimeframe;
		}

		$this->view = 'form';
	}

/**
 * edit method
 *
 * @return void
 */
	public function edit() {
		$this->set('isEdit', true);
		$this->set('isDeletable', true);

		if ($this->request->is('put')) {
			if ($this->ReservationTimeframe->saveTimeframe($this->request->data)) {
				$url = NetCommonsUrl::actionUrl(
					array(
						'controller' => 'reservation_timeframes',
						'action' => 'index',
						'frame_id' => Current::read('Frame.id'),
					)
				);
				return $this->redirect($url);
			}
			$this->NetCommons->handleValidationError($this->ReservationTimeframe->validationErrors);

		} else {
			$timeframe = $this->ReservationTimeframe->find('first', [
				'recursive' => -1,
				'conditions' => [
					'ReservationTimeframe.key' => $this->request->params['key'],
					'ReservationTimeframe.language_id' => Current::read('Language.id')
				]
			]);
			if (empty($timeframe)) {
				return $this->throwBadRequest();
			}
			$this->request->data = $timeframe;
		}

		$this->view = 'form';
	}

/**
 * delete method
 *
 * @throws InternalErrorException
 * @return void
 */
	public function delete() {
		if (! $this->request->is('delete')) {
			return $this->throwBadRequest();
		}

		if ($this->ReservationTimeframe->deleteTimeframe($this->request->data)) {
			return $this->redirect(
				NetCommonsUrl::actionUrl(
					array(
						'controller' => 'reservation_timeframes',
						'action' => 'index',
						'frame_id' => Current::read('Frame.id'),
					)
				)
			);
		} else {
			return $this->throwBadRequest();
		}
	}
}
