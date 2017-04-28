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
//		'Reservations.ReservationFrameSetting',
		'Reservations.Reservation',
//		'Blocks.Block',
		//'Reservations.ReservationEntry',¢sa
	);

/**
 * use components
 *
 * @var array
 */
	public $components = array(
//		'NetCommons.Permission' => array(
//			//アクセスの権限
//			'allow' => array(
//				'index,add,edit,delete' => 'block_editable',
//			),
//		),
//		'Paginator',
		'Categories.CategoryEdit',
		'Reservations.ReservationSettings', //NetCommons.Permissionは使わず、独自でやる
	);

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
//		'Blocks.BlockForm',
		'Blocks.BlockTabs', // 設定内容はReservationSettingsComponentにまとめた
//		'Blocks.BlockIndex',
		//'Blocks.Block',
//		'Likes.Like',
	);

/**
 * beforeFilter
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
//
//		//CategoryEditComponentの削除
//		if ($this->params['action'] === 'index') {
//			$this->Components->unload('Categories.CategoryEdit');
//		}
	}

/**
 * index
 *
 * @return void
 */
	//public function index() {
	//	$this->Paginator->settings = array(
	//		'Reservation' => $this->Reservation->getBlockIndexSettings()
	//	);
	//
	//	$blogs = $this->Paginator->paginate('Reservation');
	//	if (! $blogs) {
	//		$this->view = 'Blocks.Blocks/not_found';
	//		return;
	//	}
	//	$this->set('blogs', $blogs);
	//	$this->request->data['Frame'] = Current::read('Frame');
	//}

/**
 * add
 *
 * @return void
 */
	//public function add() {
	//	$this->view = 'edit';
	//
	//	if ($this->request->is('post')) {
	//		//登録処理
	//		if ($this->Reservation->saveReservation($this->data)) {
	//			$this->redirect(NetCommonsUrl::backToIndexUrl('default_setting_action'));
	//		}
	//		$this->NetCommons->handleValidationError($this->Reservation->validationErrors);
	//
	//	} else {
	//		//表示処理(初期データセット)
	//		$this->request->data = $this->Reservation->createReservation();
	//		$frameSetting = $this->ReservationFrameSetting->getReservationFrameSetting();
	//		$this->request->data = Hash::merge($this->request->data, $frameSetting);
	//		$this->request->data['Frame'] = Current::read('Frame');
	//	}
	//}

/**
 * edit
 *
 * @return void
 */
	public function edit() {
		if ($this->request->is(['put', 'post'])) {
			//登録処理
			if ($this->Reservation->save($this->data)) {
				$this->redirect(NetCommonsUrl::backToIndexUrl('default_setting_action'));
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
			//$frameSetting = $this->ReservationFrameSetting->getReservationFrameSetting();
			//$this->request->data = Hash::merge($this->request->data, $frameSetting);
			//$this->request->data['Frame'] = Current::read('Frame');
		}
	}

/**
 * delete
 *
 * @return void
 */
	//public function delete() {
	//	if ($this->request->is('delete')) {
	//		if ($this->Reservation->deleteReservation($this->data)) {
	//			return $this->redirect(NetCommonsUrl::backToIndexUrl('default_setting_action'));
	//		}
	//	}
	//
	//	return $this->throwBadRequest();
	//}
}
