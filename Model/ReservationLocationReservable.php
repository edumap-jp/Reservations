<?php
/**
 * ReservationLocationReservable Model
 *
 * @property Room $Room
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppModel', 'Reservations.Model');

/**
 * Summary for ReservationLocationReservable Model
 */
class ReservationLocationReservable extends ReservationsAppModel {

/**
 * 予約できる権限のデフォルト値
 *
 * @var array
 */
	public static $defaultReservables = [
		'room_administrator' => 1,
		'chief_editor' => 1,
		'editor' => 1,
		'general_user' => 1,
		'visitor' => 0,
	];

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'reservation_location_reservable';

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
		'role_key' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
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
		'Room' => array(
			'className' => 'Room',
			'foreignKey' => 'room_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
