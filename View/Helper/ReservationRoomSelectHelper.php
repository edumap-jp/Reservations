<?php
/**
 * Reservation RoomSelect Helper
 *
 * TODO:後でファイル削除する
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
//App::uses('AppHelper', 'View/Helper');
///**
// * Reservation RoomSelect Helper
// *
// * @author Allcreator Co., Ltd. <info@allcreator.net>
// * @package NetCommons\Reservations\View\Helper
// */
//class ReservationRoomSelectHelper extends AppHelper {
//
///**
// * Other helpers used by FormHelper
// *
// * @var array
// */
//	public $helpers = array(
//		'NetCommons.NetCommonsForm',
//		'NetCommons.NetCommonsHtml',
//		'Form',
//		'Rooms.Rooms'
//	);
//
///**
// * spaceSelector
// *
// * @param array $spaces space information
// * @return string
// */
//	public function spaceSelector($spaces) {
//		$html = '';
//
//		$roomTreeList = $this->_View->viewVars['roomTreeList'];
//		$rooms = $this->_View->viewVars['rooms'];
//		$html .= '<table class="table table-hover">';
//		foreach ($spaces as $space) {
//			$title = $this->Rooms->roomName($space);
//			if ($space['Space']['id'] == Space::PRIVATE_SPACE_ID) {
//				$html .= $this->_getSpaceListElm($title, $space);
//			} else {
//				$html .= $this->roomSelector(
//					$roomTreeList[$space['Space']['id']], $rooms[$space['Space']['id']]);
//			}
//		}
//		// 全会員
//		$html .= $this->_getSpaceListElm(
//			__d('reservations', 'All the members'),
//			array('Room' => array('id' => Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID)))
//		);
//
//		$html .= '</table>';
//
//		return $html;
//	}
//
///**
// * _getSpaceListElm
// *
// * スペースを表すListタグを取得する
// *
// * @param string $title liに表示する文字列
// * @param array $room ルーム情報
// * @return string
// */
//	protected function _getSpaceListElm($title, $room) {
//		$roomId = $room['Room']['id'];
//		$checkbox = $this->_getRoomSelectCheckbox($roomId);
//		$html = '';
//		$ngClass = $this->__getNgClass($roomId, 'success', '');
//		$html .= '<tr><td ' . $ngClass . '>';
//		$html .= $checkbox;
//
//		$className = 'reservation-plan-mark-';
//		if (isset($room['Room']['space_id'])) {
//			$className .= ($room['Room']['space_id'] == Space::PRIVATE_SPACE_ID) ? 'private' : 'public';
//		} else {
//			$className .= 'member';
//		}
//		$html .= "<span class='reservation-plan-mark {$className}'>";
//
//		$html .= h($title);
//		$html .= '</span>';
//
//		$html .= '</td></tr>';
//
//		//$html .= '</div></div>';
//		return $html;
//	}
//
///**
// * _getRoomSelectCheckbox
// *
// * 表示非表示の目アイコンボタン取得（実態はcheckbox
// *
// * @param int $roomId ルームID
// * @return string
// */
//	protected function _getRoomSelectCheckbox($roomId) {
//		$settingId = $this->_View->viewVars['settingId'];
//		$ngModel = $this->__getNgModelName($roomId);
//
//		$ngClassForIcon = $this->__getNgClass(
//		$roomId, 'ng-not-empty', 'ng-empty', array('glyphicon'));
//
//		$ngClassForBtn = '';
//
//		$html = '';
//		// トグルボタンにしたいがこれをやるとng-modelが機能しなくなる....FUJI
//		//$html = '<div class="btn-group" data-toggle="buttons">';
//		$html .= $this->NetCommonsForm->hidden(
//		//$html .= $this->NetCommonsForm->checkbox( //test
//
//			'ReservationFrameSettingSelectRoom.' . $roomId . '.room_id', array('value' => ''));
//		$html .= '<label ' . $ngClassForBtn . '>';
//		$html .= '<i ' . $ngClassForIcon . '></i>';
//		$html .= $this->NetCommonsForm->checkbox(
//			'ReservationFrameSettingSelectRoom.' . $roomId . '.room_id', array(
//			'div' => false,
//			'label' => false,
//			'hiddenField' => false,
//			'ng-model' => $ngModel,
//			'ng-true-value' => "'" . $roomId . "'",
//			'ng-false-value' => "''",
//			'value' => $roomId,
//			//'class' => 'nc-checkbox-toggle-btn',
//		));
//		$html .= '</label>';
//
//		$html .= $this->NetCommonsForm->hidden(
//		//$html .= $this->NetCommonsForm->checkbox( //test
//			'ReservationFrameSettingSelectRoom.' . $roomId . '.reservation_frame_setting_id',
//				array('value' => $settingId));
//		// トグルボタンにしたいがこれをやるとng-modelが機能しなくなる....FUJI
//		//$html .= '</div>';
//		return $html;
//	}
//
///**
// * __getNgModelName
// *
// * ルーム選択のためのng-model文字列を返す
// *
// * @param int $roomId ルームID
// * @return string
// */
//	private function __getNgModelName($roomId) {
//		return 'data.reservationFrameSettingSelectRoom[' . $roomId . '].roomId';
//	}
//
///**
// * __getNgClass
// *
// * ルーム選択のためのng-class設定文字列を返す
// *
// * @param int $roomId ルームID
// * @param string $trueClass チェックされているときに用いるクラス
// * @param string $falseClass チェックされていないときに用いるクラス
// * @param array $defaultClassArr チェックに関係なく用いるクラス
// * @return string
// */
//	private function __getNgClass($roomId, $trueClass, $falseClass, $defaultClassArr = array()) {
//		$defaultClass = '';
//		foreach ($defaultClassArr as $cls) {
//			$defaultClass .= '\'' . $cls . '\', ';
//		}
//		$ngModel = $this->__getNgModelName($roomId);
//		$ngClass = 'ng-class="[' . $defaultClass . '{\'' .
//			$trueClass . '\': (' . $ngModel . '==\'' . $roomId . '\'), \'' .
//			$falseClass . '\': !(' . $ngModel . '==\'' . $roomId . '\')}]"';
//		return $ngClass;
//	}
///**
// * roomSelector
// *
// * @param array $roomTreeList room tree list information
// * @param array $rooms room information
// * @return string
// */
//	public function roomSelector($roomTreeList, $rooms) {
//		$html = '';
//		$className = 'reservation-plan-mark-';
//
//		if ($roomTreeList) {
//			foreach ($roomTreeList as $roomId => $tree) {
//				$className = 'reservation-plan-mark-';
//
//				if (Hash::get($rooms, $roomId)) {
//					$nest = substr_count($tree, Room::$treeParser);
//					$ngClass = $this->__getNgClass($roomId, 'success', '', array());
//					$html .= '<tr><td ' . $ngClass . '>';
//					if ($nest > 0) {
//						$nest--;
//					}
//
//					for ($i = 0; $i < $nest; $i++) {
//						$html .= '<span class="rooms-tree"></span>';
//					}
//					$className .= ($rooms[$roomId]['Room']['space_id'] == Space::COMMUNITY_SPACE_ID) ?
//						'group' : 'public';
//					$html .= $this->_getRoomSelectCheckbox($roomId);
//					$html .= "<span class='reservation-plan-mark {$className}'>";
//					$html .= $this->Rooms->roomName($rooms[$roomId]);
//					$html .= '</span>';
//					$html .= '</td></tr>';
//
//				}
//			}
//		}
//		return $html;
//	}
//}
