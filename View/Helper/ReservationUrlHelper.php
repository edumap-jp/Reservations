<?php
/**
 * Reservation Url Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');
App::uses('ReservationFrameSetting', 'Reservations.Model');

/**
 * Reservation url Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\View\Helper
 */
class ReservationUrlHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
		'NetCommons.BackTo',
		'Reservations.ReservationCommon',
	);

/**
 * makePlanShowUrl
 *
 * 予定表示Url生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param array $plan 予定
 * @param bool $isArray 配列での戻り値を求めているか
 * @return string url
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function makePlanShowUrl($year, $month, $day, $plan, $isArray = false) {
		if ($isArray) {
			$url = $this->getReservationUrlAsArray(array(
				'plugin' => 'reservations',
				'controller' => 'reservation_plans',
				'action' => 'view',
				'key' => $plan['ReservationEvent']['key'],
				'frame_id' => Current::read('Frame.id'),
			));
		} else {
			$url = $this->getReservationUrl(array(
				'plugin' => 'reservations',
				'controller' => 'reservation_plans',
				'action' => 'view',
				'key' => $plan['ReservationEvent']['key'],
				'frame_id' => Current::read('Frame.id'),
			));
		}
		return $url;
	}

/**
 * makeEditUrl
 *
 * 編集画面URL生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param array &$vars 施設予約情報
 * @return string Url
 */
	public function makeEditUrl($year, $month, $day, &$vars) {
		$options = array(
			'plugin' => 'reservations',
			'controller' => 'reservation_plans',
			'action' => 'edit',
			'frame_id' => Current::read('Frame.id'),
			'?' => array(
				'year' => $year,
				'month' => $month,
				'day' => $day,
			)
		);
		$url = $this->getReservationUrlAsArray($options);
		return $url;
	}
/**
 * makeEditUrlWithTime
 *
 * 編集画面URL生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param int $hour 時
 * @param array &$vars 施設予約情報
 * @return string Url
 */
	public function makeEditUrlWithTime($year, $month, $day, $hour, &$vars) {
		$options = array(
			'plugin' => 'reservations',
			'controller' => 'reservation_plans',
			'action' => 'edit',
			'frame_id' => Current::read('Frame.id'),
			'?' => array(
				'year' => $year,
				'month' => $month,
				'day' => $day,
				'hour' => $hour,
			)
		);
		$url = $this->getReservationUrlAsArray($options);
		return $url;
	}

/**
 * getReservationDailyUrl
 *
 * 施設予約日次URL取得
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param array &$vars 施設予約情報
 * @return string URL
 */
	public function getReservationDailyUrl($year, $month, $day, &$vars) {
		$url = $this->getReservationUrl(array(
			'plugin' => 'reservations',
			'controller' => 'reservations',
			'action' => 'index',
			'block_id' => '',
			'frame_id' => Current::read('Frame.id'),
			'?' => array(
				'style' => $vars['style'],
				'year' => $year,
				'month' => $month,
				'day' => $day,
			)
		));
		return $url;
	}

/**
 * getBackFirstButton
 *
 * 最初の画面に戻るUrlリンクボタンの取得
 *
 * @param array $vars 施設予約情報
 * @return string URL
 */
	public function getBackFirstButton($vars) {
		// urlパラメタにstyleがなくて、表示画面がデフォルトの画面と一緒ならこのボタンは不要
		// 本当は共通関数[getQueryParam]を使いたかったが、
		// AppControllerに作っちゃったためHelperから呼びづらく、似たコードを書いてしまった
		// 許してほしい
		$isNotMain = Hash::get($this->request->params, 'requested');
		$frameId = Hash::get($this->request->query, 'frame_id');

		if ($frameId === null || $frameId != Current::read('Frame.id') || $isNotMain) {
			return '';
		}
		//return $this->BackTo->indexLinkButton(__d('reservations', 'Back to First view'));
		return $this->BackTo->pageLinkButton(__d('reservations', 'Back'));
	}

/**
 * getReservationUrl
 *
 * URL取得汎用関数
 *
 * @param array $arr URL作成のためのパラメータ配列
 * @return string URL文字列
 */
	public function getReservationUrl($arr) {
		return Router::url(NetCommonsUrl::actionUrlAsArray($arr));
	}
/**
 * getReservationUrlAsArray
 *
 * URL取得汎用関数
 *
 * @param array $arr URL作成のためのパラメータ配列
 * @return array URL配列
 */
	public function getReservationUrlAsArray($arr) {
		$ret = NetCommonsUrl::actionUrlAsArray($arr);
		$ret['block_id'] = '';
		return $ret;
	}
}
