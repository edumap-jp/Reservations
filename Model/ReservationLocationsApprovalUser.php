<?php
/**
 * ReservationLocationsApprovalUser Model
 *
 * @property User $User
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppModel', 'Reservations.Model');

/**
 * Summary for ReservationLocationsApprovalUser Model
 */
class ReservationLocationsApprovalUser extends ReservationsAppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array();

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'Users.User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * 施設の承認者idリスト ReservationLocation.keyをキーにもつ配列
 *
 * @var array
 */
	protected $_approvalUserIds = [];

/**
 * Called during validation operations, before validation. Please note that custom
 * validation rules can be defined in $validate.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = array()) {
		$this->validate = ValidateMerge::merge($this->validate, array(
			'location_key' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'user_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
		));

		return parent::beforeValidate($options);
	}

/**
 * 施設管理者の保存
 *
 * #### $data のサンプル
 * array(
 *		'ReservationLocationsApprovalUser' => array(
 *			0 => ['user_id' => '1'],
 *			1 => ['user_id' => '2'],
 *		)
 * )
 *
 * @param string $locationKey location_key
 * @param array $data save data
 * @throws InternalErrorException
 * @return void
 */
	public function saveApprovalUser($locationKey, $data) {
		$userIds = array_keys(Hash::combine($data, 'ReservationLocationsApprovalUser.{n}.user_id'));

		$this->deleteAll(['location_key' => $locationKey]);
		foreach ($userIds as $userId) {
			$this->create();
			$userData = [
				'ReservationLocationsApprovalUser' => [
					'location_key' => $locationKey,
					'user_id' => $userId
				]
			];
			if (! $this->save($userData)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}
		return true;
	}

/**
 * 施設の承認者ID一覧を返す
 *
 * @param array $location ReservationLocation data
 * @return array 承認者ID一覧
 */
	public function getApprovalUserIdsByLocation($location) {
		$locationKey = $location['ReservationLocation']['key'];
		$useWorkflow = $location['ReservationLocation']['use_workflow'];

		if (!isset($this->_approvalUserIds[$locationKey])) {
			$approvalUserIds = [];
			if ($useWorkflow) {
				// 承認が必要なら承認ユーザ取得
				$condition = [
					'ReservationLocationsApprovalUser.location_key' =>
						$locationKey,
				];
				$approvalUsers = $this->find('all',
					['conditions' => $condition]);
				$approvalUserIds = Hash::combine($approvalUsers,
					'{n}.ReservationLocationsApprovalUser.user_id',
					'{n}.ReservationLocationsApprovalUser.user_id');
			}
			$this->_approvalUserIds[$locationKey] =
				$approvalUserIds;
		}
		return $this->_approvalUserIds[$locationKey];
	}

/**
 * findApprovalUserIdsByLocations
 *
 * @param array $locations 施設リスト
 * @return array
 */
	public function findApprovalUserIdsByLocations(array $locations) {
		// 承認が必要な施設のキーだけを抜き出す
		$locationKeys = [];
		foreach ($locations as $location) {
			$useWorkflow = $location['ReservationLocation']['use_workflow'];
			if ($useWorkflow) {
				$locationKey = $location['ReservationLocation']['key'];
				$locationKeys[] = $locationKey;
			}
		}
		//  取得済みならFINDしない
		$needFind = false;
		foreach ($locationKeys as $locationKey) {
			if (!isset($this->_approvalUserIds[$locationKey])) {
				$needFind = true;
				break;
			}
		}
		if ($needFind === false) {
			// 全部キャッシュ済みなら改めて取得しなおさない
			return $this->_approvalUserIds;
		}

		// Find
		$condition = [
			'ReservationLocationsApprovalUser.location_key' =>
				$locationKeys,
		];
		$approvalUsers = $this->find(
			'all',
			[
				'conditions' => $condition,
				'recursive' => -1,
				'fields' => [
					'ReservationLocationsApprovalUser.location_key',
					'ReservationLocationsApprovalUser.user_id'
				]
			]
		);
		// [location_key => [user_id,...],  ...] 形式にする
		$approvalUserIds = [];
		foreach ($approvalUsers as $approvalUser) {
			$locationKey = $approvalUser['ReservationLocationsApprovalUser']['location_key'];
			$userId = $approvalUser['ReservationLocationsApprovalUser']['user_id'];
			$approvalUserIds[$locationKey][] = $userId;
		}
		// _approvalUserIdsにmergeする
		$this->_approvalUserIds = array_merge(
			$this->_approvalUserIds,
			$approvalUserIds
		);
		return $this->_approvalUserIds;
	}

}
