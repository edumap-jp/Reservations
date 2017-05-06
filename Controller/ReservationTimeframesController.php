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
	public $layout = 'NetCommons.setting';

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
				'timezone' => Current::read('User.timezone'),
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
			// start_time, end_timeを時間枠のタイムゾーンに変換してH:i形式へ
			$timeframeTimeZone = new DateTimeZone($timeframe['ReservationTimeframe']['timezone']);
			$startDate = new DateTime(
				$timeframe['ReservationTimeframe']['start_time'], new DateTimeZone('UTC')
			);

			$startDate->setTimezone($timeframeTimeZone);
			$timeframe['ReservationTimeframe']['start_time'] = $startDate->format('H:i');

			$endDate = new DateTime(
				$timeframe['ReservationTimeframe']['end_time'], new DateTimeZone('UTC')
			);
			$endDate->setTimezone($timeframeTimeZone);
			$timeframe['ReservationTimeframe']['end_time'] = $endDate->format('H:i');

			$this->request->data['ReservationTimeframe'] = $timeframe['ReservationTimeframe'];

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
