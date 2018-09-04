<?php
/**
 * Reservation Model
 *
 * @property Block $Block
 * @property Room $Room
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppModel', 'Reservations.Model');
App::uses('BlockSettingBehavior', 'Blocks.Model/Behavior');

/**
 * Reservation Model
 *
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\Model
 */
class ReservationPermission extends ReservationsAppModel {

/**
 * Use table config
 *
 * @var bool
 */
	public $useTable = 'reservations';

/**
 * alias
 *
 * @var string
 */
	public $alias = 'Reservation';

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'Blocks.BlockRolePermission',
		'Blocks.BlockSetting' => array(
			BlockSettingBehavior::FIELD_USE_WORKFLOW,
		),
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Block' => array(
			'className' => 'Blocks.Block',
			'foreignKey' => 'block_key',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array();

/**
 * getReservationRoomBlocks
 *
 * 現在存在する全てのルームと、そこに配置されるべきブロック、施設予約を取り出す
 *
 * @param Component $workflow workflow component
 * @return array
 */
	public function getReservationRoomBlocks($workflow) {
		$this->Room = ClassRegistry::init('Rooms.Room', true);
		$spaceIds = array(Space::PUBLIC_SPACE_ID, Space::COMMUNITY_SPACE_ID);
		$rooms = array();

		// 空間ごとに処理
		foreach ($spaceIds as $spaceId) {

			// 読み取り可能なルームを取得
			$readableRoom = $this->Room->find('all',
				$this->Room->getReadableRoomsConditions(array('Room.space_id' => $spaceId)));
			$readableRoomIds = Hash::combine($readableRoom, '{n}.Room.id', '{n}.Room.id');
			$readableRoom = Hash::combine($readableRoom, '{n}.Room.id', '{n}');

			// 読み取り可能ルームIDをもとに条件取得
			$conditions = $this->_getReservationConditions($readableRoomIds);

			// ルーム+ブロック情報取得
			$roomsBlocks = $this->Room->find('all', $conditions);

			// 取得したルーム＋ブロック情報にさらにパーミッション情報を追加でセット
			$this->_setPermission($workflow, $roomsBlocks, $readableRoom);
			// 取得したルーム＋ブロック情報にさらにブロック設定情報を追加でセット
			$this->_setBlockSetting($roomsBlocks);
			$rooms[$spaceId] = Hash::combine($roomsBlocks, '{n}.Room.id', '{n}');
		}
		return $rooms;
	}
/**
 * getReservationAllMemberRoomBlocks
 *
 * 全会員ルームと、そこに配置されるべきブロック、施設予約を取り出す
 *
 * @param Component $workflow workflow component
 * @return array
 */
	public function getReservationAllMemberRoomBlocks($workflow) {
		// 読み取り可能なルームを取得
		$condition = $this->Room->getReadableRoomsConditions();
		$condition['conditions'] = array('Room.id' => Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID));
		$roomBase = $this->Room->find('all', $condition);
		$roomBase = Hash::combine($roomBase, '{n}.Room.id', '{n}');

		$conditions = $this->_getReservationConditions(
			array(Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID))
		);
		// ルーム+ブロック情報取得
		$roomsBlocks = $this->Room->find('all', $conditions);
		$roomsBlocks = Hash::combine($roomsBlocks, '{n}.Room.id', '{n}');

		// 取得したルーム＋ブロック情報にさらにパーミッション情報を追加でセット
		$this->_setPermission($workflow, $roomsBlocks, $roomBase);
		// 取得したルーム＋ブロック情報にさらにブロック設定情報を追加でセット
		$this->_setBlockSetting($roomsBlocks);
		return array(Space::COMMUNITY_SPACE_ID => $roomsBlocks);
	}
/**
 * _getReservationConditions
 *
 * 現在存在する全てのルームと、そこに配置されるべきブロック、施設予約を取り出すためのfindのoption作成
 *
 * @param array $readableRoomIds readable room id
 * @return array
 */
	protected function _getReservationConditions($readableRoomIds) {
		return array(
			'fields' => array(
				'Room.*',
				'RoomsLanguage.*',
				'Block.*',
				//'Reservation.*'
			),
			'recursive' => -1,
			'joins' => array(
				array('table' => 'rooms_languages',
					'alias' => 'RoomsLanguage',
					'type' => 'LEFT',
					'conditions' => array(
						'Room.id = RoomsLanguage.room_id',
						'RoomsLanguage.language_id' => Current::read('Language.id')
					)
				),
				array('table' => 'blocks',
					'alias' => 'Block',
					'type' => 'LEFT',
					'conditions' => array(
						'Room.id = Block.room_id',
						'Block.plugin_key' => 'reservations',
					)
				),
				//				array('table' => 'reservations',
				//					'alias' => 'Reservation',
				//					'type' => 'LEFT',
				//					'conditions' => array(
				//						'Reservation.block_key = Block.key',
				//					)
				//				)
			),
			'conditions' => array(
				'Room.id' => $readableRoomIds
			),
			'order' => array(
				'Room.sort_key asc'
			)
		);
	}
/**
 * _setPermission
 *
 * 指定されたルーム、ブロックに相当する権限設定情報を取り出す
 *
 * @param object $workflow workflow component
 * @param array &$roomBlocks ルーム、ブロック、情報
 * @param array $readableRoom アクセス可能ルームリスト（ルームでのRole情報が見られる
 * @return array
 */
	protected function _setPermission($workflow, &$roomBlocks, $readableRoom) {
		foreach ($roomBlocks as &$roomBlock) {
			$blockKey = $roomBlock['Block']['key'];
			if (! isset($roomBlock['Block']['key'])) {
				$blockKey = '';
			}
			$permissions = $workflow->getBlockRolePermissions(
				array(
					'content_creatable',
					'content_editable',
					'content_publishable',
					'block_permission_editable',
					'mail_editable'),
				$roomBlock['Room']['id'],
				$blockKey
			);
			if ($permissions) {
				$roomBlock['BlockRolePermission'] = $permissions['BlockRolePermissions'];
				//$roomBlock['Roles'] = $permissions['Roles'];
			}
			if (isset($readableRoom[$roomBlock['Room']['id']]['RolesRoom'])) {
				$roomBlock['RolesRoom'] = $readableRoom[$roomBlock['Room']['id']]['RolesRoom'];
			}
		}
	}
/**
 * _setBlockSetting
 *
 * 指定されたルーム、ブロックに相当するブロック設定情報を取り出す
 *
 * @param array &$roomBlocks ルーム、ブロック、情報
 * @return void
 */
	protected function _setBlockSetting(&$roomBlocks) {
		foreach ($roomBlocks as &$roomBlock) {
			$blockKey = Hash::get($roomBlock, 'Block.key');
			$roomId = Hash::get($roomBlock, 'Block.room_id');
			$blockSetting = $this->getBlockSetting($blockKey, $roomId);
			$roomBlock[$this->alias] = $blockSetting[$this->alias];
		}
	}
/**
 * getDefaultRoles
 *
 * デフォルトの権限を返す
 *
 * @return array
 */
	public function getDefaultRoles() {
		$this->DefaultRolePermission = ClassRegistry::init('Roles.DefaultRolePermission', true);
		$roles = $this->DefaultRolePermission->find('all', array(
			'fields' => array(
				'DefaultRolePermission.*',
				'Role.*',
			),
			'joins' => array(
				array('table' => 'roles',
					'alias' => 'Role',
					'type' => 'LEFT',
					'conditions' => array(
						'DefaultRolePermission.role_key = Role.key',
						'Role.language_id' => Current::read('Language.id')
					)
				),
			),
			'conditions' => array(
				'DefaultRolePermission.permission' => 'content_creatable',
				'OR' => array(
					array(
						'DefaultRolePermission.fixed' => false
					),
					array(
						'DefaultRolePermission.value' => true
					),
				)
			)
		));
		return $roles;
	}
/**
 * savePermission
 *
 * 権限設定を登録
 *
 * @param array $data 保存データ
 * @return bool
 * @throws InternalErrorException
 */
	public function savePermission($data) {
		$this->Block = ClassRegistry::init('Blocks.Block', true);

		//トランザクションBegin
		$this->begin();

		try {
			foreach ($data as $spaceId => $rooms) {
				if (! is_numeric($spaceId)) {
					continue;
				}
				foreach ($rooms as $roomId => $room) {
					// Reservation.idが空っぽ
					//if (empty($room['Reservation']['id'])) {
						// その場合はブロック未作成なので前もってブロック&Reservation作る
						$block = $this->saveBlock($roomId);
						// そのブロックキーを設定して
						foreach ($room['BlockRolePermission']['content_creatable'] as &$perm) {
							$perm['block_key'] = $block['Block']['key'];
						}
						$room['Reservation']['block_key'] = $block['Block']['key'];
					//}
					// 保存する
					$this->create();
					$this->set($room);
					if (! $this->validates()) {
						$this->rollback();
						return false;
					}

					// rooom_idを指定してBlockSettingを保存
					$this->saveBlockSetting($block['Block']['key'], $block['Block']['room_id']);

					$this->Behaviors->disable('Blocks.BlockSetting');
					if (! $this->save($room, false)) {
						throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
					}
					$this->Behaviors->enable('Blocks.BlockSetting');

				}
			}
			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
			return false;
		}
		return true;
	}
/**
 * saveBlock
 *
 * ブロックを登録（すでにある場合は取得）
 *
 * @param int $roomId ルームID
 * @return array
 */
	public function saveBlock($roomId) {
		$block = $this->Block->find('first', array(
			'conditions' => array(
				'room_id' => $roomId,
				'plugin_key' => 'reservations',
			),
			'recursive' => -1
		));
		if ($block) {
			return $block;
		}
		$this->Block->create();
		$block = $this->Block->save(array(
			'room_id' => $roomId,
			'plugin_key' => 'reservations',
		));
		return $block;
	}
}
