<?php
/**
 * Reservation ExposeTarget Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('ReservationPermissiveRooms', 'Reservations.Utility');

/**
 * Reservation ExposeTarget Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\View\Helper
 */
class ReservationExposeTargetHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'Workflow.Workflow',
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
		'Form',
		'Rooms.Rooms',
		'Reservations.ReservationCategory',
		'Reservations.ReservationWorkflow',
	);

/**
 * Default Constructor
 *
 * @param View $View The View this helper is being attached to.
 * @param array $settings Configuration settings for the helper.
 */
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
	}

/**
 * makeSelectExposeTargetHtml
 *
 * 公開対象html生成
 *
 * @param array $event 対象予定データ
 * @param int $frameId フレームID
 * @param array $vars 施設予約情報
 * @param array $options 公開対象オプション情報
 * @param int $myself 自分自身のroom_id
 * @return string HTML
 */
	public function makeSelectExposeTargetHtml($event, $frameId, $vars, $options, $myself) {
		//option配列イメージ

		/*
		$options = array(
			'1' => __d('reservations', 'パブリックスペース'),
			'2' => __d('reservations', '開発部'),
			'3' => __d('reservations', 'デザインチーム'),
			'4' => __d('reservations', 'プログラマーチーム'),
			$myself => __d('reservations', '自分自身'),
			'6' => __d('reservations', '全会員'),
		);
		*/
		// 予定データが指定されていて、その予定のターゲットルームが表示形式設定で
		// 許可されているoptions（選択対象ルーム）の中にない場合は
		// URLの直叩きなどで、直接編集を試みているような場合と想定できる
		// そんなときはoptions配列に無理やりターゲットルームの選択肢を加えておく
		if ($event) {
			$eventRoomId = $event['ReservationEvent']['room_id'];
			if (! isset($options[$eventRoomId])) {
				$options[$eventRoomId] = $vars['allRoomNames'][$eventRoomId];
			}
		}
		// 渡されたoptionから投稿権限のないものを外す
		$rooms = ReservationPermissiveRooms::getCreatableRoomIdList();
		$targetRooms = array_intersect_key($options, $rooms);

		$html = $this->NetCommonsForm->label(
			'ReservationActionPlan.plan_room_id' . Inflector::camelize('room_id'),
			__d('reservations', 'Category') . $this->_View->element('NetCommons.required'));

		// 発行権限がなくて、かつ、すでに発行済みデータの場合は空間変更を認めない
		// 固定的な文字列と、hiddenを設定して返す
		if (Hash::get($event, 'ReservationEvent.is_published') &&
			! $this->ReservationWorkflow->canDelete($event)) {
			$html .= '<div>';
			$html .= $this->ReservationCategory->getCategoryName($vars, $event);
			$html .= '<span class="help-block">';
			$html .= __d('reservations', 'You can not change the target space  after published.');
			$html .= '</span>';
			$html .= $this->NetCommonsForm->hidden('ReservationActionPlan.plan_room_id');
			$html .= '</div>';
		} else {
			$html .= $this->NetCommonsForm->select('ReservationActionPlan.plan_room_id', $targetRooms, array(
				//select-expose-targetクラスをもつ要素のchangeをjqで捕まえています
				'class' => 'form-control select-expose-target',
				'empty' => false,
				'required' => true,
				//value値のoption要素がselectedになる。
				'value' => $this->request->data['ReservationActionPlan']['plan_room_id'],
				'data-frame-id' => $frameId,
				'data-myself' => $myself,
				'escape' => false,
			));
		}

		return $html;
	}
}
