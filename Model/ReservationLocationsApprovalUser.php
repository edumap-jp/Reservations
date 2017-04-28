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
	public $validate = array(
		'location_key' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * 担当者ユーザを設定
 *
 * @param array $data ToDoデータ
 * @param bool $isMyUser 作成者ユーザー取得フラグ
 * @return array
 */
	public function getSelectUsers($data, $isMyUser) {
		$this->loadModels(['User' => 'Users.User']);

		if ($isMyUser) {
			$data[$this->alias][] = array('user_id' => Current::read('User.id'));
		}
		$selectUsers['selectUsers'] = array();
		if (isset($data[$this->alias])) {
			$selectUsers = Hash::extract($data[$this->alias], '{n}.user_id');
			foreach ($selectUsers as $userId) {
				$user = $this->User->getUser($userId);
				$data['selectUsers'][] = $user;
			}
		}
		return $data;
	}

}
