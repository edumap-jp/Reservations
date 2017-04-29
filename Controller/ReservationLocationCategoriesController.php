<?php
/**
 * 施設カテゴリ設定 Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppController', 'Reservations.Controller');

/**
 * 施設カテゴリ設定 Controller
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @package NetCommons\Reservations\Controller
 */
class ReservationLocationCategoriesController extends ReservationsAppController {

/**
 * layout
 *
 * @var array
 */
	public $layout = 'NetCommons.setting';

/**
 * use models
 *
 * @var array
 */
	public $uses = array(
		'Reservations.Reservation',
	);

/**
 * use components
 *
 * @var array
 */
	public $components = array(
		'Categories.CategoryEdit',
		'Reservations.ReservationSettings', //NetCommons.Permissionは使わず、独自でやる
	);

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
		'Blocks.BlockTabs', // 設定内容はReservationSettingsComponentにまとめた
	);

/**
 * edit
 *
 * @return void
 */
	public function edit() {
		if ($this->request->is(['put', 'post'])) {
			//登録処理
			if ($this->Reservation->save($this->data)) {
				//正常の場合
				$this->NetCommons->setFlashNotification(__d('net_commons', 'Successfully saved.'), array(
					'class' => 'success',
				));

				$url = NetCommonsUrl::actionUrl(
					array(
						'controller' => 'reservation_location_categories',
						'action' => 'edit',
						'frame_id' => Current::read('Frame.id'),
					)
				);
				$this->redirect($url);
				return;
			}
			$this->NetCommons->handleValidationError($this->Reservation->validationErrors);

		} else {
			//表示処理(初期データセット)
			$this->Reservation->recursive = -1;
			if (! $reservation = $this->Reservation->findByBlockKey(Current::read('Block.key'))) {
				return $this->throwBadRequest();
			}
			$this->request->data = Hash::merge($this->request->data, $reservation);
			$this->request->data = Hash::merge($this->request->data, ['Block' => Current::read('Block')]);
		}
	}
}
