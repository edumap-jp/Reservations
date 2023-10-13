<?php
/**
 * Reservations Controller
 *
 * @property PaginatorComponent $Paginator
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppController', 'Reservations.Controller');
App::uses('NetCommonsTime', 'NetCommons.Utility');
App::uses('ReservationTime', 'Reservations.Utility');
App::uses('ReservationPermissiveRooms', 'Reservations.Utility');

/**
 * ReservationsController
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @package NetCommons\Reservations\Controller
 */
class ReservationsController extends ReservationsAppController {

/**
 * use models
 *
 * @var array
 */
	public $uses = array(
		'Reservations.ReservationRrule',
		'Reservations.ReservationEvent',
		'Reservations.ReservationFrameSetting',
		'Reservations.Reservation',
		'Reservations.ReservationEventShareUser',
		'Reservations.ReservationActionPlan',	//予定CRUDaction専用
		'Reservations.ReservationLocation',
		'Reservations.ReservationLocationsRoom',
		'Reservations.ReservationLocationReservable',
		'Reservations.ReservationTimeframe',
		'Holidays.Holiday',
		'Rooms.Room',
		'NetCommons.BackTo',
	);

/**
 * use component
 *
 * @var array
 */
	public $components = array(
		'NetCommons.Permission' => array(
			//アクセスの権限
			'allow' => array(
				//indexとviewは祖先基底クラスNetCommonsAppControllerで許可済なので、あえて書かない。
				//予定のCRUDはReservationsPlancontrollerが担当。このcontrollerは表示系conroller.とする。
			),
		),
		'Paginator',
		'Categories.Categories',
	);

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
		//'Workflow.Workflow',
		//'NetCommons.Date',
		//'NetCommons.DisplayNumber',
		//'NetCommons.Button',
		'Reservations.ReservationMonthly',
		'Reservations.ReservationTurnReservation',
		'Reservations.ReservationLegend',
		'Reservations.ReservationButton',
		'Reservations.ReservationWeeklyTimeline',
		'Reservations.ReservationUrl'
	);

/**
 * beforeRender
 *
 * @return void
 */
	//public function beforeFilter() {
	//	parent::beforeFilter();
	//
	//	// 以前はここでCurrentのブロックIDをチェックする処理があったが
	//	// 施設予約はCurrentのブロックID（＝現在表示中ページのブロックID）は
	//	// 表示データ上の意味がないのでチェックは行わない
	//	// 表示ブロックIDがないときは、パブリックTOPページで仮表示されることに話が決まった
	//
	//	//if (! $locations) {
	//	//	$this->setAction('emptyRender');
	//	//}
	//}

/**
 * index
 *
 * @return void
 */
	public function index() {
		$vars = array();
		$this->setReservationCommonCurrent($vars);
		$this->ReservationEvent->initSetting($this->Workflow);

		$style = $this->getQueryParam('style');
		if (! $style) {
			//style未指定の場合、ReservationFrameSettingモデルのdisplay_type情報から表示するctpを決める。

			$displayType = (int)Current::read('ReservationFrameSetting.display_type');
			$style = $this->__getStyleByDisplayType($displayType);

			$this->__setDefaultCategoryWhenDispTypeIsCategory($displayType);
		}

		$categoryId = Hash::get($this->request->params['named'], 'category_id');
		if ($categoryId === '') {
			// タブ切り替えや前後ページングで'カテゴリ選択'だとcategory_idが空文字になるので
			// そのときはnull（カテゴリの指定無し）にする
			$categoryId = null;
		}

		$locations = $this->ReservationLocation->getLocations($categoryId);

		if (empty($locations) && $categoryId === null) {
			//施設が1つも登録されてない
			$this->view = 'location_not_found';
			return;
		}

		if (empty($locations) && in_array($style,
				[ReservationsComponent::RESERVATION_STYLE_LACATION_WEEKLY,
					ReservationsComponent::RESERVATION_STYLE_LACATION_MONTHLY
				])) {

			$this->NetCommons->setFlashNotification(
				__d('reservations', 'Location not found in select category.'),
				array(
					'class' => 'info',
				)
			);

			$url = array(
				'?' => array(
					'frame_id' => Current::read('Frame.id'),
					'style' => $this->request->query('style'),
					'year' => $this->request->query('year'),
					'month' => $this->request->query('month'),
					'day' => $this->request->query('day'),
				)
			);
			$this->redirect($url);
		}
		$this->set('locations', $locations);

		$this->_storeRedirectPath($vars);

		$roomPermRoles = $this->ReservationEvent->prepareCalRoleAndPerm();
		ReservationPermissiveRooms::setRoomPermRoles($roomPermRoles);

		$ctpName = $this->_getCtpAndVars($style, $vars);

		$frameId = Current::read('Frame.id');
		$languageId = Current::read('Language.id');
		$this->set(compact('frameId', 'languageId', 'vars'));
		$this->set('unselectedCategory', $this->ReservationLocation->getCountUnselectedCategory());
		$this->render($ctpName);

		//$roomId = Current::read('Room.id');
		//$userId = Current::read('User.id');
	}

/**
 * 時間枠セット
 *
 * @return void
 */
	protected function _setTimeframe() {
		if (Current::read('ReservationFrameSetting.display_timeframe')) {
			$timeframes = $this->ReservationTimeframe->find('all', [
				'conditions' => [
					'ReservationTimeframe.language_id' => Current::read('Language.id')
				]
			]);
			$this->set('timeframes', $timeframes);
		}
	}
/**
 * カテゴリ別表がデフォルトのとき、初期表示では表示設定で設定されたカテゴリにする。
 *
 * @return void
 */
	protected function _setDefaultCategory() {
		$categoryId = Current::read('ReservationFrameSetting.category_id');
		//$categoryId = Hash::get($this->params['named'], 'category_id', $categoryId);
		$this->request->param('named.category_id', $categoryId);
	}

/**
 * _getMonthlyVars
 *
 * 月施設予約用変数取得
 *
 * @param array $vars カレンンダー情報
 * @return array $vars 月（縮小用）データ
 */
	protected function _getMonthlyVars($vars) {
		$this->setReservationCommonVars($vars);
		$vars['selectRooms'] = array();	//マージ前の暫定
		return $vars;
	}

/**
 * _getWeeklyVars
 *
 * 週単位変数取得
 *
 * @param array $vars カレンンダー情報
 * @return array $vars 週単位データ
 */
	protected function _getWeeklyVars($vars) {
		$this->setReservationCommonVars($vars);
		$vars['selectRooms'] = array();	//マージ前の暫定
		$vars['week'] = $this->getQueryParam('week');
		return $vars;
	}

/**
 * getDailyListVars
 *
 * 日単位（一覧）用変数取得
 *
 * @param array $vars カレンンダー情報
 * @return array $vars 日単位（一覧）データ
 */
	protected function _getDailyListVars($vars) {
		$this->setReservationCommonVars($vars);
		$vars['tab'] = 'list';
		return $vars;
	}

/**
 * 日単位（タイムライン）用変数取得
 *
 * @param array $vars カレンンダー情報
 * @return array $vars 日単位（タイムライン）データ
 */
	protected function _getDailyTimelineVars($vars) {
		$this->setReservationCommonVars($vars);
		$vars['tab'] = 'timeline';
		return $vars;
	}

/**
 * 日次施設予約変数取得
 *
 * @param array $vars カレンンダー情報
 * @return array $vars 日次施設予約変数
 */
	protected function _getDailyVars($vars) {
		//$tab = $this->getQueryParam('tab');
		//if ($tab === 'timeline') {
		$vars = $this->_getDailyTimelineVars($vars);
		//} else {
		//	$vars = $this->_getDailyListVars($vars);
		//}

		$vars['selectRooms'] = array();	//マージ前の暫定

		return $vars;
	}

/**
 * getScheduleVars
 *
 * スケジュール変数取得
 *
 * @param array $vars カレンンダー情報
 * @return array $vars スケジュール変数
 */
	//public function getScheduleVars($vars) {
	//	//$sort = $this->getQueryParam('sort');
	//	// スケジュール表示のときだけは直接覗くようにする(正式取得しない)
	//	// 理由１：スケジュール表示は左カラムから表示されない
	//	// 理由２：スケジュール表示の種別指定パラメータをデフォルト表示のときもqueryに入れている
	//	// 理由３：デフォ表示のときrequestedパラメータがないから、まるでよそ様フレーム処理に見える
	//	// 上記理由から直接見ないと処理できないし、直接見てもよそ様フレームと混同しないから
	//	$sort = $this->request->query['sort'];
	//	if ($sort === 'member') {
	//		$vars = $this->getMemberScheduleVars($vars);
	//	} else {
	//		$vars = $this->getTimeScheduleVars($vars);
	//	}
	//	return $vars;
	//}

/**
 * _getCtpAndVars
 *
 * ctpおよびvars取得
 *
 * @param string $style 表示タイプ
 * @param array &$vars 施設予約共通変数
 * @return string ctpNameを格納したstring
 */
	protected function _getCtpAndVars($style, &$vars) {
		$vars['style'] = $style;

		// プライベートルームか？
		Current::write('Reservations.accessPrivateRoom',
			(Current::read('Room.space_id') == Space::PRIVATE_SPACE_ID));

		if (in_array($vars['style'], ReservationsComponent::$stylesByLocation, true)) {
			$locationKey = $this->request->query('location_key');
			if ($locationKey) {
				$vars['location_key'] = $locationKey;
			} else {
				$vars['location_key'] = Current::read(
					'ReservationFrameSetting.location_key',
					Hash::get($this->viewVars['locations'], '0.ReservationLocation.key')
				);
			}
			// カテゴリ絞り込みの結果、指定した施設がなかったら1番目の施設を選択する。
			$locationList = Hash::combine($this->viewVars['locations'],
				'{n}.ReservationLocation.key',
				'{n}.ReservationLocation.location_name'
			//'{n}.ReservationLocation.category_id'
			);
			if (!isset($locationList[$vars['location_key']])) {
				$vars['location_key'] = Hash::get($this->viewVars['locations'], '0.ReservationLocation.key');
			}

			// ReservationLocation, ReservationReservableを設定する。
			$this->_setupCurrentValues4ByLocation($vars['location_key']);
		} else {
			// ReservationLocation, ReservationReservableを設定する。
			$this->_setupCurrentValues4ByCategory();
		}

		switch ($style) {
			case ReservationsComponent::RESERVATION_STYLE_CATEGORY_WEEKLY:
				//カテゴリー別 - 週表示
				$vars = $this->_getWeeklyVars($vars);
				break;
			case ReservationsComponent::RESERVATION_STYLE_CATEGORY_DAILY:
				//カテゴリー別 - 日表示
				$vars = $this->_getDailyVars($vars);
				$this->_setTimeframe();
				break;
			case ReservationsComponent::RESERVATION_STYLE_LACATION_MONTHLY:
				//施設別 - 月表示
				$vars = $this->_getMonthlyVars($vars);
				break;
			case ReservationsComponent::RESERVATION_STYLE_LACATION_WEEKLY:
				//施設別 - 週表示
				$vars = $this->_getWeeklyVars($vars);
				$this->_setTimeframe();
				break;
		}

		//if (in_array($vars['style'], ReservationsComponent::$reservationStylesByLocation, true)) {
		//	$vars['location_key'] = Hash::get($this->viewVars['locations'], '0.ReservationLocation.key');
		//}

		$ctpName = $vars['style'];
		return $ctpName;
	}

/**
 * Current にReservationLocation（施設別表示での施設)ReservationReservable(予約可能な施設があるか)を設定する
 *
 * @param string|null $locationKey 施設別ならReservationLocation.key カテゴリ別ならnull
 * @return void
 */
	protected function _setupCurrentValues4ByLocation($locationKey) {
		// 施設別表時
		$currentLocation = $this->ReservationLocation->find(
			'first',
			[
				'conditions' => [
					'ReservationLocation.key' => $locationKey,
					'ReservationLocation.language_id' => Current::read('Language.id')
				]
			]
		);
		Current::write('ReservationLocation', $currentLocation['ReservationLocation']);

		if (Current::read('Reservations.accessPrivateRoom')) {
			// プライベートルーム
			// 個人的な予約OKなら予約可能とする
			Current::write(
				'ReservationReservable',
				$currentLocation['ReservationLocation']['use_private']
			);
		} else {
			//施設への予約権限をセット
			Current::write(
				'ReservationReservable',
				$this->ReservationLocationReservable->isReservableByLocation($currentLocation)
			);
		}
	}

/**
 * Current にReservationLocation（施設別表示での施設)ReservationReservable(予約可能な施設があるか)を設定する
 *
 * @return void
 */
	protected function _setupCurrentValues4ByCategory() {
		// カテゴリ別表時
		// 施設が指定されてないなら、いずれかの施設で予約できれば予約権限ありと判定
		$reservable = false;
		// あらかじめ全施設の権限をロードしておく
		$this->ReservationLocationReservable->loadAll(
			$this->ReservationLocationReservable->getReadableRoomIds()
		);

		foreach ($this->viewVars['locations'] as $location) {
			if (Current::read('Reservations.accessPrivateRoom')) {
				// プライベートルーム
				if ($location['ReservationLocation']['use_private']) {
					$reservable = true;
				}
			} else {
				if ($this->ReservationLocationReservable->isReservableByLocation($location)) {
					$reservable = true;
				}
			}
		}

		Current::write('ReservationReservable', $reservable);
	}

/**
 * __setDefaultCategoryWhenDispTypeIsCategory
 *
 * @param int|string $displayType display type
 * @return void
 */
	private function __setDefaultCategoryWhenDispTypeIsCategory($displayType) {
		if (in_array($displayType, [
			ReservationsComponent::RESERVATION_DISP_TYPE_CATEGORY_WEEKLY,
			ReservationsComponent::RESERVATION_DISP_TYPE_CATEGORY_DAILY
		])) {
			$this->_setDefaultCategory();
		}
	}

/**
 * __getStyleByDisplayType
 *
 * @param int|string $displayType display type
 * @return string
 */
	private function __getStyleByDisplayType($displayType) {
		switch ($displayType) {
			case ReservationsComponent::RESERVATION_DISP_TYPE_CATEGORY_WEEKLY:
				$style = ReservationsComponent::RESERVATION_STYLE_CATEGORY_WEEKLY;
				break;
			case ReservationsComponent::RESERVATION_DISP_TYPE_CATEGORY_DAILY:
				$style = ReservationsComponent::RESERVATION_STYLE_CATEGORY_DAILY;
				break;
			case ReservationsComponent::RESERVATION_DISP_TYPE_LACATION_MONTHLY:
				$style = ReservationsComponent::RESERVATION_STYLE_LACATION_MONTHLY;
				break;
			case ReservationsComponent::RESERVATION_DISP_TYPE_LACATION_WEEKLY:
				$style = ReservationsComponent::RESERVATION_STYLE_LACATION_WEEKLY;
				break;
			default:
				$style = ReservationsComponent::RESERVATION_STYLE_DEFAULT;
		}
		return $style;
	}

/**
 * ajaxで施設の公開先ルームを取得する
 *
 * @return void
 */
	public function fetch_rooms_to_publish_reservation() {
		// get request params
		$locationKey = $this->request->query('location_key');
		// guard
		if (!$this->__validateLocationKey($locationKey)) {
			$this->throwBadRequest();
			return;
		}
		// 公開先ルーム取得
		$rooms = $this->__findRoomsToPublishReservation($locationKey);
		// jsonで返す
		$this->__jsonResponse($rooms);
	}

/**
 * __validateLocationKey
 *
 * @param mixed $locationKey 施設キー
 * @return bool
 */
	private function __validateLocationKey($locationKey) : bool {
		if (!is_string($locationKey)) {
			return false;
		}
		return true;
	}

/**
 * __findRoomsToPublishReservation
 *
 * @param string $locationKey 施設キー
 * @return array
 */
	private function __findRoomsToPublishReservation($locationKey) {
		$userId = Current::read('User.id');
		return $this->ReservationLocationsRoom->getReservableRoomsByLocationKey($locationKey, $userId);
	}

/**
 * __jsonResponse
 *
 * @param array $rooms Room data
 * @return void
 */
	private function __jsonResponse(array $rooms) {
		$result = [];
		$notSpecified = [
			'roomId' => 0,
			'name' => __d('reservations', '-- not specified --')
		];
		$result[] = $notSpecified;
		foreach ($rooms as $room) {
			$result[] = [
				'room_id' => $room['Room']['id'],
				'name' => $room['RoomsLanguage'][0]['name']
			];
		}
		$this->NetCommons->renderJson(['rooms' => $result]);
	}
}
