<?php
/**
 * ReservationExposeRoom Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

//プラグインセパレータ(.)とパスセバレータ(/)混在に注意
App::uses('ReservationAppBehavior', 'Reservations.Model/Behavior');

/**
 * ReservationExposeRoomBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model\Behavior
 */
class ReservationExposeRoomBehavior extends ReservationAppBehavior {

/**
 * getExposeRoomOptions
 *
 * 公開可能なルーム一覧をselectのoptions配列および自分自身のroomd_idを取得
 *
 * @param Model &$model 実際のモデル名
 * @param int $frameSetting フレーム設定情報
 * @return mixed 生成したoptions配列とmyself(自分自身のroom_id)とルーム毎空間名配列を返す
 */
	public function getExposeRoomOptions(Model &$model, $frameSetting) {
		//事前準備
		if (!(isset($this->Room))) {
			$model->loadModels(['Room' => 'Rooms.Room']);
		}

		$spaces = $model->Room->getSpaces();
		$spaceIds = array(Space::PUBLIC_SPACE_ID, Space::COMMUNITY_SPACE_ID);

		$rooms = array();
		$roomTreeList = array();
		foreach ($spaceIds as $spaceId) {
			$rooms[$spaceId] = $this->getRoomsOfSpace($model, $spaceId);
			$roomTreeList[$spaceId] = $this->getRoomTreeOfSpace(
				$model, $spaces[$spaceId]['Room']['id'], $rooms[$spaceId]);
		}

		//オプション生成
		$options = array();
		$spaceNameOfRooms = array();
		$allRoomNames = array();
		$myself = null;
		$userId = Current::read('User.id');
		foreach ($spaces as $space) {	//Space::PUBLIC_SPACE_ID, Space::COMMUNITY_SPACE_IDを順次処理.
			//$title = $model->Rooms->roomName($space);
			$roomsLanguage = Hash::extract($space, 'RoomsLanguage.{n}[language_id=' .
				Current::read('Language.id') . ']');
			$title = h($roomsLanguage[0]['name']);

			if ($space['Space']['id'] == Space::PRIVATE_SPACE_ID) {
				//プライベート
				list($myself, $options, $spaceNameOfRooms, $allRoomNames) =
					$this->__getRoomIdEtcWhenPrivateCase(
						$model, $space, $frameSetting, $userId, $title,
						$myself, $options, $spaceNameOfRooms, $allRoomNames);
			} else {	//公開空間またはグループ空間
				list($options, $spaceNameOfRooms, $allRoomNames) =
					$this->mergeSelectExposeTargetOptions(
						$model, $options, $title, $space, $roomTreeList[$space['Space']['id']],
						$rooms[$space['Space']['id']], $frameSetting, $spaceNameOfRooms, $allRoomNames);
			}
		}

		// 全会員
		//
		// 全会員が、指定したルームのみ表示ONの時表示ＯＫになっているか確認
		if (! empty($userId)) {
			//全会員を表すIDは↓これです。
			$roomId = Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID);
			$spaceNameOfRooms[$roomId] = 'member';	//例外的に文字列を渡す
			$allRoomNames[$roomId] = __d('reservations', 'All the members');
			//if ($this->_isEnableRoomInFrameSetting($roomId, $frameSetting)) {
				//ログインしている時、optionに積む
				$options[$roomId] = __d('reservations', 'All the members');
			//}
		}

		return array($options, $myself, $spaceNameOfRooms, $allRoomNames);
	}

/**
 * mergeSelectExposeTargetOptions
 *
 * 空間単位で、空間、ルーム、サブルーム要素を$otions配列にマージする
 *
 * @param Model &$model 実際のモデル名
 * @param array $options マージ元のoptions配列
 * @param string $title タイトル
 * @param array $space 空間配列
 * @param array $roomTreeList 単一空間でのルームツリー
 * @param array $rooms 単一空間でのルーム群
 * @param int $frameSetting 施設予約フレーム設定情報
 * @param array $spaceNameOfRooms ルーム毎空間名配列
 * @param array $allRoomNames ルーム名一覧
 * @return array マージ後のoptions配列とルーム毎空間名配列
 */
	public function mergeSelectExposeTargetOptions(Model &$model, $options, $title, $space,
		$roomTreeList, $rooms, $frameSetting, $spaceNameOfRooms, $allRoomNames) {
		$userId = Current::read('User.id');
		if ($roomTreeList) {
			foreach ($roomTreeList as $roomId => $tree) {
				if (Hash::get($rooms, $roomId)) {
					$nest = substr_count($tree, Room::$treeParser);
					$roomsLanguage = Hash::extract($rooms[$roomId],
						'RoomsLanguage.{n}[language_id=' . Current::read('Language.id') . ']');
					$targetTitle = h($roomsLanguage[0]['name']);

					$spaceNameOfRooms[$roomId] =
						($space['Space']['type'] == Space::COMMUNITY_SPACE_ID) ? 'group' : 'public';
					$allRoomNames[$roomId] = $targetTitle;

					//if ($this->_isEnableRoomInFrameSetting($roomId, $frameSetting)) {
						if ($space['Space']['type'] == Space::COMMUNITY_SPACE_ID) {
							if (empty($userId)) {
								//未ログインなので、グループ空間をoptionに積んではいけない。抜ける。
								continue;
							}
							//グループ空間の時は、インデントを１つ減らす。..これにより、NC2と同じレベルの表現になる。
							$nest -= 1;
						}
						$options[$roomId] = str_repeat('　', $nest * 1) . $targetTitle;
					//}
				}
			}
		}
		//CakeLog::debug("DBG: options[" . print_r($options, true) . "]");
		return array($options, $spaceNameOfRooms, $allRoomNames);
	}

/**
 * getRoomsOfSpace
 *
 * 指定空間のルーム群を取得する
 *
 * @param Model &$model 実際のモデル名
 * @param int $spaceId space id
 * @return array 取得されたルーム配列
 */
	public function getRoomsOfSpace(Model &$model, $spaceId) {
		//指定空間配下で読み取り可能なルーム群を取得し、(room_id => room情報配列)集合にして返す。
		$rooms = Hash::combine(
			($model->Room->find('all',
				$model->Room->getReadableRoomsConditions(array('Room.space_id' => $spaceId)))
			),
			'{n}.Room.id', '{n}'
		);
		return $rooms;
	}

/**
 * getRoomTreeOfSpace
 *
 * 指定空間にあるルーム群よりルームルームTreeリスト取得
 *
 * @param Model &$model 実際のモデル名
 * @param int $spaceRoomId room id which is space's
 * @param array $rooms room information
 * @return array 指定した
 */
	public function getRoomTreeOfSpace(Model &$model, $spaceRoomId, $rooms) {
		$roomTreeList = $model->Room->generateTreeList(
			array('Room.id' => array_merge(array($spaceRoomId), array_keys($rooms))
			), null, null, Room::$treeParser);
		return $roomTreeList;
	}

/**
 * getMyPrivateRoomId($model);
 *
 * ログイン者のプライベートルームIDを返す
 *
 * @param Model &$model 実際のモデル名
 * @return mixed 成功時、ルームIDを返す。失敗時はnullを返す。
 */
	public function getMyPrivateRoomId(&$model) {
		$userId = Current::read('User.id');
		if (!isset($model->Room)) {
			$model->loadModels(['Room' => 'Roomrs.Room']);
		}
		$readableRoomInfos = $model->Room->find('all', $model->Room->getReadableRoomsConditions());
		if (empty($userId)) {
			//未ログイン時
			return null;
		}

		//ログイン時
		$privateRoomId = Hash::extract($readableRoomInfos,
			'{n}.Room[space_id=' . Space::PRIVATE_SPACE_ID . '].id');
		$privateRoomId = array_shift($privateRoomId);	//privateRoomID取得
		return $privateRoomId;
	}

/**
 * __getRoomIdEtcWhenPrivateCase
 *
 * プライベート時、各種変数・配列を取得セット
 *
 * @param Model &$model 実際のモデル名
 * @param array $space space配列
 * @param array $frameSetting frameSetting配列
 * @param int $userId userId
 * @param string $title title
 * @param mixed $myself ログインしている人のプライベートルームID
 * @param array $options ルーム選択（兼、表示）時の選択できうるルームのoptions配列
 * @param array $spaceNameOfRooms ルーム毎の空間名を格納した配列
 * @param array $allRoomNames ルーム名一覧
 * @return array プライベート時の情報を、$myselfと$optionsと$spaceNameOfRoomsにセットし、配列に格納して返す。
 */
	private function __getRoomIdEtcWhenPrivateCase(&$model, $space, $frameSetting,
		$userId, $title, $myself, $options, $spaceNameOfRooms, $allRoomNames) {
		//プライベート
		//
		//プライベートが、指定したルームのみ表示ONの時表示ＯＫになっているか確認
		$roomId = $this->getMyPrivateRoomId($model);

		//プライベートルームＩＤが見つかった
		if ($roomId) {
			$spaceNameOfRooms[$roomId] = 'private';
			$allRoomNames[$roomId] = $this->__getPrivateRoomName($model, $roomId);
		}
		return array($myself, $options, $spaceNameOfRooms, $allRoomNames);
	}

/**
 * __getPrivateRoomName
 *
 * プライベートルーム名の取得
 *
 * @param Model &$model 実際のモデル名
 * @param string $myself プライベートルームのroom_id
 * @return array 取得されたプライベートルーム名
 */
	private function __getPrivateRoomName(&$model, $myself) {
		if (!isset($model->RoomsLanguage)) {
			$model->loadModels(['RoomsLanguage' => 'Rooms.RoomsLanguage']);
		}
		$roomsLang = $model->RoomsLanguage->findByRoomIdAndLanguageId(
			$myself, Current::read('Language.id'));
		if (empty($roomsLang['RoomsLanguage']['name'])) {
			return __d('reservations', 'Unknown My Room');
		}
		return $roomsLang['RoomsLanguage']['name'];
	}
}
