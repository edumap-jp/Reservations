<?php
/**
 * Reservation Many kind Button Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('ReservationPermissiveRooms', 'Reservations.Utility');

/**
 * Reservation Many kind Button Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\View\Helper
 */
class ReservationButtonHelper extends AppHelper {

/**
 * Creatable status
 *
 * @var mixed
 */
	protected $_isCreatable = array();

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'Html',
		'Form',
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
		'NetCommons.Button',
		'NetCommons.LinkButton',
		'Reservations.ReservationCommon',
		'Reservations.ReservationUrl',
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
 * makeGlyphiconPlusWithUrl
 *
 * Url付き追加アイコン生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param arrya &$vars 施設予約情報
 * @return string HTML
 */
	public function makeGlyphiconPlusWithUrl($year, $month, $day, &$vars) {
		$url = $this->_makeAddUrl($vars, $year, $month, $day);
		$options = array(
			'url' => $url,
			'button' => 'plus-link'
		);
		$html = $this->getAddButton($vars, $options);
		return $html;
	}
/**
 * makeGlyphiconPlusWithTimeUrl
 *
 * Time指定Url付き追加アイコン生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param int $hour 時
 * @param arrya &$vars 施設予約情報
 * @return string HTML
 */
	public function makeGlyphiconPlusWithTimeUrl($year, $month, $day, $hour, &$vars) {
		$url = $this->_makeAddUrl($vars, $year, $month, $day, $hour);
		$options = array(
			'url' => $url,
			'button' => 'plus-link'
		);
		$html = $this->getAddButton($vars, $options);
		return $html;
	}
/**
 * getAddButton
 *
 * 予定登録のための「追加」ボタン表示
 * どこか一部屋でも予定登録できる対象の空間があればボタンは表示されます
 *
 * @param array $vars 施設予約情報
 * @param array $options オプション情報
 * @return string
 */
	public function getAddButton($vars, $options = null) {
		//debug($vars);
		// $vars['style']よって判定わける　*_by_categoryならカテゴリ別　*_by_locationなら施設別
		// カテゴリ別のボタンならひとつでも予約可能な施設があればボタンを表示
		// 施設別表示なら予約可能な施設ならボタン表示

		// FIXME
		// フレームIDがないと編集できないため仮処置
		//$frameId = Current::read('Frame.id');
		//if (! $frameId) {
		//	return '';
		//}
		//
		//// まだ作成可能かどうかの判断フラグが設定されていない場合
		//// まず設定する
		//$frameId = Current::read('Frame.id');
		//if (Hash::get($this->_isCreatable, $frameId) === null) {
		//	$rooms = ReservationPermissiveRooms::getCreatableRoomIdList();
		//	if (empty($rooms)) {
		//		$this->_isCreatable[$frameId] = false;
		//	} else {
		//		$intersectRoom = array_intersect_key($rooms, $vars['exposeRoomOptions']);
		//		if (empty($intersectRoom)) {
		//			$this->_isCreatable[$frameId] = false;
		//		} else {
		//			$this->_isCreatable[$frameId] = true;
		//		}
		//	}
		//}
		//// 判断フラグがOFFの場合から文字列を返す
		//if ($this->_isCreatable[$frameId] === false) {
		//	return '';
		//}
		// どこかに書きこみ可能なルームが一つでもあれば追加ボタンを出す

		if (Current::read('ReservationReservable') === false) {
			return '&nbsp;'; // 空文字列にするとborder消えるので。
		}
		// 追加画面へのURL指定がある場合（時間とか日付とか)
		if (isset($options['url'])) {
			$url = $options['url'];
		} else {
			$url = $this->_makeAddUrl($vars, $vars['year'], $vars['month'], $vars['day']);
		}
		// ボタンで出すか、＋マークリンクで出すか
		if (isset($options['button']) && $options['button'] == 'plus-link') {
			$html = $this->NetCommonsHtml->link('+', $url, array(
				'class' => 'pull-right reservation-edit-plus-icon',
				'escape' => false
			));
		} else {
			$html = $this->LinkButton->add('', $url);
		}
		return $html;
	}

/**
 * _makeAddUrl
 *
 * @param array $vars 施設予約情報
 * @param int $year 年
 * @param int $mon 月
 * @param int $day 日
 * @param int $hour 時間
 * @return string url
 */
	protected function _makeAddUrl($vars, $year, $mon, $day, $hour = null) {
		if ($hour !== null) {
			$url = $this->ReservationUrl->makeEditUrlWithTime($year, $mon, $day, $hour, $vars);
		} else {
			$url = $this->ReservationUrl->makeEditUrl($year, $mon, $day, $vars);
		}
		//$url = str_replace('reservation_plans/edit', 'reservation_plans/add', $url);
		$url['action'] = 'add';
		// location_key
		$url['?']['location_key'] = Current::read('ReservationLocation.key');
		return $url;
	}

/**
 * getEditButton
 *
 * view.ctpから呼び出されることを想定
 * $eventの予定が閲覧者にとって編集可能なものであるかどうかを判断する
 *
 * @param array $vars 施設予約情報
 * @param array $event 予約
 * @return string 編集ボタンHTML
 */
	public function getEditButton($vars, $event) {
		// FIXME
		// フレームIDがないと編集できないため仮処置
		$frameId = Current::read('Frame.id');
		if (! $frameId) {
			return '';
		}

		$roomId = $event['ReservationEvent']['room_id'];
		// それ以外の時
		$canEdit = ReservationPermissiveRooms::isEditable($roomId);
		$canCreate = ReservationPermissiveRooms::isCreatable($roomId);
		// 表示ルームにおける自分の権限がeditable以上なら無条件に編集可能
		// creatbleのとき=自分が作ったデータならOK
		if (!$canCreate) {
			return '';
		}
		if (!$canEdit) {
			if ($event['ReservationEvent']['created_user'] != Current::read('User.id')) {
				return '';
			}
		}
		$html = $this->Button->editLink('', array(
			'controller' => 'reservation_plans',
			'action' => 'edit',
			'key' => $event['ReservationEvent']['key'],
			'frame_id' => Current::read('Frame.id'),
			'block_id' => '',
			'?' => array(
				'year' => $vars['year'],
				'month' => $vars['month'],
				'day' => $vars['day'],
			)
		));
		return $html;
	}

}
